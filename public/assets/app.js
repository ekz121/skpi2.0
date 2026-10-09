const root = document.documentElement;

document.querySelectorAll('[data-live-clock]').forEach((clock) => {
    const timezone = clock.dataset.timezone || 'Asia/Jakarta';
    const serverTime = Date.parse(clock.dataset.serverTime || '');
    const offset = Number.isNaN(serverTime) ? 0 : serverTime - Date.now();
    const formatter = new Intl.DateTimeFormat('id-ID', {
        timeZone: timezone,
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });
    const updateClock = () => {
        clock.textContent = `${formatter.format(new Date(Date.now() + offset))} WIB`;
    };
    updateClock();
    window.setInterval(updateClock, 1000);
});

const registration = document.querySelector('[data-registration-form]');
if (registration) {
    const search = registration.querySelector('[data-student-search]');
    const registryId = registration.querySelector('[data-student-registry-id]');
    const optionList = registration.querySelector('[data-student-options]');
    const options = [...registration.querySelectorAll('[data-student-option]')];
    const empty = registration.querySelector('[data-student-empty]');
    const status = registration.querySelector('[data-student-search-status]');
    let visibleOptions = [];
    let activeIndex = -1;

    const normalize = (value) => value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase('id')
        .trim();

    const syncRegistry = (option = null) => {
        const values = {
            nim: option?.dataset.nim ?? 'Belum dipilih',
            program: option?.dataset.program ?? 'Belum dipilih',
            cohort: option?.dataset.cohort ?? '2023',
        };
        Object.entries(values).forEach(([key, value]) => {
            const target = registration.querySelector(`[data-registry-${key}]`);
            if (target) target.textContent = value;
        });
    };

    const closeOptions = () => {
        optionList.hidden = true;
        search.setAttribute('aria-expanded', 'false');
        search.removeAttribute('aria-activedescendant');
        activeIndex = -1;
    };

    const setActiveOption = (index) => {
        visibleOptions.forEach((option) => option.classList.remove('active'));
        if (visibleOptions.length === 0) return;
        activeIndex = (index + visibleOptions.length) % visibleOptions.length;
        const option = visibleOptions[activeIndex];
        option.classList.add('active');
        search.setAttribute('aria-activedescendant', option.id);
        option.scrollIntoView({ block: 'nearest' });
    };

    const chooseStudent = (option) => {
        search.value = option.dataset.name;
        registryId.value = option.dataset.id;
        search.setCustomValidity('');
        options.forEach((item) => item.setAttribute('aria-selected', item === option ? 'true' : 'false'));
        syncRegistry(option);
        closeOptions();
    };

    const matchScore = (option, query) => {
        const name = normalize(option.dataset.name);
        const nim = normalize(option.dataset.nim);
        if (name.startsWith(query)) return 0;
        if (name.split(/\s+/).some((word) => word.startsWith(query))) return 1;
        if (name.includes(query) || nim.startsWith(query)) return 2;
        return Number.POSITIVE_INFINITY;
    };

    const filterStudents = () => {
        const query = normalize(search.value);
        registryId.value = '';
        options.forEach((option) => {
            option.hidden = true;
            option.classList.remove('active');
            option.setAttribute('aria-selected', 'false');
        });
        syncRegistry();

        if (query.length === 0) {
            status.textContent = 'Ketik nama untuk memulai pencarian.';
            closeOptions();
            return;
        }

        visibleOptions = options
            .map((option, order) => ({ option, order, score: matchScore(option, query) }))
            .filter((item) => Number.isFinite(item.score))
            .sort((left, right) => left.score - right.score || left.order - right.order)
            .slice(0, 8)
            .map((item) => item.option);

        visibleOptions.forEach((option) => { option.hidden = false; });
        empty.hidden = visibleOptions.length !== 0;
        optionList.hidden = false;
        search.setAttribute('aria-expanded', 'true');
        status.textContent = visibleOptions.length === 0
            ? 'Nama tidak ditemukan.'
            : `${visibleOptions.length} nama ditemukan.`;
    };

    options.forEach((option) => {
        option.addEventListener('pointerdown', (event) => event.preventDefault());
        option.addEventListener('click', () => chooseStudent(option));
    });
    search.addEventListener('input', filterStudents);
    search.addEventListener('focus', () => {
        if (!registryId.value && search.value.trim()) filterStudents();
    });
    search.addEventListener('blur', () => window.setTimeout(closeOptions, 120));
    search.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (optionList.hidden) filterStudents();
            setActiveOption(activeIndex + (event.key === 'ArrowDown' ? 1 : -1));
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            chooseStudent(visibleOptions[activeIndex]);
        } else if (event.key === 'Escape') {
            closeOptions();
        }
    });
    registration.addEventListener('submit', (event) => {
        if (registryId.value) return;
        const exact = options.find((option) => normalize(option.dataset.name) === normalize(search.value));
        if (exact) {
            chooseStudent(exact);
            return;
        }
        event.preventDefault();
        search.setCustomValidity('Pilih nama dari hasil pencarian.');
        search.reportValidity();
    });

    const selected = options.find((option) => option.dataset.id === registryId.value);
    if (selected) chooseStudent(selected);
}

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const input = button.closest('.password-field')?.querySelector('[data-password-input]');
    button.addEventListener('click', () => {
        if (!input) return;
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.textContent = visible ? 'Lihat' : 'Sembunyikan';
        button.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
        input.focus();
    });
});

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
        root.dataset.theme = next;
        localStorage.setItem('skem-theme', next);
        button.setAttribute('aria-label', next === 'dark' ? 'Gunakan tema terang' : 'Gunakan tema gelap');
    });
});

const openSidebar = document.querySelector('[data-sidebar-open]');
const closeSidebar = document.querySelectorAll('[data-sidebar-close]');
openSidebar?.addEventListener('click', () => {
    document.body.classList.add('sidebar-open');
    document.querySelector('[data-sidebar] a, [data-sidebar] button')?.focus();
});
closeSidebar.forEach((button) => button.addEventListener('click', () => {
    document.body.classList.remove('sidebar-open');
    openSidebar?.focus();
}));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
        document.body.classList.remove('sidebar-open');
        openSidebar?.focus();
    }
});

const guideSearch = document.querySelector('[data-guide-search]');
if (guideSearch) {
    guideSearch.addEventListener('input', () => {
        const query = guideSearch.value.trim().toLocaleLowerCase('id');
        let visible = 0;
        document.querySelectorAll('[data-guide-item]').forEach((section) => {
            const match = section.textContent.toLocaleLowerCase('id').includes(query);
            section.hidden = !match;
            if (match) visible += 1;
        });
        const empty = document.querySelector('.guide-empty');
        if (empty) empty.hidden = visible !== 0;
    });
}

const wizard = document.querySelector('[data-upload-wizard]');
if (wizard) {
    let currentStep = 1;
    const steps = [...wizard.querySelectorAll('[data-step]')];
    const indicators = [...document.querySelectorAll('[data-stepper] li')];
    const back = wizard.querySelector('[data-step-back]');
    const next = wizard.querySelector('[data-step-next]');
    const submit = wizard.querySelector('[data-step-submit]');
    const categoryInputs = [...wizard.querySelectorAll('[name="category_picker"]')];
    const ruleSelect = wizard.querySelector('[data-rule-select]');
    const estimate = wizard.querySelector('[data-estimate] strong');
    const evidenceLabel = wizard.querySelector('[data-evidence-label]');

    const selectedCategory = () => wizard.querySelector('[name="category_picker"]:checked')?.value || '';
    const selectedRule = () => ruleSelect?.selectedOptions[0];

    const filterRules = () => {
        const category = selectedCategory();
        [...ruleSelect.options].forEach((option, index) => {
            if (index === 0) return;
            option.hidden = option.dataset.category !== category;
            option.disabled = option.dataset.category !== category;
        });
        if (ruleSelect.selectedOptions[0]?.dataset.category !== category) ruleSelect.value = '';
        updateEstimate();
    };

    const updateEstimate = () => {
        const option = selectedRule();
        const points = option?.dataset.points || '0';
        if (estimate) estimate.textContent = points;
        if (evidenceLabel) evidenceLabel.textContent = option?.dataset.evidence || 'Pilih kegiatan untuk melihat bukti yang diperlukan.';
    };

    const updateSummary = () => {
        const option = selectedRule();
        const values = {
            category: selectedCategory() || 'Belum dipilih',
            rule: option?.value ? option.textContent.trim() : 'Belum dipilih',
            activity: wizard.querySelector('[name="activity_name"]')?.value || 'Belum diisi',
            organizer: wizard.querySelector('[name="organizer"]')?.value || 'Belum diisi',
            points: `${option?.dataset.points || 0} poin`,
        };
        Object.entries(values).forEach(([key, value]) => {
            const target = wizard.querySelector(`[data-summary-output="${key}"]`);
            if (target) target.textContent = value;
        });
    };

    const validateStep = () => {
        if (currentStep === 1 && !selectedCategory()) {
            categoryInputs[0]?.focus();
            categoryInputs[0]?.setCustomValidity('Pilih satu kategori kegiatan.');
            categoryInputs[0]?.reportValidity();
            return false;
        }
        categoryInputs.forEach((input) => input.setCustomValidity(''));
        const fields = [...steps[currentStep - 1].querySelectorAll('input, select, textarea')].filter((field) => !field.disabled);
        return fields.every((field) => field.reportValidity());
    };

    const showStep = () => {
        steps.forEach((step, index) => step.classList.toggle('active', index + 1 === currentStep));
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index + 1 === currentStep);
            indicator.classList.toggle('complete', index + 1 < currentStep);
        });
        back.hidden = currentStep === 1;
        next.hidden = currentStep === steps.length;
        submit.hidden = currentStep !== steps.length;
        if (currentStep === steps.length) updateSummary();
        steps[currentStep - 1].querySelector('h2')?.focus({ preventScroll: true });
        window.scrollTo({ top: Math.max(0, wizard.offsetTop - 120), behavior: 'smooth' });
    };

    categoryInputs.forEach((input) => input.addEventListener('change', filterRules));
    ruleSelect?.addEventListener('change', updateEstimate);
    next?.addEventListener('click', () => {
        if (!validateStep()) return;
        currentStep = Math.min(steps.length, currentStep + 1);
        showStep();
    });
    back?.addEventListener('click', () => {
        currentStep = Math.max(1, currentStep - 1);
        showStep();
    });
    wizard.addEventListener('submit', (event) => {
        if (currentStep === steps.length) return;
        event.preventDefault();
        if (!validateStep()) return;
        currentStep = Math.min(steps.length, currentStep + 1);
        showStep();
    });
    filterRules();

    const fileInput = wizard.querySelector('[data-file-input]');
    fileInput?.addEventListener('change', () => {
        const fileName = wizard.querySelector('[data-file-name]');
        if (fileName) fileName.textContent = fileInput.files[0]?.name || 'PDF, JPG, JPEG, atau PNG, maksimal 5 MB';
    });
}

document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelectorAll('[data-bulk-form]').forEach((form) => {
    const selectAll = form.querySelector('[data-select-all]');
    const items = [...form.querySelectorAll('[data-select-item]')];
    const count = form.querySelector('[data-selected-count]');
    const actions = [...form.querySelectorAll('[name="action"]')];
    const sync = () => {
        const selected = items.filter((item) => item.checked).length;
        if (count) count.textContent = selected;
        actions.forEach((button) => button.disabled = selected === 0);
        if (selectAll) {
            selectAll.checked = selected === items.length && items.length > 0;
            selectAll.indeterminate = selected > 0 && selected < items.length;
        }
    };
    selectAll?.addEventListener('change', () => {
        items.forEach((item) => item.checked = selectAll.checked);
        sync();
    });
    items.forEach((item) => item.addEventListener('change', sync));
    sync();
});

const loader = document.querySelector('[data-page-loader]');
document.querySelectorAll('a[href]').forEach((link) => {
    link.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.ctrlKey || event.metaKey || link.target === '_blank' || link.href.startsWith('mailto:') || link.getAttribute('href') === '#') return;
        loader?.classList.add('loading');
    });
});
document.querySelectorAll('form').forEach((form) => form.addEventListener('submit', () => loader?.classList.add('loading')));
window.addEventListener('pageshow', () => loader?.classList.remove('loading'));
