////////////////////////////////
// General variables
////////////////////////////////

	var $ = jQuery;
	
	$(function() {
		////////////////////////////////
		// Custom events
		////////////////////////////////

		if($('.wpib-backend-eventRow').length == 0)
			return false;

		// Init
		_wbib_initClickHandlers(); // attach click handlers

		// Add new custom event
		_wpib_event_row = $('.wpib-backend-eventRow')[0].outerHTML;
		$('.wpib-backend-eventRow:first').addClass('add');

		$('#wpib-backend-addNewEvent').click(function() {
			$('#wpib-backend-custom-events').prepend(_wpib_event_row);
			$('.wpib-backend-eventRow').not('.add').slideDown();
			$('.wpib-backend-alert.ib-group.noResults').slideUp();

			_wbib_initClickHandlers(); // attach click handlers
			return false;
		});


		// Remove event
			function _wbib_initClickHandlers() {
				$('.wpib-backend-removeEvent').click(function() {
					$(this).parent().css('background-color', '#e74c3c').slideUp('slow',function() {
						$(this).remove();
						$('.wpib-backend-status').fadeIn().text('Remember to save changes, to completely remove the events');
					});

					return false;
				});

				$('select[name="type[]"]').change(function() {
					__wpibUpdateTypeInputs($(this).val());
				});
			}

		// On load
			$('select[name="type[]"]').each(function() {
				__wpibUpdateTypeInputs($(this).val());
			});

		// Update placeholders/etc on type

			var textarea_height = '106px';
			var input_height = '40px';

			function __wpibUpdateTypeInputs(type) {
				$('select[name="type[]"]').each(function() {
					if(type == 'phpcode') {
						$(this).parent().parent().find('input[name="keyword[]"]').attr('placeholder', 'my custom php event');
						$(this).parent().parent().find('input[name="displaytext[]"]').attr('placeholder', 'run my custom php event');
						$(this).parent().parent().parent().find('textarea[name="data[]"]').attr('placeholder', 'echo "Hello World"; exit;').animate({height: '106px'});
					} else if(type == 'search') {
						$(this).parent().parent().find('input[name="keyword[]"]').attr('placeholder', 'search google');
						$(this).parent().parent().find('input[name="displaytext[]"]').attr('placeholder', 'Search google for {query}');
						$(this).parent().parent().parent().find('textarea[name="data[]"]').attr('placeholder', 'http://google.com/?s={query}').animate({height: '45px'});
					} else if(type == 'jsscript') {
						$(this).parent().parent().find('input[name="keyword[]"]').attr('placeholder', 'my custom javascript');
						$(this).parent().parent().find('input[name="displaytext[]"]').attr('placeholder', 'run my custom javascript');
						$(this).parent().parent().parent().find('textarea[name="data[]"]').attr('placeholder', 'alert($("body").attr("class"));').animate({height: '106px'});
					}
				});
			}

		////////////////////////////////
		// General
		////////////////////////////////

		// Save settings list
			var request, working;
			function wpibSaveSetting(button) {
			// If working, stop
				if(working) {
					return;
				}

			// We are now working!
				working = 1;

			// Original icon
				var icon_org_icon = $(button).find('.fa').removeClass('fa-spin').attr('class');

			// Set text and icon messages
				$('.wpib-backend-status').fadeIn().text('Saving.. Please wait'); // loading status
				$(button).find('.fa').addClass('fa-spin');

			// Define fields to update
				var fields = $('.wpib-backend-tab-content:visible');
				var fields = fields.find('input, select, button, textarea');
				var fields = $(fields).serialize();


			// if any current requests, then abort
				if (request) {
				   request.abort();
				}

			// fire off the request
				request = $.ajax({
				   url: '?wipb-saveSettings=' + $(button).data('setting') + '&replace=' + $(button).data('replace'),
				   type: 'post',
				   data: fields
				});

			// callback handler that will be called on success
				request.done(function (response, textStatus, jqXHR){
					working = 0;
					$(button).find('.fa').removeClass('fa-spin'); // stop spin

					if(response == 'success') {
						$('.wpib-backend-status').fadeIn().text('Great success! Settings saved. Refresh the page to apply all changes'); // loading status
						$(button).find('.fa').removeClass('fa-refresh').addClass('fa-check'); // icon
					}
					
					if(response == 'error') {
						$('.wpib-backend-status').fadeIn().text('Sorry! Some error happend'); // loading status
					}

					__wpib_clearStatusMsg();
					__wpib_initTypeahead(); // reinit

					var btnTimeout = setTimeout(function() {
						$(button).find('.fa').attr('class', icon_org_icon); // icon
						$('.wpib-backend-status').fadeOut();
					}, 2000);
				});

			// callback handler that will be called on failure
				request.fail(function (response){
					$('.wpib-backend-status').text('Sorry! Connection failed'); // loading status
					$(button).find('.fa').removeClass('fa-spin'); // stop spin
					__wpib_clearStatusMsg();
				});
			}
		// Save custom events
			$('.wpib-backend-savesetting').click(function() {
				wpibSaveSetting(this);
			});

		// Save general settings

			$('#wpib-saveGeneralSettings').click(function() {
				wpibSaveSetting($('.wpib-backend-tab-content:visible'), this);
			});

		// function clear status message
		function __wpib_clearStatusMsg() {
			setTimeout(function() {
			  $('#wpib-status').fadeOut(function() {
			 	$(this).text('');
			  }); // loading status
			}, 2000);
		}
	});

////////////////////////////////
// API
////////////////////////////////

	var request;

	function validateDomain(domain, license, success) { // license is optional - only used to allow domain
		$.ajax({ 
			type: 'POST', 
			url: 'http://wp-instantbutler.com/api/call.php', 
			crossDomain: true,
			data:  {domain: domain, license: license},
			cache: false,
			async: true,

			success: function (auth_key){ // AUTH KEY
					$.ajax({ 
						type: 'POST', 
						url: 'http://wp-instantbutler.com/api/call.php', 
						crossDomain: true,
						data:  {domain: domain, license: license, auth_key: auth_key},
						cache: false,
						async: true,

						success: function (response){ // RESPONSE
							if (request) {
							   request.abort();
							}

							var apiResponse = response;

							// fire off the request
							request = $.ajax({
								url: '?wpib-apiresponse',
								type: 'post',
								data: {response: response}, // send response to site
								success: function(response){
								 	if(typeof success === 'function') {
								 		success(apiResponse);
								 	}
								}
							});
						},
						error: function(error){
						   //console.log('Could not validate license');
						}
					});
			},
			error: function(error){
			   //console.log('Could not validate license');
			}
		}); 
	}

////////////////////////////////
// Tabs
////////////////////////////////

$(function() {
// Set init tab
	__wpibSetTab('.wpib-backend-tab-button:first');

// Change tab on click
	$('.wpib-backend-tab-button').click(function() {
		__wpibSetTab(this);
	});

// Check link for specific tab, and set
	var specific_tab = window.location.hash.substring(1);
	
	if(specific_tab)
		$('.wpib-backend-tab-button[href="#'+specific_tab+'"]').click();

// Function to set tab
	function __wpibSetTab(btn) {
		var tabNo = $(btn).data('tab');

		$('.wpib-backend-tab-button').removeClass('wpib-active-button'); // remove all actives
		$(btn).addClass('wpib-active-button'); // set current active

		$('.wpib-backend-tab-content').stop().slideUp(300); // slide up current
		$('.wpib-backend-tab-content[data-tab="' + tabNo + '"]').stop().slideDown(500); // slide down next

		$('.hide-on-tab').show();
		$('.hide-on-tab[data-tab="' + tabNo + '"]').stop().hide(); // hide defined elements on this tab

		$('#wpib-savebutton').data('setting', $(btn).data('settings-name')); // define which settings to save
	}

// Validate license
	$('#validateLicense').click(function() {
		var btn = this;

		$(btn).prop('disabled', true);
		$(btn).find('.fa').removeClass('fa-unlock').addClass('fa-spin fa-refresh');
		$(btn).parent().find('.wpib-backend-status').html('Validating license key...').slideDown();

		validateDomain($('#wplicense_domain').val(), $('#wplicense_key').val(), function(response) {
			console.log(response);
			$(btn).prop('disabled', false);

			console.log(response.status);

			if(response.status == 0 || response.type == 'trial') {
				var text = '<b style="color:red;">Invalid license key</b>';

				if(response.message)
					var text = '<b style="color:red;">' + response.message + '</b>';

				$(btn).find('.fa').removeClass('fa-spin fa-refresh').addClass('fa-unlock');
				$(btn).parent().find('.wpib-backend-status').html(text).slideDown();
			} else if(response.status == 1) {
				$(btn).find('.fa').removeClass('fa-spin fa-refresh').addClass('fa-lock');
				setInterval(function(){
					$(btn).find('.fa').removeClass('fa-lock').addClass('fa-unlock');
					location.reload(true);
				},1000);

				$(btn).parent().find('.wpib-backend-status').html('<b style="color:green;">Hurray! Full version is now activated. Enjoy!</b>').slideDown();
			}
		});
	});
});

////////////////////////////////
// General
////////////////////////////////
$(function() {
	// Remove the "no premium" dialog in settings page
	$('.nopremium').find('.alert-close').click(function() {
		$(this).parent().slideUp();
		$.get('../../../wp-admin/?wpib-action=remove-notification');
		return false;
	});


	// Enable live change of body class when changing admin color scheme
		$('#color-picker.scheme-list .color-option').click(function() {
			// Remove current
			$("#instantButlerDialog").removeClass (function (index, css) {
				return (css.match (/\badmin-color-\S+/g) || []).join(' ');
			});

			// add selected
			$('#instantButlerDialog').addClass('admin-color-' + $(this).find('input.tog').val());
		});

	// Track user clicks in menu
		$('#adminmenu li, .wp-submenu li').click(function(event) {
			var location = $(event.target).parent().attr('href');

			if(location) {
				window.location = addParameterToURL(location, 'wpib-action=menu-click');
				return false;
			}
		});

	// Remove "click count" notification
		$('.wpib-hide-click-noti').click(function() {
			$(this).parent().parent().parent().slideUp();

			if($(this).hasClass('remove-reminder')) {
				$.get('../../../wp-admin/?wpib-action=hide-click-noti&remove-reminder');
			} else {
				$.get('../../../wp-admin/?wpib-action=hide-click-noti');
			}
		})
});


// General functions
function addParameterToURL(_url, param){
	_url += (_url.split('?')[1] ? '&':'?') + param;
	return _url;
}

