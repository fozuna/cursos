document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const body = document.body;
    const themeToggles = document.querySelectorAll('[data-theme-toggle]');
    const inputMode = document.querySelector('[data-input-mode]');
    const modeChoices = document.querySelectorAll('[data-mode-choice]');
    const manualFields = document.querySelector('[data-manual-fields]');
    const uploadFields = document.querySelector('[data-upload-fields]');
    const uploadFileInput = document.querySelector('[data-upload-file]');
    const uploadFileName = document.querySelector('[data-upload-file-name]');
    const previewInputs = document.querySelectorAll('[data-preview-input]');
    const phoneInputs = document.querySelectorAll('[data-phone-mask]');
    const cpfInputs = document.querySelectorAll('[data-cpf-mask]');
    const sidebar = document.querySelector('[data-sidebar]');
    const sidebarToggles = document.querySelectorAll('[data-sidebar-toggle]');
    const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');
    const submenuToggles = document.querySelectorAll('[data-submenu-toggle]');
    const modalTriggers = document.querySelectorAll('[data-modal-open]');
    const modals = document.querySelectorAll('[data-modal]');
    const desktopSidebarQuery = window.matchMedia('(min-width: 768px)');

    const formatPreviewValue = (key, value) => {
        if (key === 'completion_date' && /^\d{4}-\d{2}-\d{2}$/.test(value)) {
            const [year, month, day] = value.split('-');
            return `${day}/${month}/${year}`;
        }

        return value;
    };

    const applyTheme = (theme) => {
        html.classList.toggle('dark', theme === 'dark');
        localStorage.setItem('certificate-theme', theme);
    };

    const setSidebarState = (open) => {
        if (!sidebar) {
            return;
        }

        const shouldStayOpen = desktopSidebarQuery.matches;
        const expanded = shouldStayOpen ? true : open;

        body.classList.toggle('app-nav-open', !shouldStayOpen && expanded);
        sidebar.setAttribute('aria-hidden', expanded ? 'false' : 'true');

        sidebarToggles.forEach((toggle) => {
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    };

    const setSubmenuState = (toggle, expand) => {
        const targetId = toggle.dataset.submenuTarget;
        const panel = targetId ? document.getElementById(targetId) : null;

        if (!panel) {
            return;
        }

        toggle.setAttribute('aria-expanded', expand ? 'true' : 'false');
        panel.classList.toggle('is-open', expand);
        panel.setAttribute('aria-hidden', expand ? 'false' : 'true');
    };

    const openModal = (modal) => {
        if (!modal) {
            return;
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusable) {
            focusable.focus();
        }
    };

    const closeModal = (modal) => {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    if (themeToggles.length > 0) {
        const storedTheme = localStorage.getItem('certificate-theme') || 'light';
        applyTheme(storedTheme);

        themeToggles.forEach((themeToggle) => {
            themeToggle.addEventListener('click', () => {
                applyTheme(html.classList.contains('dark') ? 'light' : 'dark');
            });
        });
    }

    if (sidebar) {
        setSidebarState(false);

        sidebarToggles.forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                setSidebarState(!isExpanded);
            });
        });

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => setSidebarState(false));
        }

        if (typeof desktopSidebarQuery.addEventListener === 'function') {
            desktopSidebarQuery.addEventListener('change', () => setSidebarState(false));
        } else if (typeof desktopSidebarQuery.addListener === 'function') {
            desktopSidebarQuery.addListener(() => setSidebarState(false));
        }
    }

    submenuToggles.forEach((toggle) => {
        setSubmenuState(toggle, toggle.getAttribute('aria-expanded') === 'true');

        toggle.addEventListener('click', () => {
            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            setSubmenuState(toggle, !isExpanded);
        });
    });

    modalTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const modal = document.querySelector(`[data-modal-id="${trigger.dataset.modalOpen}"]`);
            openModal(modal);
        });
    });

    modals.forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal || event.target.matches('[data-modal-close]')) {
                closeModal(modal);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setSidebarState(false);
            modals.forEach((modal) => closeModal(modal));
        }
    });

    const syncMode = () => {
        const uploadMode = inputMode && inputMode.value === 'upload';
        if (manualFields) {
            manualFields.classList.toggle('hidden', uploadMode);
            manualFields.querySelectorAll('input, select, textarea').forEach((field) => {
                field.disabled = uploadMode;
            });
        }
        if (uploadFields) {
            uploadFields.classList.toggle('hidden', !uploadMode);
            uploadFields.querySelectorAll('input, select, textarea').forEach((field) => {
                if (field.type !== 'hidden') {
                    field.disabled = !uploadMode;
                }
            });
        }
        modeChoices.forEach((choice) => {
            const active = choice.dataset.modeChoice === inputMode.value;
            choice.classList.toggle('border-brand-700', active);
            choice.classList.toggle('bg-brand-50', active);
            choice.classList.toggle('text-brand-950', active);
            choice.classList.toggle('dark:bg-brand-950/40', active);
            choice.classList.toggle('dark:border-brand-500/60', active);
            choice.classList.toggle('border-slate-200', !active);
            choice.classList.toggle('dark:border-slate-700', !active);
        });
    };

    if (inputMode) {
        syncMode();
        modeChoices.forEach((choice) => {
            choice.addEventListener('click', () => {
                inputMode.value = choice.dataset.modeChoice;
                syncMode();
            });
        });
    }

    if (uploadFileInput && uploadFileName) {
        uploadFileInput.addEventListener('change', () => {
            const [file] = uploadFileInput.files;
            uploadFileName.textContent = file ? file.name : 'Nenhum arquivo selecionado';
        });
    }

    previewInputs.forEach((input) => {
        const update = () => {
            const target = document.querySelector(`[data-preview="${input.dataset.previewInput}"]`);
            if (!target) {
                return;
            }

            const formattedValue = formatPreviewValue(input.dataset.previewInput, input.value);
            target.textContent = formattedValue || target.dataset.placeholder || '-';
        };

        update();
        input.addEventListener('input', update);
        input.addEventListener('change', update);
    });

    const applyMask = (input, formatter) => {
        input.addEventListener('input', () => {
            const digits = input.value.replace(/\D+/g, '');
            input.value = formatter(digits);
        });
    };

    phoneInputs.forEach((input) => {
        applyMask(input, (digits) => {
            if (digits.length <= 10) {
                return digits
                    .replace(/^(\d{0,2})(\d{0,4})(\d{0,4}).*/, (_, ddd, part1, part2) => {
                        let formatted = ddd ? `(${ddd}` : '';
                        if (ddd.length === 2) {
                            formatted += ') ';
                        }
                        formatted += part1;
                        if (part2) {
                            formatted += `-${part2}`;
                        }
                        return formatted;
                    });
            }

            return digits
                .replace(/^(\d{0,2})(\d{0,5})(\d{0,4}).*/, (_, ddd, part1, part2) => {
                    let formatted = ddd ? `(${ddd}` : '';
                    if (ddd.length === 2) {
                        formatted += ') ';
                    }
                    formatted += part1;
                    if (part2) {
                        formatted += `-${part2}`;
                    }
                    return formatted;
                });
        });
    });

    cpfInputs.forEach((input) => {
        applyMask(input, (digits) => digits
            .replace(/^(\d{0,3})(\d{0,3})(\d{0,3})(\d{0,2}).*/, (_, p1, p2, p3, p4) => {
                let formatted = p1;
                if (p2) {
                    formatted += `.${p2}`;
                }
                if (p3) {
                    formatted += `.${p3}`;
                }
                if (p4) {
                    formatted += `-${p4}`;
                }
                return formatted;
            }));
    });
});
