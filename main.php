<?php
/*
	Plugin Name: Instant Butler - The fastest way to navigate Wordpress
	Plugin URI: http://wp-instantbutler.com
	Description: Adds a searchbar when you type, to get you where you want - in a snap! Find your posts, pages, settings and more faster than ever - right at your fingertips. Instant Butler is a must have tool for all Wordpress-users - after few minutes of use, you can't live without it! Users familiar with 'Alfred' will also definitely love this.
	Version: 0.9.3
	Author: Mattias Fjellvang & Rasmus Wølk
	Author URI: http://wp-instantbutler.com
	Text Domain: wp-instantbutler
*/


	////////////////////////////////
	// Settings
	////////////////////////////////

	// General variables settings
		$__wpibSetup = apply_filters('wpib_setup', array(
			'click_notification_count' => 10, 					// Number of times user has clicked menu item, before showing notice. false = off
			'buy_link' => 'http://wp-instantbutler.com/buy',	// The download link
			'allow_eval' => true,								// Allow the use of eval() function.
			'settings_override' => array(						// Override user defined settings
				// Usage:
				// "setting_name" => 'value'
			),
			'enable_lic' => false,								// enable license
		));

	// Get admin site url
		$admin_url = apply_filters('wpib_site_url', Site_url() . '/wp-admin/');

	// Internationalizing
		function wpib_load_plugin_textdomain() {
			load_plugin_textdomain('wp-instantbutler', false, basename( dirname( __FILE__ ) ) . '/languages' );
		}

	// Settings
		$wp_instantButler_setting = _wp_instantButler_getSettings();

	// General init

		function instantButler_general_init() {
			// Load instantButler CSS
				wp_register_style(
					'adminDashboardIcons',
					site_url() . '/wp-includes/css/dashicons.min.css',
					array(),
					FALSE,
					'screen'
				);
				if(get_bloginfo('version') >= 3.8)
					wp_enqueue_style( 'adminDashboardIcons' );

			// Load instantButler CSS
				wp_register_style(
					'instantButler',
					plugin_dir_url( __FILE__ ) . 'css/instantbutler.css',
					array(),
					FALSE,
					'screen'
				);

				wp_enqueue_style( 'instantButler' );

			// init lang
				wpib_load_plugin_textdomain();
		}

	// Add JS/Styles
		function instantButler_init() {
			$showOnNonAuth = apply_filters('wpib_show_on_nonauth', false);

			if(is_user_logged_in() || $showOnNonAuth) {
				// Load Twitters Typeahead.js
					wp_enqueue_script('twitter-typeahead', plugins_url('/js/wpib_typeahead.min.js', __FILE__ ), array('jquery'));

				// Load Twitters Hogan (customized for Instant Butler)
					wp_enqueue_script('hogan-v2', plugins_url('/js/hogan-v2.0.0.min.js', __FILE__ ), array('twitter-typeahead'));

				// Load general JS
					wp_enqueue_script('instantButler', plugins_url('/js/instantbutler.js', __FILE__ ), array('hogan-v2'));

				// Run custom events
					if(isset($_GET['wpib-runCustomEvent'])) {
						$events = _wp_instantButler_getCustomEvents();

						$event = $events[$_GET['wpib-runCustomEvent']];

						if($event['type'] == 'phpcode' && is_super_admin()) {
							eval($event['data']);
						}
					}
			}
		}

	// Admin specific inits
		function instantButler_admin_init() {
			global $__wpibSetup;

			// Settings
				register_setting( '_wp_instantButler_settings', '_wp_instantButler_settings' );
				register_setting( '_wp_instantButler_settings', '_wp_instantButler_customEvents' ); // custom events

			// Load general JS
				wp_enqueue_script('instantButler-general-js', plugins_url('/js/general.js', __FILE__ ), array('jquery'));

			////////////////////////////////	
			// Custom events
			////////////////////////////////

				 $__wpibSetup['customEvent_types'] = array(
						'search' => 'Search',
						'jsscript' => 'Javascript code'
					);

				if($__wpibSetup['allow_eval'] && is_super_admin())
					$__wpibSetup['customEvent_types']['phpcode'] = 'PHP Code';
		}

	// The Instant Butler Dialog
		function instantButler_template($class='', $echo='1') {
			global $wp_instantButler_setting, $__wpibSetup;
			

			// Add support to use WP admin color scheme
				$additional_class = '';

				if(get_bloginfo('version') >= 3.8) {				// If higher than 3.8
					// get admin color theme
					$additional_class  = 'wpib-use-admin-colors';

					if(is_user_logged_in()) {
						$current_user = wp_get_current_user();
						$additional_class .= ' admin-color-' . $current_user->admin_color;
					}
				} else {
					$additional_class = 'wpib-own-style';
				}

			$showOnNonAuth = apply_filters('wpib_show_on_nonauth', false);

			if(isset($wp_instantButler_setting['template_set']) || 	// If template is already loaded
				!is_user_logged_in() && !$showOnNonAuth) 			// Or if user is not logged ind
				return;												// Dont output the template

			$return = '';

			// Define shortcut (if any)
				$shortcut = ($wp_instantButler_setting['keyShortCutEnabled'] == 'on') ?  'data-shortcut="' . $wp_instantButler_setting['keyShortCut'] . '"' : '';

			// Template
			$return .= '<div id="instantButlerDialog" class="wpib ' . $class . ' ' . $additional_class . '">';
				$return .= '<div class="ib-container" class="wpib ' . $class . '">';
					$return .= '<div class="ib-whitearea ib-group" class="wpib ' . $class . '">';
						$return .= '<form id="wp-instantButler-form" class="wpib ' . $class . '">';
						$return .= '<input type="text" class="wpib" id="wp-instantButler-field" data-wp-path="' . site_url() . '" data-cur-uri="' . str_replace(array('&', 'wpib-action=menu-click'), '', $_SERVER['REQUEST_URI']) . '" data-anykey="' . $wp_instantButler_setting['anyKeyShortcut'] . '" data-fetch-type="' . $wp_instantButler_setting['fetchType'] . '" ' . $shortcut . ' ' . apply_filters('wpib_custom_dialog_input_attr', '') . ' placeholder="' . __( "Just start typing") . '..." />';
						$return .= '</form>';
						$return .= '<div class="ib-tools wpib ' . $class . '">';

							if(!__ibSetting('lic_status') || __ibSetting('lic_type') == 'trial')
								$return .= '<a href="' . $__wpibSetup['buy_link'] . '" target="_blank" title="' . __('Unlock full version') . '" class="icon ibSprite help ' . $class . '" style="display:none;!important"></a>';

							$return .= '<a href="';
							$return .= (is_user_logged_in()) ? admin_url('options-general.php?page=instantButler_page') : '#';
							$return .= '" title="' . __('Go to settings') . '" class="icon ibSprite settings wpib"></a>';

						$return .= '</div>';
						$return .= '<div class="ib-poweredby"><a href="#">' . __('Powered by Instant Butler') . '</a></div>';
					$return .= '</div>';
				$return .= '</div>';
			$return .= '</div>';

			// Hide the Butler figure? :(

			if(__ibSettingSet('show_butler_image'))
				$return .= '<div id="instantButlerOverlay" class="wpib"><div id="instantButlerFigure" class="wpib ' . $class . '"></div></div>';

			$return = apply_filters('wpib_the_dialog', $return); 	// the output
			$wp_instantButler_setting['template_set'] = true; 		// only template once

			if($echo)
				echo $return;

			return $return;
		}

	// Default settings (serialized data)
		function __wpibDefaultSettings($setting) {
			switch ($setting) {
				case 'settings':
					$return = 'a:18:{s:10:"lic_status";s:1:"1";s:8:"lic_type";s:5:"trial";s:11:"lic_expires";s:19:"2014-01-12 02:00:55";s:14:"anyKeyShortcut";s:2:"on";s:14:"iconInAdminBar";s:2:"on";s:18:"keyShortCutEnabled";s:2:"on";s:11:"keyShortCut";s:10:"ctrl+alt+b";s:13:"loadInBackend";s:2:"on";s:14:"loadInFrontend";s:2:"on";s:9:"translate";s:1:"0";s:16:"searchPostsAlias";s:6:"search";s:9:"fetchType";s:1:"2";s:14:"editPostsAlias";s:4:"edit";s:14:"viewPostsAlias";s:4:"view";s:19:"translate_posttypes";s:1:"0";s:17:"show_butler_image";s:2:"on";s:14:"postTypeSearch";s:4:"post";s:16:"excludePostTypes";a:7:{s:4:"post";s:1:"0";s:4:"page";s:1:"0";s:10:"attachment";s:1:"1";s:8:"revision";s:1:"0";s:13:"nav_menu_item";s:1:"0";s:3:"acf";s:1:"0";s:18:"wpcf7_contact_form";s:1:"0";}}';
					break;
				case 'customEvents':
					$return = 'a:5:{s:7:"keyword";a:2:{i:0;s:0:"";i:1;s:1:"p";}s:11:"displaytext";a:2:{i:0;s:0:"";i:1;s:28:"Search plugins for "{query}"";}s:4:"type";a:2:{i:0;s:0:"";i:1;s:6:"search";}s:4:"data";a:2:{i:0;s:0:"";i:1;s:54:"../../wp-admin/plugin-install.php?tab=search&s={query}";}s:11:"description";a:3:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";}}';
					break;
			}

			return unserialize($return);
		}

	// Add settings (serialized data)
		add_option('_wp_instantButler_settings', __wpibDefaultSettings('settings'));
		add_option('_wp_instantButler_customEvents', __wpibDefaultSettings('customEvents'));

	/////////////////////
	// Init
	/////////////////////

	// Load in backend
		if(__ibSettingSet('loadInBackend')) {
			add_action('admin_init', 'instantButler_init'); 					// General Butler init
			add_action('admin_footer', 'instantButler_template');				// The template
		}
		
	// Load in frontend
		if(__ibSettingSet('loadInFrontend') && __ibSetting('lic_status')) {
			add_action('wp', 'instantButler_init'); 							// General Butler init
			add_action('wp_footer', 'instantButler_template');					// The template
		}

	// General admin init
		add_action('admin_init', 'instantButler_admin_init');

	// General init
		add_action('init', 'instantButler_general_init');
	

	// Strpos from array function
		function strposa($haystack, $needles=array(), $offset=0) {
			$chr = array();
			foreach($needles as $needle) {
					$res = strpos($haystack, $needle, $offset);
					if ($res !== false) $chr[$needle] = $res;
			}
			if(empty($chr)) return false;
			return min($chr);
		}

	// Convert post_type to icon
	// Menu icons for custom post types. 'post_type' => 'icon_class'
	// I.e.:
	// 'download' => 'icon-plugins'
	// Access by filter hook.

		$post_type_to_icon = apply_filters('wpib_post_types_icon', array(
				'attachment' => 'icon-media',
				'post' => 'icon-post',
				'page' => 'icon-page'
			));

	// Function to find and convert menu icon from $menu/$submenu
		function _wp_instantButler_convertIcon($string) {
			$convert = apply_filters('wpib_menu_icons', array(
				'icon-dashboard' => 'menu-icon-dashboard',
				'icon-post' => 'menu-icon-post',
				'icon-media' => 'menu-icon-media',
				'icon-page' => 'menu-icon-page',
				'icon-comments' => 'menu-icon-comments',
				'icon-appearance' => 'menu-icon-appearance',
				'icon-plugins' => 'menu-icon-plugins',
				'icon-users' => 'menu-icon-users',
				'icon-tools' => 'menu-icon-tools',
				'icon-settings' => 'menu-icon-settings',
				'icon-generic' => 'menu-icon-generic',
			));

			// icon 16 types:
			// icon-dashboard, icon-post, icon-media, icon-links, icon-page, icon-comments, icon-apperance, icon-plugins, icon-users, icon-tools, icon-settings, icon-site, icon-generic, 

			$position = strposa($string, $convert);
			$find = substr($string, $position);
			$find = explode(' ', $find);
			$find = $find[0];
			$found = array_search($find, $convert);

			return ($found) ? $found : false;
		}

	// Find icon

		function _wp_instantButler_defineIcon($data='') {
			global $post_type_to_icon;

			$icon_class = false;

			//return $data;

			if(isset($data[6]) && filter_var($data[6], FILTER_VALIDATE_URL)) 					// If is URL
				return '<div class="wpib wpib-icon"><img src="' . $data[6] . '" /></div>'; 		// return link

			if(!is_array($data) && isset($post_type_to_icon[$data]))							// If we have a icon, for the post_type
				$icon_class = $post_type_to_icon[$data];										// Return from array

			if(is_array($data) && _wp_instantButler_convertIcon($data[4]))						// If we have menu (from Wordpress menu)
				$icon_class = _wp_instantButler_convertIcon($data[4]); 							// define icon

			if(!$icon_class)																	// If we couldn't find any icon
				$icon_class = apply_filters('wpib_default_icon_class', 'icon-post');			// Set default icon

			if(isset($icon_class))
				return _wp_instantButler_icon($icon_class);										// Return icon class
		}

	// Set icon (div)
	// For search results
		function _wp_instantButler_icon($class) {
			return '<div class="wpib wpib-icon wpib-' . $class . ' wpib-icons-sprite"></div>';
		}

	////////////////////////////////	
	// General setup
	////////////////////////////////

	// Adminbar menus
		$__wpibSetup['adminBar'] = array(
				array(
					'id'	=> 'openInstantButlerDialog',
					'title' => '<img src="'.plugins_url('/img/icon16.png', __FILE__ ).'" style="vertical-align:middle;margin-right:5px" alt="' . __('Toggle Instant butler') . '" title="' . __('Toggle Instant Butler') . '" />Instant Butler',
					'href'  => '#'
				),
				array(
					'parent'=> 'openInstantButlerDialog',
					'id' => 'instantButlerAdminBarSettings',
					'title' => __('Settings'),
					'href'  => admin_url('options-general.php?page=instantButler_page')
				)
			);

		if(!__ibSetting('lic_status') || __ibSetting('lic_type') == 'trial') // If not full version
			$__wpibSetup['adminBar'][] = array(
					'parent'=> 'openInstantButlerDialog',
					'id' => 'instantButlerAdminBarGetFullVersion',
					'title' => __('Get full version'),
					'href'  => $__wpibSetup['buy_link'],
					'meta' => array('target' => '_blank')
				);

	$__wpibSetup['adminBar'] = apply_filters('wpib_post_types_icon', $__wpibSetup['adminBar']);

	// Add button to admin bar
		if(__ibSettingSet('iconInAdminBar')) {
			function _wp_instantButler_addBtnToAdminMenu($admin_bar) {
				global $__wpibSetup;

				foreach($__wpibSetup['adminBar'] as $menu) {
					$admin_bar->add_menu( $menu);
				}
			}

			add_action('admin_bar_menu', '_wp_instantButler_addBtnToAdminMenu',  100);
		}

	// Add settings link to plugins page in options
		function wpib_add_settings_link( $links ) {
			global $__wpibSetup;

			$links[] = '<a href="' . admin_url( 'options-general.php?page=instantButler_page' ) . '">' . __( 'Settings') . '</a>';

			if(!__ibSetting('lic_status') || __ibSetting('lic_type') == 'trial')
				$links[] = '<a href="' . $__wpibSetup['buy_link'] . '" target="_blank">' . __( 'Upgrade to full version') . '</a>';

			return $links;
		}

		add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'wpib_add_settings_link');

	////////////////////////////////	
	// Settings page
	////////////////////////////////

	// Transfer to any page
		function __wpibTransfer($url) {
			if(headers_sent()) {
				echo '<script>window.location="' . $url . '";</script>';
			} else {
				if(!@header('location:' .  $url)) {
					echo '<script>window.location="' . $url . '";</script>';
				}
			}
			exit;
		}
	
	// Add settings link til menu
		add_action('admin_menu', 'instantButler_page');

		function instantButler_page() {
			add_menu_page( 'Instant Butler - Settings', 'Instant Butler', 'manage_options', 'instantButler_page', 'instantButler_page_contents', plugins_url('/img/icon16.png', __FILE__ ) ); 
		}
		
	// The settings page
		function instantButler_page_contents() {
			global $menu, $submenu, $__wpibSetup;

			include("settings-page.php");				// Include file

			if($_GET['page'] != 'instantButler_page') {
				echo '<br /><br />Please be aware that we expect the settings page to be named: instantButler_page';
			}
		}

	// Include font awesome in backend admin for a sleak UI
		function __wpib_loadFA() {
			// Load instantButler CSS
				wp_register_style(
					'fontawesome',
					'http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css',
					array(),
					FALSE,
					'screen'
				);

				wp_enqueue_style( 'fontawesome' );
		}

	// Load Font Awesome on settings page only
		if(isset($_GET['page']) && $_GET['page'] == 'instantButler_page') {
			add_action('admin_init', '__wpib_loadFA');
		}

	// Save settings
		function _wp_instantButler_saveSettings($data, $optionEntityName='_wp_instantButler_settings', $replace='0') {
			$instantbutler_settings = get_option($optionEntityName);			// Get current settings
			$instantbutler_original_settings = $instantbutler_settings;			// Save current settings in new variable

			if(!count($data))													// If no data to be updated.
				return true;													// Always return true, even if nothing to update

			////////////
			// STEP 1 // - SET DATA TO SAVE
			////////////

			if(!$replace) {														// Don't replace, only update specified data
				foreach($data as $setting => $value) {							// loop through the specific data
					$instantbutler_settings[$setting] = $value;					// Set this current setting
				}
			} else {															// Replace settings (as specified in $data)
				$instantbutler_settings = $data;								// Settings should look excatly like provided data
			}

			if($instantbutler_settings == $instantbutler_original_settings)		// If no changes made
				return true;													// Still return true - success!

			$new_settings = $instantbutler_settings;							// Set new settings

			////////////
			// STEP 2 // - SAVE DATA
			////////////

			if (get_option($optionEntityName) !== false)						// If option exists
				if(update_option($optionEntityName, $new_settings))				// Simply update
					return true;												// return true on success

			if(add_option($optionEntityName, $new_settings))					// Option doesn't exist - create it now!
				return true;													// Return true on success

			return false;														// ... if all else fails - return false
		}

	// Get settings
	// By setting name
		function _wp_instantButler_getSettings($setting='settings') {
			global $__wpibSetup;
			
			$option = get_option('_wp_instantButler_' . $setting);				// Get specified setting

			if(is_array($option))												// If the setting is array
				return array_merge($option, (array)$__wpibSetup['settings_override']);	// Return settings - overridden by __SETUP if any

			return $option;														// Otherwise return option
		}

	// Save settings
		if(isset($_GET['wipb-saveSettings'])) {									// Save settings
			if(_wp_instantButler_saveSettings($_POST, $_GET['wipb-saveSettings'], (isset($_GET['replace']) && $_GET['replace'] == 1) ? 1 : 0)) {
				exit('success');
			} else {
				exit('error');
			}
		}

	// Check if specific setting is set (by checkbox)
	// NOTE: Expected value is "on" if set

		function __ibSettingSet($name, $type='checkbox') {
			global $wp_instantButler_setting;

			if(!isset($wp_instantButler_setting[$name]))						// If setting doesn't exist
				return false;													// Return false

			if($wp_instantButler_setting[$name] == 'on' && $type == 'checkbox')	// Check if checkbox is checked
				return true;													// Return true

			return false;														// If not on - return false
		}

	// Get specific setting
		function __ibSetting($name) {
			global $wp_instantButler_setting;

			if(!isset($wp_instantButler_setting[$name]))						// If setting doesn't exist
				return;															// Return nothing

			return $wp_instantButler_setting[$name];							// ... otherwise return output
		}

	// Get custom events
		function _wp_instantButler_getCustomEvents() {
			$custom_fields = get_option('_wp_instantButler_customEvents');

			if(!is_array($custom_fields))										// If no custom fields yet
				return array();													// .. return empty array

			foreach($custom_fields as $key => $datas) {							// Loop through custom fields (keys)
				foreach($datas as $id => $value) {								// Loop values
					$customEvents[$id][$key] = $value;							// Set custom events with key and value
				}
			}
			
			return (isset($customEvents)) ? $customEvents : array();			// Output custom events if is set - otherwise empty array
		}

	// Toggle butler function
	// Call anywhere on page to toggle the butler

		function wpib_toggle_butler() {	
			echo '<script type="text/javascript">$(function() { _instantButlerDialog(); });</script>';
		}

	// On plugin activation
	// Set session, so we can show welcome notice

		function wpib_plugin_activated() {
			if(session_id() == '')												// If sessions aint started
				@session_start();												// .. start them!

			$_SESSION['wpib_plugin_activated'] = 1;								// Set activated flag
		}

		register_activation_hook( __FILE__, 'wpib_plugin_activated' );			// Set the hook

	// Welcome message on activation

		function wpib_on_activation() {
			if(session_id() == '')												// If sessions aint started
				@session_start();												// .. start them!
			
			if(isset($_SESSION['wpib_plugin_activated'])) {						// If flag (for welcome message) is set
				function wpib_pluginInstalled_notice() {						// The notice... ---->
					global $__wpibSetup;

					echo '<div class="wpib-frame updated wpib-butler-image">';
						echo '<div class="wpib-notice-content">';
							echo '<p><b>' . __('Congratulations! Instant Butler is activated') . '</b><br />';
							echo __('Simply type something on your keyboard to bring up Butler!') . '</p>';

							echo '<p><b>' . __('Seven days free trial!') . '</b><br />';
							echo __('If this is the first time you try Instant Butler, you can try out the full version for seven days for free. Click the button below.') . '</p>';

							echo '<p>
								<a href="../wp-admin/admin.php?page=instantButler_page" class="button button-primary button-large">' . __('Configure Instant Butler') . '</a>
								<a href="' . $__wpibSetup['buy_link'] . '?enableTrial" target="_blank" class="button button-large">' . __('Get free trial') . '</a>
								<a href="#" class="button button-large" onClick="$(this).parent().parent().parent().slideUp();">' . __('Hide') . '</a>
								</p>';

						echo '</div>';
						
						echo '<div class="wpib-notice-image">';   
							echo '<img src="' . plugins_url('/img/instantbutler-figure.png', __FILE__ ) . '">';
						echo '</div>';
					echo '</div>';

					unset($_SESSION['wpib_plugin_activated']); 					// Remove flag
				}
				add_action( 'admin_notices', 'wpib_pluginInstalled_notice' ); 	// Show the notice
				add_action( 'admin_footer', 'wpib_toggle_butler'); 				// Introduce Butler :o)
			}
		}
		
		add_action( 'admin_init', 'wpib_on_activation');						// Run check (if activated flag exists - welcome notice shows up)

	// Show notice if user has clicked in menu several times
		function __wpibMouseClickNoti() {
			global $__wpibSetup;

			if(isset($_GET['page']) && $_GET['page'] == 'instantButler_page' ||	// If current page is settings
				!isset($_SESSION['wpib-menu-click']))							// Or if no menu clicks have been done yet
				return false;													// return false

			if($__wpibSetup['click_notification_count'] &&						// If user have made X amount of menu clicks - show notice!
				$_SESSION['wpib-menu-click'] >= $__wpibSetup['click_notification_count']
					&& !get_user_meta(get_current_user_id(), 'wpib_hide_click_notification', 1)) {

						add_action( 'admin_notices', 'wpib_menu_click_notice');	// Add the notice

						function wpib_menu_click_notice () {					// The actual notice
							echo '<div class="wpib-frame updated wpib-butler-image">';
								echo '<div class="wpib-notice-content">';
									echo '<p>' . __('Psst! Remember that you can use Instant Butler to navigate around. Simply type what you need on your keyboard, and Instant Butler will show up!') . '</p>';
									echo '<p>';
										echo '<button class="button button-primary wpib-toggle-butler">' . __('Bring me the Butler') . '</button>&nbsp;';
										echo '<button class="button wpib-hide-click-noti">' . __('Hide') . '</button>&nbsp;';
										echo '<button class="button wpib-hide-click-noti remove-reminder">' . __('I know - don\'t remind me again') . '</button>';
									echo '</p>';
								echo '</div>';
								
								echo '<div class="wpib-notice-image">';   
									echo '<img src="' . plugins_url('/img/instantbutler-figure.png', __FILE__ ) . '">';
								echo '</div>';
							echo '</div>';
						}
			}
		}

		add_action('admin_init', '__wpibMouseClickNoti');						// Run check for click notice

	// Add custom js-codes to footer of WP-admin
	// All CustomEvents of the type "jsscript" are all added to footer as functions
	// When a event is clicked from the butler, the function is triggered

		function instantButler_customEvents_jsScripts() {
			$events = _wp_instantButler_getCustomEvents();						// Get the custom events

			echo '<script type="text/javascript">';								// Open JS-tag
				foreach((array)$events as $id => $event) {						// Loop events
					if(!isset($event['type']) || $event['type'] != 'jsscript')	// If type is NOT a JS Script
						continue;												// skip

																				// Add the script -->
					echo " function __wpInstantButler_customJsscript_" . $id . "() {";
					echo $event['data'];
					echo '} ';
				}

			if(session_id() == '')												// If sessions aint started
				@session_start();												// .. start them!

				if(is_admin())													// If current user is admin
					if(!isset($_SESSION['wpib-check']) || 						// Validate domain
						isset($_SESSION['wpib-check']) && $_SESSION['wpib-check'] >= strtotime('-1 days'))
						echo "jQuery(function() {validateDomain('" . $_SERVER["SERVER_NAME"] . "');});";

			echo '</script>';
		}

		add_action( 'wp_footer', 'instantButler_customEvents_jsScripts');		// Add the scripts to FRONT-END footer
		add_action( 'admin_footer', 'instantButler_customEvents_jsScripts');	// Add the scripts to admin footer

	// Outputs the custom events for typeahead
	// $exclude = array of types to exclude

	function _wp_instantButler_outputCustomEvents($exclude=array()) {
		global $results, $query;																			// Unlock globals

		$customEvents = get_option('_wp_instantButler_customEvents');										// Get the events

		$customEventsKeywords = (isset($customEvents['keyword'])) ? $customEvents['keyword'] : '';			// Define keyword

		if(__ibSetting('lic_status'))
			foreach((array)_wp_instantButler_getCustomEvents() as $id => $field) { 							// Loop events

				if(isset($field['type']) && in_array($field['type'], $exclude) || !isset($field['type']))	// If current is meant to be skipped
					continue;																				// .. then skip it!

				$q = (isset($_GET['q'])) ? $_GET['q'] : null;												// Define query
				$getKeywordUsed = explode(' ', $q);															// Part up query phrase
				$getKeywordUsed = $getKeywordUsed[0];														// Get first word
				
				if($field['type'] == 'search' && !in_array($getKeywordUsed, $customEventsKeywords))			// if first word from query doesn't match any
					continue;																				// custom events, then skip!

				$query = str_replace($field['keyword'] . ' ', '', $q); 										// Remove the custom events keyword from search query
				$field = str_replace('{query}', $query, $field); 											// {query} replace to actual query from custom event text
				$field = str_replace('{root}', site_url(), $field); 										// {root} replace with site_url

				if(empty($query) && $field['type'] == 'search' || empty($field['keyword']))					// If query is empty - or no keyword
					continue;																				// .. skip!

				$uri = (isset($_GET['uri'])) ? $_GET['uri'] : site_url().'/';								// Get Butler's URI - alternatively replace with site_url
				$querySeperator = (parse_url($uri, PHP_URL_QUERY)) ? '&' : '?'; 							// define query seperator: & or ? - for below link

				$link = ($field['type'] == 'search') ? $field['data'] : (($field['type'] == 'jsscript') ? '#' : $uri.$querySeperator.'wpib-runCustomEvent=' . $id); // define link

				///////////////////
				// Generate output
				///////////////////

				$results[] = apply_filters('wpib_result_customevents', array(
						'value' => $field['keyword'] . ' ' . $query,										// The value (what the user types for)							
						'name' => $field['displaytext'],													// The text in the results
						'link' => $link,																	// The go-to URL
						'icon' => _wp_instantButler_icon('icon-generic'),									// The ICON
						'type' => $field['type'],															// The type (jsscript, search, phpcode, etc)
						'customEventId' => $id																// The custom event id
					));
			}

		// Return the results!
		return (array)$results;
	}

	// Premium feature text/HTML
	// Types: 1 = overlay 2 = text
	function __wpibPremiumFeature($type='1', $class='', $text='Get full version to unlock this feature') {
		global $__wpibSetup;

		if(__ibSetting('lic_status'))
			return;

		if($type == 1) {
			echo '<div class="wpib-backend-locked-feature ' . $class . '">';
				echo '<div class="wpib-backend-locked-feature-container">';
					echo '<div class="locked-feature-header">' . $text . '</div>';
					echo '<div class="locked-feature-button"><a class="wpib-backend-button greenButton" target="_blank" href="' . $__wpibSetup['buy_link'] . '"><i class="fa fa-unlock"></i> ' . __('Upgrade to full version') . '</a></div>';
			   echo '</div>';
			echo '</div>';
		} else if($type == 2) {
			echo ' - <b>' . __('Get full version, to unlock this feature!') . '</b>';
		}
	}

	// Add body class if ver. 3.8 or higher, to support
		function wpib_use_admin_colors( $classes ) {
			$classes .= 'wpib-use-admin-colors';
			return $classes;
		}
		
		function wpib_admin_set_body_class($classes) {
			if(get_bloginfo('version') >= 3.8) {
				$classes .= ' wpib-use-admin-colors';
			} else {
				$classes .= ' wpib-own-style';
			}

			return $classes;
		}
		
		add_filter( 'admin_body_class', 'wpib_admin_set_body_class' );

	////////////////////////////////	
	// API
	////////////////////////////////
	
	// Response from API will be handled here
		if(isset($_GET['wpib-apiresponse'])) {
			if(!is_admin()) 																				// If not admin
				exit;																						// Stop script execution

			$response = $_POST['response'];																	// The response from server

			$lic_status = (isset($response['status'])) ? $response['status'] : '';							// Define status
			$lic_expires = (isset($response['expires'])) ? $response['expires'] : '';						// Define expire
			$lic_type = (isset($response['type'])) ? $response['type'] : '';								// Define type
			$lic_eligbleForTrial = (isset($response['eligbleForTrial'])) ? $response['eligbleForTrial'] : 0;// Define eligble for trial

			_wp_instantButler_saveSettings(array(															// Update settings
				'lic_status' => $lic_status, 
				'lic_type' => $lic_type, 
				'lic_expires' => $lic_expires,
				'eligbleForTrial' => $lic_eligbleForTrial));

			if(session_id() == '')																			// If sessions aint started
				@session_start();																			// .. start them!

			$_SESSION['wpib-check'] = time(); 																// Set flag
			exit;																							// .. exit!
		}

	////////////////////////////////	
	// Get results
	////////////////////////////////

	// Get the results
	function __wpInstantButlerGetResults() {
		global $menu, $submenu, $__wpibSetup, $admin_url;													// Retrieve globals

		$results = array();																					// Set results array
		$wp_instantButler_setting = _wp_instantButler_getSettings();										// Retrieve settings
		$fetch_type = (__ibSetting('lic_status')) ? $wp_instantButler_setting['fetchType'] : 2;				// Get Butler Fetch Type (1 = as you type 2 = prefetch all posts).
		$query = (isset($_GET['q'])) ? $_GET['q'] : false;													// The query

		if($query &&																						// If query is set
			!isset($_GET['remote'])) {																		// And this is not a remote fetch
			$results = array_merge($results, _wp_instantButler_outputCustomEvents(							// Output 'search' queries
				apply_filters('wpib_customevents_types', array('phpcode', 'jsscript'))));					// ... (exclude phpcode and jsscript events)
		}

		if($query &&																						// If query is set
			isset($_GET['remote']) && $fetch_type == 2)														// If remote and fetch type is PREFETCH
			$wpib_doing_query = true;																		// .. We are now doing a query. Set flag!

	// Set header
		if(!headers_sent() &&																				// If no headers sent
			isset($_GET['getInstantbutlerResults'])) {														// And we are asked to output results
			header('Content-Type: application/json; Charset=UTF-8');										// Set output to JSON
		}

		///////////////////////////
		// SEARCH POSTS
		///////////////////////////

		if(isset($wp_instantButler_setting['editPostsAlias']) &&											// If setting is set
			!$wp_instantButler_setting['editPostsAlias']													// If user enabled 'edit {page}' (not empty)
				&& isset($wpib_doing_query))																// And we are doing a query
					$wp_instantButler_setting['editPostsAlias'] = 'edit';

		// Get keywords
		$keywords = array((isset($wp_instantButler_setting['editPostsAlias']) ? $wp_instantButler_setting['editPostsAlias'] : false));

		$excludePosts = __ibSetting('excludePostTypes');													// Get posts type exclude

		$translate_posttypes = __ibSettingSet('translate_posttypes');										// Check if user wants us to translate post types

		if(!isset($_GET['menuOnly'])) { 																	// if not only menu is meant to be fetched
			foreach($keywords as $keyword) {																// Loop keywords
				if($keyword && strpos($query, $keyword) !== false && !isset($_GET['custom']) ||				// If keyword matches 'edit / view / etc' is used
					$fetch_type == 2 && !isset($_GET['custom'])) { 											// .. or if this is a prefetch

					$explodedQuery = explode(' ', $query);													// Get all search words
					$post_types = get_post_types(); 														// get post types to fetch

					/////////////////////
					// Prepare params
					// for WP_Query
					/////////////////////

					if($fetch_type == 1 || isset($wpib_doing_query)) { 										// if remote setting (as you type)
						if(isset($explodedQuery[1]) && in_array($explodedQuery[1], $post_types)) { 			// If looking for specific post type
							// This will support usages
							// like this:
							//
							// edit post hello world
							// edit page Home
							// 
							// or simply just search all post_types:
							//
							// edit the_name

							$params['post_type'] = $explodedQuery[1];										// Set post_type param (only specified)
							$searchTerm = (isset($explodedQuery[2])) ? $explodedQuery[2] : ''; 				// Set the search term (post type and kewyord removed)
						} else {
							$params['post_type'] = 'any';													// Set post_type params (any!)
							$searchTerm = (isset($explodedQuery[1])) ? $explodedQuery[1] : ''; 				// Set the search term (kewyord removed)
						}

						$params['s'] = $searchTerm; 														// Add search term to parameter

					} else { 																				// .. else! If prefetching -->
						$params['posts_per_page'] = '-1'; 													// Get _all_ posts (theese will be cached)
						$params['post_type'] = 'any'; 														// Any post types
					}

				// Add query parameter if this is a search
					if(isset($wp_instantButler_setting['searchPostsAlias']) &&								// If user is searching for specfic content
							strpos($query, $wp_instantButler_setting['searchPostsAlias']) !== false &&		
								isset($wpib_doing_query)) {													// And we are doing a query
									$params['s'] = str_replace($wp_instantButler_setting['searchPostsAlias'] . ' ', '', $query);	// Set search term
					} else if(isset($wpib_doing_query)) {													// If doing query
						break;																				// break
						// 3. jan2013: removed: exit - replaced with break;
					}

				// Search in all
					$params['post_status'] = 'any';															// Search in all post_statuses

					if(!__ibSetting('lic_status'))
						$params['post_type'] = (isset($wp_instantButler_setting['postTypeSearch'])) ? $wp_instantButler_setting['postTypeSearch'] : 'page';

					$search = new WP_Query(apply_filters('wpib_result_posts_query', $params));				// Run WP_Query

					if($search->have_posts()) { 															// if any content is found
						while ( $search->have_posts() ) { 													// loop it
							
							$search->the_post(); 															// set post
							$post_type_name = get_post_type(); 												// get name, i.e. "post" or "page"
							$post_id = $search->post->ID; 													// the post_id

							$post_thumbnail = wp_get_attachment_image_src($post_id, 'thumbnail'); 			// thumbnail
							$post_thumbnail = ($post_thumbnail[0]) ? '<div class="wpib wpib-postthumbnail"><IMG class="wpib" src="' . $post_thumbnail[0] . '"></div>' : ''; // if any thumbnail; output

							$actions = array();																// set actions (i.e.: edit or view)

							if(!empty($wp_instantButler_setting['editPostsAlias']))							// If 'edit' keyword activated (not empty)
								$actions[] = $wp_instantButler_setting['editPostsAlias'];					// Add it to queue

							if(!empty($wp_instantButler_setting['viewPostsAlias']))							// If 'view' keyword activated (not empty)
								$actions[] = $wp_instantButler_setting['viewPostsAlias'];					// Add it to queue

							foreach((array)$actions as $action) {											// Loop action queue

								$name = ($action == $wp_instantButler_setting['viewPostsAlias']) ? 'View' : 'Edit'; // the action, ie: "View post home" or "edit page about"
								$link = ($action == $wp_instantButler_setting['viewPostsAlias']) ? get_permalink() : $admin_url.'post.php?action=edit&post=' . $post_id;

								if(is_array($excludePosts) && isset($excludePosts[$post_type_name])	&&		// If post type is meant to be skipped
									$excludePosts[$post_type_name])
										continue; 															// ... then skip!

								$post_name = ($translate_posttypes) ? __(ucfirst($post_type_name)) : $post_type_name;

								$results[] = apply_filters('wpib_result_post', array(						// The results
										'name' => html_entity_decode(__(ucfirst($name)) . ' ' . strtolower($post_name) . ' "' . get_the_title($post_id) . '"'),
										'value' => html_entity_decode($action . ' ' . strtolower($post_name) . ' ' . get_the_title($post_id)),
										'link' => $link,
										'icon' => _wp_instantButler_defineIcon($post_type_name),
										'image' => $post_thumbnail
									));
							}
							unset($actions);
						}
					} else {																				// If nothing is found!
						$results[] = array(	
								'name' => __('Nothing found')
							);
					}
				}
			}
		}

	/////////////////////////////////
	// PREFETCH MODE
	// Get items from admin-menu
	/////////////////////////////////

		if($fetch_type == 2 && !isset($_GET['custom']) && !isset($wpib_doing_query) || 						// If PREFETCH and not doing query/getting custom events	
			!$query && !isset($_GET['custom']) && !isset($wpib_doing_query)) {								// If no query, not custom and not doing query
				$wpib_customKeywords = _wp_instantButler_getSettings('customKeywords');						// Get the custom menu keywords
							
					foreach((array)$menu as $key => $item) {												// Loop through the menu
						// item[0]: Page name (if null: seperator)
						// item[1]: Permisson
						// item[2]: Page URL (i.e. "edit.php?post_type=page")
						// item[4]: Classes for menu
						// item[5]: ID for menu
						// item[6]: icon

						if(!$item[0])																		// if seperator
							continue;																		// .. then skip!

						if(!current_user_can($item[1]))														// If user is not allowed to this menu
							continue; 																		// .. then skip!

						$page_name = trim(strip_tags(str_replace(range(0,9),'',html_entity_decode($item[0]))));
						
						// Set value
						// And replace if any custom menu keyword exist
						// to this specific NAME
						//
						// NOTE: Values are bound to the MENU LOCALE STRING. That means if WP Language is
						// changed, any custom keywords will reset.

						$value = (__ibSetting('lic_status') && isset($wpib_customKeywords[urlencode($page_name)])) ? $wpib_customKeywords[urlencode($page_name)] : $page_name;
						$page_url = menu_page_url($item[2], 0);
						$results[] = apply_filters('wpib_result_menu', array(								// The output!
								'name' => $page_name,
								'value' => $value,
								'link' => ($page_url) ? $page_url : $admin_url.$item[2],
								'icon' => _wp_instantButler_defineIcon($item)
							));



						if(isset($submenu[$item[2]])) { 													// If current menu has SUBMENU
							foreach($submenu[$item[2]] as $key => $sub_item) {								// Loop the submenu
								// sub_item[0] => Name
								// sub_item[1] => PERMISSION NAME
								// sub_item[2] => link

								$submenu_name = trim(strip_tags(str_replace(range(0,9),'',html_entity_decode($sub_item[0]))));
								$submenu_name = $page_name . ' → ' . $submenu_name;

								// Get value / custom menu keywords
								$value = (__ibSetting('lic_status') && isset($wpib_customKeywords[urlencode($submenu_name)])) ? $wpib_customKeywords[urlencode($submenu_name)] : $submenu_name;
								$page_url = html_entity_decode(menu_page_url($sub_item[2], 0));

								$results[] = apply_filters('wpib_result_submenu', array(					// The output
										'name' => $submenu_name,
										'value' => $value,
										'link' => ($page_url) ? $page_url : $admin_url.$sub_item[2],
										'icon' => _wp_instantButler_defineIcon($item)
									));
							}
						}
					}

				if(!isset($_GET['menuOnly']))																// If not only menu is meant to be fetched
					$results = array_merge($results, _wp_instantButler_outputCustomEvents(array('search')));// Get custom events also (exclude SEARCH)
		}

		if(isset($_GET['remote'])) {																		// If this is remote search
			if(__ibSetting('lic_status') != '1') {
				$results[] = array(																			// Show upgrade add
					'name' => __('Upgrade to full version'),
					'value' => '',
					'link' => $__wpibSetup['buy_link'],
					'icon' => _wp_instantButler_defineIcon()
				);
			}
		}

		do_action('wpib_before_result_output');																// Action hook befre output

		return (isset($results)) ? apply_filters('wpib_results', $results) : array();						// Return results (if any) or empty array
	}

	// Set language to english
	// COMING SOON:
	// Use both native AND current language
	// to find menu items
	//
	// if(isset($_GET['getInstantbutlerResults'])) {
	// 	add_filter('locale', function() { return 'en_US';});
	// 	load_default_textdomain();
	// }

	///////////////////////////////////
	// Output reusults (for typeahead)
	///////////////////////////////////

	add_action('admin_init', 'output_instantbutler_results');												// Run action to listen for result queries

	function output_instantbutler_results() {
		global $admin_url;

		$showOnNonAuth = apply_filters('wpib_show_on_nonauth', false);

		if(!is_user_logged_in() && !$showOnNonAuth)															// If user is not logged in
			return;																							// Dont output results


		if(isset($_GET['getInstantbutlerResults'])) {
			@error_reporting(0);																			// Disable anything that could interfere with JSON output
			header('Content-Type: application/json');														// Set JSON output result
			$results = __wpInstantButlerGetResults();														// Get the results

			if(isset($_GET['forward'])) {																	// If this is a forward -> forward!
				$goToUrl = (isset($results[0]['link'])) ? $results[0]['link'] : $admin_url.'index.php?wpibNoResults';

				__wpibTransfer($goToUrl);																	// Transfer!
			} else {
				exit(json_encode($results));																// Output results
			}
		} else if(isset($_GET['wpib-action'])) {															// Do general actions!
			if(session_id() == '')																			// If sessions aint started
				@session_start();																			// .. start them!

			if($_GET['wpib-action'] == 'remove-notification') {												// Remove purchase notification
				$_SESSION['wpib-remove-purchase-noti'] = true;
			} else if($_GET['wpib-action'] == 'remove-trial-notification') {								// Remove trial notification
				$_SESSION['wpib-remove-trial-noti'] = true;
			} else if($_GET['wpib-action'] == 'defaults') {													// Reset default settings. Which? -->
				if(isset($_GET['keywords'])) {																// Custom keywords
					_wp_instantButler_saveSettings('', '_wp_instantButler_customKeywords', 1);
				} else if(isset($_GET['general'])) {														// General settings
					_wp_instantButler_saveSettings(__wpibDefaultSettings('settings'), '_wp_instantButler_settings');
				}
			} else if($_GET['wpib-action'] == 'menu-click') {												// Used to track menu clicks
																											// Call is triggered in settings-page.js

				if(!isset($_SESSION['wpib_last_page']) || $_SESSION['wpib_last_page'] != $_SERVER['REQUEST_URI']) {
					$current = get_user_meta(get_current_user_id(), 'wpib_menu_clicks', true);  // Get users current value
					$value = ($current) ? ($current + 1) : 1; 									// Define the new value

					if(!$current) {																// If user_meta not yet created
						add_user_meta(get_current_user_id(), 'wpib_menu_clicks', $value); 		// .. then add
					} else {
						update_user_meta(get_current_user_id(), 'wpib_menu_clicks', $value); 	// Other wise simply update
					}

					if(!isset($_SESSION['wpib-menu-click']))									// If session counter not yet set
						$_SESSION['wpib-menu-click'] = 0;										// Set

					$_SESSION['wpib-menu-click']++;												// Add +1 to counter
				}

				$_SESSION['wpib_last_page'] = $_SERVER['REQUEST_URI'];							// Keep a track of last page
			} else if($_GET['wpib-action'] == 'hide-click-noti') {								// If user clicked hide/OK on click notification
				if(isset($_GET['remove-reminder']))												// If he didn't wanted to be reminded again
					add_user_meta(get_current_user_id(), 'wpib_hide_click_notification', true);	// Keep record of it

				$_SESSION['wpib-menu-click'] = 0;												// Otherwise simply reset counter!
			}
		}
	}
?>