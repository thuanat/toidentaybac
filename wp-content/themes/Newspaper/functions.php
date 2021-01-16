<?php

define('TAGDIV_ROOT', get_template_directory_uri());
define('TAGDIV_ROOT_DIR', get_template_directory());


// load the deploy mode
require_once( TAGDIV_ROOT_DIR . '/tagdiv-deploy-mode.php' );



/**
 * Theme configuration.
 */
require_once TAGDIV_ROOT_DIR . '/includes/tagdiv-config.php';


/**
 * Theme wp booster.
 */
require_once( TAGDIV_ROOT_DIR . '/includes/wp-booster/tagdiv-wp-booster-functions.php');


/**
 * Theme page generator support.
 */
if ( ! class_exists('tagdiv_page_generator' ) ) {
	include_once ( TAGDIV_ROOT_DIR . '/includes/tagdiv-page-generator.php');
}


/* ----------------------------------------------------------------------------
 * Add theme support for sidebar
 */
add_action( 'widgets_init', function() {
    register_sidebar(
        array(
            'name'=> 'Newspaper default',
            'id' => 'td-default',
            'before_widget' => '<aside class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<div class="block-title"><span>',
            'after_title' => '</span></div>'
        )
    );
});


/**
 * Theme setup.
 */
add_action( 'after_setup_theme', function (){

	/**
	 * Loads the theme's translated strings.
	 */
	load_theme_textdomain( strtolower(TD_THEME_NAME ), get_template_directory() . '/translation' );

	/**
	 * Theme menu location.
	 */
	register_nav_menus(
		array(
			'header-menu' => 'Header Menu (main)',
			'footer-menu' => 'Footer Menu',
		)
	);
});


/* ----------------------------------------------------------------------------
 * Add theme support for features
 */
add_theme_support('title-tag');
add_theme_support('post-thumbnails');
add_theme_support('automatic-feed-links');
add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
add_theme_support('woocommerce');
add_theme_support('bbpress');
add_theme_support('align-wide');
add_theme_support('align-full');


global $content_width;
if ( !isset($content_width) ) {
    $content_width = 696;
}



/* ----------------------------------------------------------------------------
 * Woo Commerce
 */
// breadcrumb
add_filter('woocommerce_breadcrumb_defaults', 'tagdiv_woocommerce_breadcrumbs');
function tagdiv_woocommerce_breadcrumbs() {
    return array(
        'delimiter' => ' <i class="td-icon-right td-bread-sep"></i> ',
        'wrap_before' => '<div class="entry-crumbs" itemprop="breadcrumb">',
        'wrap_after' => '</div>',
        'before' => '',
        'after' => '',
        'home' => _x('Home', 'breadcrumb', 'newspaper'),
    );
}

// Number of product per page 4
add_filter('loop_shop_per_page', 'tagdiv_wc_loop_shop_per_page' );
function tagdiv_wc_loop_shop_per_page($cols) {
    return 4;
}

// use own pagination
if (!function_exists('woocommerce_pagination')) {
    // pagination
    function woocommerce_pagination() {
        tagdiv_page_generator::get_pagination();
    }
}

if (!function_exists('woocommerce_output_related_products')) {
    // Number of related products
    function woocommerce_output_related_products() {
        woocommerce_related_products(array(
            'posts_per_page' => 4,
            'columns' => 4,
            'orderby' => 'rand',
        )); // Display 4 products in rows of 1
    }
}





/* ----------------------------------------------------------------------------
* front end css files
*/
if( !function_exists('tagdiv_theme_css') ) {
    function tagdiv_theme_css() {
        wp_enqueue_style('td-theme', get_stylesheet_uri() );

        // load the WooCommerce CSS only when needed
        if ( class_exists('WooCommerce', false) ) {
            wp_enqueue_style('td-theme-woo', get_template_directory_uri() . '/style-woocommerce.css' );
        }

        // load the Bbpress CSS only when needed
        if ( class_exists('bbPress', false) ) {
            wp_enqueue_style('td-theme-bbpress', get_template_directory_uri() . '/style-bbpress.css' );
        }
    }
}
add_action('wp_enqueue_scripts', 'tagdiv_theme_css', 1001);


//Insert ads after second paragraph of single post content.
add_filter( 'the_content', 'prefix_insert_post_ads' );
 
function prefix_insert_post_ads( $content ) {
 
    $ad_code = '<div id="content-seo" style="">
<blockquote style="background: rgb(242, 242, 242); border-radius: 0px; border: 1px solid rgb(220, 191, 162); box-shadow: rgb(242, 242, 242) 0px 0px 0px 2px; padding: 8px; position: relative;text-align: left;margin: 8px 30px 10px 30px;">
Xem thêm dịch vụ tại <a href="http://luatmultilaw.com">MULTI LAW</a><br>
<ul>
<li><h2 style="font-size: 14px;line-height: 10px; margin-top: 0px; margin-bottom: 10px;"><a href="https://luatsugiadinh24h.com/ly-hon-thuan-tinh">Dịch vụ ly hôn thuận tình</a> trọn gói</h2></li>
<li><h2 style="font-size: 14px;line-height: 10px; margin-top: 0px; margin-bottom: 10px;"><a href="https://luatsugiadinh24h.com/ly-hon-don-phuong">Dịch vụ ly hôn đơn phương</a> trọn gói</h2></li>
<li><h2 style="font-size: 14px;line-height: 10px; margin-top: 0px; margin-bottom: 10px;"><a href="http://luatmultilaw.com/">Dịch vụ ly hôn trọn gói tại Hà Nội</a></h2></li>
<li><h2 style="font-size: 14px;line-height: 10px; margin-top: 0px; margin-bottom: 10px;"><a href="https://luatsugiadinh24h.com/tranh-chap-tai-san-khi-ly-hon">Trang chấp tài sản khi ly hôn</a></h2></li>
</ul>
<span style="color: #0b5394;">Luật sư chuyên giải quyết hôn nhân và gia đình, tranh chấp đất đai, tài sản, thừa kế.<br>
Gọi ngay:</span>&nbsp;<a href="tel:0989082888"><span style="color: #0b5394;"><b>0989.082.888</b></span></a>
</blockquote>
</div>';
 
    if ( is_single() && ! is_admin() ) {
        return prefix_insert_after_paragraph( $ad_code, 5, $content );
    }
 
    return $content;

}
 
// Parent Function that makes the magic happen
 
function prefix_insert_after_paragraph( $insertion, $paragraph_id, $content ) {
    $closing_p = '</p>';
    $paragraphs = explode( $closing_p, $content );
    foreach ($paragraphs as $index => $paragraph) {
 
        if ( trim( $paragraph ) ) {
            $paragraphs[$index] .= $closing_p;
        }
 
        if ( $paragraph_id == $index + 1 ) {
            $paragraphs[$index] .= $insertion;
        }
    }
 
    return implode( '', $paragraphs );

}


function wpdocs_custom_excerpt_length( $length ) {
    return 20;
}
add_filter( 'excerpt_length', 'wpdocs_custom_excerpt_length', 999 );

