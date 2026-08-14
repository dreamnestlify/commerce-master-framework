<?php
/**
 * Create Clothing Size Guide page
 * - International size chart (XS-XXL)
 * - How to measure
 * - Women's / Men's / Accessories
 */

// Check if page already exists
$existing = get_page_by_path('size-guide-clothing');
if ($existing) {
    echo "Size guide page already exists: ID {$existing->ID}\n";
    return;
}

$html = <<<HTML
<h2 style="font-family: 'Playfair Display', serif; color: #1a1a1a;">服装尺码指南 / Clothing Size Guide</h2>
<p style="color: #666; font-size: 14px;">本指南帮助你找到最合适的尺码。如需帮助，请联系 <a href="mailto:indiagianina5@gmail.com">indiagianina5@gmail.com</a></p>

<hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">

<!-- Women's Size Chart -->
<h3 style="font-family: 'Playfair Display', serif; color: #c9a96e; margin-top: 40px;">女装尺码 / Women's Size Chart</h3>
<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;">
    <thead>
        <tr style="background: #1a1a1a; color: #fff;">
            <th style="padding: 12px; text-align: left;">国际码</th>
            <th style="padding: 12px; text-align: left;">EU</th>
            <th style="padding: 12px; text-align: left;">US</th>
            <th style="padding: 12px; text-align: left;">UK</th>
            <th style="padding: 12px; text-align: left;">胸围 (cm)</th>
            <th style="padding: 12px; text-align: left;">腰围 (cm)</th>
            <th style="padding: 12px; text-align: left;">臀围 (cm)</th>
        </tr>
    </thead>
    <tbody>
        <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px;">XS</td>
            <td style="padding: 10px;">32-34</td>
            <td style="padding: 10px;">0-2</td>
            <td style="padding: 10px;">4-6</td>
            <td style="padding: 10px;">78-82</td>
            <td style="padding: 10px;">60-64</td>
            <td style="padding: 10px;">86-90</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0; background: #fafafa;">
            <td style="padding: 10px;">S</td>
            <td style="padding: 10px;">36-38</td>
            <td style="padding: 10px;">4-6</td>
            <td style="padding: 10px;">8-10</td>
            <td style="padding: 10px;">82-86</td>
            <td style="padding: 10px;">64-68</td>
            <td style="padding: 10px;">90-94</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px;">M</td>
            <td style="padding: 10px;">40-42</td>
            <td style="padding: 10px;">8-10</td>
            <td style="padding: 10px;">12-14</td>
            <td style="padding: 10px;">86-90</td>
            <td style="padding: 10px;">68-72</td>
            <td style="padding: 10px;">94-98</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0; background: #fafafa;">
            <td style="padding: 10px;">L</td>
            <td style="padding: 10px;">44-46</td>
            <td style="padding: 10px;">12-14</td>
            <td style="padding: 10px;">16-18</td>
            <td style="padding: 10px;">90-94</td>
            <td style="padding: 10px;">72-76</td>
            <td style="padding: 10px;">98-102</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px;">XL</td>
            <td style="padding: 10px;">48-50</td>
            <td style="padding: 10px;">16-18</td>
            <td style="padding: 10px;">20-22</td>
            <td style="padding: 10px;">94-100</td>
            <td style="padding: 10px;">76-82</td>
            <td style="padding: 10px;">102-108</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0; background: #fafafa;">
            <td style="padding: 10px;">XXL</td>
            <td style="padding: 10px;">52-54</td>
            <td style="padding: 10px;">20-22</td>
            <td style="padding: 10px;">24-26</td>
            <td style="padding: 10px;">100-106</td>
            <td style="padding: 10px;">82-88</td>
            <td style="padding: 10px;">108-114</td>
        </tr>
    </tbody>
</table>

<!-- Men's Size Chart -->
<h3 style="font-family: 'Playfair Display', serif; color: #c9a96e; margin-top: 40px;">男装尺码 / Men's Size Chart</h3>
<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;">
    <thead>
        <tr style="background: #1a1a1a; color: #fff;">
            <th style="padding: 12px; text-align: left;">国际码</th>
            <th style="padding: 12px; text-align: left;">EU</th>
            <th style="padding: 12px; text-align: left;">US</th>
            <th style="padding: 12px; text-align: left;">UK</th>
            <th style="padding: 12px; text-align: left;">胸围 (cm)</th>
            <th style="padding: 12px; text-align: left;">腰围 (cm)</th>
            <th style="padding: 12px; text-align: left;">颈围 (cm)</th>
        </tr>
    </thead>
    <tbody>
        <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px;">XS</td>
            <td style="padding: 10px;">44</td>
            <td style="padding: 10px;">34</td>
            <td style="padding: 10px;">34</td>
            <td style="padding: 10px;">86-90</td>
            <td style="padding: 10px;">74-78</td>
            <td style="padding: 10px;">37-38</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0; background: #fafafa;">
            <td style="padding: 10px;">S</td>
            <td style="padding: 10px;">46</td>
            <td style="padding: 10px;">36</td>
            <td style="padding: 10px;">36</td>
            <td style="padding: 10px;">90-94</td>
            <td style="padding: 10px;">78-82</td>
            <td style="padding: 10px;">38-39</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px;">M</td>
            <td style="padding: 10px;">48-50</td>
            <td style="padding: 10px;">38-40</td>
            <td style="padding: 10px;">38-40</td>
            <td style="padding: 10px;">94-100</td>
            <td style="padding: 10px;">82-88</td>
            <td style="padding: 10px;">39-41</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0; background: #fafafa;">
            <td style="padding: 10px;">L</td>
            <td style="padding: 10px;">52</td>
            <td style="padding: 10px;">42</td>
            <td style="padding: 10px;">42</td>
            <td style="padding: 10px;">100-106</td>
            <td style="padding: 10px;">88-94</td>
            <td style="padding: 10px;">41-42</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px;">XL</td>
            <td style="padding: 10px;">54-56</td>
            <td style="padding: 10px;">44-46</td>
            <td style="padding: 10px;">44-46</td>
            <td style="padding: 10px;">106-112</td>
            <td style="padding: 10px;">94-100</td>
            <td style="padding: 10px;">42-43</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0; background: #fafafa;">
            <td style="padding: 10px;">XXL</td>
            <td style="padding: 10px;">58</td>
            <td style="padding: 10px;">48</td>
            <td style="padding: 10px;">48</td>
            <td style="padding: 10px;">112-118</td>
            <td style="padding: 10px;">100-106</td>
            <td style="padding: 10px;">43-44</td>
        </tr>
    </tbody>
</table>

<!-- How to Measure -->
<h3 style="font-family: 'Playfair Display', serif; color: #c9a96e; margin-top: 40px;">如何测量 / How to Measure</h3>
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0;">
    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
        <h4 style="color: #1a1a1a; margin-bottom: 10px;">胸围 / Bust</h4>
        <p style="font-size: 13px; color: #666; line-height: 1.6;">穿着贴身内衣，双臂自然下垂，用软尺绕胸部最丰满处水平测量一周。</p>
    </div>
    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
        <h4 style="color: #1a1a1a; margin-bottom: 10px;">腰围 / Waist</h4>
        <p style="font-size: 13px; color: #666; line-height: 1.6;">自然站立，用软尺绕腰部最细处水平测量一周（通常在肚脐上方）。</p>
    </div>
    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
        <h4 style="color: #1a1a1a; margin-bottom: 10px;">臀围 / Hip</h4>
        <p style="font-size: 13px; color: #666; line-height: 1.6;">双脚并拢，用软尺绕臀部最丰满处水平测量一周。</p>
    </div>
    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
        <h4 style="color: #1a1a1a; margin-bottom: 10px;">内长 / Inseam</h4>
        <p style="font-size: 13px; color: #666; line-height: 1.6;">从裤裆底部沿内侧缝线测量至裤脚。标准内长约 80cm (32")。</p>
    </div>
</div>

<!-- Belt Size -->
<h3 style="font-family: 'Playfair Display', serif; color: #c9a96e; margin-top: 40px;">腰带尺码 / Belt Size</h3>
<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;">
    <thead>
        <tr style="background: #1a1a1a; color: #fff;">
            <th style="padding: 12px; text-align: left;">腰带尺码</th>
            <th style="padding: 12px; text-align: left;">腰围 (cm)</th>
            <th style="padding: 12px; text-align: left;">腰围 (inch)</th>
            <th style="padding: 12px; text-align: left;">EU</th>
        </tr>
    </thead>
    <tbody>
        <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px;">S</td>
            <td style="padding: 10px;">70-78</td>
            <td style="padding: 10px;">28-31</td>
            <td style="padding: 10px;">80</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0; background: #fafafa;">
            <td style="padding: 10px;">M</td>
            <td style="padding: 10px;">78-86</td>
            <td style="padding: 10px;">31-34</td>
            <td style="padding: 10px;">85</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0;">
            <td style="padding: 10px;">L</td>
            <td style="padding: 10px;">86-94</td>
            <td style="padding: 10px;">34-37</td>
            <td style="padding: 10px;">90</td>
        </tr>
        <tr style="border-bottom: 1px solid #e0e0e0; background: #fafafa;">
            <td style="padding: 10px;">XL</td>
            <td style="padding: 10px;">94-102</td>
            <td style="padding: 10px;">37-40</td>
            <td style="padding: 10px;">100</td>
        </tr>
    </tbody>
</table>

<!-- Sunglasses -->
<h3 style="font-family: 'Playfair Display', serif; color: #c9a96e; margin-top: 40px;">太阳镜尺寸 / Sunglasses Size</h3>
<p style="font-size: 14px; color: #666;">标准尺寸 (One Size) 适合大多数脸型。镜框宽约 14cm，镜片宽 5.2cm，鼻梁宽 2cm，镜腿长 14cm。</p>

<hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">

<div style="background: #f0f0f0; padding: 20px; border-radius: 8px; margin-top: 30px;">
    <h4 style="color: #1a1a1a; margin-bottom: 10px;">温馨提示 / Tips</h4>
    <ul style="font-size: 13px; color: #666; line-height: 1.8; margin: 0; padding-left: 20px;">
        <li>尺码表仅供参考，不同款式可能有细微差异</li>
        <li>如介于两个尺码之间，建议选大一号（更舒适）</li>
        <li>弹性面料可适当选小一号</li>
        <li>有任何尺码问题，欢迎联系 <a href="mailto:indiagianina5@gmail.com">indiagianina5@gmail.com</a></li>
    </ul>
</div>
HTML;

$page_id = wp_insert_post(array(
    'post_title'   => '服装尺码指南 / Clothing Size Guide',
    'post_name'    => 'size-guide-clothing',
    'post_content' => $html,
    'post_status'  => 'publish',
    'post_type'    => 'page',
    'post_author'  => 1,
));

if (is_wp_error($page_id)) {
    echo "ERROR: " . $page_id->get_error_message() . "\n";
} else {
    echo "SUCCESS: Created clothing size guide page ID {$page_id}\n";
    // Add to menu
    $menu_id = wp_get_nav_menu_object('main-menu');
    if ($menu_id) {
        wp_update_nav_menu_item($menu_id->term_id, 0, array(
            'menu-item-title' => '尺码指南',
            'menu-item-url' => home_url('/size-guide-clothing/'),
            'menu-item-status' => 'publish',
            'menu-item-parent-id' => 0,
        ));
        echo "Added to main menu\n";
    }
}
