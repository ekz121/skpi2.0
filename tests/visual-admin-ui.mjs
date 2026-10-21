import { writeFile } from 'node:fs/promises';

const endpoint = 'http://127.0.0.1:9222';
const adminPassword = process.env.VISUAL_ADMIN_PASSWORD;
if (!adminPassword) throw new Error('VISUAL_ADMIN_PASSWORD is required.');

const target = await fetch(`${endpoint}/json/new?${encodeURIComponent('http://127.0.0.1:8000/masuk')}`, { method: 'PUT' }).then((response) => response.json());
const socket = new WebSocket(target.webSocketDebuggerUrl);
const pending = new Map();
const listeners = new Map();
const browserIssues = [];
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
    if (message.method === 'Runtime.exceptionThrown' || message.method === 'Log.entryAdded') browserIssues.push(message.params);
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

await Promise.all([send('Page.enable'), send('Runtime.enable'), send('Log.enable')]);
await send('Network.clearBrowserCookies');
await send('Emulation.setDeviceMetricsOverride', { width: 320, height: 900, deviceScaleFactor: 1, mobile: true });
await navigate('http://127.0.0.1:8000/masuk');
await evaluate(`(() => {
    document.querySelector('[name="email"]').value = 'admin@polteksi.ac.id';
    document.querySelector('[name="password"]').value = ${JSON.stringify(adminPassword)};
    document.querySelector('form').requestSubmit();
})()`);
await new Promise((resolve) => setTimeout(resolve, 1500));
const loginState = await evaluate(`({ path: location.pathname, notice: document.querySelector('.notice')?.innerText })`);
if (loginState.path === '/masuk') throw new Error(`Login admin gagal: ${loginState.notice || 'tidak ada pesan'}`);

const checks = [];
for (const theme of ['light', 'dark']) {
    await evaluate(`localStorage.setItem('skem-theme', ${JSON.stringify(theme)})`);
    for (const width of [320, 768, 1440]) {
        await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 600 });
        for (const path of ['/admin/pengguna', '/admin/sertifikat', '/admin/mahasiswa', '/admin/skpi']) {
            await navigate(`http://127.0.0.1:8000${path}`);
            checks.push(await evaluate(`(() => {
                const interactive = [...document.querySelectorAll('a, button:not([disabled]), input:not([disabled]), select:not([disabled])')].filter((item) => item.getClientRects().length);
                const importantTargets = interactive.filter((item) => item.closest('.filter-panel, .pager, .admin-data-table, .bulk-toolbar'));
                const unfocusable = importantTargets.filter((item) => { item.focus(); return document.activeElement !== item; });
                return {
                    path: location.pathname,
                    theme: document.documentElement.dataset.theme,
                    width: innerWidth,
                    title: document.querySelector('h1')?.textContent.trim(),
                    overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth),
                    serverError: document.body.innerText.includes('Server Error') || document.body.innerText.includes('Whoops'),
                    controlsReachable: unfocusable.length === 0,
                    unfocusable: unfocusable.map((item) => item.outerHTML.slice(0, 120)),
                    undersizedTargets: importantTargets.filter((item) => {
                        const box = item.getBoundingClientRect();
                        return box.width < 44 || box.height < 44;
                    }).length,
                    tableRowsFit: [...document.querySelectorAll('.admin-data-table tbody tr')].every((row) => row.getBoundingClientRect().right <= innerWidth),
                };
            })()`));
        }
    }
}

await send('Emulation.setDeviceMetricsOverride', { width: 320, height: 900, deviceScaleFactor: 1, mobile: true });
await navigate('http://127.0.0.1:8000/admin/mahasiswa');
const pagerBefore = await evaluate(`({
    present: Boolean(document.querySelector('.pager')),
    label: document.querySelector('.pager-mobile-position')?.textContent.trim(),
    nextHeight: document.querySelector('.pager a[rel="next"]')?.getBoundingClientRect().height,
})`);
if (pagerBefore.present) {
    await evaluate(`document.querySelector('.pager').scrollIntoView({ block: 'center' })`);
    await new Promise((resolve) => setTimeout(resolve, 100));
    await screenshot('visual-admin-pagination-mobile.png');
    await evaluate(`document.querySelector('.pager a[rel="next"]').click()`);
    await new Promise((resolve) => setTimeout(resolve, 1000));
}
const pagerAfter = await evaluate(`({ url: location.href, current: document.querySelector('[aria-current="page"]')?.textContent.trim() })`);

await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false });
await navigate('http://127.0.0.1:8000/admin/pengguna');
await screenshot('visual-admin-users-desktop.png');
await send('Emulation.setDeviceMetricsOverride', { width: 320, height: 900, deviceScaleFactor: 1, mobile: true });
await navigate('http://127.0.0.1:8000/admin/sertifikat');
await evaluate(`document.querySelector('.panel-table')?.scrollIntoView()`);
await screenshot('visual-admin-certificates-mobile.png');

console.log(JSON.stringify({
    pages: checks.length,
    maxOverflow: Math.max(...checks.map((item) => item.overflow)),
    serverErrors: checks.filter((item) => item.serverError).length,
    keyboardFailures: checks.filter((item) => !item.controlsReachable).length,
    undersizedTargets: checks.reduce((total, item) => total + item.undersizedTargets, 0),
    tableFitFailures: checks.filter((item) => !item.tableRowsFit).length,
    keyboardFailureDetails: checks.filter((item) => !item.controlsReachable).map((item) => ({ path: item.path, width: item.width, theme: item.theme, unfocusable: item.unfocusable })),
    pagerBefore,
    pagerAfter,
    browserIssues: browserIssues.length,
}, null, 2));
socket.close();
