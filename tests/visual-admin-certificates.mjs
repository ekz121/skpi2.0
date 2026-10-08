import { writeFile } from 'node:fs/promises';

const endpoint = 'http://127.0.0.1:9222';
const adminPassword = process.env.VISUAL_ADMIN_PASSWORD;
if (!adminPassword) throw new Error('VISUAL_ADMIN_PASSWORD is required.');
const target = await fetch(`${endpoint}/json/new?${encodeURIComponent('http://127.0.0.1:8000/masuk')}`, { method: 'PUT' }).then((response) => response.json());
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
        const task = pending.get(message.id);
        pending.delete(message.id);
        message.error ? task.reject(new Error(message.error.message)) : task.resolve(message.result);
        return;
    }
    if (message.method === 'Runtime.exceptionThrown' || message.method === 'Log.entryAdded') issues.push(message.params);
    const callbacks = listeners.get(message.method) || [];
    callbacks.splice(0).forEach((callback) => callback(message.params));
});

const send = (method, params = {}) => new Promise((resolve, reject) => {
    const id = ++sequence;
    pending.set(id, { resolve, reject });
    socket.send(JSON.stringify({ id, method, params }));
});
const waitFor = (method) => new Promise((resolve) => {
    const callbacks = listeners.get(method) || [];
    callbacks.push(resolve);
    listeners.set(method, callbacks);
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
const screenshot = async (path) => {
    const result = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, fromSurface: true });
    await writeFile(path, Buffer.from(result.data, 'base64'));
};

await Promise.all([send('Page.enable'), send('Runtime.enable'), send('Log.enable'), send('Network.enable')]);
await send('Network.clearBrowserCookies');
await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
await navigate('http://127.0.0.1:8000/masuk');
const loginDesktop = await evaluate(`(() => {
    const image = document.querySelector('.auth-logo-card img');
    const box = image.getBoundingClientRect();
    return { width: innerWidth, scrollWidth: document.documentElement.scrollWidth, logoLoaded: image.complete && image.naturalWidth > 0, naturalRatio: Number((image.naturalWidth / image.naturalHeight).toFixed(3)), renderedRatio: Number((box.width / box.height).toFixed(3)), objectFit: getComputedStyle(image).objectFit, autoFillButtons: document.querySelectorAll('[data-fill-login]').length };
})()`);
await screenshot('storage/app/visual-login-refined.png');

await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
await send('Page.reload', { ignoreCache: true });
await waitFor('Page.loadEventFired');
const loginMobile = await evaluate(`({ width: innerWidth, scrollWidth: document.documentElement.scrollWidth, formVisible: Boolean(document.querySelector('form')?.getBoundingClientRect().height) })`);
await screenshot('storage/app/visual-login-refined-mobile.png');

await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
const loggedIn = waitFor('Page.loadEventFired');
await evaluate(`(() => { document.querySelector('[name="email"]').value = 'admin@polteksi.ac.id'; document.querySelector('[name="password"]').value = ${JSON.stringify(adminPassword)}; document.querySelector('form').requestSubmit(); })()`);
await loggedIn;
const dashboard = await evaluate(`({ path: location.pathname, width: innerWidth, scrollWidth: document.documentElement.scrollWidth, certificateNav: Boolean(document.querySelector('a[href*="/admin/sertifikat"]')), metricCards: document.querySelectorAll('.metric-card').length })`);
await screenshot('storage/app/visual-admin-dashboard-certificates.png');

await navigate('http://127.0.0.1:8000/admin/sertifikat?review=new');
const inbox = await evaluate(`({ path: location.pathname, width: innerWidth, scrollWidth: document.documentElement.scrollWidth, filters: document.querySelectorAll('.admin-filter input, .admin-filter select').length, rows: document.querySelectorAll('tbody tr').length, newLabels: [...document.querySelectorAll('.status-pending')].filter((item) => item.textContent.trim() === 'Baru').length, createLink: Boolean(document.querySelector('a[href$="/admin/sertifikat/baru"]')) })`);
await screenshot('storage/app/visual-admin-certificate-inbox.png');
const darkTheme = await evaluate(`(() => { document.querySelector('[data-theme-toggle]').click(); return { theme: document.documentElement.dataset.theme, background: getComputedStyle(document.body).backgroundColor, scrollWidth: document.documentElement.scrollWidth }; })()`);
await screenshot('storage/app/visual-admin-certificate-inbox-dark.png');
await evaluate(`document.querySelector('[data-theme-toggle]').click()`);

const firstDetail = await evaluate(`document.querySelector('tbody a[href*="/admin/sertifikat/"]')?.href`);
let detail = { available: false };
let edit = { available: false };
if (firstDetail) {
    await navigate(firstDetail);
    detail = await evaluate(`({ available: true, checkButton: [...document.querySelectorAll('button')].some((button) => button.textContent.includes('Tandai sudah dicek')), editLink: Boolean(document.querySelector('a[href$="/ubah"]')), deleteButton: [...document.querySelectorAll('button')].some((button) => button.textContent.includes('Hapus sertifikat')), decisionSelects: document.querySelectorAll('[data-decision-select]').length })`);
    await screenshot('storage/app/visual-admin-certificate-detail.png');
    const editUrl = await evaluate(`document.querySelector('a[href$="/ubah"]')?.href`);
    if (editUrl) {
        await navigate(editUrl);
        edit = await evaluate(`({ available: true, fields: document.querySelectorAll('input, select').length, fileOptional: !document.querySelector('[name="evidence"]').required })`);
        await screenshot('storage/app/visual-admin-certificate-edit.png');
    }
}

await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
await navigate('http://127.0.0.1:8000/admin/sertifikat?review=new');
const mobileInbox = await evaluate(`({ width: innerWidth, scrollWidth: document.documentElement.scrollWidth, menuVisible: getComputedStyle(document.querySelector('[data-sidebar-open]')).display !== 'none' })`);
await screenshot('storage/app/visual-admin-certificate-mobile.png');

console.log(JSON.stringify({ loginDesktop, loginMobile, dashboard, inbox, darkTheme, detail, edit, mobileInbox, issues: issues.length }, null, 2));
socket.close();
