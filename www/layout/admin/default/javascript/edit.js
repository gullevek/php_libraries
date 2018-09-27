/*
	code is taken and adapted from dokuwiki
*/

/* jshint esversion: 6 */

/**
 * Some browser detection
 */
var clientPC  = navigator.userAgent.toLowerCase(); // Get client info
var is_gecko  = ((clientPC.indexOf('gecko') != -1) && (clientPC.indexOf('spoofer') == -1) &&
					(clientPC.indexOf('khtml') == -1) && (clientPC.indexOf('netscape/7.0') == -1));
var is_safari = ((clientPC.indexOf('AppleWebKit') != -1) && (clientPC.indexOf('spoofer') == -1));
var is_khtml  = (navigator.vendor == 'KDE' || ( document.childNodes && !document.all && !navigator.taintEnabled ));
if (clientPC.indexOf('opera') != -1) {
	var is_opera = true;
	var is_opera_preseven = (window.opera && !document.childNodes);
	var is_opera_seven = (window.opera && document.childNodes);
}

function pop(theURL, winName, features) {
	winName = window.open(theURL, winName, features);
	winName.focus();
}

function emd_check_checkbox() {
	for (i = 0; i < document.manage_emails.length; i ++) {
		if (document.manage_emails.elements[i].checked == false && document.manage_emails.elements[i].type == 'checkbox') {
			document.manage_emails.elements[i].checked = true;
		}
	}
}

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

function ShowHideDiv(id)
{
	element = document.getElementById(id);
	if (element.className == 'visible' || !element.className) {
		element.className = 'hidden';
	} else {
		element.className = 'visible';
	}

//	alert('E: ' + element.className + ' -- ' + element.style.visibility);
}

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

// METHOD: getWindowSize
// PARAMS: none
// RETURN: array with width/height
// DESC:   wrapper to get the real window size for the current browser window
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
// DESC:   wrapper to get the correct scroll offset
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
// DESC:   centers div to current window size middle
function setCenter(id, left, top)
{
	// get size of id
	var dimensions = $(id).getDimensions();
	var type = $(id).getStyle('position');
	var viewport = getWindowSize();
	var offset = getScrollOffset();

	// console.log('Id %s, type: %s, dimensions %s x %s, viewport %s x %s', id, $(id).getStyle('position'), dimensions.width, dimensions.height, viewport.width, viewport.height);
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

// METHOD sh
// PARAMS: id -> element to hide
//         showText -> text for the element if shown
//         hideText -> text for the element if hidden
// RETURN: returns true if hidden, or false if not
// DESC:   hides an element, additional writes 1 (show) or 0 (hide) into <id>Flag field
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

// METHOD: formatBytes
// PARAMS: bytes in int
// RETURN: string in GB/MB/KB
// DESC:   converts a int number into bytes with prefix in two decimals precision
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
	element = {
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
			base.sub.push(attach);
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
		base.sub.push(attach);
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

// METHOD: rel [rese element]
// PARAMS: cel created element
// RETURN: "none", is self change, but returns base.sub
// DESC  : resets the sub elements of the base element given
const rel = (base) => base.sub = [];

// METHOD: rcssel [remove a css from the element]
// PARAMS: element, style sheet to remove
// RETURN: "none", in place because of reference
// DESC  : searches and removes style from css array
function rcssel(element, css)
{
	let css_index = element.css.indexOf(css);
	if (css_index > -1) {
		element.css.splice(css_index, 1);
	}
}

// METHOD acssel [add css element]
// PARAMS: element, style sheet to add
// RETURN: "none", in place add because of reference
// DESC  : adds a new style sheet to the element given
function acssel(element, css)
{
	let css_index = element.css.indexOf(css);
	if (css_index == -1) {
		element.css.push(css);
	}
}

// METHOD: scssel
// PARAMS: element, style to remove, style to add
// RETURN: "none", in place add because of reference
// DESC  : removes one css and adds another
//         is a wrapper around rcssel/acssel
function scssel(element, rcss, acss)
{
	rcssel(element, rcss);
	acssel(element, acss);
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

// BLOCK: html wrappers for quickly creating html data blocks
// METHOD: html_options
// PARAMS: name/id, array for the options, selected item uid
//         options_only: if this is true, it will not print the select part
//         return_string, return as string and not as element
// RETURN: html with build options block
// DESC  : creates an select/options drop down block.
//         the array needs to be key -> value format. key is for the option id and value is for the data output
function html_options(name, data, selected = '', options_only = false, return_string = false)
{
	let content = [];
	let element_select;
	let element_option;
	// set outside select, gets stripped on return if options only is true
	element_select = cel('select', name);
	// console.log('Call for %s, options: %s', name, options_only);
	$H(data).each(function(t) {
		console.log('options: key: %s, value: %s', t.key, t.value);
		// basic options init
		let options = {
			'label': t.value,
			'value': t.key
		};
		// add selected if matching
		if (selected == t.key) {
			options.selected = '';
		}
		// create the element option
		element_option = cel('option', '', t.value, '', options);
		// attach it to the select element
		ael(element_select, element_option);
	});
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

/* END */
