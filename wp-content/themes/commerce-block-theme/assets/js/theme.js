/**
 * Commerce Block Theme — Frontend JavaScript
 * Mobile navigation, accessibility enhancements, UI interactions.
 */
(function () {
    'use strict';

    var commerceTheme = {
        init: function () {
            this.initMobileNav();
            this.initSearchToggle();
            this.initStickyHeader();
            this.initAnnouncementBar();
        },

        /**
         * Mobile navigation toggle.
         */
        initMobileNav: function () {
            var navToggle = document.querySelector('.wp-block-navigation__toggle');
            if (!navToggle) {
                return;
            }

            navToggle.addEventListener('click', function () {
                var nav = document.querySelector('.site-nav, .fashion-nav');
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
            var searchBtn = document.querySelector('.header-search-toggle');
            if (!searchBtn) {
                return;
            }

            searchBtn.addEventListener('click', function () {
                var searchForm = document.querySelector('.header-search');
                if (searchForm) {
                    searchForm.classList.toggle('is-visible');
                    if (searchForm.classList.contains('is-visible')) {
                        var input = searchForm.querySelector('input');
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
            var header = document.querySelector('.site-header, .fashion-header');
            if (!header) {
                return;
            }

            var lastScroll = 0;
            var threshold = 100;

            window.addEventListener('scroll', function () {
                var currentScroll = window.pageYOffset;

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
            var bar = document.querySelector('.announcement-bar');
            if (!bar) {
                return;
            }

            // Check if dismissed in session.
            if (sessionStorage.getItem('announcement_dismissed') === '1') {
                bar.style.display = 'none';
                return;
            }

            // No close button in Phase 0, but structure for future.
            var closeBtn = bar.querySelector('.announcement-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    bar.style.display = 'none';
                    sessionStorage.setItem('announcement_dismissed', '1');
                });
            }
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
