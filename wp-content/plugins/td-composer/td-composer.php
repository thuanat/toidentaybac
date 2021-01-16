<?php
/*
	Plugin Name: tagDiv Composer
	Plugin URI: http://tagdiv.com
	Description: tagDiv Composer - Create everything on your website right on the frontend with this drag and drop builder. Perfect for articles, pages, headers, and footers. No coding skills required.
	Author: tagDiv
	Version: 2.4 | built on 28.05.2019 7:48
	Author URI: http://tagdiv.com
*/

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'td-multi-purpose/td-multi-purpose.php' ) ) {

	deactivate_plugins( 'td-multi-purpose/td-multi-purpose.php' );
	return;
}


//hash
define('TD_COMPOSER',       '61179afdbbd6a8d8c8a7f82ae3fcd87d');
define('TDC_VERSION',       '__td_aurora_deploy_version__');
define('TDC_URL',           plugins_url('td-composer'));
define('TDC_PATH',          dirname(__FILE__));

require_once 'td_deploy_mode.php';
require_once 'includes/tdc_version_check.php';

add_action('td_wp_booster_loaded', 'tdc_plugin_init');
function tdc_plugin_init() {

	//check theme version
	if (tdc_version_check::is_theme_compatible() === false) {
	    return;
	}

	if ( 'Newspaper' === TD_THEME_NAME ) {
		require_once "td-multi-purpose/td-multi-purpose.php";

		if (is_admin() && array_key_exists('theme_panel', td_global::$all_theme_panels_list) && array_key_exists('panels', td_global::$all_theme_panels_list['theme_panel'])) {
	        $separator_panel = 'td-panel-separator-plugin';

	        if (! in_array($separator_panel, td_global::$all_theme_panels_list['theme_panel']['panels'])) {
	            td_global::$all_theme_panels_list['theme_panel']['panels'][$separator_panel] = array(
	                'text' => 'PLUGINS\' SETTINGS',
	                'type' => 'separator',
	            );
	        }

	        td_global::$all_theme_panels_list['theme_panel']['panels']['td-multipurpose-plugin'] = array(
	            'text' => 'MULTI-PURPOSE',
	            'ico_class' => 'td-ico-multi',
	            'file' => plugin_dir_path(__FILE__) . 'td-multi-purpose/td_panel_settings.php',
	            'type' => 'in_plugin',
	        );
	    }
	}

	require_once "legacy/common/common.php";

	// Hook - used by other plugins to know the composer is on
	do_action( 'tdc_init' );

	// load the plugin config
	require_once('includes/tdc_config.php');

	if ( 'Newspaper' === TD_THEME_NAME ) {
		// This must be set here, not only on admin. And it must be set after td_global::$typography_settings_list has been set!
		td_api_multi_purpose::set_typography_list();
	}

	// load the plugin
	require_once "includes/tdc_main.php";

	// register 'css-live' extension
	require_once "css-live/css-live.php";

	// Hook - used by other plugins to know the composer is loaded
    // here we can map aditional shortcodes
	do_action( 'tdc_loaded' );
}




add_action('td_wp_booster_legacy', function() {

	define('TDC_URL_LEGACY',    TDC_URL . '/legacy/' . TD_THEME_NAME );
	define('TDC_PATH_LEGACY',   TDC_PATH . '/legacy/' . TD_THEME_NAME );

	define('TDC_URL_LEGACY_COMMON',   TDC_URL . '/legacy/common' );
	define('TDC_PATH_LEGACY_COMMON',   TDC_PATH . '/legacy/common' );

	// load the wp booster
	require_once('legacy/' . TD_THEME_NAME . '/functions.php');
});


/**
 * 'template_include' hook must be removed by mobile theme
 */
add_filter( 'template_include', 'tdc_template_include', 99);
add_filter( 'comments_template', 'tdc_template_include', 99 );
function tdc_template_include($template) {

	$template_file = wp_basename($template);

	$file_path = TDC_PATH_LEGACY . '/' . $template_file;

	if ( file_exists( $file_path ) ) {
		return $file_path;
	}

	return $template;
}


add_action( 'tdc_sidebar', function() {
	require_once( TDC_PATH_LEGACY_COMMON . '/wp_booster/sidebar.php');
});

add_action( 'tdc_header', function() {
	require_once( TDC_PATH_LEGACY . '/header.php');
});

add_action( 'tdc_footer', function() {
	require_once( TDC_PATH_LEGACY . '/footer.php');
});

add_action( 'tdc_woo_archive_product', function() {
	require_once( TDC_PATH_LEGACY . '/woocommerce/archive-product.php');
});

add_action( 'tdc_woo_single_product', function() {
    require_once( TDC_PATH_LEGACY . '/woocommerce/single-product.php');
});




