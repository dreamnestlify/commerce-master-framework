<?php
/**
 * Update footer HTML with logo image and social links
 */

$dark_logo_id = get_option('zalandy_logo_dark_id');
$logo_url = $dark_logo_id ? wp_get_attachment_image_url($dark_logo_id, 'medium') : '';
$brand_color = get_option('zalandy_brand_color', '#FF6B00');

// Build footer HTML with logo
$footer_html = '<div class="footer-container" style="max-width:1200px;margin:0 auto;padding:60px 20px 30px;">
  <div class="footer-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:40px;margin-bottom:40px;">
    <div>
      ' . ($logo_url ? '<img src="' . esc_url($logo_url) . '" alt="Zalandy" style="height:36px;width:auto;margin-bottom:16px;">' : '<h3 style="font-family:Playfair Display,serif;font-size:24px;margin-bottom:16px;color:' . $brand_color . ';">Zalandy</h3>') . '
      <p style="color:#aaa;font-size:14px;line-height:1.7;">Fine jewelry & contemporary fashion. Handcrafted with passion in Germany.</p>
      <p style="color:#aaa;font-size:13px;margin-top:12px;">Equi international UG (haftungsbeschränkt)<br>Großenwede Siedlung 8<br>29640 Schneverdingen, Germany<br>VAT: DE312939176 | HRB: 206966</p>
      <div style="margin-top:16px;display:flex;gap:12px;">
        <a href="#" style="color:#aaa;font-size:13px;text-decoration:none;">Instagram</a>
        <a href="#" style="color:#aaa;font-size:13px;text-decoration:none;">Facebook</a>
        <a href="#" style="color:#aaa;font-size:13px;text-decoration:none;">TikTok</a>
        <a href="#" style="color:#aaa;font-size:13px;text-decoration:none;">Pinterest</a>
      </div>
    </div>
    <div>
      <h4 style="font-size:14px;text-transform:uppercase;letter-spacing:1px;margin-bottom:20px;color:#fff;">Shop</h4>
      <ul style="list-style:none;padding:0;margin:0;line-height:2;">
        <li><a href="/shop/" style="color:#aaa;text-decoration:none;font-size:14px;">All Products</a></li>
        <li><a href="/product-category/jewelry/" style="color:#aaa;text-decoration:none;font-size:14px;">Jewelry</a></li>
        <li><a href="/product-category/fashion/" style="color:#aaa;text-decoration:none;font-size:14px;">Fashion</a></li>
        <li><a href="/product-category/accessories/" style="color:#aaa;text-decoration:none;font-size:14px;">Accessories</a></li>
      </ul>
    </div>
    <div>
      <h4 style="font-size:14px;text-transform:uppercase;letter-spacing:1px;margin-bottom:20px;color:#fff;">Help</h4>
      <ul style="list-style:none;padding:0;margin:0;line-height:2;">
        <li><a href="/contact/" style="color:#aaa;text-decoration:none;font-size:14px;">Contact Us</a></li>
        <li><a href="/faq/" style="color:#aaa;text-decoration:none;font-size:14px;">FAQ</a></li>
        <li><a href="/size-guide/" style="color:#aaa;text-decoration:none;font-size:14px;">Size Guide</a></li>
        <li><a href="/shipping-policy/" style="color:#aaa;text-decoration:none;font-size:14px;">Shipping</a></li>
        <li><a href="/return-policy/" style="color:#aaa;text-decoration:none;font-size:14px;">Returns</a></li>
      </ul>
    </div>
    <div>
      <h4 style="font-size:14px;text-transform:uppercase;letter-spacing:1px;margin-bottom:20px;color:#fff;">Legal</h4>
      <ul style="list-style:none;padding:0;margin:0;line-height:2;">
        <li><a href="/imprint/" style="color:#aaa;text-decoration:none;font-size:14px;">Imprint</a></li>
        <li><a href="/privacy-policy/" style="color:#aaa;text-decoration:none;font-size:14px;">Privacy Policy</a></li>
        <li><a href="/cookie-policy/" style="color:#aaa;text-decoration:none;font-size:14px;">Cookie Policy</a></li>
        <li><a href="/terms-and-conditions/" style="color:#aaa;text-decoration:none;font-size:14px;">Terms & Conditions</a></li>
        <li><a href="/withdrawal-right/" style="color:#aaa;text-decoration:none;font-size:14px;">Withdrawal Right</a></li>
      </ul>
    </div>
  </div>
  <div style="border-top:1px solid #333;padding-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
    <p style="color:#888;font-size:13px;margin:0;">&copy; ' . date('Y') . ' Zalandy. All rights reserved. Equi international UG</p>
    <p style="color:#888;font-size:13px;margin:0;">Designed with passion in Germany</p>
  </div>
</div>';

update_option('zalandy_custom_footer', $footer_html);
echo "Footer HTML updated with logo image and social links\n";

// Also add footer CSS with brand color
$footer_css = '
.zalandy-custom-footer {
  background: #1a1a1a;
  color: #fff;
  margin-top: 60px;
}
.zalandy-custom-footer a:hover { color: ' . $brand_color . ' !important; }
.zalandy-custom-footer img { max-width: 200px; }
';

// Store the CSS as an option for the mu-plugin to use
update_option('zalandy_footer_css', $footer_css);
echo "Footer CSS updated\n";

// Add inline CSS via wp_head
echo "DONE\n";
