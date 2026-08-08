/**
 * Commerce Core Admin JavaScript
 * Phase 0: minimal, for future enhancement.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Quick settings save feedback.
        const form = document.querySelector('.commerce-core-admin form');
        if (!form) {
            return;
        }

        form.addEventListener('submit', function () {
            const button = form.querySelector('input[type="submit"]');
            if (button) {
                button.disabled = true;
                button.value = 'Saving...';
            }
        });
    });
})();
