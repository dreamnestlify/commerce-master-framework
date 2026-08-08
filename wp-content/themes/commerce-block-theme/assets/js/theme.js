/**
 * Commerce Block Theme — Frontend JavaScript
 * Mobile navigation, accessibility enhancements, UI interactions.
 */
(function () {
    'use strict';

    const commerceTheme = {
        init: function () {
            this.initMobileNav();
            this.initSearchToggle();
            this.initStickyHeader();
            this.initAnnouncementBar();
            this.initFilterDrawer();
        },

        /**
         * Mobile navigation toggle.
         */
        initMobileNav: function () {
            const navToggle = document.querySelector('.wp-block-navigation__toggle');
            if (!navToggle) {
                return;
            }

            navToggle.addEventListener('click', function () {
                const nav = document.querySelector('.site-nav, .fashion-nav');
                if (nav) {
                    nav.classList.toggle('is-open');
                    navToggle.setAttribute('aria-expanded', nav.classList.contains('is-open'));
                }
            });
        },

        /**
         * Search toggle for header.
         */
        initSearchToggle: function () {
            const searchBtn = document.querySelector('.header-search-toggle');
            if (!searchBtn) {
                return;
            }

            searchBtn.addEventListener('click', function () {
                const searchForm = document.querySelector('.header-search');
                if (searchForm) {
                    searchForm.classList.toggle('is-visible');
                    if (searchForm.classList.contains('is-visible')) {
                        const input = searchForm.querySelector('input');
                        if (input) {
                            input.focus();
                        }
                    }
                }
            });
        },

        /**
         * Sticky header behavior.
         */
        initStickyHeader: function () {
            const header = document.querySelector('.site-header, .fashion-header');
            if (!header) {
                return;
            }

            let lastScroll = 0;
            const threshold = 100;

            window.addEventListener('scroll', function () {
                const currentScroll = window.pageYOffset;

                if (currentScroll > threshold && currentScroll > lastScroll) {
                    // Scrolling down — hide header.
                    header.style.transform = 'translateY(-100%)';
                } else {
                    // Scrolling up or at top — show header.
                    header.style.transform = 'translateY(0)';
                }

                lastScroll = currentScroll;
            }, { passive: true });
        },

        /**
         * Announce bar dismiss.
         */
        initAnnouncementBar: function () {
            const bar = document.querySelector('.announcement-bar');
            if (!bar) {
                return;
            }

            // Check if dismissed in session.
            if (sessionStorage.getItem('announcement_dismissed') === '1') {
                bar.style.display = 'none';
                return;
            }

            // No close button in Phase 0, but structure for future.
            const closeBtn = bar.querySelector('.announcement-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    bar.style.display = 'none';
                    sessionStorage.setItem('announcement_dismissed', '1');
                });
            }
        },

        /**
         * Mobile filter drawer for shop archive.
         */
        initFilterDrawer: function () {
            const toggleBtn = document.querySelector('.filter-toggle-btn .wp-element-button');
            const sidebar = document.querySelector('.shop-sidebar');
            if (!toggleBtn || !sidebar) {
                return;
            }

            // Create overlay element.
            const overlay = document.createElement('div');
            overlay.className = 'filter-overlay';
            document.body.appendChild(overlay);

            function openDrawer() {
                sidebar.classList.add('is-open');
                overlay.classList.add('is-active');
                document.body.style.overflow = 'hidden';
            }

            function closeDrawer() {
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-active');
                document.body.style.overflow = '';
            }

            toggleBtn.addEventListener('click', openDrawer);
            overlay.addEventListener('click', closeDrawer);

            // Close on Escape key.
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
                    closeDrawer();
                }
            });

            // Close drawer when clicking the ::before pseudo-element area (top-right close).
            sidebar.addEventListener('click', function (e) {
                const rect = sidebar.getBoundingClientRect();
                const clickX = e.clientX - rect.right + 40;
                const clickY = e.clientY - rect.top;
                if (clickX >= 0 && clickX <= 40 && clickY >= 0 && clickY <= 50) {
                    closeDrawer();
                }
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            commerceTheme.init();
        });
    } else {
        commerceTheme.init();
    }
})();
