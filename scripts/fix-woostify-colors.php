<?php
/**
 * Fix Woostify theme colors to orange #FF6B00
 */

set_theme_mod('woostify_color_link', '#FF6B00');
set_theme_mod('woostify_color_link_hover', '#E65F00');
set_theme_mod('woostify_color_button_background', '#FF6B00');
set_theme_mod('woostify_color_button_text', '#ffffff');
set_theme_mod('woostify_color_button_background_hover', '#E65F00');
set_theme_mod('woostify_color_primary', '#FF6B00');
set_theme_mod('woostify_footer_link_color', '#FF6B00');
set_theme_mod('woostify_link_color', '#FF6B00');
set_theme_mod('woostify_link_hover_color', '#E65F00');
set_theme_mod('woostify_button_background', '#FF6B00');
set_theme_mod('woostify_button_hover_background', '#E65F00');

// Also update any custom CSS that may have old gold color
$css = get_option('zalandy_footer_css', '');
$css = str_replace('#c9a96e', '#FF6B00', $css);
$css = str_replace('#b8975a', '#E65F00', $css);
update_option('zalandy_footer_css', $css);

echo "Woostify theme colors updated to orange\n";
echo "DONE\n";
