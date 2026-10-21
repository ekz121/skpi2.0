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
        const callback = pending.get(message.id);
        pending.delete(message.id);
        message.error ? callback.reject(new Error(message.error.message)) : callback.resolve(message.result);
        return;
    }
    if (message.method === 'Runtime.exceptionThrown' || message.method === 'Log.entryAdded') issues.push(message.params);
    (listeners.get(message.method) || []).splice(0).forEach((resolve) => resolve(message.params));
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
const screenshot = async (name) => {
    const result = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false, fromSurface: true });
    await writeFile(`storage/app/${name}`, Buffer.from(result.data, 'base64'));
};
const login = async (email, password) => {
    await navigate('http://127.0.0.1:8000/masuk');
    const loaded = waitFor('Page.loadEventFired');
    await evaluate(`(() => {
        document.querySelector('[name="email"]').value = ${JSON.stringify(email)};
        document.querySelector('[name="password"]').value = ${JSON.stringify(password)};
        document.querySelector('form').requestSubmit();
    })()`);
    await loaded;
};

await Promise.all([send('Page.enable'), send('Runtime.enable'), send('Log.enable'), send('Network.enable')]);
await send('Network.clearBrowserCookies');
await send('Emulation.setDeviceMetricsOverride', { width: 320, height: 900, deviceScaleFactor: 1, mobile: true });
await login('admin@polteksi.ac.id', adminPassword);

const adminRoutes = ['/admin/dashboard', '/admin/mahasiswa', '/admin/pengguna', '/admin/sertifikat', '/admin/skpi'];
const routeChecks = [];
for (const theme of ['light', 'dark']) {
    await evaluate(`localStorage.setItem('skem-theme', ${JSON.stringify(theme)})`);
    for (const width of [320, 768, 1440]) {
        await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 600 });
        for (const path of adminRoutes) {
            await navigate(`http://127.0.0.1:8000${path}`);
            routeChecks.push(await evaluate(`({
                theme: document.documentElement.dataset.theme,
                width: innerWidth,
                path: location.pathname,
                title: document.querySelector('h1')?.textContent.trim(),
                overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth),
                hasServerError: document.body.innerText.includes('Server Error') || document.body.innerText.includes('Whoops'),
            })`));
        }
    }
}

await send('Emulation.setDeviceMetricsOverride', { width: 320, height: 900, deviceScaleFactor: 1, mobile: true });
await navigate('http://127.0.0.1:8000/admin/mahasiswa');
const directory = await evaluate(`(() => {
    const menu = document.querySelector('[data-sidebar-open]');
    menu.click();
    const opened = document.body.classList.contains('sidebar-open');
    document.querySelector('[data-sidebar-close]').click();
    const closed = !document.body.classList.contains('sidebar-open');
    const metricValues = [...document.querySelectorAll('.student-directory-metrics strong')].map((item) => item.textContent.trim());
    return {
        opened,
        closed,
        metricValues,
        cards: document.querySelectorAll('.directory-table tbody tr').length,
        rowsFit: [...document.querySelectorAll('.directory-table tbody tr')].every((row) => row.getBoundingClientRect().right <= innerWidth),
        overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth),
    };
})()`);
await screenshot('visual-production-admin-students-mobile.png');

const loadedSearch = waitFor('Page.loadEventFired');
await evaluate(`(() => {
    document.querySelector('[name="q"]').value = 'FADHILATUL MUKMINAH';
    document.querySelector('.admin-filter').requestSubmit();
})()`);
await loadedSearch;
const directorySearch = await evaluate(`({ found: document.body.innerText.includes('FADHILATUL MUKMINAH'), overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth) })`);

await navigate('http://127.0.0.1:8000/admin/dashboard');
const mailHealth = await evaluate(`({
    text: document.querySelector('.mail-health-panel')?.innerText,
    buttonPresent: Boolean(document.querySelector('form[action$="/email-uji"] button')),
    overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth),
})`);

let loaded = waitFor('Page.loadEventFired');
await evaluate(`document.querySelector('form[action$="/keluar"]').requestSubmit()`);
await loaded;
await login('demo01@demo.polteksi.ac.id', 'demo12345');

const studentRoutes = ['/mahasiswa/dashboard', '/mahasiswa/profil', '/mahasiswa/pengajuan', '/mahasiswa/pengajuan/baru', '/mahasiswa/rekap-poin', '/mahasiswa/panduan', '/mahasiswa/skpi'];
const studentChecks = [];
for (const theme of ['light', 'dark']) {
    await evaluate(`localStorage.setItem('skem-theme', ${JSON.stringify(theme)})`);
    for (const width of [320, 768, 1440]) {
        await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 600 });
        for (const path of studentRoutes) {
            await navigate(`http://127.0.0.1:8000${path}`);
            studentChecks.push(await evaluate(`({
                theme: document.documentElement.dataset.theme,
                width: innerWidth,
                path: location.pathname,
                title: document.querySelector('h1')?.textContent.trim(),
                overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth),
                hasServerError: document.body.innerText.includes('Server Error') || document.body.innerText.includes('Whoops'),
            })`));
        }
    }
}

await send('Emulation.setDeviceMetricsOverride', { width: 320, height: 900, deviceScaleFactor: 1, mobile: true });
await navigate('http://127.0.0.1:8000/verifikasi-email');
const verification = await evaluate(`(() => {
    const details = document.querySelector('.email-correction');
    details.querySelector('summary').click();
    const input = details.querySelector('[name="email"]');
    return {
        opened: details.open,
        email: input.value,
        tapHeight: details.querySelector('summary').getBoundingClientRect().height,
        overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth),
    };
})()`);
await screenshot('visual-production-verification-mobile.png');

loaded = waitFor('Page.loadEventFired');
await evaluate(`document.querySelector('form[action$="/keluar"]').requestSubmit()`);
await loaded;
await navigate('http://127.0.0.1:8000/masuk');
const passwordToggle = await evaluate(`(() => {
    const input = document.querySelector('[name="password"]');
    const toggle = document.querySelector('[data-password-toggle]');
    const before = input.type;
    toggle.click();
    const after = input.type;
    return { before, after, tapWidth: toggle.getBoundingClientRect().width, tapHeight: toggle.getBoundingClientRect().height };
})()`);

console.log(JSON.stringify({
    admin: {
        pages: routeChecks.length,
        maxOverflow: Math.max(...routeChecks.map((item) => item.overflow)),
        serverErrors: routeChecks.filter((item) => item.hasServerError).length,
        themes: [...new Set(routeChecks.map((item) => item.theme))],
    },
    student: {
        pages: studentChecks.length,
        maxOverflow: Math.max(...studentChecks.map((item) => item.overflow)),
        serverErrors: studentChecks.filter((item) => item.hasServerError).length,
        themes: [...new Set(studentChecks.map((item) => item.theme))],
    },
    directory,
    directorySearch,
    mailHealth,
    verification,
    passwordToggle,
    browserIssues: issues.length,
}, null, 2));
socket.close();
