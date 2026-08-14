<?php
/**
 * 把完整 Terms & Conditions 内容写入页脚实际指向的页面 (ID 11, /terms-and-conditions/)
 * 并将旧页面 /terms-of-service/ (ID 272) 301 重定向过来，处理 Polylang 翻译关联。
 */
if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

global $wpdb;

$tnc_id  = 11;   // terms-and-conditions (EN, 页脚链接目标)
$tos_id  = 272;  // terms-of-service (EN, 旧页, 有完整内容)
$agb_id  = 380;  // agb (DE)

// 1. 复制完整内容到 ID 11
$content = $wpdb->get_var( $wpdb->prepare( "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d", $tos_id ) );
if ( ! $content ) {
	WP_CLI::error( 'Source page 272 has no content' );
}

$tnc = get_post( $tnc_id );
if ( ! $tnc ) {
	WP_CLI::error( 'Page 11 not found' );
}

wp_update_post( array(
	'ID'           => $tnc_id,
	'post_content' => $content,
	'post_status'  => 'publish',
) );
WP_CLI::success( "Copied full T&C content to page {$tnc_id} ({$tnc->post_name})" );

// 2. Polylang: 把 11 ↔ 380 关联（先清掉 272 与 380 的关联）
if ( function_exists( 'pll_set_post_language' ) ) {
	pll_set_post_language( $tnc_id, 'en' );
	// 解除 272 与 380 的旧关联
	delete_post_meta( $agb_id, '_pll_tr_en' );
	delete_post_meta( $tos_id, 'translations' );
	// 建立新关联
	pll_save_post_translations( array(
		'en' => $tnc_id,
		'de' => $agb_id,
	) );
	WP_CLI::success( "Linked {$tnc_id} (EN) <-> {$agb_id} (DE AGB)" );
}

// 3. 旧页 272 转为 301 重定向（保持发布但内容换成跳转说明 + meta refresh 兜底，或直接改 slug 让 404? 最稳: 用重定向插件没有, 用 wp 重定向）
// 方案: 把 272 设为草稿并在 htaccess 层面不可控 —— 改用 WordPress 方式: 保留页面但通过 template_redirect 跳转 (通过 option 存映射, 由 mu-plugin 处理)
update_option( 'zalandy_redirects', array(
	'terms-of-service' => '/terms-of-conditions/',
) );
WP_CLI::success( 'Set redirect rule: /terms-of-service/ -> /terms-and-conditions/' );

// 4. 输出验证信息
$new = $wpdb->get_var( $wpdb->prepare( "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d", $tnc_id ) );
WP_CLI::line( 'New content length: ' . strlen( $new ) . ' bytes' );
WP_CLI::line( 'Contains VAT: ' . ( strpos( $new, 'DE367264918' ) !== false ? 'YES' : 'NO' ) );
WP_CLI::line( 'Contains EPR: ' . ( strpos( $new, 'DE1649745799617' ) !== false ? 'YES' : 'NO' ) );
