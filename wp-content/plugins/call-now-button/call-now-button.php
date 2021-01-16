<?php
/*
Plugin Name: Call Now Button
Plugin URI: https://callnowbutton.com
Description: Mobile visitors will see a <strong>Call Now Button</strong> on your website. Easy to use but flexible to meet more demanding requirements. Change placement and color, hide on specific pages, track how many people click them or conversions of your Google Ads campaigns. It's all optional but possible.
Version: 0.4.1
Author: Jerry Rietveld
Author URI: http://www.jgrietveld.com
License: GPL2
*/

/*  Copyright 2013-2017  Jerry Rietveld  (email : jerry@jgrietveld.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
define('CNB_VERSION','0.4.1');
define('CNB_BASENAME', plugin_basename( __FILE__ ) );
define('CNB_BASEFOLDER', plugin_basename( dirname( __FILE__ ) ) );
define('CNB_FILENAME', str_replace( CNB_BASEFOLDER.'/', '', CNB_BASENAME ) );
define('CNB_WEBSITE','https://callnowbutton.com/');
define('CNB_SUPPORT', CNB_WEBSITE . 'support/');
add_action('admin_menu', 'cnb_register_admin_page');
add_action('admin_init', 'cnb_options_init');

$cnb_changelog = 
array(
	array(
		'4.0' => 'Text bubbles for standard buttons, set the icon color, Google Ads conversion tracking, tabbed admin interface, 6 additional button locations, small button design changes, added support articles for (nearly) all settings, control visibility on front page, plus a bunch of smaller fixes. Enjoy!',
		'3.6' => 'Small validation fixes and zoom now controls icon size in full width buttons.',
		'3.5' => 'Small JS fix',
		'3.4' => 'Option to resize your button and change where it sits in the stack order (z-index).',
		'3.3' => 'Some small improvements.',
		'3.2' => 'Option to hide icon in text button, small bug fixes.',
		'3.1' => 'You can now add text to your button and it\'s possible to switch between including and excluding specific pages.',
		'3.0' => 'Option to add text to your button.',
		'2.1' => 'Some small fixes',
		'2.0' => 'The Call Now Button has a new look!'
	)
);

$cnb_settings = cnb_get_options(); // Grabbing the settins and checking for latest version OR creating the options file for first time installations
$cnb_options = $cnb_settings['options'];
$cnb_updated = $cnb_settings['updated'];


$cnb_options['active'] = isset($cnb_options['active']) ? 1 : 0;
$cnb_options['classic'] = isset($cnb_options['classic']) ? 1 : 0;
$cnb_options['hideIcon'] = isset($cnb_options['hideIcon']) ? $cnb_options['hideIcon'] : 0;
$cnb_options['frontpage'] = isset($cnb_options['frontpage']) ? $cnb_options['frontpage'] : 0;

$plugin_title = apply_filters( 'cnb_plugin_title',  'Call Now Button');

if(isset($_GET['page']) && strpos($_GET['page'], 'call-now-button') !== false) {
	add_action( 'admin_enqueue_scripts', 'cnb_enqueue_color_picker' ); // add the color picker
}

function cnb_register_admin_page() {
	global $plugin_title;
	$page = add_submenu_page('options-general.php', $plugin_title, $plugin_title, 'manage_options', 'call-now-button', 'cnb_admin_settings_page');
	add_action( 'admin_print_styles-' . $page , 'cnb_admin_styling' );
}
function cnb_enqueue_color_picker( $hook_suffix ) {
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'cnb-script-handle', plugins_url('call-now-button.js', __FILE__ ), array( 'wp-color-picker' ), CNB_VERSION, true );
}
function cnb_admin_styling() {
	wp_enqueue_style( 'cnb_styling' );
}

function cnb_plugin_meta($links, $file) {
	if ( $file == CNB_BASENAME ) {
		$cnb_new_links = array(
		sprintf( '<a href="options-general.php?page=%s">%s</a>', CNB_BASEFOLDER, __('Settings')),
		'<a href="'.CNB_SUPPORT.'">Support</a>');
		array_push(
			$links,
			$cnb_new_links[0],
			$cnb_new_links[1]
		);
	}	
	return $links;
}
add_filter( 'plugin_row_meta', 'cnb_plugin_meta', 10, 2 );

function cnb_plugin_add_settings_link( $links ) {
    array_unshift( $links, sprintf( '<a href="options-general.php?page=%s">%s</a>', CNB_BASEFOLDER, __('Settings') ) );
  	return $links;
}
add_filter( 'plugin_action_links_'. CNB_BASENAME, 'cnb_plugin_add_settings_link' );

function cnb_options_init() {
	register_setting('cnb_options','cnb');
	wp_register_style( 'cnb_styling', plugins_url('call-now-button.css', __FILE__), false, CNB_VERSION, 'all' );
}
function cnb_admin_settings_page() { 
	global $cnb_options;
	global $plugin_title;
	global $cnb_updated;
	global $cnb_changelog;
	?>
	
	<div class="wrap">	
		<h1>Call Now Button <span class="version">v<?php echo CNB_VERSION;?></span></h1>
		
	<!--## NOTIFICATION BARS ##  -->
		<?php 
		// Display notification that the button is active or inactive
		if(!$cnb_options['active']==1) {
			echo '<div class="notice-error notice"><p>The Call Now Button is currently <b>inactive</b>.</p></div>';
		}

		// Display notification that there's a caching plugin active
		if(isset($_GET['settings-updated'])) {
			$cnb_caching_check = cnb_check_for_caching();
			if($cnb_caching_check[0] == TRUE) {
				echo '<div class="notice-error notice"><p><span class="dashicons dashicons-warning"></span> Your website is using a <strong><i>Caching Plugin</i></strong> ('.$cnb_caching_check[1].'). If you\'re not seeing your button or your changes, make sure you empty your cache first.</p></div>';
			}
		}

		// inform exisiting users about update to the button design
		if($cnb_updated[0]) { ?>
		<div class="notice-warning notice is-dismissible">
			<?php
				$cnb_old_version = substr($cnb_updated[1],2);
				echo "<h3>The Call Now Button has been updated!</h3><h4>What's new?</h4>";
				foreach ($cnb_changelog[0] as $key => $value) { // Only on first run after update show list of changes since last update
					if($key > $cnb_old_version) {
						echo '<p><span class="dashicons dashicons-yes"></span> ' . $value . '</p>';
					}
				}
			?>
		</div>
		<?php } 


if( isset( $_GET[ 'tab' ] ) ) {
    $active_tab = $_GET[ 'tab' ];
} else {
	$active_tab = "basic_options";
} // end if


		?>
<h2 class="nav-tab-wrapper">
    <a href="?page=call-now-button&tab=basic_options" class="nav-tab <?php echo $active_tab == 'basic_options' ? 'nav-tab-active' : ''; ?>">Basics</a>
    <a href="?page=call-now-button&tab=extra_options" class="nav-tab <?php echo $active_tab == 'extra_options' ? 'nav-tab-active' : ''; ?>">Presentation</a>
    <a href="?page=call-now-button&tab=advanced_options" class="nav-tab <?php echo $active_tab == 'advanced_options' ? 'nav-tab-active' : ''; ?>">Advanced</a>
</h2>
<form method="post" action="options.php" class="cnb-container">
	<?php settings_fields('cnb_options'); ?>
	<table class="form-table <?php echo $active_tab == 'basic_options' ? 'nav-tab-active' : ''; ?>">
		<tr>
			<th colspan="2"><h2>Basic Settings</h2></th>
		</tr>
    	<tr valign="top">
			<th scope="row">Button status:</th>
	    	<td class="activated">
	        	<input id="activated" name="cnb[active]" type="checkbox" value="1" <?php checked('1', $cnb_options['active']); ?> /> <label title="Enable" for="activated">Enabled</label> &nbsp; &nbsp; 
	        </td>
	    </tr>
		<tr valign="top">
			<th scope="row">Phone number:<a href="<?php echo CNB_SUPPORT; ?>phone-number/<?php cnb_utm_params("question-mark", "phone-number"); ?>" target="_blank" class="nounderscore">
	        			<span class="dashicons dashicons-editor-help"></span>
	        		</a></th>
			<td><input type="text" name="cnb[number]" value="<?php echo $cnb_options['number']; ?>" /></td>
		</tr>
		<tr valign="top" class="button-text">
			<th scope="row">Button text <small style="font-weight: 400">(optional)</small>:<a href="<?php echo CNB_SUPPORT; ?>using-text-buttons/<?php cnb_utm_params("question-mark", "using-text-buttons"); ?>" target="_blank" class="nounderscore">
	        			<span class="dashicons dashicons-editor-help"></span>
	        		</a></th>
			<td>
				<input id="buttonTextField" type="text" name="cnb[text]" value="<?php echo $cnb_options['text']; ?>" maxlength="30"  />
				<p class="description">Leave this field empty to only show an icon.</p>
			</td>
		</tr>
	</table>
    
	<table class="form-table <?php echo $active_tab == 'extra_options' ? 'nav-tab-active' : ''; ?>">
		<tr>
			<th colspan="2"><h2>Presentation Settings</h2></th>
		</tr>
		
		<tr valign="top">
			<th scope="row">Button color:</th>
	    	<td><input name="cnb[color]" type="text" value="<?php echo $cnb_options['color']; ?>" class="cnb-color-field" data-default-color="#009900" /></td>
	    </tr>
		<tr valign="top">
			<th scope="row">Icon color:</th>
	    	<td><input name="cnb[iconcolor]" type="text" value="<?php echo $cnb_options['iconcolor']; ?>" class="cnb-iconcolor-field" data-default-color="#ffffff" /></td>
	    </tr>
	    <tr valign="top">
			<th scope="row">Position: <a href="<?php echo CNB_SUPPORT; ?>button-position/<?php cnb_utm_params("question-mark", "button-position"); ?>" target="_blank" class="nounderscore">
	        			<span class="dashicons dashicons-editor-help"></span>
	        		</a></th>
	    	<td class="appearance">
	    		<div class="appearance-options">
		        	<div class="radio-item">
			            <input type="radio" id="appearance1" name="cnb[appearance]" value="right" <?php checked('right', $cnb_options['appearance']); ?>>
			            <label title="right" for="appearance1">Right corner</label>
		        	</div>
		        	<div class="radio-item">
			            <input type="radio" id="appearance2" name="cnb[appearance]" value="left" <?php checked('left', $cnb_options['appearance']); ?>>
			            <label title="left" for="appearance2">Left corner</label>
		        	</div>
		        	<div class="radio-item">
			            <input type="radio" id="appearance3" name="cnb[appearance]" value="middle" <?php checked('middle', $cnb_options['appearance']); ?>>
			            <label title="middle" for="appearance3">Center bottom</label>
		        	</div>
		        	<div class="radio-item">
			            <input type="radio" id="appearance4" name="cnb[appearance]" value="full" <?php checked('full', $cnb_options['appearance']); ?>>
			            <label title="full" for="appearance4">Full bottom</label>  
		            </div>
		            
		            <!-- Extra placement options -->
		            <br class="cnb-extra-placement">
		        	<div class="radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "mright" ? "cnb-extra-active" : ""; ?>">
			            <input type="radio" id="appearance5" name="cnb[appearance]" value="mright" <?php checked('mright', $cnb_options['appearance']); ?>>
			            <label title="mright" for="appearance5">Middle right</label>
		        	</div>
		        	<div class="radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "mleft" ? "cnb-extra-active" : ""; ?>">
			            <input type="radio" id="appearance6" name="cnb[appearance]" value="mleft" <?php checked('mleft', $cnb_options['appearance']); ?>>
			            <label title="mleft" for="appearance6">Middle left </label>
		        	</div>
		        	<br class="cnb-extra-placement">
		        	<div class="radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "tright" ? "cnb-extra-active" : ""; ?>">
			            <input type="radio" id="appearance7" name="cnb[appearance]" value="tright" <?php checked('tright', $cnb_options['appearance']); ?>>
			            <label title="tright" for="appearance7">Top right corner</label>
		        	</div>
		        	<div class="radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "tleft" ? "cnb-extra-active" : ""; ?>">
			            <input type="radio" id="appearance8" name="cnb[appearance]" value="tleft" <?php checked('tleft', $cnb_options['appearance']); ?>>
			            <label title="tleft" for="appearance8">Top left corner</label>
		        	</div>
		        	<div class="radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "tmiddle" ? "cnb-extra-active" : ""; ?>">
			            <input type="radio" id="appearance9" name="cnb[appearance]" value="tmiddle" <?php checked('tmiddle', $cnb_options['appearance']); ?>>
			            <label title="tmiddle" for="appearance9">Center top</label>
		        	</div>
		        	<div class="radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "tfull" ? "cnb-extra-active" : ""; ?>">
			            <input type="radio" id="appearance10" name="cnb[appearance]" value="tfull" <?php checked('tfull', $cnb_options['appearance']); ?>>
			            <label title="tfull" for="appearance10">Full top</label>  
		            </div>
		            <a href="#" id="cnb-more-placements">More placement options...</a>
		            <!-- END extra placement options -->
	        	</div>
	        	
	        	<div id="hideIconTR">
	        		<br>
		            <input id="hide_icon" type="checkbox" name="cnb[hideIcon]" value="1" <?php checked('1', $cnb_options['hideIcon']); ?>>
		            <label title="right" for="hide_icon">Remove icon</label>
	        	</div> 
	        </td>
	    </tr>
		<tr valign="top" class="appearance">
			<th scope="row">Limit appearance: <a href="<?php echo CNB_SUPPORT; ?>limit-appearance/<?php cnb_utm_params("question-mark", "limit-appearance"); ?>" target="_blank" class="nounderscore">
	        			<span class="dashicons dashicons-editor-help"></span>
	        		</a></th>
			<td>					 
				<input type="text" name="cnb[show]" value="<?php echo $cnb_options['show']; ?>" placeholder="E.g. 14, 345" />
				<p class="description">Enter IDs of the posts &amp; pages, separated by commas (leave blank for all). <a href="<?php echo CNB_SUPPORT; ?>limit-appearance/<?php cnb_utm_params("question-mark", "limit-appearance"); ?>">Learn more...</a></p>
				<div class="radio-item">
					<input id="limit1" type="radio" name="cnb[limit]" value="include" <?php checked('include', $cnb_options['limit']);?> />
					<label for="limit1">Limit to these posts and pages.</label>
				</div>
				<div class="radio-item">
					<input id="limit2" type="radio" name="cnb[limit]" value="exclude" <?php checked('exclude', $cnb_options['limit']);?> />
					<label for="limit2">Exclude these posts and pages.</label>
				</div>
				<br>
				<div>
		            <input id="frontpage" type="checkbox" name="cnb[frontpage]" value="1" <?php checked('1', $cnb_options['frontpage']); ?>>
		            <label title="right" for="frontpage">Hide button on front page</label>
				</div>
			</td>
		</tr>
	</table>
	<table class="form-table <?php echo $active_tab == 'advanced_options' ? 'nav-tab-active' : ''; ?>">
		<tr>
			<th colspan="2"><h2>Advanced Settings</h2></th>
		</tr>
		<tr valign="top">
			<th scope="row">Click tracking: <a href="<?php echo CNB_SUPPORT; ?>click-tracking/<?php cnb_utm_params("question-mark", "click-tracking"); ?>" target="_blank" class="nounderscore">
	        			<span class="dashicons dashicons-editor-help"></span>
	        		</a></th>
			<td> 
			    <div class="radio-item">
				    <input id="tracking3" type="radio" name="cnb[tracking]" value="0" <?php checked('0', $cnb_options['tracking']); ?> /> 
				    <label for="tracking3">Disabled</label>
				</div>
			    <div class="radio-item">
				    <input id="tracking4" type="radio" name="cnb[tracking]" value="3" <?php checked('3', $cnb_options['tracking']); ?> /> 
				    <label for="tracking4">Latest Google Analytics (gtag.js)</label>
			    </div>
				<div class="radio-item">
					<input id="tracking1" type="radio" name="cnb[tracking]" value="2" <?php checked('2', $cnb_options['tracking']); ?> /> 
					<label for="tracking1">Google Universal Analytics (analytics.js)</label>
			    </div>
			    <div class="radio-item">
				    <input id="tracking2" type="radio" name="cnb[tracking]" value="1" <?php checked('1', $cnb_options['tracking']); ?> /> 
				    <label for="tracking2">Classic Google Analytics (ga.js)</label>
			    </div>
				<p class="description">Using Google Tag Manager? Set up click tracking in GTM. <a href="<?php echo CNB_SUPPORT; ?>click-tracking/google-tag-manager-event-tracking/<?php cnb_utm_params("description_link", "google-tag-manager-event-tracking"); ?>" target="_blank">Learn how to do this...</a></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Google Ads: <a href="<?php echo CNB_SUPPORT; ?>google-ads/<?php cnb_utm_params("question-mark", "google-ads"); ?>" target="_blank" class="nounderscore">
	        			<span class="dashicons dashicons-editor-help"></span>
	        		</a></th>
	    	<td class="conversions">
			    <div class="radio-item">
				    <input name="cnb[conversions]" type="radio" value="0" <?php checked('0', $cnb_options['conversions']); ?> /> <label for="conversions">Off </label>
				</div>
	        	<div class="radio-item">
	        		<input name="cnb[conversions]" type="radio" value="1" <?php checked('1', $cnb_options['conversions']); ?> /> <label for="conversions">Conversion Tracking using Google's global site tag </label>
	        	</div>
	        	<div class="radio-item">
	        		<input name="cnb[conversions]" type="radio" value="2" <?php checked('2', $cnb_options['conversions']); ?> /> <label for="conversions">Conversion Tracking using JavaScript</label>
	        	</div>
	        	<p class="description">Select this option if you want to track clicks on the button as Google Ads conversions. This option requires the Event snippet to be present on the page. <a href="https">Learn more...</a></p>
	        </td>
	    </tr>
		<tr valign="top" class="zoom">
			<th scope="row">Button size (<span id="cnb_slider_value"></span>):</th>
			<td>
				<label class="cnb_slider_value">Smaller&nbsp;&laquo;&nbsp;</label>
				<input type="range" min="0.7" max="1.3" name="cnb[zoom]" value="<?php echo $cnb_options['zoom']; ?>" class="slider" id="cnb_slider" step="0.1">
				<label class="cnb_slider_value">&nbsp;&raquo;&nbsp;Bigger</label>				
			</td>
		</tr>
		<tr valign="top" class="z-index">
			<th scope="row">Order (<span id="cnb_order_value"></span>): <a href="https://callnowbutton.com/set-order/" target="_blank" class="nounderscore">
	        			<span class="dashicons dashicons-editor-help"></span>
	        		</a></th>
			<td>
				<label class="cnb_slider_value">Backwards&nbsp;&laquo;&nbsp;</label>
				<input type="range" min="1" max="10" name="cnb[z-index]" value="<?php echo $cnb_options['z-index']; ?>" class="slider2" id="cnb_order_slider" step="1">
				<label class="cnb_slider_value">&nbsp;&raquo;&nbsp;Front</label>
				<p class="description">The default (and recommended) value is all the way to the front so the button sits on top of everything else. In case you have a specific usecase where you want something else to sit in front of the Call Now Button (e.g. a chat window or a cookie notice) you can move this backwards one step at a time to adapt it to your situation.</p>
			</td>
		</tr>
		<?php if($cnb_options['classic'] == 1) { ?> 
		<tr valign="top" class="classic">
			<th scope="row">Classic button: <a href="https://callnowbutton.com/new-button-design/<?php cnb_utm_params("question-mark", "new-button-design"); ?>" target="_blank" class="nounderscore">
	        			<span class="dashicons dashicons-editor-help"></span>
	        		</a></th>
	    	<td>
	        	<input id="classic" name="cnb[classic]" type="checkbox" value="1" <?php checked('1', $cnb_options['classic']); ?> /> <label title="Enable" for="classic">Active</label>
	        </td>
		</tr>
		<?php } ?>
	</table>
	
	<input type="hidden" name="cnb[version]" value="<?php echo CNB_VERSION; ?>" />
	<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
</form>

		<div class="feedback-collection">
			<div class="cnb-clear"></div>
  			<p class="cnb-url cnb-center"><a href="https://callnowbutton.com" target="_blank">callnowbutton.com</a></p>
	        
	  		<p class="cnb-center cnb-spacing">
	  			<a href="<?php echo CNB_SUPPORT; cnb_utm_params("footer-links", "support"); ?>" target="_blank" title="Support">Support</a> &middot;
	        	<a href="<?php echo CNB_WEBSITE; ?>feature-request/<?php cnb_utm_params("footer-links", "suggestions"); ?>" target="_blank" title="Feature Requests">Suggestions</a> &middot; 
	        	<a href="https://www.paypal.com/paypalme/jgrietveld">Donate</a> &middot; 
	        	<a href="<?php echo CNB_WEBSITE; ?>praise/<?php cnb_utm_params("footer-links", "thanks"); ?>" target="_blank" title="Praise">Just say thanks :-)</a>
	        </p>
	        <!--// Display notification about the testing program -->
			<div class="postbox cnb-alert-box cnb-center">
				<p>The Call&nbsp;Now&nbsp;Button&nbsp;<b>Pro</b> is imminent. 
					<a class="cnb-external" href="https://callnowbutton.com/be-notified-call-now-button-pro/<?php cnb_utm_params("footer-links", "notify-of-pro"); ?>" rel="help" target="_blank">Be the first to know!</a>
				</p>
			</div>
			
	        <div class="donate cnb-center">
	            <a href="https://www.paypal.com/paypalme/jgrietveld"><img src="https://callnowbutton.com/wp-content/uploads/2020/10/btn_donateCC_LG.gif"></a>
	        </div><!--.donate-->
	    </div>
    </div>
<?php }
if(get_option('cnb') && !is_admin()) {
	
	$cnb_options = get_option('cnb');
	$cnb_enabled = (isset($cnb_options['active'])) ? true : false;

	if($cnb_enabled) {
		function cnb_head() {
			global $cnb_options;
			$cnb_has_text = ($cnb_options['text'] == '') ? false : true;
			$cnb_is_full_width = $cnb_options['appearance'] == "full" || $cnb_options['appearance'] == "tfull"  ? true : false;
			$cnb_is_classic	= isset($cnb_options['classic']) && $cnb_options['classic'] == 1 && !$cnb_has_text ? true : false;
			$cnb_button_css = "\n<!-- Call Now Button ".CNB_VERSION." by Jerry Rietveld (callnowbutton.com) -->\n";

			
			$ButtonExtra = "";
			if($cnb_is_classic) { 

			// OLD BUTTON DESIGN			
				if($cnb_options['appearance'] == 'full' || $cnb_options['appearance'] == 'middle' || $cnb_has_text) {
					$cnb_button_appearance = "width:100%;left:0;";
					$ButtonExtra = "body {padding-bottom:60px;}";				
				} 
				elseif($cnb_options['appearance'] == 'left') { 
					$cnb_button_appearance = "width:100px;left:0;border-bottom-right-radius:40px; border-top-right-radius:40px;";
				} else {
					$cnb_button_appearance = "width:100px;right:0;border-bottom-left-radius:40px; border-top-left-radius:40px;";
				}
				
				$cnb_button_css .= "<style>#callnowbutton, #callnowbutton span {display:none;} @media screen and (max-width:650px){#callnowbutton .NoButtonText{display:none;}#callnowbutton {display:block; ".$cnb_button_appearance." height:80px; position:fixed; bottom:-20px; border-top:2px solid ".changeColor($cnb_options['color'],'lighter')."; background:url(data:image/svg+xml;base64,".svg(changeColor($cnb_options['color'], 'darker'),$cnb_options['iconcolor'] ).") center 2px no-repeat ".$cnb_options['color']."; text-decoration:none; box-shadow:0 0 5px #888; z-index:".zindex($cnb_options['z-index']).";background-size:58px 58px}".$ButtonExtra."}</style>\n";

			} else {

			// NEW BUTTON DESIGN
				$cnb_button_shape = "width:65px; height:65px; border-radius:50%; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);transform: scale(" . $cnb_options['zoom'] . ");";
				$cnb_button_positions = array(
					'middle' 	=> 'bottom:15px; left:50%; margin-left:-33px;',
					'left' 		=> 'bottom:15px; left:20px;',
					'right' 	=> 'bottom:15px; right:20px;',
					'mleft' 	=> 'top:50%; margin-top:-33px; left:20px;',
					'mright' 	=> 'top:50%; margin-top:-33px; right:20px;',
					'tleft' 	=> 'top:15px; left:20px;',
					'tmiddle' 	=> 'top:15px; left:50%; margin-left:-33px;',
					'tright' 	=> 'top:15px; right:20px;',
				);

				if($cnb_options['appearance'] == 'full' || $cnb_options['appearance'] == 'tfull') {
					$cnb_top_or_bottom = ($cnb_options['appearance']) == 'full' ? "bottom" : "top";
					
					$cnb_button_appearance = "width:100%;left:0;".$cnb_top_or_bottom.":0;height:60px;";
					
					$ButtonExtra = "body {padding-".$cnb_top_or_bottom.":60px;}#callnowbutton img {transform: scale(" . $cnb_options['zoom'] . ");}";
					if($cnb_has_text) {
						$cnb_button_appearance .= "text-align:center;color:#fff; font-weight:600; font-size:120%;  overflow: hidden;";
						if(isset($cnb_options['hideIcon']) && $cnb_options['hideIcon'] == 1) {
							$cnb_button_appearance .= 'padding-right:20px;';
						}
					}
				} else {
					$cnb_button_appearance = $cnb_button_shape . $cnb_button_positions[$cnb_options['appearance']]; 
				}

				$cnb_label_side = ltrim(ltrim($cnb_options['appearance'],"m"),"t");

				if($cnb_has_text && ($cnb_options['appearance'] == 'middle' || $cnb_options['appearance'] == 'tmiddle')) { // don't show the label in this situation
					$circularButtonTextCSS = "#callnowbutton span{display: none;";
				} elseif($cnb_has_text && !$cnb_is_full_width){
					$circularButtonTextCSS = "#callnowbutton span{
						display: block;
						width: auto;
						background: rgba(243, 243, 243, 0.96);
						border-top:1px solid rgba(243, 243, 243, 1);
						position: absolute;"
						.$cnb_label_side.": 74px;
						border-radius: 5px;
						padding: 2px 15px;
						font-size: 15px;
						color: #505050;
						top: 20px;
						box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
						word-break: keep-all;
						line-height: 1;	
					}";
				} elseif(!$cnb_is_full_width) {
					$circularButtonTextCSS = "#callnowbutton span{display:none;}";
				} else {
					$circularButtonTextCSS = "";
				}

				$cnb_button_css = $cnb_button_css ."<style>";
				$cnb_button_css .= "#callnowbutton {display:none;} @media screen and (max-width:650px){#callnowbutton {display:block; position:fixed; text-decoration:none; z-index:".zindex($cnb_options['z-index']).";";
				$cnb_button_css .= $cnb_button_appearance;
				if($cnb_is_full_width) {
					$cnb_button_css .= "background:".$cnb_options['color'].";display: flex; justify-content: center; align-items: center;text-shadow: 0 1px 0px rgba(0, 0, 0, 0.18);";
				} else {
					$cnb_button_css .= "background:url(data:image/svg+xml;base64,".svg(changeColor($cnb_options['color'], 'darker'),$cnb_options['iconcolor'] ).") center/45px 45px no-repeat ".$cnb_options['color'].";";
				}
				$cnb_button_css .= "}" . $ButtonExtra . "}" . $circularButtonTextCSS;
				$cnb_button_css .= "</style>\n";
				
			}
			echo $cnb_button_css;
		}
		add_action('wp_head', 'cnb_head');
		
		function cnb_footer() {
			global $cnb_options;

			$cnb_hide_icon 	= isset($cnb_options['hideIcon']) && $cnb_options['hideIcon'] == 1 ? true : false;
			$cnb_has_text 	= ($cnb_options['text'] == '') ? false : true;
			$cnb_is_classic	= isset($cnb_options['classic']) && $cnb_options['classic'] == 1 ? true : false;
			$cnb_show_limited 	= isset($cnb_options['show']) &&  $cnb_options['show'] != '' ? true : false;
			$cnb_show_included	= $cnb_options['limit'] == 'include' ? true : false;
			$cnb_click_tracking	= $cnb_options['tracking'] > 0 ? true : false;
			$cnb_is_full_width 	= $cnb_options['appearance'] == 'full' || $cnb_options['appearance'] == 'tfull' ? true : false;
			$cnb_hide_frontpage 	= isset($cnb_options['frontpage']) && $cnb_options['frontpage'] == 1 ? true : false;
			$cnb_conversion_tracking	= $cnb_options['conversions'] > 0 ? true : false;

			if($cnb_show_limited) {
				$cnb_show_ids = explode(',', str_replace(' ', '' ,$cnb_options['show']));
			}
			
			if($cnb_click_tracking) {
				$cnb_tracking_code[1] = "_gaq.push(['_trackEvent', 'Contact', 'Call Now Button', 'Phone']);"; 
				$cnb_tracking_code[2] = "ga('send', 'event', 'Contact', 'Call Now Button', 'Phone');"; 
				$cnb_tracking_code[3] = "gtag('event', 'Call Now Button', {event_category: 'contact', event_label: 'phone'});";
				$cnb_tracking_code = $cnb_tracking_code[$cnb_options['tracking']];
			} else {
				$cnb_tracking_code = "";
			}

			if($cnb_conversion_tracking) {
				$cnb_conversion_code[1] = "return gtag_report_conversion('tel:".$cnb_options['number']."');";
				$cnb_conversion_code[2] = "goog_report_conversion('tel:".$cnb_options['number']."');";
				$cnb_conversion_code = $cnb_conversion_code[$cnb_options['conversions']];
			} else {
				$cnb_conversion_code = "";
			}

			$cnb_onclick_events = $cnb_click_tracking || $cnb_conversion_tracking ? 'onclick="' . $cnb_tracking_code . $cnb_conversion_code . '"' : "";

			if(!$cnb_has_text && !$cnb_is_full_width) {
				$cnb_button_text = '<span>Call Now Button</span>';
			} elseif(!$cnb_has_text && $cnb_is_full_width) {
				$cnb_button_text = '<img alt="Call Now Button" src="data:image/svg+xml;base64,'.svg(changeColor($cnb_options['color'], 'darker'),$cnb_options['iconcolor']).'" width="40">';
			} elseif($cnb_hide_icon && $cnb_is_full_width) {
				$cnb_button_text = '<span style="color:'.$cnb_options['iconcolor'].'">'. $cnb_options['text'] . '</span>';
			} elseif($cnb_is_full_width) {
				$cnb_button_text = '<img alt="Call Now Button" src="data:image/svg+xml;base64,'.svg(changeColor($cnb_options['color'], 'darker'),$cnb_options['iconcolor']).'" width="40"><span style="color:'.$cnb_options['iconcolor'].'">'. $cnb_options['text'] . '</span>';
			} else {
				$cnb_button_text = '<span>'.str_replace(" ", "&nbsp;", $cnb_options['text']).'</span>';
			}

			$cnb_call_link = '<a href="tel:'.$cnb_options['number'].'" id="callnowbutton" '.$cnb_onclick_events.'>'.$cnb_button_text.'</a>';
			
			if(is_front_page()) {
				if(!$cnb_hide_frontpage) {
					echo $cnb_call_link;
				}
			} elseif($cnb_show_limited) {
				if($cnb_show_included) {
					if(is_single($cnb_show_ids) || is_page($cnb_show_ids)) {
						echo $cnb_call_link;
					}
				} else {
					if(!is_single($cnb_show_ids) && !is_page($cnb_show_ids)) {
						echo $cnb_call_link;
					}					
				}
			} else {
				echo $cnb_call_link;
			}
		}
		add_action('wp_footer', 'cnb_footer');
	}
} 

function cnb_get_options() { // Grabbing existing settings and creating them if it's a first time installation
	if(!get_option('cnb')) { // Doesn't exist -> set defaults
		$default_options = array(
							  'active',
							  'number' => '',
							  'text' => '',
							  'hideIcon' => 0,
							  'color' => '#00bb00',
							  'iconcolor' => '#ffffff',
							  'appearance' => 'right',
							  'tracking' => 0,
							  'conversions' => 0,
							  'show' => '',
							  'limit' => 'include',
							  'frontpage' => 0,
							  'zoom' => '1',
							  'z-index' => '10',
							  'version' => CNB_VERSION
							  );
		add_option('cnb',$default_options);
	} else { // Does exist -> see if update is needed
		$updated = cnb_update_options();
	}
	$cnb_options['options'] = get_option('cnb');
	$cnb_options['updated'] = isset($updated) ? $updated : array(false, substr(CNB_VERSION, 0, 3));
	return $cnb_options;
}
function cnb_update_needed() { //compares version numbers
	$cnb_options = get_option('cnb');
	$pluginVersion 	= CNB_VERSION;
	$setupVersion 	= array_key_exists('version', $cnb_options) ? $cnb_options['version'] : 0.1;
	if($pluginVersion == $setupVersion) {
		$output = false;
	} elseif(substr($pluginVersion,0,3) > substr($setupVersion,0,3)) {
		$output = true;
	} elseif(substr($pluginVersion,0,3) == substr($setupVersion,0,3)) {
		$output = (substr($pluginVersion,-1) > substr($setupVersion,-1)) ? true : false;
	} else {
		$output = false;
	}
	return $output;
}
function cnb_update_options() {
	$cnb_options = get_option('cnb');
	if(cnb_update_needed()) { // Check current version and if it needs an update
		$cnb_options['active'] = isset($cnb_options['active']) ? 1 : 0;
		$cnb_options['text'] = isset($cnb_options['text']) ? $cnb_options['text'] : "";
		$cnb_options['iconcolor'] = isset($cnb_options['iconcolor']) ? $cnb_options['iconcolor'] : '#ffffff';
		$cnb_options['appearance'] = $cnb_options['text'] != "" ? "full" : $cnb_options['appearance'];
		$cnb_options['hideIcon'] = isset($cnb_options['hideIcon']) ? $cnb_options['hideIcon'] : 0;
		$cnb_options['limit'] = isset($cnb_options['limit']) ? $cnb_options['limit'] : 'include';
		$cnb_options['frontpage'] = isset($cnb_options['frontpage']) ? $cnb_options['frontpage'] : 0;
		$cnb_options['conversions'] = isset($cnb_options['conversions']) ? 1 : 0;
		$cnb_options['zoom'] = isset($cnb_options['zoom']) ? $cnb_options['zoom'] : 1;
		$cnb_options['z-index'] = isset($cnb_options['z-index']) ? $cnb_options['z-index'] : 10;
		$default_options = array(
							  'active' => $cnb_options['active'],
							  'number' => $cnb_options['number'],
							  'text' => $cnb_options['text'],
							  'hideIcon' => $cnb_options['hideIcon'],
							  'color' => $cnb_options['color'],
							  'iconcolor' => $cnb_options['iconcolor'],
							  'appearance' => $cnb_options['appearance'],
							  'tracking' => $cnb_options['tracking'],
							  'conversions' => $cnb_options['conversions'],
							  'show' => $cnb_options['show'],
							  'limit' => $cnb_options['limit'],
							  'frontpage' => $cnb_options['frontpage'],
							  'zoom' => $cnb_options['zoom'],
							  'z-index' => $cnb_options['z-index'],
							  'version' => CNB_VERSION
							  );
		if(array_key_exists('classic', $cnb_options) && $cnb_options['classic'] == 1 ) {
			$default_options['classic'] = 1;
		}
		update_option('cnb',$default_options);
		$updated = array(true, $cnb_options['version']);  // Updated and previous version number
	} else {
		$updated = array(false, $cnb_options['version']); // Not updated and current version number
	}
	return $updated;
}
// Color functions to calculate borders
function changeColor($color, $direction) {
	if(!preg_match('/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $color, $parts));
	if(!isset($direction) || $direction == "lighter") { $change = 45; } else { $change = -30; }
	for($i = 1; $i <= 3; $i++) {
	  $parts[$i] = hexdec($parts[$i]);
	  $parts[$i] = round($parts[$i] + $change);
	  if($parts[$i] > 255) { $parts[$i] = 255; } elseif($parts[$i] < 0) { $parts[$i] = 0; }
	  $parts[$i] = dechex($parts[$i]);
	} 
	$output = '#' . str_pad($parts[1],2,"0",STR_PAD_LEFT) . str_pad($parts[2],2,"0",STR_PAD_LEFT) . str_pad($parts[3],2,"0",STR_PAD_LEFT);
	return $output;
}

function svg($color, $icon) {
	$phone = '<path d="M7.104 14.032l15.586 1.984c0 0-0.019 0.5 0 0.953c0.029 0.756-0.26 1.534-0.809 2.1 l-4.74 4.742c2.361 3.3 16.5 17.4 19.8 19.8l16.813 1.141c0 0 0 0.4 0 1.1 c-0.002 0.479-0.176 0.953-0.549 1.327l-6.504 6.505c0 0-11.261 0.988-25.925-13.674C6.117 25.3 7.1 14 7.1 14" fill="'.$color.'"/><path d="M7.104 13.032l6.504-6.505c0.896-0.895 2.334-0.678 3.1 0.35l5.563 7.8 c0.738 1 0.5 2.531-0.36 3.426l-4.74 4.742c2.361 3.3 5.3 6.9 9.1 10.699c3.842 3.8 7.4 6.7 10.7 9.1 l4.74-4.742c0.897-0.895 2.471-1.026 3.498-0.289l7.646 5.455c1.025 0.7 1.3 2.2 0.4 3.105l-6.504 6.5 c0 0-11.262 0.988-25.925-13.674C6.117 24.3 7.1 13 7.1 13" fill="'.$icon.'"/>';
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 60 60">' . $phone . '</svg>';
	return base64_encode($svg);
}
function buttonActive() {
	$cnb_options = get_option('cnb');
	if(isset($cnb_options['active'])) { $output = true; } else { $output = false; }
	return $output;
}

function zindex($value) {
	$zindex = array(
		10 => 2147483647,
		9 => 214748365,
		8 => 21474836,
		7 => 2147484,
		6 => 214748,
		5 => 21475,
		4 => 2147,
		3 => 215,
		2 => 21,
		1 => 2
	);
	return $zindex[$value];
}

function cnb_check_for_caching() {
	$caching_plugins = array(
		'autoptimize/autoptimize.php',
		'breeze/breeze.php',
		'cache-control/cache-control.php',
		'cache-enabler/cache-enabler.php',
		'comet-cache/comet-cache.php',
		'fast-velocity-minify/fvm.php',
		'hyper-cache/plugin.php',
		'litespeed-cache/litespeed-cache.php',
		'simple-cache/simple-cache.php',
		'w3-total-cache/w3-total-cache.php',
		'wp-fastest-cache/wpFastestCache.php',
		'wp-super-cache/wp-cache.php'
	);
	$active = FALSE; //Default is false
	$name = 'none'; // Default name is none
	foreach ($caching_plugins as $plugin) {
		if ( is_plugin_active( $plugin ) ) {
			$active = TRUE;
			$name = explode('/', $plugin);
			$name = $name[0];
			break;
		}
	}
	return array($active,$name);
}
function cnb_utm_params($element, $page) {
	$output  = "?utm_source=wp-plugin";
	$output .= "&utm_medium=referral";
	$output .= "&utm_campaign=" . $element;
	$output .= "&utm_term=" . $page;
	echo $output;
}
?>
