/*********************************************************************
* AUTHOR: Clemens Schwaighofer
* DATE: 2015/4/24
* DESCRIPTION:
* debug javascript
* HISTORY:
**********************************************************************/

// if debug is set to true, console log messages are printed
if (!DEBUG) {
	for (var prop in window.console) {
		window.console[prop] = function () {};
	}
}
