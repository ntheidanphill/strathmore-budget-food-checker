/**
 * app.js - small, friendly JavaScript used across the site.
 * Everything is wrapped in DOMContentLoaded so the page is ready first.
 * 1. Auto-hide success messages
 * 2. Ask "Are you sure?" before destructive actions (forms with data-confirm)
 * 3. Quick-budget chips on the student search page
 * 4. Count-up animation for dashboard numbers
 */
document.addEventListener('DOMContentLoaded', () => {

    // 1. Success alerts fade out after 4 seconds
    document.querySelectorAll('[data-autohide]').forEach((el) => {
        setTimeout(() => {
            el.style.transition = 'opacity .6s, transform .6s';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-10px)';
            setTimeout(() => el.remove(), 600);
        }, 4000);
    });

    // 2. Confirmation dialog: <form data-confirm="Delete this item?">
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!confirm(form.dataset.confirm)) {
                event.preventDefault(); // user clicked Cancel: stop the submit
            }
        });
    });

    // 3. Chips: <button type="button" class="chip" data-budget="200"> fills the budget box and searches
    const budgetInput = document.getElementById('budget');
    document.querySelectorAll('.chip[data-budget]').forEach((chip) => {
        chip.addEventListener('click', () => {
            if (budgetInput) {
                budgetInput.value = chip.dataset.budget;
                budgetInput.form.submit(); // one tap = instant search (good for mobile, R6)
            }
        });
    });

    // 4. Count-up animation: <span data-count="25">0</span> counts from 0 to 25
    document.querySelectorAll('[data-count]').forEach((el) => {
        const target = parseInt(el.dataset.count, 10) || 0;
        const steps = 30;
        let current = 0;
        const timer = setInterval(() => {
            current += target / steps;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = Math.round(current);
        }, 25);
    });
});
