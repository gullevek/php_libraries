/*
	AUTHOR: Clemens Schwaighofer
	DATE: 2006/09/05
	DESC: edit shop js file
	HISTORY:
*/

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
