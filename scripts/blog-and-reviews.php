<?php
/**
 * Create blog page + sample posts
 * Add seed reviews to products
 */

// === BLOG PAGE ===
$blog_page = get_page_by_path('blog');
if (!$blog_page) {
    $blog_id = wp_insert_post(array(
        'post_title' => '时尚博客 / Fashion Blog',
        'post_name' => 'blog',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => '<p>欢迎来到 Zalandy 时尚博客。在这里，我们将分享最新的时尚趋势、珠宝搭配技巧、穿搭灵感和保养指南。</p>',
        'post_author' => 1,
    ));
    // Set as posts page
    update_option('page_for_posts', $blog_id);
    echo "Created blog page ID {$blog_id}\n";
} else {
    echo "Blog page already exists\n";
}

// === SAMPLE BLOG POSTS ===
$posts = array(
    array(
        'title' => '5 Essential Jewelry Pieces Every Woman Should Own',
        'content' => "<p>Every woman's jewelry collection should include these timeless pieces that effortlessly transition from day to night.</p>
<h2>1. Diamond Stud Earrings</h2>
<p>Classic and versatile, diamond stud earrings are the foundation of any jewelry collection. They complement both casual and formal attire.</p>
<h2>2. A Simple Gold Chain</h2>
<p>A delicate gold chain necklace is perfect for layering or wearing alone. It adds a touch of elegance to any outfit.</p>
<h2>3. Pearl Necklace</h2>
<p>A timeless pearl necklace never goes out of style. Whether single or multi-strand, pearls add sophistication to any look.</p>
<h2>4. Statement Ring</h2>
<p>A bold statement ring can transform a simple outfit into a fashion-forward ensemble. Choose one that reflects your personality.</p>
<h2>5. Classic Watch</h2>
<p>A quality timepiece is both functional and fashionable. Invest in a classic design that will last for years.</p>
<p>At Zalandy, we curate jewelry that combines timeless elegance with modern design. <a href=\"/shop/\">Explore our collection</a>.</p>",
        'category' => 'Jewelry Tips',
    ),
    array(
        'title' => 'How to Style a Silk Wrap Dress for Any Occasion',
        'content' => "<p>The silk wrap dress is one of the most versatile pieces in any wardrobe. Here's how to style it for different occasions.</p>
<h2>Office Ready</h2>
<p>Pair your silk wrap dress with a structured blazer and classic pumps. Add minimal jewelry for a polished professional look.</p>
<h2>Weekend Brunch</h2>
<p>Layer with a denim jacket and white sneakers. Add a crossbody bag and sunglasses for a casual chic vibe.</p>
<h2>Evening Out</h2>
<p>Swap casual accessories for strappy heels, statement earrings, and a clutch. A bold lip color completes the look.</p>
<h2>Layered for Fall</h2>
<p>Wear over a thin turtleneck with knee-high boots and a belt to define the waist. Perfect for cooler weather.</p>
<p>Discover our <a href=\"/product-category/fashion/womens/\">women's fashion collection</a> at Zalandy.</p>",
        'category' => 'Fashion Styling',
    ),
    array(
        'title' => 'Jewelry Care Guide: How to Keep Your Pieces Sparkling',
        'content' => "<p>Proper care ensures your jewelry stays beautiful for generations. Follow these expert tips.</p>
<h2>Daily Care</h2>
<p>Always put jewelry on last — after applying perfume, lotion, and makeup. Remove before sleeping, swimming, or exercising.</p>
<h2>Cleaning</h2>
<p>For gold and diamond jewelry: soak in warm water with mild dish soap, gently brush with a soft toothbrush, rinse, and pat dry.</p>
<p>For silver: use a silver polishing cloth to remove tarnish. Store in anti-tarnish bags.</p>
<p>For pearls: wipe with a soft damp cloth after each wear. Never soak or use ultrasonic cleaners.</p>
<h2>Storage</h2>
<p>Store pieces separately to prevent scratching. Use a jewelry box with individual compartments or soft pouches.</p>
<h2>Professional Maintenance</h2>
<p>Have fine jewelry professionally cleaned and inspected annually. Check prongs and clasps regularly.</p>
<p>Shop our <a href=\"/shop/\">jewelry collection</a> with confidence — each piece comes with a care guide.</p>",
        'category' => 'Jewelry Tips',
    ),
    array(
        'title' => "The Ultimate Men's Capsule Wardrobe Guide",
        'content' => "<p>A capsule wardrobe simplifies your life while keeping you stylish. Here's how to build one.</p>
<h2>The Essentials</h2>
<p>1. <strong>White Oxford Shirt</strong> — dress up or down, always looks sharp</p>
<p>2. <strong>Premium Cotton Tee</strong> — the foundation of casual outfits</p>
<p>3. <strong>Stretch Twill Pants</strong> — comfortable yet structured</p>
<p>4. <strong>Wool Blend Overcoat</strong> — invest in quality, it lasts decades</p>
<p>5. <strong>Leather Belt</strong> — matches your shoes for a cohesive look</p>
<h2>Color Palette</h2>
<p>Stick to neutral colors: navy, grey, black, white, and camel. These mix and match effortlessly.</p>
<h2>Quality Over Quantity</h2>
<p>Better to have fewer, well-made pieces than a closet full of fast fashion. Quality fabrics like cotton, wool, and linen last longer and look better.</p>
<p>Build your capsule wardrobe with <a href=\"/product-category/fashion/mens/\">Zalandy's men's collection</a>.</p>",
        'category' => 'Fashion Styling',
    ),
);

// Create categories
$cat_ids = array();
foreach (array('Jewelry Tips', 'Fashion Styling') as $cat_name) {
    $cat = get_category_by_slug(sanitize_title($cat_name));
    if (!$cat) {
        $cat_id = wp_insert_category(array('cat_name' => $cat_name));
        $cat_ids[$cat_name] = $cat_id;
    } else {
        $cat_ids[$cat_name] = $cat->term_id;
    }
}

// Create posts
foreach ($posts as $post_data) {
    $existing = get_page_by_path(sanitize_title($post_data['title']), OBJECT, 'post');
    if ($existing) {
        echo "Post already exists: " . $post_data['title'] . "\n";
        continue;
    }
    $post_id = wp_insert_post(array(
        'post_title' => $post_data['title'],
        'post_name' => sanitize_title($post_data['title']),
        'post_content' => $post_data['content'],
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_author' => 1,
        'post_category' => array($cat_ids[$post_data['category']]),
        'tags_input' => array('fashion', 'style', 'guide'),
    ));
    if (!is_wp_error($post_id)) {
        echo "Created post: " . $post_data['title'] . " (ID {$post_id})\n";
    }
}

// === SEED REVIEWS ===
// Get all product IDs
$args = array('post_type' => 'product', 'posts_per_page' => -1, 'post_status' => 'publish');
$products = new WP_Query($args);

$reviews = array(
    array('name' => 'Sophie M.', 'rating' => 5, 'content' => 'Absolutely love this! The quality exceeds expectations. Fast shipping to Germany.'),
    array('name' => 'James L.', 'rating' => 5, 'content' => 'Perfect fit and excellent material. Will definitely order again.'),
    array('name' => 'Emma K.', 'rating' => 4, 'content' => 'Beautiful piece, slightly smaller than expected but still gorgeous.'),
    array('name' => 'Michael R.', 'rating' => 5, 'content' => 'Outstanding quality for the price. Packaging was elegant too.'),
    array('name' => 'Yuki T.', 'rating' => 5, 'content' => 'This is now my favorite item! Compliments every time I wear it.'),
    array('name' => 'Anna S.', 'rating' => 4, 'content' => 'Good quality, delivery took a week but worth the wait.'),
    array('name' => 'David B.', 'rating' => 5, 'content' => 'Bought as a gift — she loved it! Great customer service.'),
    array('name' => 'Lena W.', 'rating' => 5, 'content' => 'Stunning! Looks even better in person. Highly recommend.'),
);

$review_count = 0;
while ($products->have_posts()) {
    $products->the_post();
    $product_id = get_the_ID();
    $num_reviews = rand(2, 4); // 2-4 reviews per product

    for ($i = 0; $i < $num_reviews; $i++) {
        $review = $reviews[array_rand($reviews)];
        $comment_id = wp_insert_comment(array(
            'comment_post_ID' => $product_id,
            'comment_author' => $review['name'],
            'comment_author_email' => 'customer' . rand(1000, 9999) . '@example.com',
            'comment_author_url' => '',
            'comment_content' => $review['content'],
            'comment_type' => 'review',
            'comment_parent' => 0,
            'user_id' => 0,
            'comment_date' => gmdate('Y-m-d H:i:s', strtotime('-' . rand(1, 60) . ' days')),
            'comment_approved' => 1,
            'comment_meta' => array(
                'rating' => $review['rating'],
            ),
        ));

        if ($comment_id && !is_wp_error($comment_id)) {
            $review_count++;
        }
    }

    // Update product rating
    $comments = get_comments(array('post_id' => $product_id, 'type' => 'review', 'status' => 'approve'));
    $total_rating = 0;
    $total_count = 0;
    foreach ($comments as $c) {
        $rating = get_comment_meta($c->comment_ID, 'rating', true);
        if ($rating) {
            $total_rating += intval($rating);
            $total_count++;
        }
    }
    if ($total_count > 0) {
        $avg = $total_rating / $total_count;
        update_post_meta($product_id, '_wc_average_rating', $avg);
        update_post_meta($product_id, '_wc_review_count', $total_count);
        update_post_meta($product_id, '_wc_rating_count', array(5 => $total_count));
    }
}

echo "Created {$review_count} reviews across products\n";
echo "DONE\n";
