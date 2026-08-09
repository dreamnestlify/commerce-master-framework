<?php
/**
 * Plugin Name: Zalandy GDPR Cookie Consent
 * Description: GDPR + CCPA compliant cookie consent banner for zalandy.top
 * Version:     1.0.0
 * Author:      Zalandy
 *
 * @package Zalandy_GDPR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ── Configuration ──────────────────────────────────────────────
 */
define( 'ZALANDY_COOKIE_VERSION', '1.0.0' );
define( 'ZALANDY_COOKIE_POLICY_URL', '/cookie-policy/' );
define( 'ZALANDY_PRIVACY_URL', '/privacy-policy/' );

/**
 * Enqueue banner CSS + JS on the front end only.
 */
function zalandy_cookie_enqueue_assets() {
	if ( is_admin() ) {
		return;
	}

	$css = zalandy_cookie_get_css();
	$js  = zalandy_cookie_get_js();

	wp_register_style( 'zalandy-cookie', false, array(), ZALANDY_COOKIE_VERSION );
	wp_enqueue_style( 'zalandy-cookie' );
	wp_add_inline_style( 'zalandy-cookie', $css );

	wp_register_script( 'zalandy-cookie', false, array(), ZALANDY_COOKIE_VERSION, true );
	wp_enqueue_script( 'zalandy-cookie' );
	wp_add_inline_script( 'zalandy-cookie', $js );
}
add_action( 'wp_enqueue_scripts', 'zalandy_cookie_enqueue_assets' );

/**
 * Inject the banner HTML via wp_footer.
 */
function zalandy_cookie_banner_html() {
	if ( is_admin() ) {
		return;
	}

	$policy_url  = esc_url( home_url( ZALANDY_COOKIE_POLICY_URL ) );
	$privacy_url = esc_url( home_url( ZALANDY_PRIVACY_URL ) );
	?>
	<!-- Zalandy GDPR Cookie Consent Banner -->
	<div id="zalandy-cookie-overlay" class="zalandy-cookie-overlay" style="display:none;" role="dialog" aria-modal="true" aria-label="Cookie consent">
		<div class="zalandy-cookie-banner">
			<div class="zalandy-cookie-banner__body">
				<div class="zalandy-cookie-banner__icon" aria-hidden="true">
					<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 2a10 10 0 1 0 10 10c0-.46-.04-.92-.1-1.36a5.39 5.39 0 0 1-4.51-5.39A5.36 5.36 0 0 1 12.36 2.1C12.24 2.04 12.12 2 12 2z"/>
						<path d="M15.5 8.5l.5.5M9.5 8.5l-.5.5M12 13l.5.5M8 15l-.5.5"/>
					</svg>
				</div>
				<div class="zalandy-cookie-banner__text">
					<h3>We value your privacy</h3>
					<p>
						We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic.
						By clicking "Accept All", you consent to our use of cookies.
						<a href="<?php echo $policy_url; ?>">Cookie Policy</a>
					</p>
				</div>
			</div>

			<!-- Preferences panel (hidden by default) -->
			<div id="zalandy-cookie-prefs" class="zalandy-cookie-prefs" style="display:none;">
				<div class="zalandy-cookie-prefs__category">
					<label class="zalandy-cookie-prefs__label">
						<input type="checkbox" checked disabled />
						<span class="zalandy-cookie-prefs__name">Necessary</span>
						<span class="zalandy-cookie-prefs__desc">Essential for the website to function. Cannot be disabled.</span>
					</label>
				</div>
				<div class="zalandy-cookie-prefs__category">
					<label class="zalandy-cookie-prefs__label">
						<input type="checkbox" name="analytics" id="zalandy-cookie-analytics" />
						<span class="zalandy-cookie-prefs__name">Analytics</span>
						<span class="zalandy-cookie-prefs__desc">Help us understand how visitors interact with our website (Google Analytics).</span>
					</label>
				</div>
				<div class="zalandy-cookie-prefs__category">
					<label class="zalandy-cookie-prefs__label">
						<input type="checkbox" name="marketing" id="zalandy-cookie-marketing" />
						<span class="zalandy-cookie-prefs__name">Marketing</span>
						<span class="zalandy-cookie-prefs__desc">Used to display personalized advertisements (Meta Pixel, Google Ads).</span>
					</label>
				</div>
			</div>

			<div class="zalandy-cookie-banner__buttons">
				<button type="button" class="zalandy-cookie-btn zalandy-cookie-btn--secondary" id="zalandy-cookie-prefs-toggle">
					Customize
				</button>
				<button type="button" class="zalandy-cookie-btn zalandy-cookie-btn--secondary" id="zalandy-cookie-reject">
					Reject All
				</button>
				<button type="button" class="zalandy-cookie-btn zalandy-cookie-btn--primary" id="zalandy-cookie-accept">
					Accept All
				</button>
			</div>
		</div>
	</div>

	<!-- CCPA Do Not Sell link (floating, bottom-left) -->
	<div id="zalandy-ccpa-link" class="zalandy-ccpa-link" style="display:none;">
		<button type="button" id="zalandy-ccpa-btn">Do Not Sell My Personal Information</button>
	</div>

	<!-- CCPA opt-out modal -->
	<div id="zalandy-ccpa-modal" class="zalandy-cookie-overlay" style="display:none;" role="dialog" aria-modal="true" aria-label="CCPA opt-out">
		<div class="zalandy-cookie-banner zalandy-ccpa-modal">
			<h3>Your Privacy Choices</h3>
			<p>
				Under the California Consumer Privacy Act (CCPA), you have the right to opt out of the "sale" or "sharing" of your personal information.
			</p>
			<div class="zalandy-ccpa-modal__option">
				<label>
					<input type="checkbox" id="zalandy-ccpa-optout" />
					<span>Opt out of the sale/sharing of my personal information</span>
				</label>
			</div>
			<div class="zalandy-cookie-banner__buttons">
				<button type="button" class="zalandy-cookie-btn zalandy-cookie-btn--primary" id="zalandy-ccpa-save">Save</button>
				<button type="button" class="zalandy-cookie-btn zalandy-cookie-btn--secondary" id="zalandy-ccpa-close">Close</button>
			</div>
			<p class="zalandy-ccpa-modal__footer">
				<a href="<?php echo $privacy_url; ?>">Privacy Policy</a>
			</p>
		</div>
	</div>
	<!-- /Zalandy GDPR Cookie Consent -->
	<?php
}
add_action( 'wp_footer', 'zalandy_cookie_banner_html' );

/**
 * Inject script-blocking logic into <head>.
 * This outputs before analytics/marketing scripts so they can be gated.
 */
function zalandy_cookie_head_script() {
	if ( is_admin() ) {
		return;
	}
	?>
	<script>
	// Zalandy Cookie Consent — head bootstrap
	window.zalandyConsent = window.zalandyConsent || {};

	// Read stored consent
	(function() {
		var raw = null;
		try { raw = localStorage.getItem('zalandy_cookie_consent'); } catch(e) {}
		if (raw) {
			try {
				window.zalandyConsent = JSON.parse(raw);
			} catch(e) {
				window.zalandyConsent = {};
			}
		}
	})();

	// Helper: check if a category has consent
	window.zalandyHasConsent = function(category) {
		if (category === 'necessary') return true;
		return !!(window.zalandyConsent && window.zalandyConsent[category] === true);
	};

	// Queue for scripts waiting for consent
	window.zalandyScriptQueue = window.zalandyScriptQueue || [];

	// Execute queued scripts for a category
	window.zalandyExecuteQueue = function(category) {
		window.zalandyScriptQueue = window.zalandyScriptQueue.filter(function(item) {
			if (item.category === category || window.zalandyHasConsent(item.category)) {
				item.fn();
				return false; // remove from queue
			}
			return true;
		});
	};

	// Public API: run code when consent is given for a category
	window.zalandyOnConsent = function(category, fn) {
		if (window.zalandyHasConsent(category)) {
			fn();
		} else {
			window.zalandyScriptQueue.push({ category: category, fn: fn });
		}
	};
	</script>
	<?php
}
add_action( 'wp_head', 'zalandy_cookie_head_script', 1 );

/**
 * ── CSS ────────────────────────────────────────────────────────
 */
function zalandy_cookie_get_css() {
	return <<<'CSS'
/* Zalandy Cookie Consent — Brand colors: gold #c9a96e */
.zalandy-cookie-overlay {
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	z-index: 999999;
	background: rgba(0, 0, 0, 0.5);
	display: flex;
	align-items: flex-end;
	justify-content: center;
	padding: 0;
	animation: zalandy-fade-in 0.3s ease;
}
.zalandy-cookie-overlay[style*="display:none"] {
	animation: none;
}
@keyframes zalandy-fade-in {
	from { opacity: 0; }
	to { opacity: 1; }
}
.zalandy-cookie-banner {
	background: #fff;
	border-top: 3px solid #c9a96e;
	border-radius: 0;
	width: 100%;
	max-width: 720px;
	padding: 24px 28px;
	box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.12);
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
.zalandy-ccpa-modal {
	max-width: 520px;
	border-radius: 8px;
	border-top: 3px solid #c9a96e;
	padding: 32px;
}
.zalandy-cookie-banner__body {
	display: flex;
	gap: 16px;
	align-items: flex-start;
	margin-bottom: 16px;
}
.zalandy-cookie-banner__icon {
	color: #c9a96e;
	flex-shrink: 0;
	margin-top: 2px;
}
.zalandy-cookie-banner__text h3 {
	font-size: 18px;
	font-weight: 600;
	color: #1a1a1a;
	margin: 0 0 8px;
}
.zalandy-cookie-banner__text p {
	font-size: 14px;
	line-height: 1.6;
	color: #555;
	margin: 0;
}
.zalandy-cookie-banner__text a {
	color: #c9a96e;
	text-decoration: underline;
}
.zalandy-cookie-banner__text a:hover {
	color: #b8965a;
}
.zalandy-cookie-banner__buttons {
	display: flex;
	gap: 10px;
	flex-wrap: wrap;
	justify-content: flex-end;
}
.zalandy-cookie-btn {
	padding: 10px 22px;
	font-size: 14px;
	font-weight: 600;
	border-radius: 4px;
	border: 1px solid transparent;
	cursor: pointer;
	transition: all 0.2s ease;
	font-family: inherit;
}
.zalandy-cookie-btn--primary {
	background: #c9a96e;
	color: #fff;
	border-color: #c9a96e;
}
.zalandy-cookie-btn--primary:hover {
	background: #b8965a;
	border-color: #b8965a;
}
.zalandy-cookie-btn--secondary {
	background: transparent;
	color: #666;
	border-color: #ddd;
}
.zalandy-cookie-btn--secondary:hover {
	color: #333;
	border-color: #bbb;
}
.zalandy-cookie-prefs {
	margin-bottom: 16px;
	padding: 16px;
	background: #f9f8f6;
	border-radius: 6px;
	border: 1px solid #eee;
}
.zalandy-cookie-prefs__category {
	margin-bottom: 12px;
}
.zalandy-cookie-prefs__category:last-child {
	margin-bottom: 0;
}
.zalandy-cookie-prefs__label {
	display: flex;
	align-items: flex-start;
	gap: 10px;
	cursor: pointer;
}
.zalandy-cookie-prefs__label input[type="checkbox"] {
	margin-top: 3px;
	accent-color: #c9a96e;
	width: 18px;
	height: 18px;
	flex-shrink: 0;
}
.zalandy-cookie-prefs__label input[type="checkbox"]:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}
.zalandy-cookie-prefs__name {
	font-weight: 600;
	font-size: 14px;
	color: #1a1a1a;
	display: block;
}
.zalandy-cookie-prefs__desc {
	font-size: 13px;
	color: #888;
	display: block;
	margin-top: 2px;
}
.zalandy-ccpa-link {
	position: fixed;
	bottom: 12px;
	left: 12px;
	z-index: 999998;
}
.zalandy-ccpa-link button {
	background: rgba(255, 255, 255, 0.92);
	border: 1px solid #ddd;
	color: #666;
	font-size: 12px;
	padding: 6px 14px;
	border-radius: 4px;
	cursor: pointer;
	font-family: inherit;
	transition: all 0.2s ease;
}
.zalandy-ccpa-link button:hover {
	color: #c9a96e;
	border-color: #c9a96e;
}
.zalandy-ccpa-modal h3 {
	font-size: 20px;
	font-weight: 600;
	color: #1a1a1a;
	margin: 0 0 12px;
}
.zalandy-ccpa-modal p {
	font-size: 14px;
	line-height: 1.6;
	color: #555;
	margin-bottom: 16px;
}
.zalandy-ccpa-modal__option label {
	display: flex;
	align-items: center;
	gap: 10px;
	font-size: 14px;
	color: #333;
	cursor: pointer;
}
.zalandy-ccpa-modal__option input {
	accent-color: #c9a96e;
	width: 18px;
	height: 18px;
}
.zalandy-ccpa-modal__buttons {
	margin-top: 20px;
}
.zalandy-ccpa-modal__footer {
	margin-top: 16px !important;
	text-align: center;
}
.zalandy-ccpa-modal__footer a {
	color: #c9a96e;
	font-size: 13px;
	text-decoration: underline;
}

/* Responsive */
@media (max-width: 600px) {
	.zalandy-cookie-banner {
		padding: 18px 16px;
	}
	.zalandy-cookie-banner__body {
		flex-direction: column;
		gap: 10px;
	}
	.zalandy-cookie-banner__buttons {
		flex-direction: column-reverse;
	}
	.zalandy-cookie-btn {
		width: 100%;
		text-align: center;
	}
	.zalandy-ccpa-link button {
		font-size: 11px;
		padding: 5px 10px;
	}
}
CSS;
}

/**
 * ── JavaScript ─────────────────────────────────────────────────
 */
function zalandy_cookie_get_js() {
	return <<<'JS'
(function() {
	'use strict';

	var overlay = document.getElementById('zalandy-cookie-overlay');
	var prefsPanel = document.getElementById('zalandy-cookie-prefs');
	var prefsToggle = document.getElementById('zalandy-cookie-prefs-toggle');
	var acceptBtn = document.getElementById('zalandy-cookie-accept');
	var rejectBtn = document.getElementById('zalandy-cookie-reject');
	var ccpaLink = document.getElementById('zalandy-ccpa-link');
	var ccpaModal = document.getElementById('zalandy-ccpa-modal');
	var ccpaBtn = document.getElementById('zalandy-ccpa-btn');
	var ccpaClose = document.getElementById('zalandy-ccpa-close');
	var ccpaSave = document.getElementById('zalandy-ccpa-save');
	var ccpaOptout = document.getElementById('zalandy-ccpa-optout');

	if (!overlay) return;

	// Check if consent already given
	var hasConsent = false;
	try {
		hasConsent = !!localStorage.getItem('zalandy_cookie_consent');
	} catch(e) {}

	// Show banner if no consent stored
	if (!hasConsent) {
		overlay.style.display = 'flex';
	}

	// Check CCPA opt-out status
	var ccpaOptedOut = false;
	try {
		ccpaOptedOut = localStorage.getItem('zalandy_ccpa_optout') === 'true';
	} catch(e) {}
	if (ccpaOptedOut) {
		ccpaOptout.checked = true;
	}

	// Always show CCPA link (California residents)
	ccpaLink.style.display = 'block';

	// Save consent
	function saveConsent(consent) {
		try {
			localStorage.setItem('zalandy_cookie_consent', JSON.stringify(consent));
		} catch(e) {}

		// Also set a cookie for server-side detection
		var d = new Date();
		d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000);
		document.cookie = 'zalandy_consent=' + encodeURIComponent(JSON.stringify(consent)) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax;Secure';

		// Update global state
		window.zalandyConsent = consent;

		// Execute queued scripts
		if (consent.analytics) {
			window.zalandyExecuteQueue('analytics');
		}
		if (consent.marketing) {
			window.zalandyExecuteQueue('marketing');
		}

		// Hide banner
		overlay.style.display = 'none';
	}

	// Accept all
	acceptBtn.addEventListener('click', function() {
		saveConsent({
			necessary: true,
			analytics: true,
			marketing: true,
			timestamp: Date.now()
		});
	});

	// Reject all
	rejectBtn.addEventListener('click', function() {
		saveConsent({
			necessary: true,
			analytics: false,
			marketing: false,
			timestamp: Date.now()
		});
	});

	// Toggle preferences panel
	prefsToggle.addEventListener('click', function() {
		if (prefsPanel.style.display === 'none') {
			prefsPanel.style.display = 'block';
			prefsToggle.textContent = 'Save Preferences';
			// Change button behavior to save preferences
			prefsToggle.onclick = function() {
				var analytics = document.getElementById('zalandy-cookie-analytics').checked;
				var marketing = document.getElementById('zalandy-cookie-marketing').checked;
				saveConsent({
					necessary: true,
					analytics: analytics,
					marketing: marketing,
					timestamp: Date.now()
				});
			};
		}
	});

	// CCPA modal
	ccpaBtn.addEventListener('click', function() {
		ccpaModal.style.display = 'flex';
	});

	ccpaClose.addEventListener('click', function() {
		ccpaModal.style.display = 'none';
	});

	ccpaSave.addEventListener('click', function() {
		var optedOut = ccpaOptout.checked;
		try {
			localStorage.setItem('zalandy_ccpa_optout', String(optedOut));
		} catch(e) {}
		var d = new Date();
		d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000);
		document.cookie = 'zalandy_ccpa_optout=' + optedOut + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax;Secure';
		ccpaModal.style.display = 'none';

		// If opting out, disable marketing
		if (optedOut) {
			window.zalandyConsent.marketing = false;
		}
	});

	// Close modal when clicking overlay
	ccpaModal.addEventListener('click', function(e) {
		if (e.target === ccpaModal) {
			ccpaModal.style.display = 'none';
		}
	});

	// Re-open consent banner from anywhere
	window.zalandyReopenConsent = function() {
		overlay.style.display = 'flex';
	};
})();
JS;
}

/**
 * ── REST API endpoint for server-side consent detection ────────
 * Allows server-side code to check consent status.
 */
function zalandy_cookie_rest_init() {
	register_rest_route(
		'zalandy/v1',
		'/cookie-consent',
		array(
			'methods'             => 'GET',
			'callback'            => function () {
				$consent = isset( $_COOKIE['zalandy_consent'] ) ? json_decode( stripslashes( $_COOKIE['zalandy_consent'] ), true ) : null;
				return array(
					'necessary' => true,
					'analytics' => $consent && isset( $consent['analytics'] ) ? (bool) $consent['analytics'] : false,
					'marketing' => $consent && isset( $consent['marketing'] ) ? (bool) $consent['marketing'] : false,
				);
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'zalandy_cookie_rest_init' );

/**
 * ── WooCommerce: disable marketing cookies until consent ──────
 * Prevents WooCommerce from setting analytics/tracking cookies
 * until the user has given explicit consent.
 */
function zalandy_cookie_gate_woocommerce_tracking() {
	$consent = isset( $_COOKIE['zalandy_consent'] ) ? json_decode( stripslashes( $_COOKIE['zalandy_consent'] ), true ) : null;
	$has_analytics_consent = $consent && isset( $consent['analytics'] ) && $consent['analytics'];

	if ( ! $has_analytics_consent ) {
		// Disable WooCommerce tracking
		add_filter( 'woocommerce_allow_tracking', '__return_false' );
	}
}
add_action( 'init', 'zalandy_cookie_gate_woocommerce_tracking', 1 );

/**
 * ── Helper: Output Google Analytics 4 with consent gating ─────
 * Usage in theme: do_action( 'zalandy_analytics', 'G-XXXXXXXXXX' );
 */
function zalandy_cookie_output_ga4( $measurement_id ) {
	if ( empty( $measurement_id ) ) {
		return;
	}
	?>
	<script>
	window.zalandyOnConsent('analytics', function() {
		var s = document.createElement('script');
		s.async = true;
		s.src = 'https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js( $measurement_id ); ?>';
		document.head.appendChild(s);
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', '<?php echo esc_js( $measurement_id ); ?>', { anonymize_ip: true });
	});
	</script>
	<?php
}
add_action( 'zalandy_analytics', 'zalandy_cookie_output_ga4' );

/**
 * ── Helper: Output Meta (Facebook) Pixel with consent gating ──
 * Usage in theme: do_action( 'zalandy_pixel', '1234567890' );
 */
function zalandy_cookie_output_pixel( $pixel_id ) {
	if ( empty( $pixel_id ) ) {
		return;
	}
	?>
	<script>
	window.zalandyOnConsent('marketing', function() {
		!function(f,b,e,v,n,t,s)
		{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};
		if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
		n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t,s)}(window, document,'script',
		'https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '<?php echo esc_js( $pixel_id ); ?>');
		fbq('track', 'PageView');
	});
	</script>
	<?php
}
add_action( 'zalandy_pixel', 'zalandy_cookie_output_pixel' );
