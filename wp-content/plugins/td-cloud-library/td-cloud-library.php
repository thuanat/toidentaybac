<?php
/*
Plugin Name: tagDiv Cloud Library
Plugin URI: http://tagdiv.com
Description: Access a huge collection of pre-made templates you can import on your website and customize on the frontend using the tagDiv Composer plugin.
Author: tagDiv
Version: 1.2 | built on 28.05.2019 7:48
Author URI: http://tagdiv.com
*/

//td_cloud location (local or live) - it's set to live automatically on deploy
define('TDB_CLOUD_LOCATION', 'live');

//hash
define('TD_CLOUD_LIBRARY', 'd158fac1e2f85794ec26781eb2a38fd9');

// the deploy mode: dev or deploy  - it's set to deploy automatically on deploy
define('TDB_DEPLOY_MODE', 'deploy');


define('TDB_TEMPLATE_BUILDER_DIR', dirname( __FILE__ ));
define('TDB_URL', plugins_url('td-cloud-library'));

//version check
require_once('tdb_version_check.php');

add_action('td_global_after', 'tdb_hook_td_global_after');
function tdb_hook_td_global_after() {

	//check active theme and automatically disable the plugin if the active theme doesn't support it
	if ( tdb_version_check::is_active_theme_compatible() === false ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	//check PHP version
	if (tdb_version_check::is_php_compatible() === false) {
		return;
	}

	//check theme version
	if (tdb_version_check::is_theme_compatible() === false) {
		return;
	}

	add_action('tdc_init', 'tdb_on_init_template_builder');
	function tdb_on_init_template_builder() {
		require_once( 'includes/tdb_functions.php' );
	}

}

add_action( 'admin_head', 'tdb_on_admin_head' );
function tdb_on_admin_head() {
	echo '<script type="text/javascript">var tdbPluginUrl = "' . TDB_URL . '"</script>';
}

/**
 *  Add 'tdb-template-type' input hidden field to the edit page.
 *  It's used by composer
 */
add_action( 'edit_form_top', 'tdc_on_edit_form_top' );
function tdc_on_edit_form_top() {
	global $post;
	$tdb_template_type = get_post_meta($post->ID, 'tdb_template_type', true);
	if ( empty( $tdb_template_type ) ) {
		$tdb_template_type = 'page';
	}
    echo '<input type="hidden" id="tdb-template-type" name="tdb-template-type" value="' . $tdb_template_type . '" />';
}

/**
 * register the custom post type CPT - this should happen regardless if we have the composer or not to maintain correct wp cpt
 */
add_action('init', 'tdb_on_init_cpt');
function tdb_on_init_cpt() {

	/**
	 * add the tdb_templates custom post type
	 * https://codex.wordpress.org/Function_Reference/register_post_type
	 */

    $labels = array(
        'name'               => 'Cloud Templates',
        'singular_name'      => 'Cloud Template',
        'menu_name'          => 'Cloud Templates',
        'name_admin_bar'     => 'Cloud Template',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Template',
        'new_item'           => 'New Template',
        'edit_item'          => 'Edit Template',
        'view_item'          => 'View Template',
        'all_items'          => 'All Templates',
        'search_items'       => 'Search Templates',
        'not_found'          => 'No templates found.',
        'not_found_in_trash' => 'No templates found in Trash.'
    );

	$args = array(
		'public' => true,
//		'label'  => 'Cloud Templates',
        'labels'  => $labels,
		'supports' => array( // here we specify what the taxonomy supports
			'title',
			'editor',
			'revisions'
		),
		'show_in_admin_bar' => false,
		'show_in_nav_menus' => false,
		'publicly_queryable' => true,
		'hierarchical' => true,
		'exclude_from_search' => true,
	);
	register_post_type( 'tdb_templates', $args );
}



/**
 * Flush permalinks
 */
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'tdb_on_register_activation_hook' );
function tdb_on_register_activation_hook() {
	tdb_on_init_cpt();      // register the cpt
	flush_rewrite_rules();  // and... flush
}



// add the load template button on the welcome screen of td-composer - last (11 priority)
add_action('tdc_welcome_panel_text', function() {
    if (tdc_util::get_get_val('tdbTemplateType') !== false) {
        ?>
        <div class="tdc-sidebar-w-button tdc-zone-button">Header manager</div>
        <?php
    }
}, 11);

// remove wpml translation metabox for tdb_templates
add_action( 'admin_head', function() {
	remove_meta_box('icl_div_config', 'tdb_templates', 'normal');
}, 11);




