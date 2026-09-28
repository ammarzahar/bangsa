import './bootstrap';
import 'flowbite';

document.addEventListener('DOMContentLoaded', () => {
    const wizard = document.querySelector('[data-community-wizard]');

    if (!wizard) {
        return;
    }

    const form = wizard.querySelector('[data-community-form]');
    const panels = [...wizard.querySelectorAll('[data-wizard-panel]')];
    const stepButtons = [...wizard.querySelectorAll('[data-wizard-step-button]')];
    const backButton = wizard.querySelector('[data-wizard-back]');
    const nextButton = wizard.querySelector('[data-wizard-next]');
    const submitButton = wizard.querySelector('[data-wizard-submit]');
    let currentStep = 1;
    let furthestStep = 1;

    const updateReview = () => {
        const name = form.elements.name.value.trim() || 'Your community';
        const slug = form.elements.slug.value.trim() || 'community-url';
        const visibility = form.elements.visibility.value || 'PUBLIC';

        wizard.querySelector('[data-review-name]').textContent = name;
        wizard.querySelector('[data-review-slug]').textContent = slug;
        wizard.querySelector('[data-review-initial]').textContent = name.charAt(0).toUpperCase();
        wizard.querySelector('[data-review-visibility]').textContent = visibility.charAt(0) + visibility.slice(1).toLowerCase();
    };

    const showStep = (step) => {
        currentStep = step;
        furthestStep = Math.max(furthestStep, step);

        panels.forEach((panel) => {
            panel.classList.toggle('hidden', Number(panel.dataset.wizardPanel) !== step);
        });

        stepButtons.forEach((button) => {
            const buttonStep = Number(button.dataset.wizardStepButton);
            const circle = button.querySelector('[data-wizard-step-circle]');
            const label = button.querySelector('[data-wizard-step-label]');
            const isActive = buttonStep === step;
            const isComplete = buttonStep < furthestStep;

            button.disabled = buttonStep > furthestStep;
            button.setAttribute('aria-current', isActive ? 'step' : 'false');
            button.classList.toggle('bg-brand-50', isActive);
            circle.className = `flex h-9 w-9 shrink-0 items-center justify-center rounded-full border text-sm font-bold ${isActive ? 'border-brand-600 bg-brand-600 text-white' : isComplete ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-400'}`;
            label?.classList.toggle('text-brand-700', isActive);
            label?.classList.toggle('text-slate-700', !isActive);
        });

        backButton.classList.toggle('invisible', step === 1);
        nextButton.classList.toggle('hidden', step === panels.length);
        submitButton.classList.toggle('hidden', step !== panels.length);

        if (step === panels.length) {
            updateReview();
        }
    };

    nextButton.addEventListener('click', () => {
        const currentPanel = panels[currentStep - 1];
        const fields = [...currentPanel.querySelectorAll('input, textarea, select')];
        const invalidField = fields.find((field) => !field.checkValidity());

        if (invalidField) {
            invalidField.reportValidity();
            return;
        }

        showStep(Math.min(currentStep + 1, panels.length));
    });

    backButton.addEventListener('click', () => showStep(Math.max(currentStep - 1, 1)));

    stepButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const step = Number(button.dataset.wizardStepButton);
            if (step <= furthestStep) {
                showStep(step);
            }
        });
    });

    form.elements.name.addEventListener('input', updateReview);
    form.elements.slug.addEventListener('input', (event) => {
        event.target.value = event.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
        updateReview();
    });
    [...form.elements.visibility].forEach((field) => field.addEventListener('change', updateReview));

    showStep(1);
});
