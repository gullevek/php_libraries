/* general edit javascript */
/* prototype version */

/* jshint esversion: 6 */

// debug set
/*var FRONTEND_DEBUG = false;
var DEBUG = true;
if (!DEBUG) {
	$($H(window.console)).each(function(w) {
		window.console[w.key] = function() {};
	});
}*/

/**
 * opens a popup window with winName and given features (string)
 * @param {String} theURL   the url
 * @param {String} winName  window name
 * @param {Object} features popup features
 */
function pop(theURL, winName, features) {
	winName = window.open(theURL, winName, features);
	winName.focus();
}

/**
 * automatically resize a text area based on the amount of lines in it
 * @param {[string} ta_id element id
 */
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

/**
 * shows or hides the menu
 * this is used in some old menu templates
 * @param {String} status show or hide
 * @param {String} id     element id to work on
 */
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

/**
 * used in old templates
 * move element action
 * @param {String} id        element id to move
 * @param {String} direction move direction
 */
function mv(id, direction)
{
	document.forms[form_name].action.value = 'move';
	document.forms[form_name].action_flag.value = direction;
	document.forms[form_name].action_id.value = id;
	document.forms[form_name].submit();
}

/**
 * used in old templates
 * load element action
 * @param {String} id the element id to load
 */
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

/**
 * hides an element, additional writes 1 (show) or 0 (hide) into <id>Flag field
 * this needs scriptacolous installed for BlindUp/BlindDown
 * @param  {String}  id       element id to hide
 * @param  {String}  showText text for the element if shown
 * @param  {String}  hideText text for the element if hidden
 * @return {Boolean}          returns true if hidden, or false if not
 */
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

/**
 * wrapper to get the real window size for the current browser window
 * @return {Object} object with width/height
 */
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

/**
 * wrapper to get the correct scroll offset
 * @return {Object} object with x/y px
 */
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

/**
 * centers div to current window size middle
 * @param {String}  id   element to center
 * @param {Boolean} left if true centers to the middle from the left
 * @param {Boolean} top  if true centers to the middle from the top
 */
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

/**
 * goes to an element id position
 * @param {String} element    element id to move to
 * @param {Number} [offset=0] offset from top, default is 0 (px)
 */
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

/**
 * uses the i18n object created in the translation template
 * that is filled from gettext in PHP
 * @param  {String} string text to translate
 * @return {String}        translated text (based on PHP selected language)
 */
function __(string)
{
	if (typeof i18n !== 'undefined' && isObject(i18n) && i18n[string]) {
		return i18n[string];
	} else {
		return string;
	}
}

/**
 * simple sprintf formater for replace
 * usage: "{0} is cool, {1} is not".format("Alpha", "Beta");
 * First, checks if it isn't implemented yet.
 * @param  {String} !String.prototype.format string with elements to be replaced
 * @return {String}                          Formated string
 */
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

/**
 * formats flat number 123456 to 123,456
 * @param  {Number} x number to be formated
 * @return {String}   formatted with , in thousands
 */
const numberWithCommas = (x) => {
	var parts = x.toString().split(".");
	parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	return parts.join(".");
};

/**
 * converts line breaks to br
 * @param  {String} string any string
 * @return {String}        string with <br>
 */
function convertLBtoBR(string)
{
	return string.replace(/(?:\r\n|\r|\n)/g, '<br>');
}

/**
 * escape HTML string
 * @param  {String} !String.prototype.escapeHTML HTML data string to be escaped
 * @return {String}                              escaped string
 */
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

/**
 * unescape a HTML encoded string
 * @param  {String} !String.prototype.unescapeHTML data with escaped entries
 * @return {String}                                HTML formated string
 */
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

/**
 * returns current timestamp (unix timestamp)
 * @return {Number} timestamp (in milliseconds)
 */
function getTimestamp()
{
	var date = new Date();
	return date.getTime();
}

/**
 * dec2hex :: Integer -> String
 * i.e. 0-255 -> '00'-'ff'
 * @param  {Number} dec decimal string
 * @return {String}     hex encdoded number
 */
function dec2hex(dec)
{
	return ('0' + dec.toString(16)).substr(-2);
}

/**
 * generateId :: Integer -> String
 * only works on mondern browsers
 * @param  {Number} len length of unique id string
 * @return {String}     random string in length of len
 */
function generateId(len)
{
	var arr = new Uint8Array((len || 40) / 2);
	(window.crypto || window.msCrypto).getRandomValues(arr);
	return Array.from(arr, dec2hex).join('');
}

/**
 * creates a pseudo random string of 10 characters
 * works on all browsers
 * after many runs it will create duplicates
 * @return {String} not true random string
 */
function randomIdF()
{
	return Math.random().toString(36).substring(2);
}

/**
 * checks if a variable is an object
 * @param  {Mixed}   val possible object
 * @return {Boolean}     true/false if it is an object or not
 */
function isObject(val) {
	if (val === null) {
		return false;
	}
	return ((typeof val === 'function') || (typeof val === 'object'));
}

/**
 * checks if a key exists in a given object
 * @param  {String}  key    key name
 * @param  {Object}  object object to search key in
 * @return {Boolean}        true/false if key exists in object
 */
const keyInObject = (key, object) => (Object.prototype.hasOwnProperty.call(object, key)) ? true : false;
/*function keyInObject(key, object)
{
	return (Object.prototype.hasOwnProperty.call(object, key)) ? true : false;
}*/

/**
 * returns matching key of value
 * @param  {Object} obj   object to search value in
 * @param  {Mixed}  value any value (String, Number, etc)
 * @return {String}       the key found for the first matching value
 */
const getKeyByValue = (obj, value) => Object.keys(obj).find(key => obj[key] === value);
// function getKeyByValue(object, value)
// {
// 	return Object.keys(object).find(key => object[key] === value);
// }

/**
 * returns true if value is found in object with a key
 * @param  {Object}  obj   object to search value in
 * @param  {Mixed}   value any value (String, Number, etc)
 * @return {Boolean}       true on value found, false on not found
 */
const valueInObject = (obj, value) => (Object.keys(obj).find(key => obj[key] === value)) ? true : false;

/**
 * checks if a DOM element actually exists
 * @param  {String}  id Element id to check for
 * @return {Boolean}    true if element exists, false on failure
 */
const exists = (id) => $(id).length > 0 ? true : false;
/*function exists(id)
{
	return $(id).length > 0 ? true : false;
}*/

/**
 * converts a int number into bytes with prefix in two decimals precision
 * currently precision is fixed, if dynamic needs check for max/min precision
 * @param  {Number} bytes bytes in int
 * @return {String}       string in GB/MB/KB
 */
function formatBytes(bytes)
{
	var i = -1;
	do {
		bytes = bytes / 1024;
		i++;
	} while (bytes > 99);

	return parseFloat(Math.round(bytes * Math.pow(10, 2)) / Math.pow(10, 2)) + ['kB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];
}

/**
 * prints out error messages based on data available from the browser
 * @param {Object} err error from try/catch block
 */
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

/**
 * show or hide the "do" overlay
 * @param {String} [loc=''] location name for action indicator, default empty. for console.log
 */
function actionIndicator(loc = '')
{
	if ($('overlayBox').visible()) {
		actionIndicatorHide(loc);
	} else {
		 actionIndicatorShow(loc);
	}
}

/**
 * explicit show for action Indicator
 * instead of automatically show or hide, do on command show
 * @param {String} [loc=''] optional location name, empty if not set. for console.log
 */
function actionIndicatorShow(loc = '')
{
	console.log('Indicator: SHOW [%s]', loc);
	$('indicator').addClassName('progress');
	setCenter('indicator', true, true);
	$('indicator').show();
	overlayBoxShow();
}

/**
 * explicit hide for action Indicator
 * instead of automatically show or hide, do on command hide
 * @param {String} [loc=''] optional location name, empty if not set. for console.log
 */
function actionIndicatorHide(loc = '')
{
	console.log('Indicator: HIDE [%s]', loc);
	$('indicator').hide();
	$('indicator').removeClassName('progress');
	overlayBoxHide();
}

/**
 * shows the overlay box
 */
function overlayBoxShow()
{
	// check if overlay box exists and if yes set the z-index to 100
	if ($('overlayBox').visible()) {
		$('overlayBox').style.zIndex = "100";
	} else {
		$('overlayBox').show();
	}
}

/**
 * hides the overlay box
 */
function overlayBoxHide()
{
	// if the overlay box z-index is 100, do no hide, but set to 98
	if ($('overlayBox').style.zIndex == 100) {
		$('overlayBox').style.zIndex = "98";
	} else {
		$('overlayBox').hide();
	}
}

/**
 * position the overlay block box and shows it
 */
function setOverlayBox()
{
	var viewport = document.viewport.getDimensions();
	$('overlayBox').setStyle ({
		width: '100%',
		height: '100%'
	});
	$('overlayBox').show();
}

/**
 * the abort call, clears the action box and hides it and the overlay box
 */
function ClearCall()
{
	$('actionBox').innerHTML = '';
	$('actionBox').hide();
	$('overlayBox').hide();
}

// *** DOM MANAGEMENT FUNCTIONS
/**
 * reates object for DOM element creation flow
 * @param  {String} tag          must set tag (div, span, etc)
 * @param  {String} [id='']      optional set for id, if input, select will be used for name
 * @param  {String} [content=''] text content inside, is skipped if sub elements exist
 * @param  {Array}  [css=[]]     array for css tags
 * @param  {Object} [options={}] anything else (value, placeholder, OnClick, style)
 * @return {Object}              created element as an object
 */
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

/**
 * attach a cel created object to another to create a basic DOM tree
 * @param  {Object} base    object where to attach/search
 * @param  {Object} attach  the object to be attached
 * @param  {String} [id=''] optional id, if given search in base for this id and attach there
 * @return {Object}         "none", technically there is no return needed as it is global attach
 */
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

/**
 * directly attach n elements to one master base element
 * this type does not support attach with optional id
 * @param  {Object}    base   object to where we attach the elements
 * @param  {...Object} attach attach 1..n: attach directly to the base element those attachments
 * @return {Object}           "none", technically there is no return needed, global attach
 */
function aelx(base, ...attach)
{
	attach.each(function(t) {
		base.sub.push(Object.assign({}, t));
	});
	return base;
}

/**
 * resets the sub elements of the base element given
 * @param  {Object} base cel created element
 * @return {Object}      returns reset base element
 */
const rel = (base) => {
	base.sub = [];
	return base;
};

/**
 * searches and removes style from css array
 * @param  {Object} _element element to work one
 * @param  {String css      style sheet to remove (name)
 * @return {Object}          returns full element
 */
function rcssel(_element, css)
{
	var css_index = _element.css.indexOf(css);
	if (css_index > -1) {
		_element.css.splice(css_index, 1);
	}
	return _element;
}

/**
 * adds a new style sheet to the element given
 * @param  {Object} _element element to work on
 * @param  {String} css      style sheet to add (name)
 * @return {Object}         returns full element
 */
function acssel(_element, css)
{
	var css_index = _element.css.indexOf(css);
	if (css_index == -1) {
		_element.css.push(css);
	}
	return _element;
}

/**
 * removes one css and adds another
 * is a wrapper around rcssel/acssel
 * @param  {Object} _element element to work on
 * @param  {String} rcss     style to remove (name)
 * @param  {String} acss     style to add (name)
 * @return {Object}          returns full element
 */
function scssel(_element, rcss, acss)
{
	rcssel(_element, rcss);
	acssel(_element, acss);
	return _element;
}

/**
 * parses the object tree created with cel/ael and converts it into an HTML string
 * that can be inserted into the page
 * @param  {Object} tree object tree with dom element declarations
 * @return {String}      HTML string that can be used as innerHTML
 */
function phfo(tree)
{
	// holds the elements
	var content = [];
	// main part line
	var line = '<' + tree.tag;
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

/**
 * NOTE: OLD FORMAT which misses multiple block set
 * creates an select/options drop down block.
 * the array needs to be key -> value format.
 * key is for the option id and value is for the data output
 * @param  {String}  name                  name/id
 * @param  {Object}  data                  array for the options
 * @param  {String}  [selected='']         selected item uid
 * @param  {Boolean} [options_only=false]  if this is true, it will not print the select part
 * @param  {Boolean} [return_string=false] return as string and not as element
 * @param  {String}  [sort='']             if empty as is, else allowed 'keys',
 *                                         'values' all others are ignored
 * @return {String}                        html with build options block
 */
function html_options(name, data, selected = '', options_only = false, return_string = false, sort = '')
{
	// wrapper to new call
	return html_options_block(name, data, selected, false, options_only, return_string, sort);
}

/**
 * NOTE: USE THIS CALL, the above one is deprecated
 * creates an select/options drop down block.
 * the array needs to be key -> value format.
 * key is for the option id and value is for the data output
 * @param  {String}  name                  name/id
 * @param  {Object}  data                  array for the options
 * @param  {String}  [selected='']         selected item uid
 * @param  {Number}  [multiple=0]          if this is 1 or larger, the drop down
 *                                         will be turned into multiple select
 *                                         the number sets the size value unless it is 1,
 *                                         then it is default
 * @param  {Boolean} [options_only=false]  if this is true, it will not print the select part
 * @param  {Boolean} [return_string=false] return as string and not as element
 * @param  {String}  [sort='']             if empty as is, else allowed 'keys',
 *                                         'values' all others are ignored
 * @return {String}                        html with build options block
 */
function html_options_block(name, data, selected = '', multiple = 0, options_only = false, return_string = false, sort = '')
{
	var content = [];
	var element_select;
	var select_options = {};
	var element_option;
	var data_list = []; // for sorted output
	var value;
	var option;
	if (multiple > 0) {
		select_options.multiple = '';
		if (multiple > 1) {
			select_options.size = multiple;
		}
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
		// console.log('create [%s] options: key: %s, value: %s', name, key, value);
		// basic options init
		options = {
			'label': value,
			'value': key
		};
		// add selected if matching
		if (multiple == 0 && !Array.isArray(selected) && selected == key) {
			options.selected = '';
		}
		// for multiple, we match selected as array
		if (multiple == 1 && Array.isArray(selected) && selected.indexOf(key) != -1) {
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

/**
 *  refills a select box with options and keeps the selected
 * @param {String} name      name/id
 * @param {Object} data      array of options
 * @param {String} [sort=''] if empty as is, else allowed 'keys', 'values'
 *                            all others are ignored
 */
function html_options_refill(name, data, sort = '')
{
	var element_option;
	var option_selected;
	var data_list = []; // for sorted output
	var value;
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
			value = data[key];
			// console.log('add [%s]  options: key: %s, value: %s', name, key, value);
			element_option = document.createElement('option');
			element_option.label = value;
			element_option.value = key;
			element_option.innerHTML = value;
			$(name).appendChild(element_option);
		}
	}
}

/**
 * parses a query string from window.location.search.substring(1)
 * ALTERNATIVE CODE
 * var url = new URL(window.location.href);
 * param_uid = url.searchParams.get('uid');
 * @param  {String}        [query='']      the query string to parse
 *                                         if not set will auto fill
 * @param  {String}        [return_key=''] if set only returns this key entry
 *                                         or empty for none
 * @return {Object|String}                 parameter entry list
 */
function parseQueryString(query = '', return_key = '') {
	if (!query) {
		query = window.location.search.substring(1);
	}
	var vars = query.split("&");
	var query_string = {};
	for (var i = 0; i < vars.length; i++) {
		var pair = vars[i].split("=");
		var key = decodeURIComponent(pair[0]);
		var value = decodeURIComponent(pair[1]);
		// If first entry with this name
		if (typeof query_string[key] === "undefined") {
			query_string[key] = decodeURIComponent(value);
			// If second entry with this name
		} else if (typeof query_string[key] === "string") {
			var arr = [query_string[key], decodeURIComponent(value)];
			query_string[key] = arr;
			// If third or later entry with this name
		} else {
			query_string[key].push(decodeURIComponent(value));
		}
	}
	if (return_key) {
		if (keyInObject(return_key, query_string)) {
			return query_string[return_key];
		} else {
			return '';
		}
	} else {
		return query_string;
	}
}

/**
 * searchs the current url for a parameter
 * @param  {String} key uid key to get data for
 * @return {String}     value for the key or '' for not found
 */
function getQueryStringParam(key)
{
	var url = new URL(window.location.href);
 	var param = url.searchParams.get(key);
 	if (param) {
 		return param;
 	} else {
 		return '';
 	}
}

// *** MASTER logout call
/**
 * submits basic data for form logout
 */
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
