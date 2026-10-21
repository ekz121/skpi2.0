import { writeFile } from 'node:fs/promises';

const endpoint = 'http://127.0.0.1:9222';
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

await Promise.all([send('Page.enable'), send('Runtime.enable'), send('Log.enable'), send('Network.enable')]);
await send('Network.clearBrowserCookies');
await navigate('http://127.0.0.1:8000/masuk');
let loaded = waitFor('Page.loadEventFired');
await evaluate(`(() => {
    document.querySelector('[name="email"]').value = 'demo01@demo.polteksi.ac.id';
    document.querySelector('[name="password"]').value = 'demo12345';
    document.querySelector('form').requestSubmit();
})()`);
await loaded;

const layouts = [];
for (const width of [320, 390, 768, 1366]) {
    await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 600 });
    await navigate('http://127.0.0.1:8000/mahasiswa/pengajuan/baru');
    layouts.push(await evaluate(`({
        width: innerWidth,
        categories: document.querySelectorAll('.category-option').length,
        overflow: document.documentElement.scrollWidth - innerWidth,
        smallestTapHeight: Math.min(...[...document.querySelectorAll('.category-option')].map((item) => item.getBoundingClientRect().height)),
    })`));
}

await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 900, deviceScaleFactor: 1, mobile: true });
await navigate('http://127.0.0.1:8000/mahasiswa/pengajuan/baru');
const internship = await evaluate(`(() => {
    const card = [...document.querySelectorAll('.category-option')].find((item) => item.innerText.includes('Sertifikat Magang'));
    card.querySelector('input').click();
    document.querySelector('[data-step-next]').click();
    return {
        fixedVisible: !document.querySelector('[data-fixed-rule]').hidden,
        fixedText: document.querySelector('[data-fixed-rule-output]').textContent.trim(),
        selectHidden: document.querySelector('[data-rule-field]').hidden,
        estimateVisible: !document.querySelector('[data-estimate]').hidden,
        overflow: document.documentElement.scrollWidth - innerWidth,
    };
})()`);
await screenshot('visual-special-internship-mobile.png');

await navigate('http://127.0.0.1:8000/mahasiswa/pengajuan/baru');
const custom = await evaluate(`(() => {
    const card = [...document.querySelectorAll('.category-option')].find((item) => item.innerText.includes('Sertifikat Lain-lain'));
    card.querySelector('input').click();
    document.querySelector('[data-step-next]').click();
    const input = document.querySelector('[data-custom-activity-type]');
    return {
        customVisible: !document.querySelector('[data-custom-rule-field]').hidden,
        customRequired: input.required,
        customEnabled: !input.disabled,
        selectHidden: document.querySelector('[data-rule-field]').hidden,
        estimateHidden: document.querySelector('[data-estimate]').hidden,
        overflow: document.documentElement.scrollWidth - innerWidth,
    };
})()`);
await screenshot('visual-special-custom-mobile.png');

console.log(JSON.stringify({ layouts, internship, custom, issues: issues.length }, null, 2));
socket.close();
