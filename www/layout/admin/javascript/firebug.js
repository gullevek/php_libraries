/* vim: set fileencoding=utf-8 expandtab! shiftwidth=2 : */
/* modified version of firebugx.js which can cope with
 * firebug 1.2+ and the webkit console */

var ConsoleSetup = function() {
	if (!window.console)
		window.console = {};

	var names = ['log', 'debug', 'info', 'warn', 'error', 'assert', 'dir', 'dirxml', 'group', 'groupEnd', 'time', 'timeEnd', 'count', 'trace', 'profile', 'profileEnd'];

	for (var i = 0; i < names.length; ++i) {
		if (!window.console[names[i]]) {
			window.console[names[i]] = function() {};
		}
	}
}();
