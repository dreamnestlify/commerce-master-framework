<?php
/**
 * Restore fashion product full English titles
 */

$fashion_titles = array(
    324 => 'Silk Wrap Midi Dress — Romantic Floral Print',
    325 => 'Cotton Linen Summer Jumpsuit — Wide Leg',
    326 => 'Oversized Knit Cardigan — Cozy Chunky Weave',
    327 => 'High-Waist Wide Leg Trousers — Tailored Fit',
    328 => 'Pleated Mini Skirt — A-Line Cut',
    329 => 'Cropped Blazer — Structured Shoulder',
    330 => 'Premium Cotton T-Shirt — Minimalist Essential',
    331 => 'Oxford Button-Down Shirt — Classic Fit',
    332 => 'Slim-Fit Chino Pants — Stretch Comfort',
    333 => 'Wool Blend Overcoat — Tailored Longline',
    334 => 'Relaxed Cargo Pants — Utility Style',
    335 => 'Genuine Leather Belt — Italian Craftsmanship',
    336 => 'Cashmere Blend Scarf — Luxury Soft',
    337 => 'Structured Crossbody Bag — Vegan Leather',
    338 => 'Retro Square Sunglasses — UV400 Protection',
);

foreach ($fashion_titles as $id => $title) {
    $post = get_post($id);
    if ($post && $post->post_type === 'product') {
        wp_update_post(array(
            'ID' => $id,
            'post_title' => $title,
        ));
        echo "Restored product {$id}: {$title}\n";
    }
}
echo "DONE\n";
