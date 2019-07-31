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
	var dimensions = {};
	dimensions.height = $('#' + id).height();
	dimensions.width = $('#' + id).width();
	var type = $('#' + id).css('position');
	var viewport = getWindowSize();
	var offset = getScrollOffset();

	// console.log('Id %s, type: %s, dimensions %s x %s, viewport %s x %s', id, type, dimensions.width, dimensions.height, viewport.width, viewport.height);
	// console.log('Scrolloffset left: %s, top: %s', offset.left, offset.top);
	// console.log('Left: %s, Top: %s (%s)', parseInt((viewport.width / 2) - (dimensions.width / 2) + offset.left), parseInt((viewport.height / 2) - (dimensions.height / 2) + offset.top), parseInt((viewport.height / 2) - (dimensions.height / 2)));
	if (left) {
		$('#' + id).css({
			left: parseInt((viewport.width / 2) - (dimensions.width / 2) + offset.left) + 'px'
		});
	}
	if (top) {
		// if we have fixed, we do not add the offset, else it moves out of the screen
		var top_pos = type == 'fixed' ?
			parseInt((viewport.height / 2) - (dimensions.height / 2)) :
			parseInt((viewport.height / 2) - (dimensions.height / 2) + offset.top);
		$('#' + id).css({
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
		if ($('#' + element).length)
		{
			$('body,html').animate({
				scrollTop: $('#' + element).offset().top - offset
			}, 500);
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
	var date = new Date();
	return date.getTime();
}

// METHOD: dec2hex
// PARAMS: decimal string
// RETURN: string
// DESC  : dec2hex :: Integer -> String
//         i.e. 0-255 -> '00'-'ff'
function dec2hex(dec)
{
	return ('0' + dec.toString(16)).substr(-2);
}

// METHOD: generateId
// PARAMS: lenght in int
// RETURN: random string
// DESC  : generateId :: Integer -> String
//         only works on mondern browsers
function generateId(len)
{
	var arr = new Uint8Array((len || 40) / 2);
	(window.crypto || window.msCrypto).getRandomValues(arr);
	return Array.from(arr, dec2hex).join('');
}

// METHOD: randomIdF()
// PARAMS: none
// RETURN: not true random string
// DESC  : creates a pseudo random string of 10 characters
//         after many runs it will create duplicates
function randomIdF()
{
	return Math.random().toString(36).substring(2);
}

// METHOD: isObject
// PARAMS: possible object
// RETURN: true/false if it is an object or not
// DESC  : checks if a variable is an object
function isObject(val) {
	if (val === null) {
		return false;
	}
	return ((typeof val === 'function') || (typeof val === 'object'));
}

// METHOD: keyInObject
// PARAMS: key name, object
// RETURN: true/false if key exists in object
// DESC  : checks if a key exists in a given object
const keyInObject = (key, object) => (key in object) ? true : false;
/*function keyInObject(key, object)
{
	return (key in object) ? true : false;
}*/

// METHOD: exists
// PARAMS: uid
// RETURN: true/false
// DESC  : checks if a DOM element actually exists
const exists = (id) => $('#' + id).length > 0 ? true : false;
/*function exists(id)
{
	return $('#' + id).length > 0 ? true : false;
}*/

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
	if ($('#overlayBox').is(':visible')) {
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
	$('#indicator').addClass('progress');
	setCenter('indicator', true, true);
	$('#indicator').show();
	overlayBoxShow();
}
function actionIndicatorHide(loc = '')
{
	console.log('Indicator: HIDE [%s]', loc);
	$('#indicator').hide();
	$('#indicator').removeClass('progress');
	overlayBoxHide();
}

// METHOD: overlayBoxView
// PARAMS: none
// RETURN: none
// DESC  : shows or hides the overlay box
function overlayBoxShow()
{
	// check if overlay box exists and if yes set the z-index to 100
	if ($('#overlayBox').is(':visible')) {
		$('#overlayBox').css('zIndex', '100');
	} else {
		$('#overlayBox').show();
	}
}
function overlayBoxHide()
{
	// if the overlay box z-index is 100, do no hide, but set to 98
	if ($('#overlayBox').css('zIndex') == 100) {
		$('#overlayBox').css('zIndex', '98');
	} else {
		$('#overlayBox').hide();
	}
}

// METHOD: setOverlayBox
// PARAMS: none
// RETURN: none
// DESC   : position the overlay block box and shows it
function setOverlayBox()
{
	var viewport = document.viewport.getDimensions();
	$('#overlayBox').setStyle ({
		width: '100%',
		height: '100%'
	});
	$('#overlayBox').show();
}

// METHOD: ClearCall
// PARAMS: none
// RETURN: none
// DESC  : the abort call, clears the action box and hides it and the overlay box
function ClearCall()
{
	$('#actionBox').innerHTML = '';
	$('#actionBox').hide();
	$('#overlayBox').hide();
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
			if (isObject(base.sub) && base.sub.length > 0) {
				for (var i = 0; i < base.sub.length; i ++) {
					// recursive call to sub element
					ael(base.sub[i], attach, id);
				}
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
	for (var i = 0; i < attach.length; i ++) {
		base.sub.push(Object.assign({}, attach[i]));
	}
	return base;
}

// METHOD: rel [reset element]
// PARAMS: cel created element
// RETURN: returns sub reset base element
// DESC  : resets the sub elements of the base element given
const rel = (base) => {
	base.sub = [];
	return base;
};

// METHOD: rcssel [remove a css from the element]
// PARAMS: element, style sheet to remove
// RETURN: "none", in place because of reference
// DESC  : searches and removes style from css array
function rcssel(_element, css)
{
	var css_index = _element.css.indexOf(css);
	if (css_index > -1) {
		_element.css.splice(css_index, 1);
	}
	return _element;
}

// METHOD: acssel [add css element]
// PARAMS: element, style sheet to add
// RETURN: "none", in place add because of reference
// DESC  : adds a new style sheet to the element given
function acssel(_element, css)
{
	var css_index = _element.css.indexOf(css);
	if (css_index == -1) {
		_element.css.push(css);
	}
	return _element;
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
	var content = [];
	// main part line
	var line = '<' + tree.tag;
	var i;
	// first id, if set
	if (tree.id) {
		line += ' id="' + tree.id + '"';
		// if anything input (input, textarea, select then add name too)
		if (['input', 'textarea', 'select'].includes(tree.tag)) {
			line += ' name="' + (tree.name ? tree.name : tree.id) + '"';
		}
	}
	// second CSS
	if (isObject(tree.css) && tree.css.length > 0) {
		line += ' class="';
		for (i = 0; i < tree.css.length; i ++) {
			line += tree.css[i] + ' ';
		}
		// strip last space
		line = line.slice(0, -1);
		line += '"';
	}
	// options is anything key = "data"
	if (isObject(tree.options)) {
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
	if (isObject(tree.sub) && tree.sub.length > 0) {
		if (tree.content) {
			content.push(tree.content);
		}
		for (i = 0; i < tree.sub.length; i ++) {
			content.push(phfo(tree.sub[i]));
		}
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

// NOTE  : OLD FORMAT which misses multiple block set
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
	// wrapper to new call
	return html_options_block(name, data, selected, false, options_only, return_string, sort);
}

// NOTE  : USE THIS CALL, the above one is deprecated
// METHOD: html_options
// PARAMS: name/id, array for the options,
//         selected item uid
//         multiple [def false] if this is true, the drop down will be turned into multiple select
//         options_only [def false] if this is true, it will not print the select part
//         return_string [def false]: return as string and not as element
//         sort [def '']: if empty as is, else allowed 'keys', 'values' all others are ignored
// RETURN: html with build options block
// DESC  : creates an select/options drop down block.
//         the array needs to be key -> value format. key is for the option id and value is for the data output
function html_options_block(name, data, selected = '', multiple = false, options_only = false, return_string = false, sort = '')
{
	var content = [];
	var element_select;
	var select_options = {};
	var element_option;
	var data_list = []; // for sorted output
	var value;
	var option;
	if (multiple === true) {
		select_options.multiple = '';
	}
	// set outside select, gets stripped on return if options only is true
	element_select = cel('select', name, '', [], select_options);
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
		value = data[key];
		console.log('create [%s] options: key: %s, value: %s', name, key, value);
		// basic options init
		options = {
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
			for (var i = 0; i < element_select.sub.length; i ++) {
				content.push(phfo(element_select.sub[i]));
			}
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
	var element_option;
	var option_selected;
	var data_list = []; // for sorted output
	var value;
	// skip if not exists
	if (document.getElementById(name)) {
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
		document.getElementById(name).innerHTML = '';
		for (const key of data_list) {
			value = data[key];
			// console.log('add [%s]  options: key: %s, value: %s', name, key, value);
			element_option = document.createElement('option');
			element_option.label = value;
			element_option.value = key;
			element_option.innerHTML = value;
			document.getElementById(name).appendChild(element_option);
		}
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
