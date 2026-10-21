const root = document.documentElement;

document.querySelectorAll('[data-notice]').forEach((notice) => {
    const close = () => {
        notice.classList.add('is-closing');
        window.setTimeout(() => notice.remove(), 160);
    };
    notice.querySelector('[data-notice-close]')?.addEventListener('click', close);
    const delay = Number.parseInt(notice.dataset.autoDismiss || '', 10);
    if (Number.isFinite(delay) && delay > 0) {
        let timer = window.setTimeout(close, delay);
        notice.addEventListener('mouseenter', () => window.clearTimeout(timer));
        notice.addEventListener('mouseleave', () => { timer = window.setTimeout(close, 1800); });
        notice.addEventListener('focusin', () => window.clearTimeout(timer));
        notice.addEventListener('focusout', () => { timer = window.setTimeout(close, 1800); });
    }
});

const fieldLabel = (field) => {
    const container = field.closest('.field, [data-field-container]');
    const label = container?.querySelector(':scope > span, :scope > label, [data-field-label]')?.textContent || field.name;
    return label.replace(/opsional/gi, '').replace(/\*/g, '').trim() || 'Kolom ini';
};

const fieldMessage = (field) => {
    const label = fieldLabel(field);
    if (field.validity.valueMissing) return `${label} wajib diisi.`;
    if (field.validity.typeMismatch && field.type === 'email') return 'Email harus berupa alamat email yang valid.';
    if (field.validity.typeMismatch && field.type === 'url') return 'Tautan verifikasi harus berupa URL yang valid dan diawali http:// atau https://.';
    if (field.validity.patternMismatch && field.name === 'verification_url') return 'Tautan verifikasi harus diawali http:// atau https://.';
    if (field.validity.tooLong) return `${label} tidak boleh lebih dari ${field.maxLength} karakter.`;
    if (field.validity.rangeOverflow) return `${label} tidak boleh melewati batas yang ditentukan.`;
    if (field.validity.rangeUnderflow) return `${label} tidak boleh kurang dari batas yang ditentukan.`;
    return field.validationMessage || `${label} belum valid.`;
};

const setFieldError = (field, message = '') => {
    const container = field.closest('.field, [data-field-container]') || field.parentElement;
    let error = container?.querySelector('[data-field-error]');
    if (!error && container) {
        error = document.createElement('small');
        error.className = 'field-error';
        error.dataset.fieldError = '';
        error.setAttribute('role', 'alert');
        container.append(error);
    }
    if (message) {
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        if (error) {
            error.textContent = message;
            error.hidden = false;
            if (!error.id) error.id = `${field.name || field.id || 'field'}-client-error`;
            field.setAttribute('aria-describedby', error.id);
        }
        return false;
    }
    field.classList.remove('is-invalid');
    field.removeAttribute('aria-invalid');
    if (error) {
        error.textContent = '';
        error.hidden = true;
    }
    return true;
};

const validateField = (field) => {
    if (field.disabled || field.type === 'hidden') return true;
    return setFieldError(field, field.checkValidity() ? '' : fieldMessage(field));
};

document.querySelectorAll('[data-validate-form]').forEach((form) => {
    const fields = [...form.querySelectorAll('input:not([type="hidden"]), select, textarea')];
    fields.forEach((field) => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => {
            if (field.getAttribute('aria-invalid') === 'true') validateField(field);
        });
    });
    form.addEventListener('submit', (event) => {
        const invalid = fields.filter((field) => !validateField(field));
        if (invalid.length === 0) return;
        event.preventDefault();
        invalid[0].focus();
        invalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});

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
    const accountStatus = registration.querySelector('[data-student-account-status]');
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
        if (option.dataset.available !== 'true') {
            registryId.value = '';
            search.setCustomValidity('Nama ini sudah memiliki akun. Silakan masuk atau gunakan lupa password.');
            options.forEach((item) => item.setAttribute('aria-selected', item === option ? 'true' : 'false'));
            syncRegistry(option);
            if (accountStatus) accountStatus.hidden = false;
            setFieldError(search);
            closeOptions();
            return;
        }
        registryId.value = option.dataset.id;
        search.setCustomValidity('');
        if (accountStatus) accountStatus.hidden = true;
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

    const filterStudents = (showInitial = false) => {
        const query = normalize(search.value);
        registryId.value = '';
        options.forEach((option) => {
            option.hidden = true;
            option.classList.remove('active');
            option.setAttribute('aria-selected', 'false');
        });
        syncRegistry();

        if (query.length === 0 && !showInitial) {
            status.textContent = 'Sentuh kolom untuk melihat daftar nama.';
            closeOptions();
            return;
        }

        visibleOptions = options
            .map((option, order) => ({ option, order, score: matchScore(option, query) }))
            .filter((item) => showInitial && query.length === 0 ? true : Number.isFinite(item.score))
            .sort((left, right) => left.score - right.score || left.order - right.order)
            .slice(0, 8)
            .map((item) => item.option);

        visibleOptions.forEach((option) => { option.hidden = false; });
        empty.hidden = visibleOptions.length !== 0;
        optionList.hidden = false;
        search.setAttribute('aria-expanded', 'true');
        status.textContent = visibleOptions.length === 0
            ? 'Nama atau NIM tidak ditemukan.'
            : `${visibleOptions.length} nama ditemukan.`;
    };

    options.forEach((option) => {
        option.addEventListener('pointerdown', (event) => {
            event.preventDefault();
            if (event.pointerType === 'touch' || event.pointerType === 'pen') chooseStudent(option);
        });
        option.addEventListener('click', () => chooseStudent(option));
    });
    const runSearch = () => {
        search.setCustomValidity('');
        if (accountStatus) accountStatus.hidden = true;
        setFieldError(search);
        filterStudents();
    };
    search.addEventListener('input', runSearch);
    search.addEventListener('search', runSearch);
    search.addEventListener('compositionend', runSearch);
    search.addEventListener('focus', () => {
        if (!registryId.value) filterStudents(true);
    });
    search.addEventListener('click', () => {
        if (!registryId.value && optionList.hidden) filterStudents(true);
    });
    search.addEventListener('blur', (event) => window.setTimeout(() => {
        if (event.relatedTarget && optionList.contains(event.relatedTarget)) return;
        closeOptions();
        if (search.value.trim() && !registryId.value) {
            search.setCustomValidity('Pilih nama dari hasil pencarian.');
            setFieldError(search, 'Pilih nama dari hasil pencarian.');
        }
    }, 280));
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
        setFieldError(search, 'Pilih nama dari hasil pencarian.');
        search.focus();
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
        button.querySelector('[data-password-icon-show]').hidden = !visible;
        button.querySelector('[data-password-icon-hide]').hidden = visible;
        button.setAttribute('aria-label', visible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        button.setAttribute('aria-pressed', visible ? 'false' : 'true');
        input.focus();
    });
});

document.querySelectorAll('[data-user-role-form]').forEach((form) => {
    const role = form.querySelector('[data-role-select]');
    const faculty = form.querySelector('[data-faculty-select]');
    const program = form.querySelector('[data-program-select]');
    const studentFields = form.querySelector('[data-student-fields]');
    const facultyRequired = form.querySelector('[data-faculty-required]');
    const facultyOptional = form.querySelector('[data-faculty-optional]');
    const programOptions = program ? [...program.options] : [];

    const updatePrograms = () => {
        if (!program || !faculty) return;
        const selectedFaculty = faculty.value;
        programOptions.forEach((option) => {
            if (option.value) option.hidden = Boolean(selectedFaculty && option.dataset.faculty !== selectedFaculty);
        });
        if (program.options[program.selectedIndex]?.hidden) program.value = '';
    };

    const updateRoleFields = () => {
        if (!role) return;
        const isStudent = role.value === 'student';
        const needsProgram = isStudent || role.value === 'program_verifier';
        const needsFaculty = role.value === 'leader';
        if (studentFields) {
            studentFields.hidden = !isStudent;
            studentFields.querySelectorAll('input, select').forEach((input) => {
                input.disabled = !isStudent;
                input.required = isStudent;
            });
        }
        if (program) program.required = needsProgram;
        if (faculty) faculty.required = needsFaculty;
        if (facultyRequired) facultyRequired.hidden = !needsFaculty;
        if (facultyOptional) facultyOptional.hidden = needsFaculty;
    };

    role?.addEventListener('change', updateRoleFields);
    faculty?.addEventListener('change', updatePrograms);
    updatePrograms();
    updateRoleFields();
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
    let currentStep = Number.parseInt(wizard.dataset.initialStep || '1', 10);
    const steps = [...wizard.querySelectorAll('[data-step]')];
    const indicators = [...document.querySelectorAll('[data-stepper] li')];
    const back = wizard.querySelector('[data-step-back]');
    const next = wizard.querySelector('[data-step-next]');
    const submit = wizard.querySelector('[data-step-submit]');
    const categoryInputs = [...wizard.querySelectorAll('[name="category_picker"]')];
    const ruleSelect = wizard.querySelector('[data-rule-select]');
    const ruleField = wizard.querySelector('[data-rule-field]');
    const fixedRule = wizard.querySelector('[data-fixed-rule]');
    const fixedRuleOutput = wizard.querySelector('[data-fixed-rule-output]');
    const customRuleField = wizard.querySelector('[data-custom-rule-field]');
    const customActivityType = wizard.querySelector('[data-custom-activity-type]');
    const estimateBox = wizard.querySelector('[data-estimate]');
    const estimate = estimateBox?.querySelector('strong');
    const evidenceLabel = wizard.querySelector('[data-evidence-label]');

    const selectedCategory = () => wizard.querySelector('[name="category_picker"]:checked')?.value || '';
    const selectedRule = () => ruleSelect?.selectedOptions[0];

    const filterRules = () => {
        const category = selectedCategory();
        const matchingOptions = [];
        [...ruleSelect.options].forEach((option, index) => {
            if (index === 0) return;
            const matches = option.dataset.category === category;
            option.hidden = !matches;
            option.disabled = !matches;
            if (matches) matchingOptions.push(option);
        });
        if (ruleSelect.selectedOptions[0]?.dataset.category !== category) ruleSelect.value = '';
        if (matchingOptions.length === 1 && ['custom', 'fixed'].includes(matchingOptions[0].dataset.entryMode)) {
            ruleSelect.value = matchingOptions[0].value;
        }
        updateEstimate();
    };

    const updateEstimate = () => {
        const option = selectedRule();
        const mode = option?.dataset.entryMode || 'standard';
        const points = option?.dataset.points || '0';
        if (ruleField) ruleField.hidden = mode === 'custom' || mode === 'fixed';
        if (fixedRule) fixedRule.hidden = mode !== 'fixed';
        if (fixedRuleOutput) fixedRuleOutput.textContent = option?.dataset.activityType || 'Magang Industri';
        if (customRuleField) customRuleField.hidden = mode !== 'custom';
        if (customActivityType) {
            customActivityType.disabled = mode !== 'custom';
            customActivityType.required = mode === 'custom';
        }
        if (estimateBox) estimateBox.hidden = mode === 'custom';
        if (estimate) estimate.textContent = points;
        if (evidenceLabel) evidenceLabel.textContent = option?.dataset.evidence || 'Pilih kegiatan untuk melihat bukti yang diperlukan.';
    };

    const updateSummary = () => {
        const option = selectedRule();
        const isCustom = option?.dataset.entryMode === 'custom';
        const values = {
            category: selectedCategory() || 'Belum dipilih',
            rule: isCustom ? (customActivityType?.value.trim() || 'Belum diisi') : (option?.dataset.activityType || (option?.value ? option.textContent.trim() : 'Belum dipilih')),
            activity: wizard.querySelector('[name="activity_name"]')?.value || 'Belum diisi',
            organizer: wizard.querySelector('[name="organizer"]')?.value || 'Belum diisi',
            points: isCustom ? '-' : `${option?.dataset.points || 0} poin`,
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
            setFieldError(categoryInputs[0], 'Pilih satu kategori kegiatan.');
            return false;
        }
        categoryInputs.forEach((input) => input.setCustomValidity(''));
        const fields = [...steps[currentStep - 1].querySelectorAll('input, select, textarea')]
            .filter((field) => !field.disabled && field.type !== 'hidden');
        const startedAt = wizard.querySelector('[name="started_at"]');
        const endedAt = wizard.querySelector('[name="ended_at"]');
        if (currentStep === 3 && startedAt && endedAt) {
            endedAt.setCustomValidity(startedAt.value && endedAt.value && endedAt.value < startedAt.value
                ? 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'
                : '');
        }
        const invalid = fields.filter((field) => !validateField(field));
        if (invalid.length === 0) return true;
        invalid[0].focus();
        invalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
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
    [...wizard.querySelectorAll('input, select, textarea')].forEach((field) => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => {
            if (field.getAttribute('aria-invalid') === 'true') validateField(field);
        });
    });
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
    showStep();

    const fileInput = wizard.querySelector('[data-file-input]');
    fileInput?.addEventListener('change', () => {
        const fileName = wizard.querySelector('[data-file-name]');
        if (fileName) fileName.textContent = fileInput.files[0]?.name || 'PDF, JPG, JPEG, atau PNG, maksimal 5 MB';
        const file = fileInput.files[0];
        const allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        if (file && file.size > 5 * 1024 * 1024) {
            fileInput.setCustomValidity('Ukuran dokumen bukti maksimal 5 MB.');
        } else if (file && !allowed.includes(file.type)) {
            fileInput.setCustomValidity('Dokumen bukti harus berformat PDF, JPG, JPEG, atau PNG.');
        } else {
            fileInput.setCustomValidity('');
        }
        validateField(fileInput);
    });
}

document.querySelectorAll('[data-custom-rule-select]').forEach((select) => {
    const form = select.closest('form');
    const field = form?.querySelector('[data-admin-custom-rule-field]');
    const input = field?.querySelector('input');
    const syncCustomRule = () => {
        const isCustom = select.selectedOptions[0]?.dataset.entryMode === 'custom';
        if (field) field.hidden = !isCustom;
        if (input) {
            input.disabled = !isCustom;
            input.required = isCustom;
        }
    };
    select.addEventListener('change', syncCustomRule);
    syncCustomRule();
});

document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelectorAll('[data-dialog-open]').forEach((button) => {
    button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.dialogOpen);
        if (!(dialog instanceof HTMLDialogElement)) return;
        dialog.showModal();
        dialog.querySelector('textarea, input, button')?.focus();
    });
});

document.querySelectorAll('[data-dialog]').forEach((dialog) => {
    dialog.querySelectorAll('[data-dialog-close]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
    if (dialog.hasAttribute('data-open-on-load')) {
        dialog.showModal();
        dialog.querySelector('[aria-invalid="true"], textarea, input')?.focus();
    }
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
document.querySelectorAll('form').forEach((form) => form.addEventListener('submit', (event) => {
    if (!event.defaultPrevented) loader?.classList.add('loading');
}));
window.addEventListener('pageshow', () => loader?.classList.remove('loading'));
