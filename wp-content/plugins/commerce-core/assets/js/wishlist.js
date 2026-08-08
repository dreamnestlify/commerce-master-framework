/**
 * Commerce Core — Wishlist Frontend JavaScript
 * Handles add/remove on product pages and cards.
 */
(function () {
    'use strict';

    if (typeof commerceWishlist === 'undefined') {
        return;
    }

    var restUrl = commerceWishlist.restUrl;
    var nonce = commerceWishlist.nonce;
    var wishlist = commerceWishlist.wishlist || [];
    var labels = commerceWishlist.labels || {};

    function isInWishlist(productId) {
        return wishlist.indexOf(parseInt(productId, 10)) !== -1;
    }

    function toggleWishlist(productId, btn) {
        var action = isInWishlist(productId) ? 'remove' : 'add';

        fetch(restUrl + '/wishlist/' + action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce
            },
            body: JSON.stringify({ product_id: parseInt(productId, 10) })
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    if (action === 'add') {
                        wishlist.push(parseInt(productId, 10));
                        if (btn) {
                            btn.classList.add('is-active');
                            btn.textContent = '♥ Wishlisted';
                        }
                    } else {
                        var idx = wishlist.indexOf(parseInt(productId, 10));
                        if (idx !== -1) {
                            wishlist.splice(idx, 1);
                        }
                        if (btn) {
                            btn.classList.remove('is-active');
                            btn.textContent = '♡ Add to Wishlist';
                        }
                    }
                    updateCountBadge(data.count);
                }
            })
            .catch(function (err) {
                if (window.console) {
                    console.error('Wishlist error:', err);
                }
            });
    }

    function updateCountBadge(count) {
        var badges = document.querySelectorAll('.wishlist-count-badge');
        badges.forEach(function (badge) {
            badge.textContent = count > 0 ? count : '';
            badge.style.display = count > 0 ? 'flex' : 'none';
        });
    }

    function initButtons() {
        // Single product page buttons.
        var buttons = document.querySelectorAll('.commerce-wishlist-btn');
        buttons.forEach(function (btn) {
            var productId = btn.getAttribute('data-product-id');
            if (isInWishlist(productId)) {
                btn.classList.add('is-active');
                btn.textContent = '♥ Wishlisted';
            }
            btn.addEventListener('click', function () {
                toggleWishlist(productId, btn);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initButtons);
    } else {
        initButtons();
    }
})();
