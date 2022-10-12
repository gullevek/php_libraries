/* general edit javascript */
/* jquery version */

/* jshint esversion: 6 */

/* global i18n */

// debug set
/*var FRONTEND_DEBUG = false;
var DEBUG = true;
if (!DEBUG) {
	$($H(window.console)).each(function(w) {
		window.console[w.key] = function() {};
	});
}*/

// open overlay boxes counter for z-index
var GL_OB_S = 100;
var GL_OB_BASE = 100;

/**
 * opens a popup window with winName and given features (string)
 * @param {String} theURL   the url
 * @param {String} winName  window name
 * @param {Object} features popup features
 */
function pop(theURL, winName, features) // eslint-disable-line no-unused-vars
{
	winName = window.open(theURL, winName, features);
	winName.focus();
}

/**
 * automatically resize a text area based on the amount of lines in it
 * @param {[string} ta_id element id
 */
function expandTA(ta_id) // eslint-disable-line no-unused-vars
{
	var ta;
	// if a string comes, its a get by id, else use it as an element pass on
	if (!ta_id.length) {
		ta = ta_id;
	} else {
		ta = document.getElementById(ta_id);
	}
	var maxChars = ta.cols;
	var theRows = ta.value.split('\n');
	var numNewRows = 0;

	for ( var i = 0; i < theRows.length; i++ ) {
		if ((theRows[i].length+2) > maxChars) {
			numNewRows += Math.ceil( (theRows[i].length+2) / maxChars ) ;
		}
	}
	ta.rows = numNewRows + theRows.length;
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
	var dimensions = {
		height: $('#' + id).height(),
		width: $('#' + id).width()
	};
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

/**
 * goes to an element id position
 * @param {String} element            element id to move to
 * @param {Number} [offset=0]         offset from top, default is 0 (px)
 * @param {Number} [duration=500]     animation time, default 500ms
 * @param {String} [base='body,html'] base element for offset scroll
 */
function goToPos(element, offset = 0, duration = 500, base = 'body,html') // eslint-disable-line no-unused-vars
{
	try {
		if ($('#' + element).length) {
			$(base).animate({
				scrollTop: $('#' + element).offset().top - offset
			}, duration);
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
 * @param  {String} String.prototype.format string with elements to be replaced
 * @return {String}                         Formated string
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
 * round to digits (float)
 * @param  {Float}  Number.prototype.round Float type number to round
 * @param  {Number} prec                   Precision to round to
 * @return {Float}                         Rounded number
 */
if (Number.prototype.round) {
	Number.prototype.round = function (prec) {
		return Math.round(this * Math.pow(10, prec)) / Math.pow(10, prec);
	};
}

/**
 * formats flat number 123456 to 123,456
 * @param  {Number} x number to be formated
 * @return {String}   formatted with , in thousands
 */
function numberWithCommas(x) // eslint-disable-line no-unused-vars
{
	var parts = x.toString().split('.');
	parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
	return parts.join('.');
}

/**
 * converts line breaks to br
 * @param  {String} string any string
 * @return {String}        string with <br>
 */
function convertLBtoBR(string) // eslint-disable-line no-unused-vars
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
		return this.replace(/[&<>"'/]/g, function (s) {
			var entityMap = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				'\'': '&#39;',
				'/': '&#x2F;'
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
				'&amp;': '&',
				'&lt;': '<',
				'&gt;': '>',
				'&quot;': '"',
				'&#39;': '\'',
				'&#x2F;': '/'
			};

			return entityMap[s];
		});
	};
}

/**
 * returns current timestamp (unix timestamp)
 * @return {Number} timestamp (in milliseconds)
 */
function getTimestamp() // eslint-disable-line no-unused-vars
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
function generateId(len) // eslint-disable-line no-unused-vars
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
function randomIdF() // eslint-disable-line no-unused-vars
{
	return Math.random().toString(36).substring(2);
}

/**
 * generate a number between min/max
 * with min/max inclusive.
 * eg: 1,5 will create a number ranging from 1 o 5
 * @param  {Number} min minimum int number inclusive
 * @param  {Number} max maximumg int number inclusive
 * @return {Number}     Random number
 */
function getRandomIntInclusive(min, max) // eslint-disable-line no-unused-vars
{
	min = Math.ceil(min);
	max = Math.floor(max);
	// The maximum is inclusive and the minimum is inclusive
	return Math.floor(Math.random() * (max - min + 1) + min);
}

/**
 * check if name is a function
 * @param  {string}  name Name of function to check if exists
 * @return {Boolean}      true/false
 */
function isFunction(name) // eslint-disable-line no-unused-vars
{
	if (typeof window[name] !== 'undefined' &&
		typeof window[name] === 'function') {
		return true;
	} else {
		return false;
	}
}

/**
 * call a function by its string name
 * https://stackoverflow.com/a/359910
 * example: executeFunctionByName("My.Namespace.functionName", window, arguments);
 * @param  {string} functionName The function name or namespace + function
 * @param  {mixed}  context      context (window or first namespace)
 *                               hidden next are all the arguments
 * @return {mixed}               Return values from functon
 */
function executeFunctionByName(functionName, context /*, args */) // eslint-disable-line no-unused-vars
{
	var args = Array.prototype.slice.call(arguments, 2);
	var namespaces = functionName.split('.');
	var func = namespaces.pop();
	for (var i = 0; i < namespaces.length; i++) {
		context = context[namespaces[i]];
	}
	return context[func].apply(context, args);
}

/**
 * checks if a variable is an object
 * @param  {Mixed}   val possible object
 * @return {Boolean}     true/false if it is an object or not
 */
function isObject(val)
{
	if (val === null) {
		return false;
	}
	return ((typeof val === 'function') || (typeof val === 'object'));
}

/**
 * get the length of an object (entries)
 * @param  {Object} object object to check
 * @return {Number} number of entry
 */
function getObjectCount(object)
{
	return Object.keys(object).length;
}

/**
 * checks if a key exists in a given object
 * @param  {String}  key    key name
 * @param  {Object}  object object to search key in
 * @return {Boolean}        true/false if key exists in object
 */
function keyInObject(key, object)
{
	return Object.prototype.hasOwnProperty.call(object, key) ? true : false;
}

/**
 * returns matching key of value
 * @param  {Object} obj   object to search value in
 * @param  {Mixed}  value any value (String, Number, etc)
 * @return {String}       the key found for the first matching value
 */
function getKeyByValue(object, value) // eslint-disable-line no-unused-vars
{
	return Object.keys(object).find(key => object[key] === value);
	// return Object.keys(object).find(function (key) {
	// 	return object[key] === value;
	// });
}

/**
 * returns true if value is found in object with a key
 * @param  {Object}  obj   object to search value in
 * @param  {Mixed}   value any value (String, Number, etc)
 * @return {Boolean}       true on value found, false on not found
 */
function valueInObject(object, value) // eslint-disable-line no-unused-vars
{
	return Object.keys(object).find(key => object[key] === value) ? true : false;
	// return Object.keys(object).find(function (key) {
	// 	return object[key] === value;
	// }) ? true : false;
}

/**
 * true deep copy for Javascript objects
 * if Object.assign({}, obj) is not working (shallow)
 * or if JSON.parse(JSON.stringify(obj)) is failing
 * @param  {Object} inObject Object to copy
 * @return {Object}          Copied Object
 */
function deepCopyFunction(inObject)
{
	var outObject, value, key;
	if (typeof inObject !== 'object' || inObject === null) {
		return inObject; // Return the value if inObject is not an object
	}
	// Create an array or object to hold the values
	outObject = Array.isArray(inObject) ? [] : {};
	// loop over ech entry in object
	for (key in inObject) {
		value = inObject[key];
		// Recursively (deep) copy for nested objects, including arrays
		outObject[key] = deepCopyFunction(value);
	}

	return outObject;
}

/**
 * checks if a DOM element actually exists
 * @param  {String}  id Element id to check for
 * @return {Boolean}    true if element exists, false on failure
 */
function exists(id)
{
	return $('#' + id).length > 0 ? true : false;
}

/**
 * converts a int number into bytes with prefix in two decimals precision
 * currently precision is fixed, if dynamic needs check for max/min precision
 * @param  {Number} bytes bytes in int
 * @return {String}       string in GB/MB/KB
 */
function formatBytes(bytes) // eslint-disable-line no-unused-vars
{
	var i = -1;
	do {
		bytes = bytes / 1024;
		i++;
	} while (bytes > 99);
	return parseFloat(Math.round(bytes * Math.pow(10, 2)) / Math.pow(10, 2)) +
		['kB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];
}

/**
 * like formatBytes, but returns bytes for <1KB and not 0.n KB
 * @param  {Number} bytes bytes in int
 * @return {String}       string in GB/MB/KB
 */
function formatBytesLong(bytes) // eslint-disable-line no-unused-vars
{
	var i = Math.floor(Math.log(bytes) / Math.log(1024));
	var sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
	return (bytes / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + sizes[i];
}

/**
 * Convert a string with B/K/M/etc into a byte number
 * @param  {String|Number} bytes Any string with B/K/M/etc
 * @return {String|Number}       A byte number, or original string as is
 */
function stringByteFormat(bytes) // eslint-disable-line no-unused-vars
{
	// if anything not string return
	if (!(typeof bytes === 'string' || bytes instanceof String)) {
		return bytes;
	}
	// for pow exponent list
	let valid_units = 'bkmgtpezy';
	// valid string that can be converted
	let regex = /([\d.,]*)\s?(eb|pb|tb|gb|mb|kb|e|p|t|g|m|k|b)$/i;
	let matches = bytes.match(regex);
	// if nothing found, return original input
	if (matches !== null) {
		// remove all non valid entries outside numbers and .
		// convert to float number
		let m1 = parseFloat(matches[1].replace(/[^0-9.]/,''));
		// only get the FIRST letter from the size, convert it to lower case
		let m2 = matches[2].replace(/[^bkmgtpezy]/i, '').charAt(0).toLowerCase();
		if (m2) {
			// use the position in the valid unit list to do the math conversion
			bytes = m1 * Math.pow(1024, valid_units.indexOf(m2));
		}
	}
	return bytes;
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

/*************************************************************
 * OLD action indicator and overlay boxes calls
 * DO NOT USE
 * actionIndicator -> showActionIndicator
 * actionIndicator -> hideActionIndicator
 * actionIndicatorShow -> showActionIndicator
 * actionIndicatorHide -> hideActionIndicator
 * overlayBoxShow -> showOverlayBoxLayers
 * overlayBoxHide -> hideOverlayBoxLayers
 * setOverlayBox -> showOverlayBoxLayers
 * hideOverlayBox -> hideOverlayBoxLayers
 * ClearCall -> ClearCallActionBox
 * ***********************************************************/

/**
 * show or hide the "do" overlay
 * @param {String}  loc            location name for action indicator
 *                                 default empty. for console.log
 * @param {Boolean} [overlay=true] override the auto hide/show over the overlay div block
 */
function actionIndicator(loc, overlay = true) // eslint-disable-line no-unused-vars
{
	if ($('#indicator').is(':visible')) {
		actionIndicatorHide(loc, overlay);
	} else {
		actionIndicatorShow(loc, overlay);
	}
}

/**
 * explicit show for action Indicator
 * instead of automatically show or hide, do on command show
 * @param {String}  loc            location name for action indicator
 *                                 default empty. for console.log
 * @param {Boolean} [overlay=true] override the auto hide/show over the overlay div block
 */
function actionIndicatorShow(loc, overlay = true)
{
	// console.log('Indicator: SHOW [%s]', loc);
	if (!$('#indicator').is(':visible')) {
		if (!$('#indicator').hasClass('progress')) {
			$('#indicator').addClass('progress');
		}
		setCenter('indicator', true, true);
		$('#indicator').show();
	}
	if (overlay === true) {
		overlayBoxShow();
	}
}

/**
 * explicit hide for action Indicator
 * instead of automatically show or hide, do on command hide
 * @param {String}  loc            location name for action indicator
 *                                 default empty. for console.log
 * @param {Boolean} [overlay=true] override the auto hide/show over the overlay div block
 */
function actionIndicatorHide(loc, overlay = true)
{
	// console.log('Indicator: HIDE [%s]', loc);
	$('#indicator').hide();
	if (overlay === true) {
		overlayBoxHide();
	}
}

/**
 * shows the overlay box or if already visible, bumps the zIndex to 100
 */
function overlayBoxShow()
{
	// check if overlay box exists and if yes set the z-index to 100
	if ($('#overlayBox').is(':visible')) {
		$('#overlayBox').css('zIndex', '100');
	} else {
		$('#overlayBox').show();
		$('#overlayBox').css('zIndex', '98');
	}
}

/**
 * hides the overlay box or if zIndex is 100 bumps it down to previous level
 */
function overlayBoxHide()
{
	// if the overlay box z-index is 100, do no hide, but set to 98
	if ($('#overlayBox').css('zIndex') >= 100) {
		$('#overlayBox').css('zIndex', '98');
	} else {
		$('#overlayBox').hide();
	}
}

/**
 * position the overlay block box and shows it
 */
function setOverlayBox() // eslint-disable-line no-unused-vars
{
	if (!$('#overlayBox').is(':visible')) {
		$('#overlayBox').show();
	}
}

/**
 * opposite of set, always hides overlay box
 */
function hideOverlayBox() // eslint-disable-line no-unused-vars
{
	if ($('#overlayBox').is(':visible')) {
		$('#overlayBox').hide();
	}
}

/**
 * the abort call, clears the action box and hides it and the overlay box
 */
function ClearCall() // eslint-disable-line no-unused-vars
{
	$('#actionBox').html('');
	$('#actionBox').hide();
	$('#overlayBox').hide();
}

/*************************************************************
 * NEW action indicator and overlay box calls
 * USE THIS
 * ***********************************************************/

/**
 * show action indicator
 * - checks if not existing and add
 * - only shows if not visible (else ignore)
 * - overlaybox check is called and shown on a fixzed
 *   zIndex of 1000
 * - indicator is page centered
 * @param {String} loc ID string, only used for console log
 */
function showActionIndicator(loc) // eslint-disable-line no-unused-vars
{
	// console.log('Indicator: SHOW [%s]', loc);
	// check if indicator element exists
	if ($('#indicator').length == 0) {
		var el = document.createElement('div');
		el.className = 'progress hide';
		el.id = 'indicator';
		$('body').append(el);
	} else if (!$('#indicator').hasClass('progress')) {
		// if I add a class it will not be hidden anymore
		// hide it
		$('#indicator').addClass('progress').hide();
	}
	// indicator not visible
	if (!$('#indicator').is(':visible')) {
		// check if overlay box element exits
		checkOverlayExists();
		// if not visible show
		if (!$('#overlayBox').is(':visible')) {
			$('#overlayBox').show();
		}
		// always set to 1000 zIndex to be top
		$('#overlayBox').css('zIndex', 1000);
		// show indicator
		$('#indicator').show();
		// center it
		setCenter('indicator', true, true);
	}
}

/**
 * hide action indicator, if it is visiable
 * If the global variable GL_OB_S is > GL_OB_BASE then
 * the overlayBox is not hidden but the zIndex
 * is set to this value
 * @param {String} loc ID string, only used for console log
 */
function hideActionIndicator(loc) // eslint-disable-line no-unused-vars
{
	// console.log('Indicator: HIDE [%s]', loc);
	// check if indicator is visible
	if ($('#indicator').is(':visible')) {
		// hide indicator
		$('#indicator').hide();
		// if global overlay box count is > 0
		// then set it to this level and keep
		if (GL_OB_S > GL_OB_BASE) {
			$('#overlayBox').css('zIndex', GL_OB_S);
		} else {
			// else hide overlay box and set zIndex to 0
			$('#overlayBox').hide();
			$('#overlayBox').css('zIndex', GL_OB_BASE);
		}
	}
}

/**
 * checks if overlayBox exists, if not it is
 * added as hidden item at the body end
 */
function checkOverlayExists()
{
	// check if overlay box exists, if not create it
	if ($('#overlayBox').length == 0) {
		var el = document.createElement('div');
		el.className = 'overlayBoxElement hide';
		el.id = 'overlayBox';
		$('body').append(el);
	}
}

/**
 * show overlay box
 * if not visible show and set zIndex to 10 (GL_OB_BASE)
 * if visible, add +1 to the GL_OB_S variable and
 * up zIndex by this value
 */
function showOverlayBoxLayers(el_id) // eslint-disable-line no-unused-vars
{
	// console.log('SHOW overlaybox: %s', GL_OB_S);
	// if overlay box is not visible show and set zIndex to 0
	if (!$('#overlayBox').is(':visible')) {
		$('#overlayBox').show();
		$('#overlayBox').css('zIndex', GL_OB_BASE);
		// also set start variable to 0
		GL_OB_S = GL_OB_BASE;
	}
	// up the overlay box counter by 1
	GL_OB_S ++;
	// set zIndex
	$('#overlayBox').css('zIndex', GL_OB_S);
	// if element given raise zIndex and show
	if (el_id) {
		if ($('#' + el_id).length > 0) {
			$('#' + el_id).css('zIndex', GL_OB_S + 1);
			$('#' + el_id).show();
		}
	}
	// console.log('SHOW overlaybox NEW zIndex: %s', $('#overlayBox').css('zIndex'));
}

/**
 * hide overlay box
 * lower GL_OB_S value by -1
 * if we are 10 (GL_OB_BASE) or below hide the overlayIndex
 * and set zIndex and GL_OB_S to 0
 * else just set zIndex to the new GL_OB_S value
 * @param {String} el_id Target to hide layer
 */
function hideOverlayBoxLayers(el_id)
{
	// console.log('HIDE overlaybox: %s', GL_OB_S);
	// remove on layer
	GL_OB_S --;
	// if 0 or lower (overflow) hide it and
	// set zIndex to 0
	if (GL_OB_S <= GL_OB_BASE) {
		GL_OB_S = GL_OB_BASE;
		$('#overlayBox').hide();
		$('#overlayBox').css('zIndex', GL_OB_BASE);
	} else {
		// if OB_S > 0 then set new zIndex
		$('#overlayBox').css('zIndex', GL_OB_S);
	}
	if (el_id) {
		$('#' + el_id).hide();
		$('#' + el_id).css('zIndex', 0);
	}
	// console.log('HIDE overlaybox NEW zIndex: %s', $('#overlayBox').css('zIndex'));
}

/**
 * only for single action box
 */
function clearCallActionBox() // eslint-disable-line no-unused-vars
{
	$('#actionBox').html('');
	$('#actionBox').hide();
	hideOverlayBoxLayers();
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
function cel(tag, id = '', content = '', css = [], options = {})
{
	return {
		tag: tag,
		id: id,
		name: options.name, // override name if set [name gets ignored in tree build anyway]
		content: content,
		css: css,
		options: options,
		sub: []
	};
}

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
			// base.sub.push(Object.assign({}, attach));
			base.sub.push(deepCopyFunction(attach));
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
		// base.sub.push(Object.assign({}, attach));
		base.sub.push(deepCopyFunction(attach));
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
	for (var i = 0; i < attach.length; i ++) {
		// base.sub.push(Object.assign({}, attach[i]));
		base.sub.push(deepCopyFunction(attach[i]));
	}
	return base;
}

/**
 * same as aelx, but instead of using objects as parameters
 * get an array of objects to attach
 * @param  {Object} base   object to where we attach the elements
 * @param  {Array}  attach array of objects to attach
 * @return {Object}        "none", technically there is no return needed, global attach
 */
function aelxar(base, attach) // eslint-disable-line no-unused-vars
{
	for (var i = 0; i < attach.length; i ++) {
		// base.sub.push(Object.assign({}, attach[i]));
		base.sub.push(deepCopyFunction(attach[i]));
	}
	return base;
}

/**
 * resets the sub elements of the base element given
 * @param  {Object} base cel created element
 * @return {Object}      returns reset base element
 */
function rel(base) // eslint-disable-line no-unused-vars
{
	base.sub = [];
	return base;
}

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
function scssel(_element, rcss, acss) // eslint-disable-line no-unused-vars
{
	rcssel(_element, rcss);
	acssel(_element, acss);
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

/**
 * Create HTML elements from array list
 * as a flat element without master object file
 * Is like tree.sub call
 * @param  {Array}  list Array of cel created objects
 * @return {String}      HTML String
 */
function phfa(list) // eslint-disable-line no-unused-vars
{
	var content = [];
	for (var i = 0; i < list.length; i ++) {
		content.push(phfo(list[i]));
	}
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
function html_options(name, data, selected = '', options_only = false, return_string = false, sort = '') // eslint-disable-line no-unused-vars
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
 * @param  {String}  [onchange='']         onchange trigger call, default unset
 * @return {String}                        html with build options block
 */
function html_options_block(name, data, selected = '', multiple = 0, options_only = false, return_string = false, sort = '', onchange = '')
{
	var content = [];
	var element_select;
	var select_options = {};
	var element_option;
	var data_list = []; // for sorted output
	var value;
	var options = {};
	// var option;
	if (multiple > 0) {
		select_options.multiple = '';
		if (multiple > 1) {
			select_options.size = multiple;
		}
	}
	if (onchange) {
		select_options.OnChange = onchange;
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
			for (var i = 0; i < element_select.sub.length; i ++) {
				content.push(phfo(element_select.sub[i]));
			}
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
function html_options_refill(name, data, sort = '') // eslint-disable-line no-unused-vars
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
			if (key == option_selected) {
				element_option.selected = true;
			}
			document.getElementById(name).appendChild(element_option);
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
function parseQueryString(query = '', return_key = '') // eslint-disable-line no-unused-vars
{
	if (!query) {
		query = window.location.search.substring(1);
	}
	var vars = query.split('&');
	var query_string = {};
	for (var i = 0; i < vars.length; i++) {
		var pair = vars[i].split('=');
		var key = decodeURIComponent(pair[0]);
		var value = decodeURIComponent(pair[1]);
		// skip over run if there is nothing
		if (!key || value === 'undefined') {
			continue;
		}
		// If first entry with this name
		if (typeof query_string[key] === 'undefined') {
			query_string[key] = decodeURIComponent(value);
			// If second entry with this name
		} else if (typeof query_string[key] === 'string') {
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
 * searches query parameters for entry and returns data either as string or array
 * if no search is given the whole parameters are returned as an object
 * if a parameter is set several times it will be returned as an array
 * if search parameter set and nothing found and empty string is returned
 * if no parametes exist and no serach is set and empty object is returned
 * @param  {String}        [search='']    if set searches for this entry, if empty
 *                                        all parameters are returned
 * @param  {String}        [query='']     different query string to parse, if not
 *                                        set (default) the current window href is used
 * @param  {Bool}          [single=false] if set to true then only the first found
 *                                        will be returned
 * @return {Object|Array|String}          if search is empty, object, if search is set
 *                                        and only one entry, then string, else array
 *                                        unless single is true
 */
function getQueryStringParam(search = '', query = '', single = false) // eslint-disable-line no-unused-vars
{
	if (!query) {
		query = window.location.href;
	}
	const url = new URL(query);
	let param = '';
	if (search) {
		let _params = url.searchParams.getAll(search);
		if (_params.length == 1 || single === true) {
			param = _params[0];
		} else if (_params.length > 1) {
			param = _params;
		}
	} else {
		// will be object, so declare it one
		param = {};
		// loop over paramenters
		for (const [key] of url.searchParams.entries()) {
			// check if not yet set
			if (typeof param[key] === 'undefined') {
				// get the parameters multiple
				let _params = url.searchParams.getAll(key);
				// if 1 set as string, else attach array as is
				param[key] = _params.length < 2 || single === true ?
					_params[0] :
					_params;
			}
		}
	}
	return param;
}

// *** MASTER logout call
/**
 * submits basic data for form logout
 */
function loginLogout() // eslint-disable-line no-unused-vars
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

/**
 * create login string and logout button elements
 * @param {String} login_string             the login string to show on the left
 * @param {String} [header_id='mainHeader'] the target for the main element block
 *                                          if not set mainHeader is assumed
 *                                          this is the target div for the "loginRow"
 */
function createLoginRow(login_string, header_id = 'mainHeader') // eslint-disable-line no-unused-vars
{
	// if header does not exist, we do nothing
	if (exists(header_id)) {
		// that row must exist already, if not it must be the first in the "mainHeader"
		if (!exists('loginRow')) {
			$('#' + header_id).html(phfo(cel('div', 'loginRow', '', ['loginRow', 'flx-spbt'])));
		}
		// clear out just in case for first entry
		// fill with div name & login/logout button
		$('#loginRow').html(phfo(cel('div', 'loginRow-name', login_string)));
		$('#loginRow').append(phfo(cel('div', 'loginRow-info', '')));
		$('#loginRow').append(phfo(
			aelx(
				// outer div
				cel('div', 'loginRow-logout'),
				// inner element
				cel('input', 'logout', '', [], {
					value: __('Logout'),
					type: 'button',
					onClick: 'loginLogout()'
				})
			)
		));
	}
}

/**
 * create the top nav menu that switches physical between pages
 * (edit access data based)
 * @param {Object} nav_menu                 the built nav menu with highlight info
 * @param {String} [header_id='mainHeader'] the target for the main element block
 *                                          if not set mainHeader is assumed
 *                                          this is the target div for the "menuRow"
 */
function createNavMenu(nav_menu, header_id = 'mainHeader') // eslint-disable-line no-unused-vars
{
	// must be an object
	if (isObject(nav_menu) && getObjectCount(nav_menu) > 1) {
		// do we have more than one entry, if not, do not show (single page)
		if (!exists('menuRow')) {
			$('#' + header_id).html(phfo(cel('div', 'menuRow', '', ['menuRow', 'flx-s'])));
		}
		var content = [];
		$.each(nav_menu, function(key, item) {
			// key is number
			// item is object with entries
			if (key != 0) {
				content.push(phfo(cel('div', '', '&middot;', ['pd-2'])));
			}
			// ignore item.popup for now
			if (item.enabled) {
				// set selected based on window.location.href as the php set will not work
				if (window.location.href.indexOf(item.url) != -1) {
					item.selected = 1;
				}
				// create the entry
				content.push(phfo(
					aelx(
						cel('div'),
						cel('a', '', item.name, ['pd-2'].concat(item.selected ? 'highlight': ''), {
							href: item.url
						})
					)
				));
			}
		});
		$('#menuRow').html(content.join(''));
	} else {
		$('#menuRow').hide();
	}
}

/* END */
