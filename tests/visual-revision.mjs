import { writeFile } from 'node:fs/promises';

const endpoint = 'http://127.0.0.1:9222';
const adminPassword = process.env.VISUAL_ADMIN_PASSWORD;
if (!adminPassword) throw new Error('VISUAL_ADMIN_PASSWORD is required.');

const target = await fetch(`${endpoint}/json/new?${encodeURIComponent('http://127.0.0.1:8000/daftar')}`, { method: 'PUT' }).then((response) => response.json());
const socket = new WebSocket(target.webSocketDebuggerUrl);
const pending = new Map();
const listeners = new Map();
const issues = [];
let sequence = 0;

await new Promise((resolve, reject) => {
    socket.addEventListener('open', resolve, { once: true });
    socket.addEventListener('error', reject, { once: true });
});

socket.addEventListener('message', (event) => {
    const message = JSON.parse(event.data);
    if (message.id && pending.has(message.id)) {
        const callback = pending.get(message.id);
        pending.delete(message.id);
        message.error ? callback.reject(new Error(message.error.message)) : callback.resolve(message.result);
        return;
    }
    if (message.method === 'Runtime.exceptionThrown' || message.method === 'Log.entryAdded') issues.push(message.params);
    const eventListeners = listeners.get(message.method) || [];
    eventListeners.splice(0).forEach((resolve) => resolve(message.params));
});

const send = (method, params = {}) => new Promise((resolve, reject) => {
    const id = ++sequence;
    pending.set(id, { resolve, reject });
    socket.send(JSON.stringify({ id, method, params }));
});
const waitFor = (method) => new Promise((resolve) => {
    const eventListeners = listeners.get(method) || [];
    eventListeners.push(resolve);
    listeners.set(method, eventListeners);
});
const evaluate = async (expression) => {
    const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
    return result.result.value;
};
const navigate = async (url) => {
    const loaded = waitFor('Page.loadEventFired');
    await send('Page.navigate', { url });
    await loaded;
};
const screenshot = async (name) => {
    const result = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false, fromSurface: true });
    await writeFile(`storage/app/${name}`, Buffer.from(result.data, 'base64'));
};

await Promise.all([send('Page.enable'), send('Runtime.enable'), send('Log.enable'), send('Network.enable')]);
await send('Network.clearBrowserCookies');
await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
await navigate('http://127.0.0.1:8000/daftar');

const registration = await evaluate(`(() => {
    const search = document.querySelector('[data-student-search]');
    const list = document.querySelector('[data-student-options]');
    const visibleNames = () => [...document.querySelectorAll('[data-student-option]:not([hidden])')].map((option) => option.dataset.name);
    search.focus();
    const initial = visibleNames();
    const listRect = list.getBoundingClientRect();
    search.value = 'ahmad';
    search.dispatchEvent(new Event('input', { bubbles: true }));
    const lower = visibleNames();
    search.value = 'AHMAD';
    search.dispatchEvent(new Event('input', { bubbles: true }));
    const upper = visibleNames();
    const unavailable = document.querySelector('[data-student-option][data-available="false"]');
    search.value = unavailable?.dataset.name || '';
    search.dispatchEvent(new Event('input', { bubbles: true }));
    unavailable?.click();
    const accountStatus = document.querySelector('[data-student-account-status]');
    return {
        total: document.querySelectorAll('[data-student-option]').length,
        initial: initial.length,
        caseInsensitive: JSON.stringify(lower) === JSON.stringify(upper),
        matches: upper.length,
        linkedStudentVisible: Boolean(unavailable),
        linkedStudentBlocked: Boolean(unavailable) && !document.querySelector('[data-student-registry-id]').value,
        linkedStudentMessageVisible: Boolean(accountStatus) && !accountStatus.hidden,
        listVisible: !list.hidden,
        withinViewport: listRect.left >= 0 && listRect.right <= innerWidth,
        overflow: document.documentElement.scrollWidth - innerWidth,
    };
})()`);
await screenshot('visual-revision-register-mobile.png');

await navigate('http://127.0.0.1:8000/masuk');
let loaded = waitFor('Page.loadEventFired');
await evaluate(`(() => {
    document.querySelector('[name="email"]').value = 'demo01@demo.polteksi.ac.id';
    document.querySelector('[name="password"]').value = 'demo12345';
    document.querySelector('form').requestSubmit();
})()`);
await loaded;
await navigate('http://127.0.0.1:8000/mahasiswa/profil');
loaded = waitFor('Page.loadEventFired');
await evaluate(`(() => {
    document.querySelector('[name="birthplace"]').value = 'Surabaya';
    document.querySelector('[name="birthdate"]').value = '2004-01-01';
    document.querySelector('form[action$="/profil"]').requestSubmit();
})()`);
await loaded;

const notification = await evaluate(`(() => {
    const notice = document.querySelector('[data-notice]');
    const icon = notice.querySelector('.notice-icon').getBoundingClientRect();
    const content = notice.querySelector('.notice-content').getBoundingClientRect();
    const close = notice.querySelector('.notice-close').getBoundingClientRect();
    const noticeRect = notice.getBoundingClientRect();
    return {
        display: getComputedStyle(notice).display,
        titleAboveMessage: notice.querySelector('strong').getBoundingClientRect().bottom <= notice.querySelector('p').getBoundingClientRect().top + 1,
        aligned: Math.abs(icon.top - content.top) <= 3 && Math.abs(icon.top - close.top) <= 3,
        closeSize: [close.width, close.height],
        inside: close.right <= noticeRect.right && close.left >= noticeRect.left,
        overflow: document.documentElement.scrollWidth - innerWidth,
    };
})()`);
await screenshot('visual-revision-notification-mobile.png');

loaded = waitFor('Page.loadEventFired');
await evaluate(`document.querySelector('form[action$="/keluar"]').requestSubmit()`);
await loaded;
loaded = waitFor('Page.loadEventFired');
await evaluate(`(() => {
    document.querySelector('[name="email"]').value = 'admin@polteksi.ac.id';
    document.querySelector('[name="password"]').value = ${JSON.stringify(adminPassword)};
    document.querySelector('form').requestSubmit();
})()`);
await loaded;

const userPages = [];
for (const width of [320, 768, 1440]) {
    await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 600 });
    await navigate('http://127.0.0.1:8000/admin/pengguna');
    userPages.push(await evaluate(`({
        width: innerWidth,
        overflow: document.documentElement.scrollWidth - innerWidth,
        rows: document.querySelectorAll('.user-table tbody tr').length,
        addVisible: document.body.innerText.includes('Tambah user') || document.body.innerText.includes('Tambah mahasiswa'),
        importVisible: document.body.innerText.includes('Impor user') || document.body.innerText.includes('Impor mahasiswa'),
    })`));
    if (width === 320) await screenshot('visual-revision-users-mobile.png');
}

const firstUser = await evaluate(`document.querySelector('.user-table tbody a')?.href`);
await send('Emulation.setDeviceMetricsOverride', { width: 320, height: 900, deviceScaleFactor: 1, mobile: true });
await navigate(firstUser);
const userDetail = await evaluate(`(() => {
    const stack = document.querySelector('.admin-action-stack');
    const buttons = [...stack.querySelectorAll('.button')].map((button) => button.getBoundingClientRect());
    return {
        overflow: document.documentElement.scrollWidth - innerWidth,
        stackGap: getComputedStyle(stack).gap,
        buttonWidths: buttons.map((button) => button.width),
        separated: buttons.length < 2 || buttons[1].top > buttons[0].bottom,
    };
})()`);
await screenshot('visual-revision-user-detail-mobile.png');

console.log(JSON.stringify({ registration, notification, userPages, userDetail, issues: issues.length }, null, 2));
socket.close();
