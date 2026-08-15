<?php
/**
 * 按 Mollie 审核员提供的 10 节模板格式重写 T&C / AGB
 * 套用新公司: Seniorenpflegeheim Bevern GmbH & Co. KG
 *
 * 写入页面:
 *   - ID 11  terms-and-conditions  (EN, 页脚链接目标)
 *   - ID 380 agb                  (DE)
 */
if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

global $wpdb;

// 拼装英文 T&C (按 Mollie 模板 10 节)
$en = <<<'HTML'
<!-- wp:paragraph -->
<p>Welcome to Zalandy. These Terms &amp; Conditions ("Terms") govern your use of the Zalandy website and the purchase of any products from Seniorenpflegeheim Bevern GmbH &amp; Co. KG. By accessing this site or placing an order, you agree to these Terms.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>1. Orders &amp; Contract Formation</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>All orders placed through Zalandy are subject to acceptance by us. A contract of sale is formed only when we confirm your order. We reserve the right to refuse or cancel any order at our discretion.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>2. Prices &amp; Taxes</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>All prices are stated in Euros (EUR) and include applicable statutory VAT. Prices and offers may change without prior notice. The total price, including any shipping costs, is shown at checkout before you confirm your order.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>3. Payment</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Payments are processed securely through Mollie B.V. We accept credit/debit cards, PayPal, Klarna, and Apple Pay. Your payment information is encrypted and never stored on our servers. Goods remain our property until payment is received in full.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>4. Shipping &amp; Delivery</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>We offer insured shipping on all orders, dispatched within 1–3 business days. Estimated delivery is 3–7 business days within the EU and 7–14 business days worldwide. Delivery times are estimates and not guaranteed. Any customs duties or import taxes for destinations outside the EU are the responsibility of the customer.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>5. Returns &amp; Refunds</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>We offer a 30-day return policy on non-customized items. Bespoke and personalized pieces are final sale unless there is a manufacturing defect. Items must be returned in their original condition. Refunds are processed to your original payment method within 14 days of receiving the return.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>6. Right of Withdrawal (EU Consumers)</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>If you are a consumer in the European Union, you have the right to withdraw from your purchase within 14 days without giving any reason. This right does not apply to bespoke or personalized items made to your specifications. To exercise this right, contact us at indiagianina5@gmail.com. We will reimburse all payments received, including standard delivery costs.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>7. Intellectual Property</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>All content on this website — including text, images, logos, and design — is the property of Seniorenpflegeheim Bevern GmbH &amp; Co. KG or its licensors and is protected by applicable intellectual property laws. Reproduction without prior written consent is prohibited.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>8. Limitation of Liability</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>To the maximum extent permitted by law, Zalandy is not liable for indirect, incidental, or consequential damages arising from the use of our products or website. Our total liability shall not exceed the amount paid for the relevant order.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>9. Governing Law &amp; Jurisdiction</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>These Terms are governed by the laws of the Federal Republic of Germany. The place of jurisdiction for all disputes is Amtsgericht Tostedt, unless mandatory consumer-protection law provides otherwise.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>10. Company Information</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Seniorenpflegeheim Bevern GmbH &amp; Co. KG<br>Managing Director: Katrin Dübbers<br>Im Ziegelfeld 16, 27432 Bremervörde, Germany<br>Commercial Register: HRA 204407, Amtsgericht Tostedt<br>VAT ID (USt-IdNr): DE367264918<br>EPR Packaging Registration (LUCID / VerpackG): DE1649745799617<br>Contact: indiagianina5@gmail.com | +1 929 568 3010</p>
<!-- /wp:paragraph -->
HTML;

// 德文 AGB (对应翻译)
$de = <<<'HTML'
<!-- wp:paragraph -->
<p>Willkommen bei Zalandy. Diese Allgemeinen Geschäftsbedingungen („AGB") regeln Ihre Nutzung der Website Zalandy sowie den Kauf von Produkten der Seniorenpflegeheim Bevern GmbH &amp; Co. KG. Mit dem Zugriff auf diese Website oder der Aufgabe einer Bestellung erklären Sie sich mit diesen AGB einverstanden.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>1. Bestellungen &amp; Vertragsschluss</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Alle über Zalandy aufgegebenen Bestellungen stehen unter dem Vorbehalt unserer Annahme. Ein Kaufvertrag kommt erst zustande, wenn wir Ihre Bestellung bestätigen. Wir behalten uns vor, Bestellungen nach eigenem Ermessen abzulehnen oder zu stornieren.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>2. Preise &amp; Steuern</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Alle Preise verstehen sich in Euro (EUR) inklusive der gesetzlichen Mehrwertsteuer. Preise und Angebote können ohne vorherige Ankündigung geändert werden. Der Gesamtpreis inklusive Versandkosten wird Ihnen vor der Bestellbestätigung im Checkout angezeigt.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>3. Zahlung</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Die Zahlungsabwicklung erfolgt sicher über Mollie B.V. Wir akzeptieren Kredit-/Debitkarten, PayPal, Klarna und Apple Pay. Ihre Zahlungsdaten werden verschlüsselt übertragen und niemals auf unseren Servern gespeichert. Die Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>4. Versand &amp; Lieferung</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Wir bieten versicherten Versand für alle Bestellungen, der innerhalb von 1–3 Werktagen versendet wird. Die voraussichtliche Lieferzeit beträgt 3–7 Werktage innerhalb der EU und 7–14 Werktage weltweit. Lieferzeiten sind Schätzungen und nicht garantiert. Etwaige Zollgebühren oder Einfuhrsteuern für Bestimmungsorte außerhalb der EU gehen zu Lasten des Kunden.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>5. Rückgabe &amp; Erstattung</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Für nicht individualisierte Artikel bieten wir eine 30-tägige Rückgabefrist an. Maßgefertigte und personalisierte Stücke sind vom Umtausch ausgeschlossen, es sei denn, es liegt ein Herstellungsfehler vor. Die Artikel müssen im Originalzustand zurückgegeben werden. Die Rückerstattung erfolgt innerhalb von 14 Tagen nach Eingang der Rücksendung auf Ihre ursprüngliche Zahlungsmethode.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>6. Widerrufsrecht (EU-Verbraucher)</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Wenn Sie Verbraucher in der Europäischen Union sind, haben Sie das Recht, Ihren Kauf innerhalb von 14 Tagen ohne Angabe von Gründen zu widerrufen. Dieses Recht gilt nicht für maßgefertigte oder personalisierte Artikel. Zur Ausübung dieses Rechts kontaktieren Sie uns bitte unter indiagianina5@gmail.com. Wir erstatten alle erhaltenen Zahlungen einschließlich der Standardversandkosten.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>7. Geistiges Eigentum</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Alle Inhalte dieser Website — einschließlich Texte, Bilder, Logos und Design — sind Eigentum der Seniorenpflegeheim Bevern GmbH &amp; Co. KG oder deren Lizenzgeber und durch geltendes Recht zum Schutz geistigen Eigentums geschützt. Eine Vervielfältigung ohne vorherige schriftliche Zustimmung ist untersagt.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>8. Haftungsbeschränkung</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Soweit gesetzlich zulässig, haftet Zalandy nicht für indirekte, zufällige oder Folgeschäden, die sich aus der Nutzung unserer Produkte oder Website ergeben. Unsere Gesamthaftung ist auf den für die jeweilige Bestellung gezahlten Betrag begrenzt.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>9. Anwendbares Recht &amp; Gerichtsstand</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Diese AGB unterliegen dem Recht der Bundesrepublik Deutschland. Gerichtsstand für alle Streitigkeiten ist das Amtsgericht Tostedt, sofern nicht zwingendes Verbraucherschutzrecht etwas anderes vorsieht.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>10. Anbieterangaben</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Seniorenpflegeheim Bevern GmbH &amp; Co. KG<br>Geschäftsführerin: Katrin Dübbers<br>Im Ziegelfeld 16, 27432 Bremervörde, Deutschland<br>Handelsregister: HRA 204407, Amtsgericht Tostedt<br>USt-IdNr.: DE367264918<br>LUCID-Verpackungsregister: DE1649745799617<br>Kontakt: indiagianina5@gmail.com | +1 929 568 3010</p>
<!-- /wp:paragraph -->
HTML;

// 写入 ID 11 (EN) 和 ID 380 (DE)
$result = wp_update_post( array(
	'ID'           => 11,
	'post_content' => $en,
	'post_status'  => 'publish',
) );
WP_CLI::line( 'ID 11 update result: ' . ( is_wp_error( $result ) ? $result->get_error_message() : 'OK' ) );

$result = wp_update_post( array(
	'ID'           => 380,
	'post_content' => $de,
	'post_status'  => 'publish',
) );
WP_CLI::line( 'ID 380 update result: ' . ( is_wp_error( $result ) ? $result->get_error_message() : 'OK' ) );

// 验证
foreach ( array( 11 => $en, 380 => $de ) as $id => $expected ) {
	$saved = $wpdb->get_var( $wpdb->prepare( "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d", $id ) );
	WP_CLI::line( sprintf( "ID %d: %d bytes, VAT:%s EPR:%s HRA:%s MOLLIE:%s",
		$id,
		strlen( $saved ),
		strpos( $saved, 'DE367264918' ) !== false ? 'YES' : 'NO',
		strpos( $saved, 'DE1649745799617' ) !== false ? 'YES' : 'NO',
		strpos( $saved, 'HRA 204407' ) !== false ? 'YES' : 'NO',
		strpos( $saved, 'Mollie B.V.' ) !== false ? 'YES' : 'NO'
	) );
}