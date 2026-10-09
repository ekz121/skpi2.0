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

const ready = new Promise((resolve, reject) => {
    socket.addEventListener('open', resolve, { once: true });
    socket.addEventListener('error', reject, { once: true });
});

socket.addEventListener('message', (event) => {
    const message = JSON.parse(event.data);
    if (message.id && pending.has(message.id)) {
        const { resolve, reject } = pending.get(message.id);
        pending.delete(message.id);
        message.error ? reject(new Error(message.error.message)) : resolve(message.result);
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
const screenshot = async (path) => {
    const result = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, fromSurface: true });
    await writeFile(path, Buffer.from(result.data, 'base64'));
};

await ready;
await Promise.all([send('Page.enable'), send('Runtime.enable'), send('Log.enable'), send('Network.enable')]);
await send('Network.clearBrowserCookies');
const initialNavigation = waitFor('Page.loadEventFired');
await send('Page.navigate', { url: 'http://127.0.0.1:8000/masuk' });
await initialNavigation;

const loginLoaded = await evaluate(`Boolean(document.querySelector('form') && document.querySelector('[name="email"]'))`);
if (!loginLoaded) throw new Error('Login page did not load.');
const loginControls = await evaluate(`({ autoFillButtons: document.querySelectorAll('[data-fill-login]').length, logoLoaded: document.querySelector('.brand-logo-card img')?.complete })`);
await evaluate(`(() => { document.documentElement.dataset.theme = 'light'; localStorage.setItem('skem-theme', 'light'); })()`);
await screenshot('storage/app/visual-login-desktop.png');

await send('Emulation.setDeviceMetricsOverride', { width: 1366, height: 900, deviceScaleFactor: 1, mobile: false });
let pageNavigation = waitFor('Page.loadEventFired');
await send('Page.navigate', { url: 'http://127.0.0.1:8000/daftar' });
await pageNavigation;
const registrationDesktop = await evaluate(`(() => {
    const search = document.querySelector('[data-student-search]');
    const options = [...document.querySelectorAll('[data-student-option]')];
    const searchFor = (query) => {
        search.value = query;
        search.dispatchEvent(new Event('input', { bubbles: true }));
        return options.filter((option) => !option.hidden).map((option) => option.dataset.name);
    };
    const lowerResults = searchFor('damar');
    const upperResults = searchFor('DAMAR');
    search.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowDown', bubbles: true }));
    search.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
    const password = document.querySelector('[data-password-input]');
    const toggle = document.querySelector('[data-password-toggle]');
    toggle.click();
    const logo = document.querySelector('.brand-logo-card img');
    return {
        choices: options.length,
        suggestions: lowerResults.length,
        caseInsensitive: JSON.stringify(lowerResults) === JSON.stringify(upperResults),
        selectedId: document.querySelector('[data-student-registry-id]').value,
        nim: document.querySelector('[data-registry-nim]').textContent.trim(),
        program: document.querySelector('[data-registry-program]').textContent.trim(),
        cohort: document.querySelector('[data-registry-cohort]').textContent.trim(),
        passwordVisible: password.type === 'text',
        manualNimInput: Boolean(document.querySelector('[name="nim"]')),
        scrollWidth: document.documentElement.scrollWidth,
        width: innerWidth,
        logo: { loaded: logo.complete && logo.naturalWidth > 0, fit: getComputedStyle(logo).objectFit },
    };
})()`);
await screenshot('storage/app/visual-registration-desktop.png');

await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
await send('Page.reload', { ignoreCache: true });
await waitFor('Page.loadEventFired');
const registrationMobile = await evaluate(`(() => {
    const search = document.querySelector('[data-student-search]');
    search.value = 'muhammad';
    search.dispatchEvent(new Event('input', { bubbles: true }));
    const list = document.querySelector('[data-student-options]');
    const firstSuggestion = document.querySelector('[data-student-option]:not([hidden])');
    const suggestionCount = document.querySelectorAll('[data-student-option]:not([hidden])').length;
    const listWithinViewport = list.getBoundingClientRect().right <= innerWidth && list.getBoundingClientRect().left >= 0;
    firstSuggestion.click();
    return {
        width: innerWidth,
        scrollWidth: document.documentElement.scrollWidth,
        fields: document.querySelectorAll('form input, form select').length,
        suggestions: suggestionCount,
        listWithinViewport,
        touchSelectionStored: Boolean(document.querySelector('[data-student-registry-id]').value),
    };
})()`);
await screenshot('storage/app/visual-registration-mobile.png');

const registrationWidths = [];
for (const width of [320, 768]) {
    await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 600 });
    await send('Page.reload', { ignoreCache: true });
    await waitFor('Page.loadEventFired');
    registrationWidths.push(await evaluate(`({ width: innerWidth, scrollWidth: document.documentElement.scrollWidth })`));
}

await send('Emulation.setDeviceMetricsOverride', { width: 1366, height: 900, deviceScaleFactor: 1, mobile: false });
pageNavigation = waitFor('Page.loadEventFired');
await send('Page.navigate', { url: 'http://127.0.0.1:8000/masuk' });
await pageNavigation;

const navigation = waitFor('Page.loadEventFired');
await evaluate(`(() => {
    document.querySelector('[name="email"]').value = 'demo01@demo.polteksi.ac.id';
    document.querySelector('[name="password"]').value = 'demo12345';
    document.querySelector('form').requestSubmit();
})()`);
await navigation;

await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
await send('Page.reload', { ignoreCache: true });
await waitFor('Page.loadEventFired');
const desktop = await evaluate(`({ title: document.title, width: innerWidth, scrollWidth: document.documentElement.scrollWidth, path: location.pathname })`);
await screenshot('storage/app/visual-dashboard-desktop.png');

await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
await send('Page.reload', { ignoreCache: true });
await waitFor('Page.loadEventFired');
const mobile = await evaluate(`({ title: document.title, width: innerWidth, scrollWidth: document.documentElement.scrollWidth, menuVisible: getComputedStyle(document.querySelector('[data-sidebar-open]')).display !== 'none' })`);
await screenshot('storage/app/visual-dashboard-mobile.png');

const menuInteraction = await evaluate(`(() => {
    const button = document.querySelector('[data-sidebar-open]');
    button.click();
    const opened = document.body.classList.contains('sidebar-open');
    document.querySelector('[data-sidebar-close]').click();
    return { opened, closed: !document.body.classList.contains('sidebar-open') };
})()`);

await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
pageNavigation = waitFor('Page.loadEventFired');
await send('Page.navigate', { url: 'http://127.0.0.1:8000/mahasiswa/pengajuan/baru' });
await pageNavigation;
const wizardInteraction = await evaluate(`(() => {
    const form = document.querySelector('[data-upload-wizard]');
    const next = form.querySelector('[data-step-next]');
    form.querySelector('[name="category_picker"]').click();
    next.click();
    const rule = form.querySelector('[data-rule-select]');
    const option = [...rule.options].find((item) => item.value && !item.disabled);
    rule.value = option.value;
    rule.dispatchEvent(new Event('change', { bubbles: true }));
    next.click();
    form.querySelector('[name="activity_name"]').value = 'Pemeriksaan browser';
    form.querySelector('[name="organizer"]').value = 'Pengujian lokal';
    form.querySelector('[name="started_at"]').value = '2026-09-01';
    next.click();
    const summaryVisible = form.querySelector('[data-step="4"]').classList.contains('active');
    const points = form.querySelector('[data-summary-output="points"]').textContent.trim();
    form.querySelector('[data-step-back]').click();
    return { summaryVisible, points, backWorked: form.querySelector('[data-step="3"]').classList.contains('active') };
})()`);

pageNavigation = waitFor('Page.loadEventFired');
await send('Page.navigate', { url: 'http://127.0.0.1:8000/mahasiswa/panduan' });
await pageNavigation;
const guideInteraction = await evaluate(`(() => {
    const input = document.querySelector('[data-guide-search]');
    input.value = 'BNSP';
    input.dispatchEvent(new Event('input', { bubbles: true }));
    const matchingGroups = [...document.querySelectorAll('[data-guide-item]')].filter((item) => !item.hidden).length;
    input.value = 'kegiatan-yang-tidak-ada';
    input.dispatchEvent(new Event('input', { bubbles: true }));
    return { matchingGroups, emptyShown: !document.querySelector('.guide-empty').hidden };
})()`);

pageNavigation = waitFor('Page.loadEventFired');
await send('Page.navigate', { url: 'http://127.0.0.1:8000/mahasiswa/skpi' });
await pageNavigation;
const skpiRequestForm = await evaluate(`(() => {
    const form = document.querySelector('.skpi-request-form, .reapply-form');
    if (!form) return { available: false, fields: 0 };
    form.querySelector('[name="birthplace"]').value = 'Surabaya';
    form.querySelector('[name="birthdate"]').value = '2004-01-01';
    return { available: true, fields: form.querySelectorAll('input:not([type="hidden"])').length };
})()`);
if (skpiRequestForm.available) {
    pageNavigation = waitFor('Page.loadEventFired');
    await evaluate(`document.querySelector('.skpi-request-form, .reapply-form').requestSubmit()`);
    await pageNavigation;
}

pageNavigation = waitFor('Page.loadEventFired');
await evaluate(`document.querySelector('form[action$="/keluar"]').requestSubmit()`);
await pageNavigation;
pageNavigation = waitFor('Page.loadEventFired');
await evaluate(`(() => {
    document.querySelector('[name="email"]').value = 'admin@polteksi.ac.id';
    document.querySelector('[name="password"]').value = ${JSON.stringify(adminPassword)};
    document.querySelector('form').requestSubmit();
})()`);
await pageNavigation;
const admin = await evaluate(`({ title: document.title, width: innerWidth, scrollWidth: document.documentElement.scrollWidth, path: location.pathname })`);
await screenshot('storage/app/visual-dashboard-admin.png');
const darkTheme = await evaluate(`(() => { document.documentElement.dataset.theme = 'light'; localStorage.setItem('skem-theme', 'light'); document.querySelector('[data-theme-toggle]').click(); return { theme: document.documentElement.dataset.theme, background: getComputedStyle(document.body).backgroundColor }; })()`);
await screenshot('storage/app/visual-dashboard-dark.png');

pageNavigation = waitFor('Page.loadEventFired');
await send('Page.navigate', { url: 'http://127.0.0.1:8000/admin/skpi' });
await pageNavigation;
const adminSearch = await evaluate(`({
    available: Boolean(document.querySelector('[name="q"]')),
    placeholder: document.querySelector('[name="q"]')?.placeholder || '',
})`);
const bulkInteraction = await evaluate(`(() => {
    const form = document.querySelector('[data-bulk-form]');
    if (!form) return { available: false };
    const selectAll = form.querySelector('[data-select-all]');
    selectAll.click();
    return {
        available: true,
        selected: form.querySelectorAll('[data-select-item]:checked').length,
        count: form.querySelector('[data-selected-count]').textContent.trim(),
        enabledActions: [...form.querySelectorAll('[name="action"]')].filter((button) => !button.disabled).length,
    };
})()`);
await screenshot('storage/app/visual-skpi-admin-list.png');
const firstRequestUrl = await evaluate(`document.querySelector('a[href*="/admin/skpi/"]:not([href$="/unduh"])')?.href`);
let adminDecision = { available: false };
if (firstRequestUrl) {
    pageNavigation = waitFor('Page.loadEventFired');
    await send('Page.navigate', { url: firstRequestUrl });
    await pageNavigation;
    adminDecision = await evaluate(`({
        available: true,
        selectInputs: document.querySelectorAll('[data-decision-select], [data-decision-note]').length,
        decisionButtons: document.querySelectorAll('[name="decision"]').length,
        downloadLinks: document.querySelectorAll('a[href$="/unduh"]').length,
    })`);
    await screenshot('storage/app/visual-skpi-admin-detail.png');
}

console.log(JSON.stringify({ loginControls, registrationDesktop, registrationMobile, registrationWidths, desktop, mobile, menuInteraction, wizardInteraction, guideInteraction, skpiRequestForm, admin, darkTheme, adminSearch, bulkInteraction, adminDecision, issues: issues.length }, null, 2));
socket.close();
