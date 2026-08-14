<?php
/**
 * Zalandy — Full Terms & Conditions (EN) + AGB (DE).
 *
 * Replaces the minimal T&C content on:
 *   - terms-of-service (ID 272, English)
 *   - agb (ID 380, German)
 *
 * Includes current company data:
 *   Seniorenpflegeheim Bevern GmbH & Co. KG
 *   Im Ziegelfeld 16, 27432 Bremervörde
 *   HRA 204407, Amtsgericht Tostedt
 *   VAT: DE367264918, EPR (LUCID): DE1649745799617
 *
 * Usage:
 *   wp eval-file /tmp/terms-conditions.php --allow-root
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

WP_CLI::log( '=== Terms & Conditions / AGB ===' );

$en_content = <<<HTML
<h2>Terms and Conditions</h2>
<p><strong>Last updated:</strong> August 14, 2026</p>

<h3>1. Provider / Contractor</h3>
<p>
<strong>Seniorenpflegeheim Bevern GmbH & Co. KG</strong> (brand: Zalandy)<br/>
Im Ziegelfeld 16<br/>
27432 Bremervörde<br/>
Niedersachsen, Germany<br/>
Commercial Register: HRA 204407, Amtsgericht Tostedt<br/>
VAT ID: DE367264918<br/>
EPR Registration Number (LUCID): DE1649745799617<br/>
Email: support@zalandy.top
</p>

<h3>2. Conclusion of Contract</h3>
<p>All products presented in our online shop constitute non-binding invitations. By placing an order (clicking "Place Order"), you submit a binding offer to purchase. We accept your offer by sending an order confirmation via email or by delivering the goods. The contract text is stored and can be requested from us.</p>

<h3>3. Prices and Payment</h3>
<p>All prices are listed in the currency shown at checkout and include applicable statutory VAT. Shipping costs are displayed before you complete your order. We accept the payment methods shown at checkout (e.g., credit card via Stripe, PayPal). In the case of late payment, we reserve the right to assert statutory rights.</p>

<h3>4. Delivery</h3>
<p>Orders are processed within 2-3 business days after payment confirmation. Delivery times vary by destination (standard 7-14 business days, express 3-5 business days). If a delivery delay occurs, we will inform you promptly. Risk of loss passes to you upon handover of the goods to the carrier.</p>

<h3>5. Right of Withdrawal (EU Consumers)</h3>
<p>Consumers in the European Union have the right to withdraw from a distance contract within 14 days without giving any reason. Details and the model withdrawal form can be found on our <a href="/withdrawal-right/">Right of Withdrawal</a> page.</p>

<h3>6. Warranty for Defects</h3>
<p>Statutory warranty rights apply. In case of defective goods, please contact support@zalandy.top with your order number and a description of the defect.</p>

<h3>7. Reservations of Title</h3>
<p>Delivered goods remain our property until full payment has been received.</p>

<h3>8. Liability</h3>
<p>We are liable without limitation for intent and gross negligence, for injury to life, body or health, and under the German Product Liability Act. For slight negligence, we are liable only for breach of essential contractual obligations, limited to the foreseeable damage typical for this type of contract.</p>

<h3>9. Intellectual Property</h3>
<p>All content on this website (texts, images, logos, designs) is protected by copyright. Reproduction, distribution, or commercial use requires our prior written consent.</p>

<h3>10. Out-of-Court Dispute Resolution</h3>
<p>The European Commission provides a platform for online dispute resolution (ODR): <a href="https://ec.europa.eu/consumers/odr/">https://ec.europa.eu/consumers/odr/</a>. We are not obliged or willing to participate in dispute settlement proceedings before a consumer arbitration board.</p>

<h3>11. Applicable Law</h3>
<p>These terms and conditions are governed by the laws of the Federal Republic of Germany, without prejudice to mandatory consumer protection provisions of the country in which the consumer has their habitual residence.</p>

<h3>12. Changes to These Terms</h3>
<p>We may update these terms from time to time. The version valid at the time of your order applies. Significant changes will be announced on our website.</p>

<h3>13. Contact</h3>
<p>For questions regarding these terms, contact <strong>support@zalandy.top</strong>.</p>
HTML;

$de_content = <<<HTML
<h2>Allgemeine Geschäftsbedingungen (AGB)</h2>
<p><strong>Stand:</strong> 14. August 2026</p>

<h3>§ 1 Anbieter / Vertragspartner</h3>
<p>
<strong>Seniorenpflegeheim Bevern GmbH & Co. KG</strong> (Marke: Zalandy)<br/>
Im Ziegelfeld 16<br/>
27432 Bremervörde<br/>
Niedersachsen, Deutschland<br/>
Handelsregister: HRA 204407, Amtsgericht Tostedt<br/>
USt-IdNr.: DE367264918<br/>
Verpackungsregister (LUCID): DE1649745799617<br/>
E-Mail: support@zalandy.top
</p>

<h3>§ 2 Vertragsschluss</h3>
<p>Alle in unserem Online-Shop dargestellten Produkte stellen unverbindliche Angebote dar. Mit der Bestellung (Klick auf „Bestellung abschicken") geben Sie ein verbindliches Angebot zum Kauf ab. Wir nehmen Ihr Angebot durch Zusendung einer Auftragsbestätigung per E-Mail oder durch Lieferung der Ware an. Der Vertragstext wird gespeichert und kann auf Anfrage zur Verfügung gestellt werden.</p>

<h3>§ 3 Preise und Zahlung</h3>
<p>Alle Preise verstehen sich in der beim Checkout angezeigten Währung und enthalten die gesetzliche Mehrwertsteuer. Versandkosten werden vor Abschluss der Bestellung angezeigt. Wir akzeptieren die beim Checkout angebotenen Zahlungsarten (z.&nbsp;B. Kreditkarte via Stripe, PayPal). Bei Zahlungsverzug behalten wir uns die Geltendmachung gesetzlicher Rechte vor.</p>

<h3>§ 4 Lieferung</h3>
<p>Bestellungen werden innerhalb von 2-3 Werktagen nach Zahlungseingang bearbeitet. Die Lieferzeiten variieren je nach Ziel (Standard 7-14 Werktage, Express 3-5 Werktage). Bei Lieferverzögerungen informieren wir Sie unverzüglich. Die Gefahr des zufälligen Untergangs geht mit Übergabe der Ware an das Transportunternehmen auf Sie über.</p>

<h3>§ 5 Widerrufsrecht (Verbraucher)</h3>
<p>Verbraucher in der Europäischen Union haben das Recht, binnen 14 Tagen ohne Angabe von Gründen den Vertrag zu widerrufen. Details und das Muster-Widerrufsformular finden Sie auf unserer Seite <a href="/de/widerrufsrecht/">Widerrufsrecht</a>.</p>

<h3>§ 6 Gewährleistung</h3>
<p>Es gelten die gesetzlichen Gewährleistungsrechte. Bei mangelhafter Ware kontaktieren Sie bitte support@zalandy.top unter Angabe Ihrer Bestellnummer und einer Beschreibung des Mangels.</p>

<h3>§ 7 Eigentumsvorbehalt</h3>
<p>Die gelieferte Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.</p>

<h3>§ 8 Haftung</h3>
<p>Wir haften unbeschränkt für Vorsatz und grobe Fahrlässigkeit, bei Verletzung von Leben, Körper oder Gesundheit sowie nach dem Produkthaftungsgesetz. Bei einfacher Fahrlässigkeit haften wir nur bei Verletzung wesentlicher Vertragspflichten, begrenzt auf den vorhersehbaren, vertragstypischen Schaden.</p>

<h3>§ 9 Urheberrecht</h3>
<p>Sämtliche Inhalte dieser Website (Texte, Bilder, Logos, Designs) sind urheberrechtlich geschützt. Vervielfältigung, Verbreitung oder kommerzielle Nutzung bedarf unserer vorherigen schriftlichen Zustimmung.</p>

<h3>§ 10 Außergerichtliche Streitbeilegung</h3>
<p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit: <a href="https://ec.europa.eu/consumers/odr/">https://ec.europa.eu/consumers/odr/</a>. Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>

<h3>§ 11 Anwendbares Recht</h3>
<p>Es gilt das Recht der Bundesrepublik Deutschland unter Vorbehalt zwingender Verbraucherschutzvorschriften des Staates, in dem der Verbraucher seinen gewöhnlichen Aufenthalt hat.</p>

<h3>§ 12 Änderungen dieser Bedingungen</h3>
<p>Wir können diese Bedingungen von Zeit zu Zeit aktualisieren. Maßgeblich ist die zum Zeitpunkt Ihrer Bestellung gültige Fassung. Wesentliche Änderungen werden auf unserer Website angekündigt.</p>

<h3>§ 13 Kontakt</h3>
<p>Fragen zu diesen Bedingungen richten Sie bitte an <strong>support@zalandy.top</strong>.</p>
HTML;

// ── Update EN page ───────────────────────────
$en_page = get_page_by_path( 'terms-of-service' );
if ( $en_page ) {
	wp_update_post( array(
		'ID'           => $en_page->ID,
		'post_content' => $en_content,
	) );
	WP_CLI::log( "  Updated EN Terms of Service (ID {$en_page->ID})" );
}

// ── Update DE page ───────────────────────────
$de_page = get_page_by_path( 'agb' );
if ( $de_page ) {
	wp_update_post( array(
		'ID'           => $de_page->ID,
		'post_content' => $de_content,
	) );
	WP_CLI::log( "  Updated DE AGB (ID {$de_page->ID})" );
}

wp_cache_flush();
WP_CLI::log( 'Done.' );
