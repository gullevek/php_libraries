/*
	code is taken and adapted from dokuwiki
*/

/**
 * Some browser detection
 */
var clientPC  = navigator.userAgent.toLowerCase(); // Get client info
var is_gecko  = ((clientPC.indexOf('gecko') != -1) && (clientPC.indexOf('spoofer') == -1)
				&& (clientPC.indexOf('khtml') == -1) && (clientPC.indexOf('netscape/7.0') == -1));
var is_safari = ((clientPC.indexOf('AppleWebKit') != -1) && (clientPC.indexOf('spoofer') == -1));
var is_khtml  = (navigator.vendor == 'KDE' || ( document.childNodes && !document.all && !navigator.taintEnabled ));
if (clientPC.indexOf('opera') != -1)
{
	var is_opera = true;
	var is_opera_preseven = (window.opera && !document.childNodes);
	var is_opera_seven = (window.opera && document.childNodes);
}

function pop(theURL, winName, features)
{
	winName = window.open(theURL, winName, features);
	winName.focus();
}

function emd_check_checkbox()
{
	for (i = 0; i < document.manage_emails.length; i ++)
	{
		if (document.manage_emails.elements[i].checked == false && document.manage_emails.elements[i].type == 'checkbox')
		{
			document.manage_emails.elements[i].checked = true;
		}
	}
}

function expandTA(ta_id)
{
	var ta;
	// if a string comes, its a get by id, else use it as an element pass on
	if (!ta_id.length)
		ta = ta_id;
	else
		ta = document.getElementById(ta_id);
	var maxChars = ta.cols;
	var theRows = ta.value.split("\n");
	var numNewRows = 0;

	for ( var i = 0; i < theRows.length; i++ )
	{
		if ((theRows[i].length+2) > maxChars)
		{
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
	if (status == 'show')
	{
		document.getElementById(id).style.visibility = 'visible';
		if (document.getElementById('search_results').innerHTML)
			document.getElementById('search_results').style.visibility = 'visible';
	}
	else if (status == 'hide')
	{
		document.getElementById(id).style.visibility = 'hidden';
		if (document.getElementById('search_results').style.visibility == 'visible')
			document.getElementById('search_results').style.visibility = 'hidden';
	}
}

function ShowHideDiv(id)
{
	element = document.getElementById(id);
	if (element.className == 'visible' || !element.className)
		element.className = 'hidden';
	else
		element.className = 'visible';

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
	if (load_id)
		document.forms[form_name].action_yes.value = confirm('Do you want to load this data?');
	else
		document.forms[form_name].action_yes.value = 'true';
	document.forms[form_name].action_id.value = id;
	document.forms[form_name].action_menu.value = id;
	if (document.forms[form_name].action_yes.value == 'true')
		document.forms[form_name].submit();
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
	return {width: width, height: height};
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
	return {left: left, top: top};
}

// METHOD: setCenter
// PARAMS: id to set center
// RETURN: none
// DESC:   centers div to current window size middle
function setCenter(id, left, top)
{
	// get size of id
	var dimensions = $(id).getDimensions();
	var viewport = getWindowSize();
	var offset = getScrollOffset();

	console.log('Id %s, dimensions %s x %s, viewport %s x %s', id, dimensions.width, dimensions.height, viewport.width, viewport.height);
	console.log('Scrolloffset left: %s, top: %s', offset.left, offset.top);
	console.log('Left: %s, Top: %s', parseInt((viewport.width / 2) - (dimensions.width / 2)), parseInt((viewport.height / 2) - (dimensions.height / 2)));
	if (left)
	{
		$(id).setStyle ({
			left: parseInt((viewport.width / 2) - (dimensions.width / 2) + offset.left) + 'px'
		});
	}
	if (top)
	{
		$(id).setStyle ({
			top: parseInt((viewport.height / 2) - (dimensions.height / 2) + offset.top) + 'px'
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
	if (divStatus)
	{
		// hide the element
		Effect.BlindUp(id, {duration:0.3});
		$(flag).value = 0;
		$(btn).innerHTML = showText;
	}
	else if (!divStatus)
	{
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
