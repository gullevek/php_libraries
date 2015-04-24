/*
	********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* DATE: 2015/4/24
	* DESCRIPTION:
	* debug javascript
	* HISTORY:
	********************************************************************
*/

// if debug is set to true, console log messages are printed
if (!DEBUG)
{
	$($H(window.console)).each(function(w) {
		window.console[w.key] = function() {}
	});
}
