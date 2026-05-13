document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const inputMode = document.querySelector('[data-input-mode]');
    const modeChoices = document.querySelectorAll('[data-mode-choice]');
    const manualFields = document.querySelector('[data-manual-fields]');
    const uploadFields = document.querySelector('[data-upload-fields]');
    const uploadFileInput = document.querySelector('[data-upload-file]');
    const uploadFileName = document.querySelector('[data-upload-file-name]');
    const previewInputs = document.querySelectorAll('[data-preview-input]');

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

    if (themeToggle) {
        const storedTheme = localStorage.getItem('certificate-theme') || 'light';
        applyTheme(storedTheme);

        themeToggle.addEventListener('click', () => {
            applyTheme(html.classList.contains('dark') ? 'light' : 'dark');
        });
    }

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
});
