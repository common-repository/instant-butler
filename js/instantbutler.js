////////////////////////////////
// General variables
////////////////////////////////

// PLEASE NOTE!
// typeahead function is renamed to wpib_typeahead
// for compability issues (with other libraries, like Bootstrap 2.3.2)
																			
	var _instantButlerContainer = '#instantButlerDialog, #instantButlerOverlay';								// the dialog container
	var _instantButlerInput 	= '#wp-instantButler-field'; 													// the dialog input
	var _instantButlerButton 	= '#wp-admin-bar-openInstantButlerDialog a[href="#"], .wpib-toggle-butler'; 	// button(s) to trigger dialog

////////////////////////////////
// General
////////////////////////////////

// Navi Butler Dialog

	function _instantButlerDialog() { // toggle dialog
		jQuery(_instantButlerContainer).toggle();

		if(_instantButlerDialogIsVisble()) // if it is now visible, prepare butler
			return jQuery(_instantButlerInput).focus().val('').wpib_typeahead('setQuery', ''); // delete input contents
		
		jQuery(_instantButlerInput).blur(); // otherwise - blur the textfield
	}

// Returns true/false depending if the butler is visible or not
	function _instantButlerDialogIsVisble() {
		return jQuery(_instantButlerContainer).is(':visible');
	}

// Remove butler, if clicking outside element
	jQuery(document).click(function(event) {
		if(jQuery(_instantButlerContainer).hasClass('disable-hide-on-click-doc'))		// If option is disabled
			return;																		// Don't do anything

	    if(jQuery(event.target).parents().index(jQuery(_instantButlerContainer)) == -1) // if clicking outside Butler container
	        if(_instantButlerDialogIsVisble()) 											// if it is vissble
	            _instantButlerDialog(); 												// toggle (hide)
	});

////////////////////////////////
// Where the magic happens
////////////////////////////////

	// Function to init the typeahead
	// should be runned on page init
		function __wpib_initTypeahead() {
			var __wpibCurUri = jQuery(_instantButlerInput).data('cur-uri'); 	// curent URI where Butler is being accessed
			var __wpibWPRoot = jQuery(_instantButlerInput).data('wp-path'); 	// path to WP installation

			var __wpibSource = jQuery(_instantButlerInput).data('source');		// Custom source (if needed)
			if(!__wpibSource)
				var __wpibSource = __wpibWPRoot + '/wp-admin/?getInstantbutlerResults';

			var template = '<a class="wpib" href="{{link}}" title=\'{{name}}\'>{{icon}}<div class="wpib wpib-title" data-link="{{link}}">{{name}}</div>{{image}}</a>';

			jQuery(_instantButlerInput).wpib_typeahead('destroy'); 				// destroy previous instances (i.e. if settings are changed)
			jQuery(_instantButlerInput).wpib_typeahead([
			{
				name: '', 														// caching temporarly disabled till "clear cache" function avaiable
				prefetch: __wpibSource + '&prefetch&uri=' + __wpibCurUri,
				remote: __wpibSource + '&remote&uri=' + __wpibCurUri + '&q=%QUERY',
				autoselect: true,
				template: template,
				engine: Hogan,
			},
			{ // custom events
				name: 'instantbutlerCustomEvents',
				remote: __wpibSource + '&custom&uri=' + __wpibCurUri + '&q=%QUERY',
				limit: 4,
				autoselect: true,
				template: template,
			  	engine: Hogan                                                              
			  }                                                                                                                   
			]);
		}

	jQuery(function() {
		__wpib_initTypeahead(); // init on page load

		// Send user to destination on autocomplete
		jQuery(_instantButlerInput).on('typeahead:selected typeahead:autocompleted', function(e,datum) { 
			_instantButlerDialog(); // toggle

			if(datum.type == 'jsscript') { // if this is a custom js event - run the specific function
				executeFunctionByName('__wpInstantButler_customJsscript_' + datum.customEventId, window);
			} else if(datum.link) { // else simply go to link
				window.location = datum.link;
			} else if(jQuery(_instantButlerInput).data('fetch-type') != 2) {
	        	// if mode "as you type" is selected
	        	// let user type and click enter, before
	        	// results has arrived

				window.location = '../wp-admin/?getInstantbutlerResults&forward&q='+jQuery(_instantButlerInput).val();
			} else {
				alert('Error happend - could not forward');
			}
		});

		// Submit on 'enter' hotfix (untill jharding reupdates) - for indirect queries
		   jQuery(_instantButlerInput).keypress(function (event) { // if you click enter
		        if (event.which == 13) {			
		        	var location = jQuery('.tt-suggestion:first').find('div[data-link]').data('link');

		        	if(location) { // if any location is set
		        		_instantButlerDialog(); // hide
			            window.location = location; // go to the locaation
			            return false;
		        	} else if(jQuery(_instantButlerInput).data('fetch-type') == 1) {
			        	// if mode "as you type" is selected
			        	// let user type and click enter, before
			        	// results has arrived

			        	window.location = '../wp-admin/?getInstantbutlerResults&forward&q='+jQuery(_instantButlerInput).val();
		        	}
		        }
		    });

		// Submit on 'enter' (will imitate TAB function)
		// limitations do, however, exist, if there is no suggestion
			jQuery(_instantButlerInput).on('keydown', function(event) {
				// Define tab key
				var e = jQuery.Event("keydown");
				e.keyCode = e.which = 9; // 9 == tab

				if (event.which == 13) // if pressing enter
					jQuery(_instantButlerInput).trigger(e); // trigger "tab" key
			});
	});

////////////////////////////////
// OTHER LIBRARYS
////////////////////////////////

/* mousetrap v1.4.5 craig.is/killing/mice  - thanks! */
(function(J,r,f){function s(a,b,c){a.addEventListener?a.addEventListener(b,c,!1):a.attachEvent("on"+b,c)}function A(a){if("keypress"==a.type){var b=String.fromCharCode(a.which);a.shiftKey||(b=b.toLowerCase());return b}return h[a.which]?h[a.which]:B[a.which]?B[a.which]:String.fromCharCode(a.which).toLowerCase()}function t(a){a=a||{};var b=!1,c;for(c in n)a[c]?b=!0:n[c]=0;b||(u=!1)}function C(a,b,c,d,e,v){var g,k,f=[],h=c.type;if(!l[a])return[];"keyup"==h&&w(a)&&(b=[a]);for(g=0;g<l[a].length;++g)if(k=
l[a][g],!(!d&&k.seq&&n[k.seq]!=k.level||h!=k.action||("keypress"!=h||c.metaKey||c.ctrlKey)&&b.sort().join(",")!==k.modifiers.sort().join(","))){var m=d&&k.seq==d&&k.level==v;(!d&&k.combo==e||m)&&l[a].splice(g,1);f.push(k)}return f}function K(a){var b=[];a.shiftKey&&b.push("shift");a.altKey&&b.push("alt");a.ctrlKey&&b.push("ctrl");a.metaKey&&b.push("meta");return b}function x(a,b,c){m.stopCallback(b,b.target||b.srcElement,c)||!1!==a(b,c)||(b.preventDefault&&b.preventDefault(),b.stopPropagation&&b.stopPropagation(),
b.returnValue=!1,b.cancelBubble=!0)}function y(a){"number"!==typeof a.which&&(a.which=a.keyCode);var b=A(a);b&&("keyup"==a.type&&z===b?z=!1:m.handleKey(b,K(a),a))}function w(a){return"shift"==a||"ctrl"==a||"alt"==a||"meta"==a}function L(a,b,c,d){function e(b){return function(){u=b;++n[a];clearTimeout(D);D=setTimeout(t,1E3)}}function v(b){x(c,b,a);"keyup"!==d&&(z=A(b));setTimeout(t,10)}for(var g=n[a]=0;g<b.length;++g){var f=g+1===b.length?v:e(d||E(b[g+1]).action);F(b[g],f,d,a,g)}}function E(a,b){var c,
d,e,f=[];c="+"===a?["+"]:a.split("+");for(e=0;e<c.length;++e)d=c[e],G[d]&&(d=G[d]),b&&"keypress"!=b&&H[d]&&(d=H[d],f.push("shift")),w(d)&&f.push(d);c=d;e=b;if(!e){if(!p){p={};for(var g in h)95<g&&112>g||h.hasOwnProperty(g)&&(p[h[g]]=g)}e=p[c]?"keydown":"keypress"}"keypress"==e&&f.length&&(e="keydown");return{key:d,modifiers:f,action:e}}function F(a,b,c,d,e){q[a+":"+c]=b;a=a.replace(/\s+/g," ");var f=a.split(" ");1<f.length?L(a,f,b,c):(c=E(a,c),l[c.key]=l[c.key]||[],C(c.key,c.modifiers,{type:c.action},
d,a,e),l[c.key][d?"unshift":"push"]({callback:b,modifiers:c.modifiers,action:c.action,seq:d,level:e,combo:a}))}var h={8:"backspace",9:"tab",13:"enter",16:"shift",17:"ctrl",18:"alt",20:"capslock",27:"esc",32:"space",33:"pageup",34:"pagedown",35:"end",36:"home",37:"left",38:"up",39:"right",40:"down",45:"ins",46:"del",91:"meta",93:"meta",224:"meta"},B={106:"*",107:"+",109:"-",110:".",111:"/",186:";",187:"=",188:",",189:"-",190:".",191:"/",192:"`",219:"[",220:"\\",221:"]",222:"'"},H={"~":"`","!":"1",
"@":"2","#":"3",jQuery:"4","%":"5","^":"6","&":"7","*":"8","(":"9",")":"0",_:"-","+":"=",":":";",'"':"'","<":",",">":".","?":"/","|":"\\"},G={option:"alt",command:"meta","return":"enter",escape:"esc",mod:/Mac|iPod|iPhone|iPad/.test(navigator.platform)?"meta":"ctrl"},p,l={},q={},n={},D,z=!1,I=!1,u=!1;for(f=1;20>f;++f)h[111+f]="f"+f;for(f=0;9>=f;++f)h[f+96]=f;s(r,"keypress",y);s(r,"keydown",y);s(r,"keyup",y);var m={bind:function(a,b,c){a=a instanceof Array?a:[a];for(var d=0;d<a.length;++d)F(a[d],b,c);return this},
unbind:function(a,b){return m.bind(a,function(){},b)},trigger:function(a,b){if(q[a+":"+b])q[a+":"+b]({},a);return this},reset:function(){l={};q={};return this},stopCallback:function(a,b){return-1<(" "+b.className+" ").indexOf(" mousetrap ")?!1:"INPUT"==b.tagName||"SELECT"==b.tagName||"TEXTAREA"==b.tagName||b.isContentEditable},handleKey:function(a,b,c){var d=C(a,b,c),e;b={};var f=0,g=!1;for(e=0;e<d.length;++e)d[e].seq&&(f=Math.max(f,d[e].level));for(e=0;e<d.length;++e)d[e].seq?d[e].level==f&&(g=!0,
b[d[e].seq]=1,x(d[e].callback,c,d[e].combo)):g||x(d[e].callback,c,d[e].combo);d="keypress"==c.type&&I;c.type!=u||w(a)||d||t(b);I=g&&"keydown"==c.type}};J.Mousetrap=m;"function"===typeof define&&define.amd&&define(m)})(window,document);

////////////////////////////////
// Shortcut's
////////////////////////////////

	jQuery(function() {
		// WHEN PAGE HAS SUCCESSFULLY
		// LOADED ALL CONTENT

		var _instantButlerCustomKey = jQuery(_instantButlerInput).data('shortcut'); // get shortcut key to toggle butler
		var _instantButlerAnyKey = jQuery(_instantButlerInput).data('anykey'); // press any key to show (returns 'on' if set)

		// Shortcut key
			if(_instantButlerCustomKey) { // if any set
				Mousetrap.bind(_instantButlerCustomKey, function(e) {
				    _instantButlerDialog(); // toggle the dialog
				});
			}

		// Admin Bar Menu Button
			jQuery(_instantButlerButton).click(function() {
				_instantButlerDialog(); // open the dialog
				return false; // prevent defaults
			});

		// Any button shortcut
			if(!_instantButlerDialogIsVisble()) {
				// Reserved keys, that can't trigger event
					var reservedKeys = [9,13,16,17,18,19,20,32,35,36,37,38,39,40,91,93,224]; // reserved keys (ctrl, space, shift, etc)

				// Show dialog on key press
					jQuery('html').keydown(function (e) {
						if(e.keyCode == 27 && _instantButlerDialogIsVisble()) // close on esc
							return _instantButlerDialog();

						if(jQuery.inArray(e.keyCode,reservedKeys) !== -1 || e.altKey || e.ctrlKey || e.shiftKey || e.metaKey || _instantButlerAnyKey != 'on' || e.keyCode == 27)
							return;

						var elemFocused = jQuery(':focus'); // get current focused element

						if(!jQuery(elemFocused).is("input, textarea, select")) { // if current focused element is not input, show dialog
							_instantButlerDialog();
						}
					});
			}
	});

////////////////////////////////
// For custom events
////////////////////////////////

// Exceute function by name
// Used by custom events
	function executeFunctionByName(functionName, context) {
	  var args = Array.prototype.slice.call(arguments).splice(2);
	  var namespaces = functionName.split(".");
	  var func = namespaces.pop();
	  for(var i = 0; i < namespaces.length; i++) {
	    context = context[namespaces[i]];
	  }
	  return context[func].apply(this, args);
	}