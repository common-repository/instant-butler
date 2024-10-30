<?php
	//////////////////////
	// HEADER
	//////////////////////

	$wp_instantButler_setting = _wp_instantButler_getSettings(); // get settings!

	echo '<div class="wpib-frame wpib-backend-wrap">';
		echo '<div class="wbib-backend-header ib-group">'; 
			echo '<div class="ibSprite wpib-logo"></div>';
			echo '<h2>' . __('Instant Butler Settings') . '</h2><br />';
		echo '</div>';

		echo '<div class="wpib-backend-inner-wrap">';
		
		// Buy now notification
		if(!isset($_SESSION['wpib-remove-purchase-noti']))
			if(!__ibSetting('lic_status') || __ibSetting('lic_type') == 'trial') {
				echo '<div class="wpib-backend-alert ib-group large nopremium">';
					echo '<div class="alert-icon"><i class="fa fa-info-circle"></i></div>';
					echo '<a href="#" class="alert-close"><i class="fa fa-times"></i></a>';
					echo '<div class="alert-content">';
						echo '<h3>' . __('Do you like Instant Butler? Enable all awesome features!') . '</h3>';
						echo '<div class="text">' . __('Upgrade to the full version for just $5 and <span class="strong">get access to all features</span>.') . '</div>';
						echo '<a href="' . $__wpibSetup['buy_link'] . '" target="_blank" class="wpib-backend-button greenButton"><i class="fa fa-bolt"></i> ' . __('Get Full Version') . '</a>';
					echo '</div>';
				echo '</div>';

				$noti_showing = true;
			}
		
		// Buy now notification
		if(!$noti_showing)
			if(!isset($_SESSION['wpib-remove-trial-noti']))
				if(__ibSetting('lic_type') != 'trial' && __ibSetting('eligbleForTrial')) {
					echo '<div class="wpib-backend-alert ib-group large trialnotification">';
						echo '<div class="alert-icon"><i class="fa fa-info-circle"></i></div>';
						echo '<a href="#" class="alert-close"><i class="fa fa-times"></i></a>';
						echo '<div class="alert-content">';
							echo '<h3>' . __('Try out the full version for free') . '</h3>';
							echo '<div class="text">' . __('You can try out the full version on this domain for 7 days <span class="strong">for free</span>.') . '</div>';
							echo '<a href="' . $__wpibSetup['buy_link'] . '?enableTrial" target="_blank" class="wpib-backend-button greenButton"><i class="fa fa-bolt"></i> ' . __('Enable trial') . '</a>';
						echo '</div>';
					echo '</div>';
				}
		
		echo '<div class="wpib-backend-tabs ib-group">';
			echo '<div class="wpib-backend-tab"><a href="#accessibility" class="wpib-backend-tab-button" data-tab="1" data-settings-name="_wp_instantButler_settings">' . __('Accessibility') . '<div class="wpib-arrow"></div></a></div>';
			echo '<div class="wpib-backend-tab"><a href="#general" class="wpib-backend-tab-button" data-tab="2" data-settings-name="_wp_instantButler_settings">' . __('General') . '<div class="wpib-arrow"></div></a></div>';
			echo '<div class="wpib-backend-tab"><a href="#searching" class="wpib-backend-tab-button" data-tab="3" data-settings-name="_wp_instantButler_settings">' . __('Searching & keywords') . '<div class="wpib-arrow"></div></a></div>';
			echo '<div class="wpib-backend-tab"><a href="#customevents" class="wpib-backend-tab-button" data-tab="4" data-settings-name="_wp_instantButler_customEvents">' . __('Custom events') . '<div class="wpib-arrow"></div></a></div>';
			echo '<div class="wpib-backend-tab"><a href="#customkeywords" class="wpib-backend-tab-button" data-tab="5" data-settings-name="_wp_instantButler_customKeywords">' . __('Custom menu keywords') . '<div class="wpib-arrow"></div></a></div>';

			if($__wpibSetup['enable_lic'])
				echo '<div class="wpib-backend-tab"><a href="#tab-6" class="wpib-backend-tab-button last" data-tab="6">' . __('Manage license') . '<div class="wpib-arrow"></div></a></div>';
		echo '</div>';

		do_action('wpib_notice'); // notices			

		if(isset($_GET['save']) && $_POST) {
			if(_wp_instantButler_saveSettings($_POST['_wp_instantButler_setting'])) {
				$message = __('Settings saved');
			} else {
				$message = __('Settings saved');
			}

			echo '<div id="setting-error-settings_updated" class="updated settings-error">';
			echo '<p><strong>' . $message . '</strong></p></div>';
		}

				
		//////////////////////
		// TAB 1 - Usage
		//////////////////////

		echo '<div class="wpib-backend-tab-content" data-tab="1" style="display:none;">'; // tab 1	
			echo '<div class="wpib-backend-textfield-container">';
				
				echo '<div class="wpib-backend-texfield-block ib-group">';
					echo '<div class="wpib-backend-textfield-title">';
					echo __('Get Butler when...');
					echo '</div>';
					
					echo '<div class="wpib-backend-textfield-fieldarea">';
					
					// Pressing keys
					echo '<div class="wpib-backend-textfield-checkbox">';
						$checked = (__ibSettingSet('anyKeyShortcut')) ? 'checked' : '';
						echo '<label><input type="hidden" value="0" name="anyKeyShortcut"><input type="checkbox" name="anyKeyShortcut" ' . $checked . ' />&nbsp;' . __('Pressing any letters on the keyboard <strong>(fast, simple and clever)</strong>');
						echo '<div class="wpip-backend-textfield-helpblock">' . __('You simply have to type on your keyboard, and Instant Butler will be there. Be sure not to have any textfield in focus.') . '</div></label>';
					echo '</div>';


					// Admin menu
					echo '<div class="wpib-backend-textfield-checkbox">';
						$checked = (__ibSettingSet('iconInAdminBar')) ? 'checked' : '';
						echo '<label><input type="hidden" value="0" name="iconInAdminBar"><input type="checkbox" name="iconInAdminBar" ' . $checked . ' />&nbsp;' . __('Clicking on the button in the adminbar');
						echo '<div class="wpip-backend-textfield-helpblock">' . __('For increased usability, you can choose to add a button in the adminbar, which will bring up Instant Butler.') . '</div></label>';
					echo '</div>';

					// Admin menu
					echo '<div class="wpib-backend-textfield-checkbox">';
						$checked = (__ibSettingSet('keyShortCutEnabled')) ? 'checked' : '';
						echo '<label><input type="hidden" value="0" name="keyShortCutEnabled"><input type="checkbox" name="keyShortCutEnabled" ' . $checked . ' />&nbsp;' . __('Pressing the keyboard shortcut I have defined below:');
						echo '<div class="wpip-backend-textfield-helpblock">' . __('This will enable the custom keyboard shortcut defined below.') . '</div></label>';
					echo '</div>';
					
					echo '</div>';
				echo '</div>';

				// Define custom key
				echo '<div class="wpib-backend-texfield-block ib-group">';
					echo '<div class="wpib-backend-textfield-title">';
					echo __('Custom Keyboard Shortcut');
					echo '</div>';
					
					echo '<div class="wpib-backend-textfield-fieldarea">';

					echo '<input type="text" name="keyShortCut" value="' . __ibSetting('keyShortCut') . '" /><br><br>';
					echo '<div class="wpip-backend-textfield-helpblock">
							' . __('Any key combination you like. Just remember to format it corretly like this:') . ' 
							<pre>' . __('ctrl+alt+b') . '</pre><pre>' . __('command+z') . '</pre>
							<strong>' . __('Or even cool combos like this:') . '</strong>
							<pre>' . __('up up down down') . '</pre>';
					echo '</div>';
							
					echo '</div>';
				echo '</div>';

			echo '</div>';
		echo '</div>'; // end tab 1
				
		//////////////////////
		// TAB 3 - General
		//////////////////////

		echo '<div class="wpib-backend-tab-content wpib-backend-general-settings" data-tab="2" style="display:none;">'; // tab 2			
			// show instant butler on...
			echo '<div class="wpib-backend-textfield-container">';
				echo '<div class="wpib-backend-texfield-block ib-group">';
					echo '<div class="wpib-backend-textfield-title">';
						echo __('Show Instant Butler in');
					echo '</div>';

					echo '<div class="wpib-backend-textfield-fieldarea">';
						echo '<div class="wpib-backend-textfield-checkbox">';
							// backend
							$checked = (__ibSettingSet('loadInBackend')) ? 'checked' : '';
							echo '<label><input type="hidden" value="0" name="loadInBackend"><input type="checkbox" name="loadInBackend" ' . $checked . ' />&nbsp;' . __('Admin section');
							echo '<div class="wpip-backend-textfield-helpblock">' . __('This will enable Instant Butler in the admin section.') . '</div></label>';
						echo '</div>';
						
						
						echo '<div class="wpib-backend-textfield-checkbox">';
							// frontend
							$checked = (__ibSettingSet('loadInFrontend') && __ibSetting('lic_status')) ? 'checked' : '';
							echo '<label><input type="hidden" value="0" name="loadInFrontend"><input type="checkbox" name="loadInFrontend" ' . $checked;
									if(!__ibSetting('lic_status'))
										echo ' disabled';
							echo '/>&nbsp;' . __('Website');

							__wpibPremiumFeature(2);

							echo '<div class="wpip-backend-textfield-helpblock">' . __('This will enable Instant Butler on your frontend website.') . '</div></label>';
						echo '</div>';
					
					echo '</div>';
				echo '</div>';
			// end of 'show instant butler on' textfield group
				echo '<div class="wpib-backend-texfield-block ib-group">';	
					echo '<div class="wpib-backend-textfield-title">';
						echo __('Posts fetch type');
					echo '</div>';
						
					echo '<div class="wpib-backend-textfield-fieldarea">';
						echo '<div class="wpib-backend-textfield-checkbox radio">';
							$checked = (__ibSetting('fetchType') == 2 || !__ibSetting('lic_status')) ? 'checked="checked"' : '';
							echo '<label><input type="radio" name="fetchType" value="2" ' . $checked . '><span>' . __('Prefetched <strong>(fastest!)</strong>') . '</span>';
							echo '<div class="wpip-backend-textfield-helpblock">' . __('Fastest way to access your result. <span class="strong">All</span> data is fetched on page load, and cached in your browser.') . '</div></label>';
						echo '</div>';
					
						echo '<div class="wpib-backend-textfield-checkbox radio">';
								$checked = (__ibSetting('fetchType') == 1 && __ibSetting('lic_status')) ? 'checked="checked"' : '';
								echo '<label><input type="radio" name="fetchType" value="1"' . $checked;
								if(!__ibSetting('lic_status'))
									echo ' disabled';
								echo '>' . __('As you type');
								
								__wpibPremiumFeature(2);

							echo '<div class="wpip-backend-textfield-helpblock">' . __('Queries are made as you type, to keep server usage down.') . '</div></label>';
						echo '</div>';
					echo '</div>';
				echo '</div>';

			// Misc.
				echo '<div class="wpib-backend-texfield-block ib-group">';
					echo '<div class="wpib-backend-textfield-title">';
						echo __('Misc.');
					echo '</div>';
					
					echo '<div class="wpib-backend-textfield-fieldarea">';
						echo '<div class="wpib-backend-textfield-checkbox">';
							$checked = (__ibSettingSet('translate_posttypes')) ? 'checked' : '';
							echo '<label><input type="hidden" value="0" name="translate_posttypes"><input type="checkbox" name="translate_posttypes" ' . $checked . ' />&nbsp;' . __('Translate post types (if available)');
							echo '<div class="wpip-backend-textfield-helpblock">' . __('Translate post types to current Wordpress language') . ' (' . get_locale() . ')</div></label>';
						echo '</div>';

						echo '<div class="wpib-backend-textfield-checkbox">';
							$checked = (__ibSettingSet('show_butler_image')) ? 'checked' : '';
							echo '<label><input type="hidden" value="0" name="show_butler_image"><input type="checkbox" name="show_butler_image" ' . $checked . ' />&nbsp;' . __('Show butler image');

							echo '<div class="wpip-backend-textfield-helpblock">' . __('Show butler image on top of the search dialog') . '</div></label>';
						echo '</div>';
						
					echo '</div>';
				echo '</div>';

			// Click count
				if(get_bloginfo('version') >= 3.8) {
					echo '<div class="wpib-backend-texfield-group ib-group">';
						echo '<div class="wpib-backend-texfield-block ib-group">';
							echo '<div class="wpib-backend-textfield-title">';
								echo __('Menu clicks this session');
								echo '<div class="title-underline">' . __('Number of clicks this session') . '</div>';
							echo '</div>';		
							echo '<div class="wpib-backend-textfield-fieldarea">';
								echo (isset($_SESSION['wpib-menu-click'])) ? $_SESSION['wpib-menu-click'] : 0;
							echo '</div>';
						echo '</div>';

						echo '<div class="wpib-backend-texfield-block ib-group fullAccess">';
							echo '<div class="wpib-backend-textfield-title">';
								echo __('Total menu clicks');
								echo '<div class="title-underline">' . __('Number of clicks since <br>Instant Butler was installed') . '</div>';
							echo '</div>';
					
							echo '<div class="wpib-backend-textfield-fieldarea">';
								echo get_user_meta(get_current_user_id(), 'wpib_menu_clicks', true);
							echo '</div>';
						echo '</div>';
					echo '</div>';
				}		
						
					
				echo '<div class="wpib-backend-texfield-block ib-group">';
					echo '<div class="wpib-backend-textfield-title">';
						echo __('Reset to default settings');
					echo '</div>';

					echo '<div class="wpib-backend-textfield-fieldarea">';
						echo '<div class="wpib-backend-textfield-checkbox">';
							echo '<label><a href="' . $_SERVER['REQUEST_URI'] . '&wpib-action=defaults&general#tab-2" class="button"><i class="fa fa-cog"></i> ' . __('Reset general settings') . '</a></label>';
						echo '</div>';
					echo '</div>';
				echo '</div>';
			echo '</div>'; // end of texfield container
		echo '</div>'; // tab 2
				
		////////////////////////
		// TAB 3 - Searching
		////////////////////////

		echo '<div class="wpib-backend-tab-content" data-tab="3" style="display:none;">'; // tab 3
			echo '<div class="wpib-backend-textfield-container">';

			if(!__ibSetting('lic_status')) {
				echo '<div class="wpib-backend-texfield-block ib-group">';
					echo '<div class="wpib-backend-textfield-title">';
						echo __('Post type to search for');
					echo '</div>';
					echo '<div class="wpib-backend-textfield-fieldarea">';
						echo '<select name="postTypeSearch">';

							foreach(get_post_types() as $post_type) {
								echo '<option value="' . $post_type . '"';

									if($post_type == __ibSetting('postTypeSearch')) {
										echo ' selected';
									}

								echo '>' . $post_type . '</option>';
							}

						echo '</select>';
						echo '<div class="wpip-backend-textfield-helpblock">' . __('Choose which posttype to search in. <span class="strong">Get the full version, to choose multiply posttypes</span>') . '</div>';
					echo '</div>';
				echo '</div>';
			}
						
				echo '<div class="wpib-backend-texfield-block ib-group">';

				__wpibPremiumFeature(1, false, __('Get full version to change these settings'));
					
					echo '<div class="wpib-backend-texfield-grouping ib-group">';
						echo '<div class="wpib-backend-textfield-title">';
							echo __('Edit post keyword');
						echo '</div>';
						echo '<div class="wpib-backend-textfield-fieldarea">';
							echo '<input type="text" name="editPostsAlias" placeholder="disabled" value="' . __ibSetting('editPostsAlias') . '" />';
							echo '<div class="wpip-backend-textfield-helpblock">' . __('Use the keyword followed by the search term, e.g. "edit home" to edit the post. Leave blank to disable this feature.') . '</div>';
						echo '</div>';
					echo '</div>';


					echo '<div class="wpib-backend-texfield-grouping ib-group">';
						echo '<div class="wpib-backend-textfield-title">';
							echo __('View post keyword');
						echo '</div>';

						echo '<div class="wpib-backend-textfield-fieldarea">';
							echo '<input type="text" name="viewPostsAlias" placeholder="disabled" value="' . __ibSetting('viewPostsAlias') . '" />';
							echo '<div class="wpip-backend-textfield-helpblock">' . __('Use the keyword followed by the search term, e.g. "view about" to view the post. Leave blank to disable this feature.') . '</div>';
						echo '</div>';
					echo '</div>';


					echo '<div class="wpib-backend-texfield-grouping ib-group">';
						echo '<div class="wpib-backend-textfield-title">';
							echo __('Search posts keyword');
						echo '</div>';

						echo '<div class="wpib-backend-textfield-fieldarea">';
							echo '<input type="text" name="searchPostsAlias" placeholder="disabled" value="' . __ibSetting('searchPostsAlias') . '" />';
							echo '<div class="wpip-backend-textfield-helpblock">' . __('Use the keyword followed by the search term, e.g. "search instant butler" to search for contents of a post. Leave blank to disable this feature.') . '</div>';
						echo '</div>';
					echo '</div>';
						
				echo '</div>';
				
				
				echo '<div class="wpib-backend-texfield-block ib-group wpib-dont-show-block">';
					__wpibPremiumFeature(1);

					echo '<div class="wpib-backend-textfield-title">';
						echo __('Don\'t show');
					echo '</div>';
					echo '<div class="wpib-backend-textfield-fieldarea">';
						foreach(get_post_types() as $post_type) {
							echo '<div class="wpib-backend-textfield-checkbox">';
								echo '<label><input type="hidden" value="0" name="excludePostTypes[' . $post_type . ']"><input type="checkbox" name="excludePostTypes[' . $post_type . ']" value="1"';

								if(__ibSetting('excludePostTypes') && isset($wp_instantButler_setting['excludePostTypes'][$post_type]) && $wp_instantButler_setting['excludePostTypes'][$post_type])
									echo ' checked';

							echo '>&nbsp;' . $post_type . '<br />';		
							echo '</div>';
						}
					echo '</div>';
				echo '</div>';
			echo '</div>'; // end of textfield container
		echo '</div>'; // end tab 3

		////////////////////////
		// TAB 4 - Custom events
		////////////////////////

		$customevents = _wp_instantButler_getCustomEvents();

		echo '<div class="wpib-backend-tab-content" data-tab="4" style="display:none;">';
			__wpibPremiumFeature(1, (count($customevents) > 4) ? 'wpib-title-top' : '');

			echo '<div id="wpib-backend-addNewEvent" class="button"><i class="fa fa-plus"></i> ' . __('Add custom event') . '</div> 
			<div data-setting="_wp_instantButler_customEvents" class="button button-primary wpib-backend-savesetting"><i class="fa fa-refresh"></i> ' . __('Save changes') . '</div> 
			<small class="wpib-backend-status" data-setting="#wpib-backend-customevents-list" style="display:none;"></small>';

			echo '<div id="wpib-backend-custom-events">';

				// START: for jQuery ADD -->
				
				echo '<div class="wpib-backend-eventRow" style="display:none;">';
					echo '<a href="#" class="wpib-backend-removeEvent" title="Remove Row"><i class="fa fa-times"></i></a>';

					echo '<div class="eventRow-header description ib-group">';
						echo '<div class="eventRow-block title">' . __('Keyword') . '</div>';
						echo '<div class="eventRow-block title">' . __('Display text') . '</div>';
						echo '<div class="eventRow-block title">' . __('Type') . '</div>';
					echo '</div>'; 	
					echo '<div class="eventRow-header fields ib-group">';
						echo '<div class="eventRow-block keyword"><input type="text" name="keyword[]" placeholder="' . __('fx google search') . '" /></div>';	
						echo '<div class="eventRow-block displaytext"><input type="text" name="displaytext[]" placeholder="' . __('fx Search plugins for \'{query}\'') . '" /></div>';	
						echo '<div class="eventRow-block type"><select name="type[]"><option value="">' . __('Choose') . '</option>';
							foreach($__wpibSetup['customEvent_types'] as $value => $type) {
								echo '<option value="' . $value . '">' . $type . '</option>';
							}
						echo '</select></div>';								
					echo '</div>';
						
					echo '<div class="eventRow-textarea">';
						echo '<textarea style="height:106px" name="data[]" cols="32" placeholder="' . __('i.e. http://google.com/?s={query} or code') . '"></textarea>';
					echo '</div>';
				echo '</div>';

				// <-- END: for jQuery ADD

				foreach($customevents as $id => $field) {
					if(!isset($field['keyword']) || !isset($field['displaytext']) || !isset($field['data']) ||
						!$field['keyword'] && !$field['displaytext'] && !$field['data'])
							continue;

					$set = 1;

					echo '<div class="wpib-backend-eventRow">';
						echo '<a href="#" class="wpib-backend-removeEvent" title="' . __('Remove Row') . '"><i class="fa fa-times"></i></a>';

						echo '<div class="eventRow-header description ib-group">';
							echo '<div class="eventRow-block title">' . __('Keyword') . '</div>';
							echo '<div class="eventRow-block title">' . __('Display text') . '</div>';
							echo '<div class="eventRow-block title">' . __('Type') . '</div>';
						echo '</div>'; 	
						echo '<div class="eventRow-header fields ib-group">';
							echo '<div class="eventRow-block keyword"><input type="text" name="keyword[]" value="' . $field['keyword'] . '" placeholder="' . __('fx find plugin') . '" /></div>';	
							echo '<div class="eventRow-block displaytext"><input type="text" name="displaytext[]" value="' . htmlspecialchars($field['displaytext']) . '"  placeholder="' . __('fx Search plugins for \'{query}\'') . '" /></div>';	
							echo '<div class="eventRow-block type"><select name="type[]"><option value="">' . __('Choose') . '</option>';
								foreach($__wpibSetup['customEvent_types'] as $value => $type) {
									echo '<option value="' . $value . '"';
										if($field['type'] == $value)
											echo ' selected';

									echo '>' . $type . '</option>';
								}
							echo '</select></div>';								
						echo '</div>';

						echo '<div class="eventRow-textarea">';
							echo '<textarea style="height:106px" name="data[]" cols="32" placeholder="' . __('i.e. http://google.com/?s={query} or code') . '">' . $field['data'] . '</textarea>';
						echo '</div>';
					echo '</div>';
				}
				
				if(!isset($set)) {
					echo '<div class="wpib-backend-alert ib-group noResults">';
						echo '<div class="alert-icon"><i class="fa fa-info-circle"></i></div>';
						echo '<div class="alert-content">' . __('You currently have no custom events - Add your first custom event above.') . '</div>';
					echo '</div>';
				}
			echo '</div>'; // ending of #wpib-backend-custom-events
		echo '</div>'; // end of tab 4


		////////////////////////
		// TAB 5 - Results shortcuts
		////////////////////////

		echo '<div class="wpib-backend-tab-content" data-tab="5" style="display:none;">';
			__wpibPremiumFeature(1, 'wpib-title-top');

			echo '
					<a href="' . $_SERVER['REQUEST_URI'] . '&wpib-action=defaults&keywords#tab-5" class="button"><i class="fa fa-cog"></i> ' . __('Reset defaults') . '</a>

					<div data-replace="1" data-setting="_wp_instantButler_customKeywords" class="button button-primary wpib-backend-savesetting">
					<i class="fa fa-refresh"></i> ' . __('Save Changes') . '</div> 
					<small class="wpib-backend-status" data-setting="#wpib-backend-customkeywords-list" style="display:none;"></small>';

			echo '<div class="wbib-customevents" id="wpib-backend-customkeywords-list">';

				echo '<div class="wpib-backend-customkeywords-block ib-group header">';
					echo '<div class="wpib-title">' . __('Result') . '</div>';
					echo '<div class="wpib-field">' . __('What you type') . '</div>';
				echo '</div>';

				$_GET['menuOnly'] = true;
				$wpib_customKeywords = _wp_instantButler_getSettings('customKeywords');

				foreach(__wpInstantButlerGetResults() as $key => $value) {
					$input_value = (isset($wpib_customKeywords[urlencode($value['name'])])) ? $wpib_customKeywords[urlencode($value['name'])] : $value['value'];

					echo '<div class="wpib-backend-customkeywords-block ib-group">';
						echo '<div class="wpib-title">' . $value['name'] . '</div>';
						echo '<div class="wpib-field"><input type="text" value="' . $input_value . '" name="' . urlencode($value['name']) . '" placeholder="" /></div>';
					echo '</div>';
				}

			echo '</div>';
		echo '</div>';

		////////////////////////
		// TAB 6 - License 	  //
		////////////////////////

		$license_status = (__ibSetting('lic_status') == 1) ? __('Full version (all features unlocked!)') : __('Limited version (free)');
		$license_type = (__ibSetting('lic_type') == 'trial') ? __('Trial version') : false;
		$license_expires = (__ibSetting('lic_expires') && __ibSetting('lic_status') == 1) ? __ibSetting('lic_expires') : false;

		echo '<div class="wpib-backend-tab-content" data-tab="6" style="display:none;">';
			echo '<div class="wpib-backend-textfield-container">';
			
				echo '<div class="wpib-backend-texfield-group ib-group">';

					// License type
					echo '<div class="wpib-backend-texfield-block ib-group">';
						echo '<div class="wpib-backend-textfield-title">';
						echo __('Current mode');
						echo '</div>';
						
						echo '<div class="wpib-backend-textfield-fieldarea">';			
							// Pressing keys
								echo $license_status;
								echo '<div class="wpip-backend-textfield-helpblock">';
	
								if(__ibSetting('lic_type') == 'trial')
									echo __('We are so happy that you have downloaded Instant Butler. Because of this, we have given you the unique oppurtunity to test the full version for seven days.');
	
								echo '</div>';
						echo '</div>';
					echo '</div>';
					
						

					// License type
					if($license_type) {
						echo '<div class="wpib-backend-texfield-block ib-group">';
							echo '<div class="wpib-backend-textfield-title">';
							echo __('License type');
							echo '</div>';
							
							echo '<div class="wpib-backend-textfield-fieldarea">';
									echo $license_type;
							echo '</div>';
						echo '</div>';
					}

					// License expires
					if($license_expires) {
						echo '<div class="wpib-backend-texfield-block ib-group">';
							echo '<div class="wpib-backend-textfield-title">';
							echo __('License expires');
							echo '</div>';
							
							echo '<div class="wpib-backend-textfield-fieldarea">';
									echo date('Y-m-d', strtotime($license_expires));
							echo '</div>';
						echo '</div>';
					}
				echo '</div>';

				// Activate full version
				if(__ibSetting('lic_type') !== 'key') {

					echo '<div class="wpib-backend-texfield-block ib-group fullAccess">';

						// Header
							echo '<div class="wpib-backend-textfield-title">';
								echo __('Unlock full version');
								echo '<div class="title-underline">' . __('in less than 2 minutes') . '</div>';
							echo '</div>';

							echo '<div class="wpib-backend-textfield-fieldarea">';
							echo __('Get access to all features including:');
								echo '<ul>';
									echo '<li><i class="fa fa-check"></i>' . __('Search in all post types') . '</li>';
									echo '<li><i class="fa fa-check"></i>' . __('Enable the Butler on your frontend website') . '</li>';
									echo '<li><i class="fa fa-check"></i>' . __('Add custom events to increase work effenciency even more') . '</li>';
									echo '<li><i class="fa fa-check"></i>' . __('Assign custom keywords for the WP-menu - type less') . '</li>';
									echo '<li><i class="fa fa-check"></i>' . __('Support the further development of the plugin') . '</li>';
								echo '</ul>';
								echo '<a class="wpib-backend-button greenButton" target="_blank" href="' . $__wpibSetup['buy_link'] . '"><i class="fa fa-key"></i> ' . __('Get a license key') . '</a>'; 
							echo '</div>';

					echo '</div>';

					echo '<div class="wpib-backend-texfield-block ib-group">';

						// Enter license key
						echo '<div class="wpib-backend-texfield-grouping ib-group">';
							echo '<div class="wpib-backend-textfield-title">';
								echo __('Enter your license key');
							echo '</div>';
							
							echo '<div class="wpib-backend-textfield-fieldarea">';			
								// Pressing keys
									echo '<input type="hidden" id="wplicense_domain" value="' . $_SERVER["SERVER_NAME"] . '">';
									echo '<input type="text" name="licensekey" id="wplicense_key" />';
									echo '<div class="wpip-backend-textfield-helpblock">' . __('If you don\'t have a license key, you can get one in less than two minutes!') . ' <a href="' . $__wpibSetup['buy_link'] . '" target="_blank">' . __('Get a license key') . '</a></div>';
							echo '</div>';
						echo '</div>';

						echo '<div class="wpib-backend-texfield-grouping ib-group">';
							echo '<div class="wpib-backend-textfield-title">';
								echo '&nbsp;'; 
							echo '</div>';

							echo '<div class="wpib-backend-textfield-fieldarea">';
								echo '<button class="button" id="validateLicense"><i class="fa fa-unlock"></i> ' . __('Submit & validate') . '</button>';
								echo '<small class="wpib-backend-status" style="display:none;"></small>';
							echo '</div>';
						echo '</div>';

					echo '</div>';

				}

			echo '</div>';
		echo '</div>';
		
		echo '<div class="hide-on-tab" data-tab="6">';
			echo '<div class="wpib-backend-footer ib-group">';
					echo '<div id="wpib-savebutton" data-setting="_wp_instantButler_settings" class="button button-primary wpib-backend-savesetting"><i class="fa fa-refresh"></i> Save Changes</div>';
					echo '<small class="wpib-backend-status" data-setting="#wpib-backend-customevents-list" style="display:none;"></small>';
			echo "</div>";
		echo '</div>';
	echo '</div>';
?>