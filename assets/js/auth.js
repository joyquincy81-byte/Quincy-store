document.addEventListener('DOMContentLoaded', () => {
    const passwordToggle = document.querySelector('[data-password-toggle]');
    const passwordInput = document.querySelector('#password');
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', () => {
            const visible = passwordInput.type === 'text';
            passwordInput.type = visible ? 'password' : 'text';
            passwordToggle.textContent = visible ? 'Show' : 'Hide';
            passwordToggle.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        });
    }

    const captcha = document.querySelector('[data-slider-captcha]');
    const track = document.querySelector('[data-slider-track]');
    const handle = document.querySelector('[data-slider-handle]');
    const progress = document.querySelector('[data-slider-progress]');
    const status = document.querySelector('[data-captcha-status]');
    const verifiedInput = document.querySelector('[data-human-verified]');
    const submit = document.querySelector('[data-login-submit]');
    if (!captcha || !track || !handle || !progress || !status || !verifiedInput || !submit) return;

    let dragging = false;
    let verified = false;

    const updatePosition = (clientX) => {
        if (verified) return;
        const bounds = track.getBoundingClientRect();
        const max = bounds.width - handle.offsetWidth - 6;
        const position = Math.max(0, Math.min(max, clientX - bounds.left - handle.offsetWidth / 2));
        const percentage = max > 0 ? position / max : 0;
        handle.style.left = `${position + 3}px`;
        progress.style.width = `${Math.min(100, percentage * 100 + 5)}%`;
        handle.setAttribute('aria-valuenow', String(Math.round(percentage * 100)));
        if (percentage >= .94) {
            verified = true;
            handle.style.left = `${max + 3}px`;
            progress.style.width = '100%';
            handle.setAttribute('aria-valuenow', '100');
            captcha.classList.add('verified');
            status.textContent = 'Verified';
            verifiedInput.value = '1';
            submit.disabled = false;
        }
    };

    handle.addEventListener('pointerdown', (event) => {
        dragging = true;
        handle.setPointerCapture(event.pointerId);
    });
    handle.addEventListener('pointermove', (event) => {
        if (dragging) updatePosition(event.clientX);
    });
    handle.addEventListener('pointerup', () => { dragging = false; });
    handle.addEventListener('keydown', (event) => {
        if (verified) return;
        if (event.key === 'ArrowRight' || event.key === 'End') {
            event.preventDefault();
            updatePosition(track.getBoundingClientRect().right - 4);
        }
        if (event.key === 'ArrowLeft' || event.key === 'Home') {
            event.preventDefault();
            updatePosition(track.getBoundingClientRect().left + 4);
        }
    });
});