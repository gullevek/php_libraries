/* general edit javascript */

/* jshint esversion: 6 */

// debug set
/*var FRONTEND_DEBUG = false;
var DEBUG = true;
if (!DEBUG) {
	$($H(window.console)).each(function(w) {
		window.console[w.key] = function() {};
	});
}*/

// METHOD: pop
// PARAMS: url, window name, features
// RETURN: none
// DESC  : opens a popup window with winNAme and given features (string)
function pop(theURL, winName, features) {
	winName = window.open(theURL, winName, features);
	winName.focus();
}

// METHOD: expandTA
// PARAMS: id
// RETURN: none
// DESC  : automatically resize a text area based on the amount of lines in it
function expandTA(ta_id) {
	var ta;
	// if a string comes, its a get by id, else use it as an element pass on
	if (!ta_id.length) {
		ta = ta_id;
	} else {
		ta = document.getElementById(ta_id);
	}
	var maxChars = ta.cols;
	var theRows = ta.value.split("\n");
	var numNewRows = 0;

	for ( var i = 0; i < theRows.length; i++ ) {
		if ((theRows[i].length+2) > maxChars) {
			numNewRows += Math.ceil( (theRows[i].length+2) / maxChars ) ;
		}
	}
	ta.rows = numNewRows + theRows.length;
}

// METHOD: ShowHideMenu
// PARAMS: status -> show or hide
//         id -> id to work on
// RETURN: none
// DESC:   shows or hides the menu
//         this is used in some old menu templates
function ShowHideMenu(status, id)
{
	if (status == 'show') {
		document.getElementById(id).style.visibility = 'visible';
		if (document.getElementById('search_results').innerHTML) {
			document.getElementById('search_results').style.visibility = 'visible';
		}
	} else if (status == 'hide') {
		document.getElementById(id).style.visibility = 'hidden';
		if (document.getElementById('search_results').style.visibility == 'visible') {
			document.getElementById('search_results').style.visibility = 'hidden';
		}
	}
}

// used in old templates
// move element action
function mv(id, direction)
{
	document.forms[form_name].action.value = 'move';
	document.forms[form_name].action_flag.value = direction;
	document.forms[form_name].action_id.value = id;
	document.forms[form_name].submit();
}

// load element action
function le(id)
{
	document.forms[form_name].action.value = 'load';
	if (load_id) {
		document.forms[form_name].action_yes.value = confirm('Do you want to load this data?');
	} else {
		document.forms[form_name].action_yes.value = 'true';
	}
	document.forms[form_name].action_id.value = id;
	document.forms[form_name].action_menu.value = id;
	if (document.forms[form_name].action_yes.value == 'true') {
		document.forms[form_name].submit();
	}
}

// METHOD: sh
// PARAMS: id -> element to hide
//         showText -> text for the element if shown
//         hideText -> text for the element if hidden
// RETURN: returns true if hidden, or false if not
// DESC  : hides an element, additional writes 1 (show) or 0 (hide) into <id>Flag field
//         this needs scriptacolous installed for BlindUp/BlindDown
function sh(id, showText, hideText)
{
	flag = id + 'Flag';
	btn = id + 'Btn';
	// get status from element (hidden or visible)
	divStatus = $(id).visible();
	//console.log('Set flag %s for element %s', divStatus, id);
	if (divStatus) {
		// hide the element
		Effect.BlindUp(id, {duration:0.3});
		$(flag).value = 0;
		$(btn).innerHTML = showText;
	} else if (!divStatus) {
		// show the element
		Effect.BlindDown(id, {duration:0.3});
		$(flag).value = 1;
		$(btn).innerHTML = hideText;
	}
	// return current button status
	return divStatus;
}

// METHOD: getWindowSize
// PARAMS: none
// RETURN: array with width/height
// DESC  : wrapper to get the real window size for the current browser window
function getWindowSize()
{
	var width, height;
	width = window.innerWidth || (window.document.documentElement.clientWidth || window.document.body.clientWidth);
	height = window.innerHeight || (window.document.documentElement.clientHeight || window.document.body.clientHeight);
	return {
		width: width,
		height: height
	};
}

// METHOD: getScrollOffset
// PARAMS: none
// RETURN: array with x/y px
// DESC  : wrapper to get the correct scroll offset
function getScrollOffset()
{
	var left, top;
	left = window.pageXOffset || (window.document.documentElement.scrollLeft || window.document.body.scrollLeft);
	top = window.pageYOffset || (window.document.documentElement.scrollTop || window.document.body.scrollTop);
	return {
		left: left,
		top: top
	};
}

// METHOD: setCenter
// PARAMS: id to set center
// RETURN: none
// DESC  : centers div to current window size middle
function setCenter(id, left, top)
{
	// get size of id
	var dimensions = $(id).getDimensions();
	var type = $(id).getStyle('position');
	var viewport = getWindowSize();
	var offset = getScrollOffset();

	console.log('Id %s, type: %s, dimensions %s x %s, viewport %s x %s', id, type, dimensions.width, dimensions.height, viewport.width, viewport.height);
	// console.log('Scrolloffset left: %s, top: %s', offset.left, offset.top);
	// console.log('Left: %s, Top: %s (%s)', parseInt((viewport.width / 2) - (dimensions.width / 2) + offset.left), parseInt((viewport.height / 2) - (dimensions.height / 2) + offset.top), parseInt((viewport.height / 2) - (dimensions.height / 2)));
	if (left) {
		$(id).setStyle ({
			left: parseInt((viewport.width / 2) - (dimensions.width / 2) + offset.left) + 'px'
		});
	}
	if (top) {
		// if we have fixed, we do not add the offset, else it moves out of the screen
		var top_pos = type == 'fixed' ? parseInt((viewport.height / 2) - (dimensions.height / 2)) : parseInt((viewport.height / 2) - (dimensions.height / 2) + offset.top);
		$(id).setStyle ({
			top: top_pos + 'px'
		});
	}
}

// METHOD: goToPos()
// PARAMS: element, offset (default 0)
// RETURN: none
// DESC:   goes to an element id position
function goToPos(element, offset = 0)
{
	try {
		if ($(element))
		{
			// get the element pos
			var pos = $(element).cumulativeOffset();
			// if not top element and no offset given, set auto offset for top element
			// also compensate by -40 for some offset calc issue and not have it too much to the header
			if (pos.top != 0 && offset == 0) {
				offset = ($(GL_main_content_div).style.paddingTop.replace('px', '') * -1) - 40;
			}
			//console.log('Scroll to: %s, Offset: %s [%s], PT: %s', element, offset, $('pbsMainContent').style.paddingTop.replace('px', ''), pos.top);
			window.scrollTo(pos.left, pos.top + offset);
		}
	} catch (err) {
		errorCatch(err);
	}
}

// METHOD: __
// PARAMS: text
// RETURN: translated text (based on PHP selected language)
// DESC  : uses the i18n array created in the translation template, that is filled from gettext in PHP (Smarty)
function __(string)
{
	if (typeof i18n !== 'undefined' && isObject(i18n) && i18n[string]) {
		return i18n[string];
	} else {
		return string;
	}
}

// METHOD: string.format
// PARAMS: any, for string format
// RETURN: formatted string
// DESC  : simple sprintf formater for replace
//         "{0} is cool, {1} is not".format("Alpha", "Beta");
// First, checks if it isn't implemented yet.
if (!String.prototype.format) {
	String.prototype.format = function()
	{
		var args = arguments;
		return this.replace(/{(\d+)}/g, function(match, number)
		{
			return typeof args[number] != 'undefined' ?
				args[number] :
				match
			;
		});
	};
}

// METHOD: numberWithCommas
// PARAMS: number
// RETURN: formatted with , in thousands
// DESC  : formats flat number 123456 to 123,456
const numberWithCommas = (x) => {
	var parts = x.toString().split(".");
	parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	return parts.join(".");
};

// METHOD:
// PARAMS: string
// RETURN: string with <br>
// DESC  : converts line breaks to br
function convertLBtoBR(string)
{
	return string.replace(/(?:\r\n|\r|\n)/g, '<br>');
}

if (!String.prototype.escapeHTML) {
	String.prototype.escapeHTML = function() {
		return this.replace(/[&<>"'\/]/g, function (s) {
			var entityMap = {
				"&": "&amp;",
				"<": "&lt;",
				">": "&gt;",
				'"': '&quot;',
				"'": '&#39;',
				"/": '&#x2F;'
			};

			return entityMap[s];
		});
	};
}

if (!String.prototype.unescapeHTML) {
	String.prototype.unescapeHTML = function() {
		return this.replace(/&[#\w]+;/g, function (s) {
			var entityMap = {
				"&amp;": "&",
				"&lt;": "<",
				"&gt;": ">",
				'&quot;': '"',
				'&#39;': "'",
				'&#x2F;': "/"
			};

			return entityMap[s];
		});
	};
}

// METHOD: getTimestamp
// PARAMS: none
// RETURN: timestamp (in milliseconds)
// DESC  : returns current timestamp (unix timestamp)
function getTimestamp()
{
	let date = new Date();
	return date.getTime();
}

// METHOD: isObject
// PARAMS: possible object
// RETURN: true/false if it is an object or not
function isObject(val) {
	if (val === null) {
		return false;
	}
	return ((typeof val === 'function') || (typeof val === 'object'));
}

// METHOD: exists
// PARAMS: uid
// RETURN: true/false
// DESC  : checks if a DOM element actually exists
const exists = (id) => $(id) ? true : false;

// METHOD: formatBytes
// PARAMS: bytes in int
// RETURN: string in GB/MB/KB
// DESC  : converts a int number into bytes with prefix in two decimals precision
//         currently precision is fixed, if dynamic needs check for max/min precision
function formatBytes(bytes)
{
	var i = -1;
	do {
		bytes = bytes / 1024;
		i++;
	} while (bytes > 99);

	return parseFloat(Math.round(bytes * Math.pow(10, 2)) / Math.pow(10, 2)) + ['kB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];
}

// METHOD: errorCatch
// PARAMS: err (error from try/catch
// RETURN: none
// DESC  : prints out error messages based on data available from the browser
function errorCatch(err)
{
	// for FF & Chrome
	if (err.stack) {
		// only FF
		if (err.lineNumber) {
			console.log('ERROR[%s:%s] %s', err.name, err.lineNumber, err.message);
		} else if (err.line) {
			// only Safari
			console.log('ERROR[%s:%s] %s', err.name, err.line, err.message);
		} else {
			console.log('ERROR[%s] %s', err.name, err.message);
		}
		// stack trace
		console.log('ERROR[stack] %s', err.stack);
	} else if (err.number) {
		// IE
		console.log('ERROR[%s:%s] %s', err.name, err.number, err.message);
		console.log('ERROR[description] %s', err.description);
	} else {
		// the rest
		console.log('ERROR[%s] %s', err.name, err.message);
	}
}

// METHOD: actionIndicator
// PARAMS: none
// RETURN: none
// DESC   : show or hide the "do" overlay
function actionIndicator(loc = '')
{
	if ($('overlayBox').visible()) {
		actionIndicatorHide(loc);
	} else {
		 actionIndicatorShow(loc);
	}
}

// METHOD: actionIndicatorShow/actionIndicatorHide
// PARAMS: loc for console log info
// RETURN: none
// DESC  : explicit show/hide for action Indicator
//         instead of automatically show or hide, do
//         on command
function actionIndicatorShow(loc = '')
{
	console.log('Indicator: SHOW [%s]', loc);
	$('indicator').addClassName('progress');
	setCenter('indicator', true, true);
	$('indicator').show();
	overlayBoxShow();
}
function actionIndicatorHide(loc = '')
{
	console.log('Indicator: HIDE [%s]', loc);
	$('indicator').hide();
	$('indicator').removeClassName('progress');
	overlayBoxHide();
}

// METHOD: overlayBoxView
// PARAMS: none
// RETURN: none
// DESC  : shows or hides the overlay box
function overlayBoxShow()
{
	// check if overlay box exists and if yes set the z-index to 100
	if ($('overlayBox').visible()) {
		$('overlayBox').style.zIndex = "100";
	} else {
		$('overlayBox').show();
	}
}
function overlayBoxHide()
{
	// if the overlay box z-index is 100, do no hide, but set to 98
	if ($('overlayBox').style.zIndex == 100) {
		$('overlayBox').style.zIndex = "98";
	} else {
		$('overlayBox').hide();
	}
}

// METHOD: setOverlayBox
// PARAMS: none
// RETURN: none
// DESC   : position the overlay block box and shows it
function setOverlayBox()
{
	var viewport = document.viewport.getDimensions();
	$('overlayBox').setStyle ({
		width: '100%',
		height: '100%'
	});
	$('overlayBox').show();
}

// METHOD: ClearCall
// PARAMS: none
// RETURN: none
// DESC  : the abort call, clears the action box and hides it and the overlay box
function ClearCall()
{
	$('actionBox').innerHTML = '';
	$('actionBox').hide();
	$('overlayBox').hide();
}

// *** DOM MANAGEMENT FUNCTIONS
// METHOD: cel [create element]
// PARAMS: tag: must set tag (div, span, etc)
//         id: optional set for id, if input, select will be used for name
//         content: text content inside, is skipped if sub elements exist
//         css: array for css tags
//         options: anything else (value, placeholder, OnClick, style)
// RETURN: object
// DESC  : creates object for DOM element creation flow
const cel = (tag, id = '', content = '', css = [], options = {}) =>
	_element = {
		tag: tag,
		id: id,
		name: options.name, // override name if set [name gets ignored in tree build anyway]
		content: content,
		css: css,
		options: options,
		sub: []
	};

// METHOD: ael [attach element]
// PARAMS: base: object where to attach/search
//         attach: the object to be attached
//         id: optional id, if given search in base for this id and attach there
// RETURN: "none", technically there is no return needed
// DESC  : attach a cel created object to another to create a basic DOM tree
function ael(base, attach, id = '')
{
	if (id) {
		// base id match already
		if (base.id == id) {
			base.sub.push(Object.assign({}, attach));
		} else {
			// sub check
			if (base.sub.length > 0) {
				base.sub.each(function(t) {
					// recursive call to sub element
					ael(t, attach, id);
				});
			}
		}
	} else {
		base.sub.push(Object.assign({}, attach));
	}
	return base;
}

// METHOD: aelx [attach n elements]
// PARAMS: base: object to where we attach the elements
//         attach 1..n: attach directly to the base element those attachments
// RETURN: "none", technically there is no return needed
// DESC  : directly attach n elements to one master base element
//         this type does not support attach with optional id
function aelx(base, ...attach)
{
	attach.each(function(t) {
		base.sub.push(Object.assign({}, t));
	});
	return base;
}

// METHOD: rel [reset element]
// PARAMS: cel created element
// RETURN: "none", is self change, but returns base.sub
// DESC  : resets the sub elements of the base element given
const rel = (base) => base.sub = [];

// METHOD: rcssel [remove a css from the element]
// PARAMS: element, style sheet to remove
// RETURN: "none", in place because of reference
// DESC  : searches and removes style from css array
function rcssel(_element, css)
{
	let css_index = _element.css.indexOf(css);
	if (css_index > -1) {
		_element.css.splice(css_index, 1);
	}
}

// METHOD: acssel [add css element]
// PARAMS: element, style sheet to add
// RETURN: "none", in place add because of reference
// DESC  : adds a new style sheet to the element given
function acssel(_element, css)
{
	let css_index = _element.css.indexOf(css);
	if (css_index == -1) {
		_element.css.push(css);
	}
}

// METHOD: scssel
// PARAMS: element, style to remove, style to add
// RETURN: "none", in place add because of reference
// DESC  : removes one css and adds another
//         is a wrapper around rcssel/acssel
function scssel(_element, rcss, acss)
{
	rcssel(_element, rcss);
	acssel(_element, acss);
}

// METHOD: phfo [produce html from object]
// PARAMS: object tree with dom element declarations
// RETURN: HTML string that can be used as innerHTML
// DESC  : parses the object tree created with cel/ael
//         and converts it into an HTML string that can
//         be inserted into the page
function phfo(tree)
{
	// holds the elements
	let content = [];
	// main part line
	let line = '<' + tree.tag;
	// first id, if set
	if (tree.id) {
		line += ' id="' + tree.id + '"';
		// if anything input (input, textarea, select then add name too)
		if (['input', 'textarea', 'select'].includes(tree.tag)) {
			line += ' name="' + (tree.name ? tree.name : tree.id) + '"';
		}
	}
	// second CSS
	if (tree.css.length > 0) {
		line += ' class="';
		tree.css.each(function(t) {
			line += t + ' ';
		});
		// strip last space
		line = line.slice(0, -1);
		line += '"';
	}
	// options is anything key = "data"
	if (tree.options) {
		// ignores id, name, class as key
		for (const [key, item] of Object.entries(tree.options)) {
			if (!['id', 'name', 'class'].includes(key)) {
				line += ' ' + key + '="' + item + '"';
			}
		}
	}
	// finish open tag
	line += '>';
	// push finished line
	content.push(line);
	// dive into sub tree to attach sub nodes
	// NOTES: we can have content (text) AND sub nodes at the same level
	// CONTENT (TEXT) takes preference over SUB NODE in order
	if (tree.sub.length > 0) {
		if (tree.content) {
			content.push(tree.content);
		}
		tree.sub.each(function(t) {
			content.push(phfo(t));
		});
	} else if (tree.content) {
		content.push(tree.content);
	}
	// if not input close
	if (tree.tag != 'input') {
		content.push('</' + tree.tag + '>');
	}
	// combine to string
	return content.join('');
}
// *** DOM MANAGEMENT FUNCTIONS

// BLOCK: html wrappers for quickly creating html data blocks
// METHOD: html_options
// PARAMS: name/id, array for the options, selected item uid
//         options_only [def false] if this is true, it will not print the select part
//         return_string [def false]: return as string and not as element
//         sort [def '']: if empty as is, else allowed 'keys', 'values' all others are ignored
// RETURN: html with build options block
// DESC  : creates an select/options drop down block.
//         the array needs to be key -> value format. key is for the option id and value is for the data output
function html_options(name, data, selected = '', options_only = false, return_string = false, sort = '')
{
	let content = [];
	let element_select;
	let element_option;
	let data_list = []; // for sorted output
	// set outside select, gets stripped on return if options only is true
	element_select = cel('select', name);
	// console.log('Call for %s, options: %s', name, options_only);
	if (sort == 'keys') {
		data_list = Object.keys(data).sort();
	} else if (sort == 'values') {
		data_list = Object.keys(data).sort((a, b) => ('' + data[a]).localeCompare(data[b]));
	} else {
		data_list = Object.keys(data);
	}
	// console.log('ORDER: %s', data_list);
	// use the previously sorted list
	// for (const [key, value] of Object.entries(data)) {
	for (const key of data_list) {
		let value = data[key];
		console.log('create [%s] options: key: %s, value: %s', name, key, value);
		// basic options init
		let options = {
			'label': value,
			'value': key
		};
		// add selected if matching
		if (selected == key) {
			options.selected = '';
		}
		// create the element option
		element_option = cel('option', '', value, '', options);
		// attach it to the select element
		ael(element_select, element_option);
	}
	// if with select part, convert to text
	if (!options_only) {
		if (return_string) {
			content.push(phfo(element_select));
			return content.join('');
		} else {
			return element_select;
		}
	} else {
		// strip select part
		if (return_string) {
			element_select.sub.each(function(t) {
				content.push(phfo(t));
			});
			return content.join('');
		} else {
			return element_select.sub;
		}
	}
}

// METHOD: html_options_refill
// PARAMS: name/id, array of options, sort = ''
//         sort [def '']: if empty as is, else allowed 'keys', 'values' all others are ignored
// RETURN: none
// DESC  : refills a select box with options and keeps the selected
function html_options_refill(name, data, sort = '')
{
	let element_option;
	let option_selected;
	let data_list = []; // for sorted output
	// skip if not exists
	if ($(name)) {
		// console.log('Call for %s, options: %s', name, options_only);
		if (sort == 'keys') {
			data_list = Object.keys(data).sort();
		} else if (sort == 'values') {
			data_list = Object.keys(data).sort((a, b) => ('' + data[a]).localeCompare(data[b]));
		} else {
			data_list = Object.keys(data);
		}
		// first read in existing ones from the options and get the selected one
		[].forEach.call(document.querySelectorAll('#' + name + ' :checked'), function(elm) {
			option_selected = elm.value;
		});
		$(name).innerHTML = '';
		for (const key of data_list) {
			let value = data[key];
			// console.log('add [%s]  options: key: %s, value: %s', name, key, value);
			element_option = document.createElement('option');
			element_option.label = value;
			element_option.value = key;
			element_option.innerHTML = value;
			$(name).appendChild(element_option);
		}
	}
}

// METHOD: initDatepickr
// PARAMS: initial date ID (#)
// RETURN: true on ok, false on failure
// DESC  : inits date pickr which translations for dates (week/month)
function initDatepickr(init_date)
{
	if ($(init_date)) {
		datepickr('#' + init_date); // we need to add this so we have it initialized before we can actually change the definitions
		// dates in japanese
		datepickr.prototype.l10n.months.shorthand = [__('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'), __('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec')];
		datepickr.prototype.l10n.months.longhand = [__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December')];
		datepickr.prototype.l10n.weekdays.shorthand = [__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')];
		datepickr.prototype.l10n.weekdays.longhand = [__('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'), __('Sunday')];
		return true;
	} else {
		return false;
	}
}

// *** MASTER logout call
// METHOD: loginLogout
// PARAMS: none
// RETURN: none
// DESC  : submits basic data for form logout
function loginLogout()
{
	const form = document.createElement('form');
	form.method = 'post';
	const hiddenField = document.createElement('input');
	hiddenField.type = 'hidden';
	hiddenField.name = 'login_logout';
	hiddenField.value = 'Logout';
	form.appendChild(hiddenField);
	document.body.appendChild(form);
	form.submit();
}

/* END */
