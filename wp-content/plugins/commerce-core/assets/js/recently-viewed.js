/**
 * Commerce Core — Recently Viewed Tracking
 * Tracks product views via REST API.
 */
(function () {
    'use strict';

    if (typeof commerceRecentlyViewed === 'undefined') {
        return;
    }

    var data = commerceRecentlyViewed;

    if (!data.productId) {
        return;
    }

    // Track view via REST API (fire and forget).
    fetch(data.restUrl + '/recently-viewed/track', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': data.nonce
        },
        body: JSON.stringify({ product_id: data.productId })
    }).catch(function (err) {
        if (window.console) {
            console.error('Recently viewed tracking error:', err);
        }
    });
})();
