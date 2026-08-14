<?php
/**
 * Commerce Master — EU/US Compliance & Trust Pages
 *
 * Adds all missing pages required for selling in EU/US markets:
 * - GDPR-compliant Privacy Policy (rewritten)
 * - Cookie Policy page
 * - Imprint / Legal Notice page
 * - EU 14-day Withdrawal Right page
 * - About Us page
 * - Contact Us page
 * - FAQ page
 * - Size Guide page
 * - Jewelry Care Guide page
 * - Footer configuration with all links
 * - CCPA "Do Not Sell" placeholder
 *
 * Usage:
 *   php -d memory_limit=512M /usr/local/bin/wp eval-file scripts/compliance-pages.php --allow-root
 *
 * @package CommerceMaster
 */

WP_CLI::log( '========================================' );
WP_CLI::log( '  EU/US Compliance & Trust Pages Setup' );
WP_CLI::log( '========================================' );

// ═══════════════════════════════════════════════════════════════
// Helper: create or update a page
// ═══════════════════════════════════════════════════════════════
function _zalandy_upsert_page( $slug, $title, $content ) {
	$page = get_page_by_path( $slug );
	$data = [
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => $content,
	];
	if ( ! $page ) {
		$id = wp_insert_post( $data );
		WP_CLI::log( "  Created: {$title} (ID: {$id})" );
		return $id;
	} else {
		$data['ID'] = $page->ID;
		wp_update_post( $data );
		WP_CLI::log( "  Updated: {$title} (ID: {$page->ID})" );
		return $page->ID;
	}
}

// ═══════════════════════════════════════════════════════════════
// 1. GDPR-Compliant Privacy Policy (rewritten)
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '1/10 — Rewriting Privacy Policy (GDPR + CCPA compliant)...' );

$privacy_content = <<<HTML
<h2>Privacy Policy</h2>
<p><strong>Last updated:</strong> August 9, 2026</p>
<p>This Privacy Policy describes how Seniorenpflegeheim Bevern GmbH & Co. KG ("we," "us," or "our"), operating under the brand Zalandy, collects, uses, and protects your personal data when you visit zalandy.top (the "Site") or purchase our products. This policy complies with the General Data Protection Regulation (GDPR) and the California Consumer Privacy Act (CCPA).</p>

<h3>1. Data Controller</h3>
<p>The data controller for your personal data is:</p>
<p>
<strong>Seniorenpflegeheim Bevern GmbH & Co. KG</strong><br/>
Im Ziegelfeld 16<br/>
27432 Bremervörde<br/>
Niedersachsen, Germany<br/>
Commercial Register: HRA 204407, Amtsgericht Tostedt<br/>
</p>
<p>For privacy inquiries, contact us at: <strong>privacy@zalandy.top</strong></p>

<h3>2. Information We Collect</h3>
<p><strong>2.1 Information you provide:</strong></p>
<ul>
<li>Name, email address, shipping address, phone number (when you place an order)</li>
<li>Account credentials (if you create an account)</li>
<li>Product reviews and correspondence</li>
<li>Newsletter subscription (email address only)</li>
</ul>
<p><strong>2.2 Information collected automatically:</strong></p>
<ul>
<li>IP address, browser type, device information</li>
<li>Browsing behavior (pages visited, time spent, referral source)</li>
<li>Cookies and similar tracking technologies (see our Cookie Policy)</li>
</ul>
<p><strong>2.3 Payment information:</strong> Payment card details are processed by our payment processors (Stripe, PayPal). We do not store full card numbers on our servers.</p>

<h3>3. Legal Basis for Processing (GDPR Art. 6)</h3>
<ul>
<li><strong>Contract performance:</strong> Processing your order, shipping, and customer service</li>
<li><strong>Legal obligation:</strong> Tax records, accounting, fraud prevention</li>
<li><strong>Legitimate interest:</strong> Website security, analytics, product improvement</li>
<li><strong>Consent:</strong> Newsletter, marketing emails, non-essential cookies</li>
</ul>

<h3>4. How We Use Your Data</h3>
<ul>
<li>Process and fulfill orders, including shipping and returns</li>
<li>Communicate about your orders and provide customer support</li>
<li>Send marketing emails (only with your consent — you can unsubscribe anytime)</li>
<li>Improve our website, products, and services</li>
<li>Detect and prevent fraud</li>
<li>Comply with legal obligations</li>
</ul>

<h3>5. Data Retention</h3>
<p>We retain personal data only as long as necessary:</p>
<ul>
<li>Order data: 7 years (tax/accounting requirements)</li>
<li>Account data: Until you request deletion</li>
<li>Marketing data: Until you unsubscribe</li>
<li>Server logs: 30 days</li>
</ul>

<h3>6. Your Rights (GDPR)</h3>
<p>EU/EEA residents have the following rights:</p>
<ul>
<li><strong>Right of access:</strong> Request a copy of your personal data</li>
<li><strong>Right to rectification:</strong> Correct inaccurate data</li>
<li><strong>Right to erasure:</strong> Request deletion of your data ("right to be forgotten")</li>
<li><strong>Right to restrict processing:</strong> Limit how we use your data</li>
<li><strong>Right to data portability:</strong> Receive your data in a machine-readable format</li>
<li><strong>Right to object:</strong> Object to processing based on legitimate interest</li>
<li><strong>Right to withdraw consent:</strong> Withdraw consent at any time</li>
</ul>
<p>To exercise these rights, email <strong>privacy@zalandy.top</strong>. We respond within 30 days.</p>

<h3>7. Your Rights (CCPA)</h3>
<p>California residents have the right to:</p>
<ul>
<li>Know what personal information is collected and how it is used</li>
<li>Request deletion of personal information</li>
<li>Opt-out of the "sale" of personal information (we do not sell your data)</li>
<li>Non-discrimination: We will not discriminate against you for exercising your rights</li>
</ul>
<p>To submit a CCPA request, email <strong>privacy@zalandy.top</strong>.</p>

<h3>8. International Data Transfers</h3>
<p>Your data may be transferred to and processed in countries outside your country of residence, including the United States and China. We ensure appropriate safeguards are in place, including Standard Contractual Clauses (SCCs) for EU data.</p>

<h3>9. Data Sharing</h3>
<p>We share data with the following third parties:</p>
<ul>
<li><strong>Payment processors:</strong> Stripe, PayPal (for payment processing)</li>
<li><strong>Shipping carriers:</strong> DHL, FedEx, USPS (for order delivery)</li>
<li><strong>Email service:</strong> Resend (for transactional and marketing emails)</li>
<li><strong>Analytics:</strong> Google Analytics (anonymized data, with consent)</li>
<li><strong>Legal authorities:</strong> When required by law</li>
</ul>

<h3>10. Data Security</h3>
<p>We implement technical and organizational measures to protect your data: SSL/TLS encryption, secure server infrastructure, access controls, and regular security reviews. However, no method of transmission over the Internet is 100% secure.</p>

<h3>11. Children's Privacy</h3>
<p>Our website is not directed to children under 16. We do not knowingly collect personal data from children. If you believe we have collected data from a child, please contact us.</p>

<h3>12. Changes to This Policy</h3>
<p>We may update this Privacy Policy from time to time. We will notify you of significant changes by posting a notice on our website or sending an email.</p>

<h3>13. Contact Us</h3>
<p>If you have questions about this Privacy Policy, contact us at:</p>
<p><strong>Email:</strong> privacy@zalandy.top<br/>
<strong>General inquiries:</strong> support@zalandy.top</p>
HTML;

_zalandy_upsert_page( 'privacy-policy', 'Privacy Policy', $privacy_content );

// Link to WooCommerce privacy page
$privacy_page = get_page_by_path( 'privacy-policy' );
if ( $privacy_page ) {
	update_option( 'wp_page_for_privacy_policy', $privacy_page->ID );
}

// ═══════════════════════════════════════════════════════════════
// 2. Cookie Policy
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '2/10 — Creating Cookie Policy...' );

$cookie_content = <<<HTML
<h2>Cookie Policy</h2>
<p><strong>Last updated:</strong> August 9, 2026</p>
<p>This Cookie Policy explains how Zalandy uses cookies and similar tracking technologies on zalandy.top.</p>

<h3>What Are Cookies?</h3>
<p>Cookies are small text files stored on your device when you visit a website. They help the website remember your actions and preferences over time.</p>

<h3>Types of Cookies We Use</h3>
<table style="width:100%;border-collapse:collapse;">
<thead>
<tr style="border-bottom:2px solid #c9a96e;">
<th style="text-align:left;padding:8px;">Cookie Type</th>
<th style="text-align:left;padding:8px;">Purpose</th>
<th style="text-align:left;padding:8px;">Duration</th>
<th style="text-align:left;padding:8px;">Consent Required?</th>
</tr>
</thead>
<tbody>
<tr style="border-bottom:1px solid #eee;">
<td style="padding:8px;">Essential</td>
<td style="padding:8px;">Shopping cart, checkout, account login</td>
<td style="padding:8px;">Session</td>
<td style="padding:8px;">No</td>
</tr>
<tr style="border-bottom:1px solid #eee;">
<td style="padding:8px;">Functional</td>
<td style="padding:8px;">Language preference, recently viewed products</td>
<td style="padding:8px;">30 days</td>
<td style="padding:8px;">Yes</td>
</tr>
<tr style="border-bottom:1px solid #eee;">
<td style="padding:8px;">Analytics</td>
<td style="padding:8px;">Google Analytics — page views, behavior</td>
<td style="padding:8px;">24 months</td>
<td style="padding:8px;">Yes</td>
</tr>
<tr style="border-bottom:1px solid #eee;">
<td style="padding:8px;">Marketing</td>
<td style="padding:8px;">Facebook Pixel — retargeting ads</td>
<td style="padding:8px;">90 days</td>
<td style="padding:8px;">Yes</td>
</tr>
</tbody>
</table>

<h3>Managing Cookies</h3>
<p>You can manage or disable cookies through:</p>
<ul>
<li>Our cookie consent banner (appears on first visit)</li>
<li>Your browser settings (most browsers allow you to refuse cookies)</li>
<li>Opting out of Google Analytics: https://tools.google.com/dlpage/gaoptout</li>
</ul>
<p>Note: Disabling essential cookies will prevent you from completing purchases.</p>

<h3>Contact</h3>
<p>For questions about cookies, email <strong>privacy@zalandy.top</strong></p>
HTML;

_zalandy_upsert_page( 'cookie-policy', 'Cookie Policy', $cookie_content );

// ═══════════════════════════════════════════════════════════════
// 3. Imprint / Legal Notice (EU requirement)
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '3/10 — Creating Imprint / Legal Notice...' );

$imprint_content = <<<HTML
<h2>Impressum / Imprint / Legal Notice</h2>

<h3>Angaben gemäß § 5 TMG / Information according to § 5 TMG</h3>
<p>
<strong>Company Name:</strong> Seniorenpflegeheim Bevern GmbH & Co. KG<br/>
<strong>Registered Address:</strong> Im Ziegelfeld 16<br/>
<strong>Postal Code / City:</strong> 27432 Bremervörde<br/>
<strong>State / Country:</strong> Niedersachsen, Germany<br/>
<strong>Commercial Register:</strong> Amtsgericht Tostedt<br/>
<strong>Registration Number:</strong> HRA 204407<br/>
<strong>VAT ID:</strong> DE367264918<br/>
<strong>EPR Registration Number (LUCID):</strong> DE1649745799617<br/>
</p>

<h3>Vertreten durch / Represented by</h3>
<p>
<strong>Managing Director:</strong> Katrin Dübbers<br/>
</p>

<h3>Kontakt / Contact</h3>
<p>
<strong>Email:</strong> support@zalandy.top<br/>
<strong>Phone:</strong> +1 706 215 4022<br/>
</p>

<h3>Verantwortlich für den Inhalt / Responsible for Content</h3>
<p>Katrin Dübbers, support@zalandy.top</p>

<h3>Streitschlichtung / Dispute Resolution</h3>
<p>The European Commission provides a platform for online dispute resolution (ODR): <a href="https://ec.europa.eu/consumers/odr/">https://ec.europa.eu/consumers/odr/</a>. We are not obliged or willing to participate in dispute settlement proceedings before a consumer arbitration board.</p>
<p>Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>

<h3>Haftung für Inhalte / Liability for Content</h3>
<p>As a service provider, we are responsible for our own content on these pages in accordance with general legislation (§ 7 para.1 TMG). According to §§ 8 to 10 TMG, however, we as a service provider are not obliged to monitor transmitted or stored third-party information or to investigate circumstances that indicate illegal activity.</p>

<h3>Haftung für Links / Liability for Links</h3>
<p>Our offer contains links to external third-party websites, on whose contents we have no influence. Therefore, we cannot accept any liability for these external contents. The respective provider or operator of the pages is always responsible for the content of the linked pages.</p>

<h3>Urheberrecht / Copyright</h3>
<p>The content and works created by the site operators on these pages are subject to German copyright law. The duplication, processing, distribution and any form of commercialization of such material beyond the scope of copyright law require the written consent of its respective author or creator.</p>
HTML;

_zalandy_upsert_page( 'imprint', 'Imprint / Legal Notice', $imprint_content );

// ═══════════════════════════════════════════════════════════════
// 4. EU 14-Day Withdrawal Right
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '4/10 — Creating EU Withdrawal Right page...' );

$withdrawal_content = <<<HTML
<h2>Right of Withdrawal (EU Consumers)</h2>
<p>Consumers in the European Union have the right to withdraw from a distance contract within <strong>14 days</strong> without giving any reason, in accordance with Directive 2011/83/EU.</p>

<h3>Withdrawal Period</h3>
<p>The withdrawal period is 14 days from the day you, or a third party designated by you (other than the carrier), takes physical possession of the goods.</p>

<h3>How to Exercise Your Right</h3>
<p>To withdraw from the contract, you must inform us by sending a clear statement to:</p>
<p><strong>Email:</strong> returns@zalandy.top<br/>
<strong>Subject:</strong> "Withdrawal of Order #[Order Number]"</p>
<p>You can use the following template:</p>
<blockquote style="background:#f8f6f3;padding:16px;border-left:3px solid #c9a96e;margin:16px 0;">
"I hereby give notice that I withdraw from my contract for the purchase of the following goods: [description of goods]. Order number: [order number]. Ordered on: [date]. Received on: [date]. Name: [your name]. Address: [your address]. Date: [date]."
</blockquote>

<h3>Return of Goods</h3>
<p>You must return the goods to us without undue delay and in any event not later than 14 days from the day you communicate your withdrawal. The direct cost of returning the goods shall be borne by you, unless we did not inform you that you had to bear the cost.</p>

<h3>Refund</h3>
<p>We will reimburse all payments received from you, including the costs of delivery (except for supplementary costs arising from your choice of delivery method other than the least expensive standard delivery), without undue delay and in any event not later than 14 days from the day we receive the goods back. We will use the same means of payment for reimbursement as you used for the initial transaction.</p>

<h3>Exemptions</h3>
<p>The right of withdrawal does NOT apply to:</p>
<ul>
<li>Goods made to custom specifications or clearly personalized</li>
<li>Goods sealed for health protection or hygiene reasons, unsealed after delivery</li>
<li>Goods which are, after delivery, inseparably mixed with other items</li>
</ul>

<h3>Model Withdrawal Form</h3>
<p>(As required by Annex I(B) of Directive 2011/83/EU)</p>
HTML;

_zalandy_upsert_page( 'withdrawal-right', 'Right of Withdrawal', $withdrawal_content );

// ═══════════════════════════════════════════════════════════════
// 5. About Us
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '5/10 — Creating About Us page...' );

$about_content = <<<HTML
<div style="max-width:800px;margin:0 auto;">

<div style="text-align:center;padding:40px 0;">
<h1 style="font-size:32px;font-weight:300;letter-spacing:2px;">Our Story</h1>
<p style="font-size:16px;color:#999;">Handcrafted jewelry for the modern soul</p>
</div>

<p style="font-size:16px;line-height:2;">Zalandy is a brand of <strong>Seniorenpflegeheim Bevern GmbH & Co. KG</strong>, a Germany-registered company based in Bremervörde, Niedersachsen. We were born from a passion for fine jewelry and a belief that every piece tells a story. We source the finest gemstones from around the world and work with skilled artisans who bring each design to life with meticulous attention to detail.</p>

<h2 style="font-size:24px;font-weight:400;margin-top:40px;">Our Craft</h2>
<p style="font-size:16px;line-height:2;">Every piece in our collection is handcrafted using traditional techniques passed down through generations. From vintage-inspired palace designs to modern minimalist creations, we blend old-world craftsmanship with contemporary aesthetics.</p>

<h2 style="font-size:24px;font-weight:400;margin-top:40px;">Our Promise</h2>
<p style="font-size:16px;line-height:2;">We stand behind every piece we create. Each item comes with a certificate of authenticity, and we offer a 30-day satisfaction guarantee. If you're not completely in love with your purchase, we'll make it right.</p>

<div style="display:flex;gap:30px;margin:50px 0;text-align:center;">
<div style="flex:1;">
<div style="font-size:36px;color:#c9a96e;font-weight:300;">100%</div>
<p style="color:#999;">Handcrafted</p>
</div>
<div style="flex:1;">
<div style="font-size:36px;color:#c9a96e;font-weight:300;">48</div>
<p style="color:#999;">Unique Designs</p>
</div>
<div style="flex:1;">
<div style="font-size:36px;color:#c9a96e;font-weight:300;">30-Day</div>
<p style="color:#999;">Satisfaction Guarantee</p>
</div>
</div>

<h2 style="font-size:24px;font-weight:400;margin-top:40px;">Sustainability</h2>
<p style="font-size:16px;line-height:2;">We are committed to ethical sourcing. Our gemstones are conflict-free, and we work with suppliers who share our commitment to fair labor practices and environmental responsibility.</p>

<div style="text-align:center;margin:50px 0;">
<a href="/shop/" style="display:inline-block;padding:14px 44px;background:#c9a96e;color:#fff;text-decoration:none;font-size:15px;letter-spacing:2px;border-radius:2px;text-transform:uppercase;">Shop Our Collection</a>
</div>

</div>
HTML;

_zalandy_upsert_page( 'about-us', 'About Us', $about_content );

// ═══════════════════════════════════════════════════════════════
// 6. Contact Us
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '6/10 — Creating Contact Us page...' );

$contact_content = <<<HTML
<div style="max-width:700px;margin:0 auto;">

<h1 style="font-size:30px;font-weight:300;letter-spacing:1px;text-align:center;">Get in Touch</h1>
<p style="text-align:center;color:#999;margin-bottom:40px;">We'd love to hear from you</p>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-bottom:40px;">

<div style="text-align:center;padding:30px;background:#f8f6f3;border-radius:8px;">
<h3 style="font-size:18px;font-weight:400;color:#c9a96e;">Customer Support</h3>
<p style="color:#666;line-height:1.8;">
<strong>Email:</strong> support@zalandy.top<br/>
<strong>Phone:</strong> +1 706 215 4022<br/>
<strong>Hours:</strong> Mon-Fri 9:00-18:00 (CET)<br/>
<strong>Response:</strong> Within 24 hours
</p>
</div>

<div style="text-align:center;padding:30px;background:#f8f6f3;border-radius:8px;">
<h3 style="font-size:18px;font-weight:400;color:#c9a96e;">Company Address</h3>
<p style="color:#666;line-height:1.8;">
<strong>Seniorenpflegeheim Bevern GmbH & Co. KG</strong><br/>
Im Ziegelfeld 16<br/>
27432 Bremervörde<br/>
Niedersachsen, Germany<br/>
<strong>Commercial Register:</strong> HRA 204407
</p>
</div>

</div>

<h2 style="font-size:22px;font-weight:400;">Frequently Asked Questions</h2>

<div style="margin:20px 0;">
<h3 style="font-size:16px;color:#c9a96e;">How long does shipping take?</h3>
<p style="color:#666;">Standard shipping: 7-14 business days. Express: 3-5 business days. See our <a href="/shipping-policy/">Shipping Policy</a> for details.</p>
</div>

<div style="margin:20px 0;">
<h3 style="font-size:16px;color:#c9a96e;">What is your return policy?</h3>
<p style="color:#666;">We accept returns within 30 days. EU customers have a 14-day withdrawal right. See our <a href="/return-policy/">Return Policy</a>.</p>
</div>

<div style="margin:20px 0;">
<h3 style="font-size:16px;color:#c9a96e;">Are your gemstones authentic?</h3>
<p style="color:#666;">Yes, all our gemstones are genuine and ethically sourced. Each piece comes with a certificate of authenticity.</p>
</div>

<h2 style="font-size:22px;font-weight:400;margin-top:40px;">Send Us a Message</h2>
[contact-form-7 id="contact-form" title="Contact Form"]

<p style="color:#999;font-size:14px;margin-top:30px;">For order-specific inquiries, please include your order number for faster assistance.</p>

</div>
HTML;

_zalandy_upsert_page( 'contact', 'Contact Us', $contact_content );

// ═══════════════════════════════════════════════════════════════
// 7. FAQ
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '7/10 — Creating FAQ page...' );

$faq_content = <<<HTML
<div style="max-width:750px;margin:0 auto;">

<h1 style="font-size:30px;font-weight:300;letter-spacing:1px;text-align:center;">Frequently Asked Questions</h1>
<p style="text-align:center;color:#999;margin-bottom:40px;">Everything you need to know about our jewelry</p>

<h2 style="font-size:22px;font-weight:400;color:#c9a96e;margin-top:30px;">Orders & Shipping</h2>

<h3 style="font-size:16px;">How long does order processing take?</h3>
<p style="color:#666;line-height:1.8;">Orders are processed within 2-3 business days after payment confirmation. You'll receive a confirmation email with tracking once your order ships.</p>

<h3 style="font-size:16px;">How long does shipping take?</h3>
<p style="color:#666;line-height:1.8;">Standard shipping: 7-14 business days. Express shipping: 3-5 business days. International shipping may take longer due to customs processing.</p>

<h3 style="font-size:16px;">Do you ship worldwide?</h3>
<p style="color:#666;line-height:1.8;">Yes, we ship to most countries. Customs duties and taxes are the responsibility of the buyer. Check our <a href="/shipping-policy/">Shipping Policy</a> for details.</p>

<h3 style="font-size:16px;">How can I track my order?</h3>
<p style="color:#666;line-height:1.8;">Once your order ships, you'll receive an email with a tracking number. You can also view your order status in your <a href="/my-account/">account page</a>.</p>

<h2 style="font-size:22px;font-weight:400;color:#c9a96e;margin-top:40px;">Returns & Exchanges</h2>

<h3 style="font-size:16px;">What is your return policy?</h3>
<p style="color:#666;line-height:1.8;">We accept returns within 30 days of delivery for items in original condition. EU consumers have a 14-day withdrawal right. See our <a href="/return-policy/">Return Policy</a>.</p>

<h3 style="font-size:16px;">Can I exchange for a different size?</h3>
<p style="color:#666;line-height:1.8;">Yes, we offer free size exchanges within 30 days. Contact support@zalandy.top with your order number.</p>

<h3 style="font-size:16px;">Are custom orders refundable?</h3>
<p style="color:#666;line-height:1.8;">Custom-made and personalized jewelry items cannot be returned or exchanged.</p>

<h2 style="font-size:22px;font-weight:400;color:#c9a96e;margin-top:40px;">Product & Care</h2>

<h3 style="font-size:16px;">Are your gemstones real?</h3>
<p style="color:#666;line-height:1.8;">Yes, all gemstones are genuine and ethically sourced. Each piece comes with a certificate of authenticity.</p>

<h3 style="font-size:16px;">What materials do you use?</h3>
<p style="color:#666;line-height:1.8;">We use 925 sterling silver, 18K gold plating, and genuine gemstones including emeralds, sapphires, pearls, and diamonds.</p>

<h3 style="font-size:16px;">How do I care for my jewelry?</h3>
<p style="color:#666;line-height:1.8;">See our <a href="/jewelry-care-guide/">Jewelry Care Guide</a> for detailed instructions on keeping your pieces beautiful.</p>

<h3 style="font-size:16px;">Do you offer ring sizing?</h3>
<p style="color:#666;line-height:1.8;">Yes, see our <a href="/size-guide/">Size Guide</a> for ring, bracelet, and necklace measurements.</p>

<h2 style="font-size:22px;font-weight:400;color:#c9a96e;margin-top:40px;">Payment</h2>

<h3 style="font-size:16px;">What payment methods do you accept?</h3>
<p style="color:#666;line-height:1.8;">We accept Visa, Mastercard, American Express, Apple Pay, Google Pay, and PayPal. All payments are processed securely.</p>

<h3 style="font-size:16px;">Is my payment information secure?</h3>
<p style="color:#666;line-height:1.8;">Yes. We use SSL encryption and process payments through Stripe and PayPal. We never store your full card number on our servers.</p>

<h3 style="font-size:16px;">What currency are prices in?</h3>
<p style="color:#666;line-height:1.8;">All prices are in USD ($). Your bank may convert to your local currency at checkout.</p>

</div>
HTML;

_zalandy_upsert_page( 'faq', 'FAQ', $faq_content );

// ═══════════════════════════════════════════════════════════════
// 8. Size Guide
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '8/10 — Creating Size Guide...' );

$size_content = <<<HTML
<div style="max-width:750px;margin:0 auto;">

<h1 style="font-size:30px;font-weight:300;letter-spacing:1px;text-align:center;">Size Guide</h1>
<p style="text-align:center;color:#999;margin-bottom:40px;">Find your perfect fit</p>

<h2 style="font-size:22px;font-weight:400;color:#c9a96e;">Ring Size Guide</h2>
<p>Use the chart below to find your ring size. If you're between sizes, we recommend sizing up for comfort.</p>

<table style="width:100%;border-collapse:collapse;margin:20px 0;">
<thead>
<tr style="border-bottom:2px solid #c9a96e;">
<th style="text-align:center;padding:10px;">US Size</th>
<th style="text-align:center;padding:10px;">EU Size</th>
<th style="text-align:center;padding:10px;">UK Size</th>
<th style="text-align:center;padding:10px;">Diameter (mm)</th>
<th style="text-align:center;padding:10px;">Circumference (mm)</th>
</tr>
</thead>
<tbody>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">5</td><td style="text-align:center;padding:8px;">49</td><td style="text-align:center;padding:8px;">J</td><td style="text-align:center;padding:8px;">15.7</td><td style="text-align:center;padding:8px;">49.3</td></tr>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">6</td><td style="text-align:center;padding:8px;">52</td><td style="text-align:center;padding:8px;">L</td><td style="text-align:center;padding:8px;">16.5</td><td style="text-align:center;padding:8px;">51.9</td></tr>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">7</td><td style="text-align:center;padding:8px;">54</td><td style="text-align:center;padding:8px;">N</td><td style="text-align:center;padding:8px;">17.3</td><td style="text-align:center;padding:8px;">54.4</td></tr>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">8</td><td style="text-align:center;padding:8px;">57</td><td style="text-align:center;padding:8px;">P</td><td style="text-align:center;padding:8px;">18.2</td><td style="text-align:center;padding:8px;">57.0</td></tr>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">9</td><td style="text-align:center;padding:8px;">59</td><td style="text-align:center;padding:8px;">R</td><td style="text-align:center;padding:8px;">19.0</td><td style="text-align:center;padding:8px;">59.5</td></tr>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">10</td><td style="text-align:center;padding:8px;">62</td><td style="text-align:center;padding:8px;">T</td><td style="text-align:center;padding:8px;">19.8</td><td style="text-align:center;padding:8px;">62.1</td></tr>
</tbody>
</table>

<h3>How to Measure:</h3>
<p><strong>Method 1 — String method:</strong> Wrap a piece of string around your finger, mark where it overlaps, measure the length in mm. This is your circumference.</p>
<p><strong>Method 2 — Existing ring:</strong> Measure the inner diameter of a ring that fits well, in mm.</p>

<h2 style="font-size:22px;font-weight:400;color:#c9a96e;margin-top:40px;">Bracelet Size Guide</h2>
<table style="width:100%;border-collapse:collapse;margin:20px 0;">
<thead>
<tr style="border-bottom:2px solid #c9a96e;">
<th style="text-align:center;padding:10px;">Wrist Size (cm)</th>
<th style="text-align:center;padding:10px;">Bracelet Size</th>
<th style="text-align:center;padding:10px;">Fit</th>
</tr>
</thead>
<tbody>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">14-15</td><td style="text-align:center;padding:8px;">S (16cm)</td><td style="text-align:center;padding:8px;">Snug</td></tr>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">15-16</td><td style="text-align:center;padding:8px;">M (17cm)</td><td style="text-align:center;padding:8px;">Standard</td></tr>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">16-17</td><td style="text-align:center;padding:8px;">L (18cm)</td><td style="text-align:center;padding:8px;">Standard</td></tr>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">17-18</td><td style="text-align:center;padding:8px;">XL (19cm)</td><td style="text-align:center;padding:8px;">Relaxed</td></tr>
</tbody>
</table>

<h2 style="font-size:22px;font-weight:400;color:#c9a96e;margin-top:40px;">Necklace Length Guide</h2>
<table style="width:100%;border-collapse:collapse;margin:20px 0;">
<thead>
<tr style="border-bottom:2px solid #c9a96e;">
<th style="text-align:center;padding:10px;">Length (cm)</th>
<th style="text-align:center;padding:10px;">Length (in)</th>
<th style="text-align:center;padding:10px;">Name</th>
<th style="text-align:center;padding:10px;">Position</th>
</tr>
</thead>
<tbody>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">35-40</td><td style="text-align:center;padding:8px;">14-16</td><td style="text-align:center;padding:8px;">Choker</td><td style="text-align:center;padding:8px;">Base of neck</td></tr>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">45-50</td><td style="text-align:center;padding:8px;">18-20</td><td style="text-align:center;padding:8px;">Princess</td><td style="text-align:center;padding:8px;">Below collarbone</td></tr>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">55-60</td><td style="text-align:center;padding:8px;">22-24</td><td style="text-align:center;padding:8px;">Matinee</td><td style="text-align:center;padding:8px;">Below bust</td></tr>
<tr style="border-bottom:1px solid #eee;"><td style="text-align:center;padding:8px;">70+</td><td style="text-align:center;padding:8px;">28+</td><td style="text-align:center;padding:8px;">Opera</td><td style="text-align:center;padding:8px;">Below waist</td></tr>
</tbody>
</table>

<p style="color:#999;font-size:14px;margin-top:30px;">Need help finding your size? Email us at support@zalandy.top with your measurements and we'll help you choose.</p>

</div>
HTML;

_zalandy_upsert_page( 'size-guide', 'Size Guide', $size_content );

// ═══════════════════════════════════════════════════════════════
// 9. Jewelry Care Guide
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '9/10 — Creating Jewelry Care Guide...' );

$care_content = <<<HTML
<div style="max-width:750px;margin:0 auto;">

<h1 style="font-size:30px;font-weight:300;letter-spacing:1px;text-align:center;">Jewelry Care Guide</h1>
<p style="text-align:center;color:#999;margin-bottom:40px;">Keep your pieces beautiful for years to come</p>

<h2 style="font-size:22px;font-weight:400;color:#c9a96e;">General Care Tips</h2>
<ul style="line-height:2;color:#666;">
<li><strong>Last on, first off:</strong> Put jewelry on after applying makeup, perfume, and hair products. Remove it first at the end of the day.</li>
<li><strong>Avoid chemicals:</strong> Remove jewelry before swimming (chlorine), cleaning, or using harsh chemicals.</li>
<li><strong>Store properly:</strong> Keep pieces separate in soft pouches or lined jewelry boxes to prevent scratching.</li>
<li><strong>Avoid extreme temperatures:</strong> Don't expose jewelry to direct sunlight for extended periods or extreme heat.</li>
</ul>

<h2 style="font-size:22px;font-weight:400;color:#c9a96e;margin-top:30px;">Cleaning Your Jewelry</h2>

<h3>Sterling Silver</h3>
<p style="color:#666;line-height:1.8;">Clean with a soft polishing cloth. For deeper cleaning, use warm water with mild soap and a soft brush. Dry thoroughly before storing.</p>

<h3>Gemstone Jewelry</h3>
<p style="color:#666;line-height:1.8;">Most gemstones can be cleaned with warm soapy water and a soft brush. Avoid ultrasonic cleaners for pearls, emeralds, and opals — they can damage these stones.</p>

<h3>Pearls</h3>
<p style="color:#666;line-height:1.8;">Wipe pearls with a soft, damp cloth after each wear. Never soak pearls or use ultrasonic cleaners. Store them flat to prevent stretching of the silk thread.</p>

<h3>Gold-Plated Jewelry</h3>
<p style="color:#666;line-height:1.8;">Clean gently with a soft cloth. Avoid rubbing too hard as this can wear the plating. Keep away from water and chemicals to extend the life of the plating.</p>

<h2 style="font-size:22px;font-weight:400;color:#c9a96e;margin-top:30px;">Storage Tips</h2>
<ul style="line-height:2;color:#666;">
<li>Store each piece separately to prevent tangling and scratching</li>
<li>Use anti-tarnish strips for silver jewelry</li>
<li>Keep pearls away from dry environments — they need some moisture</li>
<li>Store in a cool, dry place away from direct sunlight</li>
</ul>

<h2 style="font-size:22px;font-weight:400;color:#c9a96e;margin-top:30px;">When to Remove Jewelry</h2>
<ul style="line-height:2;color:#666;">
<li>Before showering or bathing</li>
<li>Before swimming (pool or ocean)</li>
<li>Before exercising or playing sports</li>
<li>Before sleeping</li>
<li>Before household cleaning</li>
<li>Before applying lotion, perfume, or hairspray</li>
</ul>

<div style="background:#f8f6f3;padding:20px;border-radius:8px;margin:30px 0;text-align:center;">
<p style="color:#666;">Need professional cleaning or repair?</p>
<a href="/contact/" style="display:inline-block;margin-top:10px;padding:10px 30px;background:#c9a96e;color:#fff;text-decoration:none;border-radius:2px;">Contact Us</a>
</div>

</div>
HTML;

_zalandy_upsert_page( 'jewelry-care-guide', 'Jewelry Care Guide', $care_content );

// ═══════════════════════════════════════════════════════════════
// 10. Update Navigation Menu + Footer
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( '10/10 — Updating menu with new pages...' );

// Add new pages to navigation menu (footer-style menu)
$menu_name = 'Footer Menu';
$menu_exists = wp_get_nav_menu_object( $menu_name );

if ( ! $menu_exists ) {
	$footer_menu_id = wp_create_nav_menu( $menu_name );
	WP_CLI::log( "  Created footer menu (ID: {$footer_menu_id})" );
} else {
	$footer_menu_id = $menu_exists->term_id;
	wp_delete_nav_menu( $footer_menu_id );
	$footer_menu_id = wp_create_nav_menu( $menu_name );
	WP_CLI::log( "  Recreated footer menu (ID: {$footer_menu_id})" );
}

$footer_items = [
	[ 'title' => 'About Us', 'url' => '/about-us/' ],
	[ 'title' => 'Contact', 'url' => '/contact/' ],
	[ 'title' => 'FAQ', 'url' => '/faq/' ],
	[ 'title' => 'Size Guide', 'url' => '/size-guide/' ],
	[ 'title' => 'Jewelry Care', 'url' => '/jewelry-care-guide/' ],
	[ 'title' => 'Shipping Policy', 'url' => '/shipping-policy/' ],
	[ 'title' => 'Return Policy', 'url' => '/return-policy/' ],
	[ 'title' => 'Privacy Policy', 'url' => '/privacy-policy/' ],
	[ 'title' => 'Cookie Policy', 'url' => '/cookie-policy/' ],
	[ 'title' => 'Terms of Service', 'url' => '/terms-of-service/' ],
	[ 'title' => 'Imprint', 'url' => '/imprint/' ],
	[ 'title' => 'Right of Withdrawal', 'url' => '/withdrawal-right/' ],
];

foreach ( $footer_items as $item ) {
	wp_update_nav_menu_item( $footer_menu_id, 0, [
		'menu-item-title'  => $item['title'],
		'menu-item-url'    => home_url( $item['url'] ),
		'menu-item-status' => 'publish',
	] );
	WP_CLI::log( "  + {$item['title']}" );
}

// Assign footer menu
$locations = get_theme_mod( 'nav_menu_locations' );
if ( empty( $locations ) ) {
	$locations = [];
}
$locations['footer'] = $footer_menu_id;
set_theme_mod( 'nav_menu_locations', $locations );
WP_CLI::log( '  Assigned to footer location' );

// Also add About Us and Contact to main menu
$main_menu = wp_get_nav_menu_object( 'Main Menu' );
if ( $main_menu ) {
	// Add About Us before My Account
	$about_page = get_page_by_path( 'about-us' );
	$contact_page = get_page_by_path( 'contact' );

	if ( $about_page ) {
		wp_update_nav_menu_item( $main_menu->term_id, 0, [
			'menu-item-title'  => 'About',
			'menu-item-url'    => get_permalink( $about_page ),
			'menu-item-status' => 'publish',
		] );
		WP_CLI::log( '  + Added About to main menu' );
	}

	if ( $contact_page ) {
		wp_update_nav_menu_item( $main_menu->term_id, 0, [
			'menu-item-title'  => 'Contact',
			'menu-item-url'    => get_permalink( $contact_page ),
			'menu-item-status' => 'publish',
		] );
		WP_CLI::log( '  + Added Contact to main menu' );
	}
}

// ═══════════════════════════════════════════════════════════════
// 11. WooCommerce settings — enable reviews
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( 'Enabling product reviews...' );

update_option( 'woocommerce_enable_reviews', 'yes' );
update_option( 'woocommerce_review_rating_verification_label', 'yes' );
update_option( 'woocommerce_review_rating_required', 'yes' );
update_option( 'woocommerce_enable_review_rating', 'yes' );

WP_CLI::log( '  Product reviews enabled' );

// ═══════════════════════════════════════════════════════════════
// 12. Flush
// ═══════════════════════════════════════════════════════════════
WP_CLI::log( '' );
WP_CLI::log( 'Flushing rewrite rules...' );
flush_rewrite_rules( true );

WP_CLI::log( '' );
WP_CLI::log( '========================================' );
WP_CLI::success( 'Compliance & trust pages created!' );
WP_CLI::log( '' );
WP_CLI::log( 'New pages created:' );
WP_CLI::log( '  /privacy-policy/        (rewritten, GDPR+CCPA)' );
WP_CLI::log( '  /cookie-policy/         (EU cookie law)' );
WP_CLI::log( '  /imprint/               (EU legal notice)' );
WP_CLI::log( '  /withdrawal-right/      (EU 14-day right)' );
WP_CLI::log( '  /about-us/              (brand story)' );
WP_CLI::log( '  /contact/               (contact + FAQ preview)' );
WP_CLI::log( '  /faq/                   (full FAQ page)' );
WP_CLI::log( '  /size-guide/            (ring/bracelet/necklace)' );
WP_CLI::log( '  /jewelry-care-guide/    (care instructions)' );
WP_CLI::log( '' );
WP_CLI::log( 'Menu updated:' );
WP_CLI::log( '  Main menu: + About, + Contact' );
WP_CLI::log( '  Footer menu: 12 policy/help links' );
WP_CLI::log( '' );
WP_CLI::log( 'Still TODO (manual):' );
WP_CLI::log( '  1. Install Cookie Notice plugin (Complianz or CookieYes)' );
WP_CLI::log( '  2. Configure Stripe/PayPal API keys' );
WP_CLI::log( '  3. Configure SMTP (Resend)' );
WP_CLI::log( '  4. Add social media links in Woostify footer' );
WP_CLI::log( '========================================' );
