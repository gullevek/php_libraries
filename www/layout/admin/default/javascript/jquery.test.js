/* jquery tests */

/* global setCenter ClearCall overlayBoxShow actionIndicatorShow actionIndicatorHide */
/* eslint no-undef: "error" */

$(document).ready(function() {
	setCenter('test-div', true, true);
	ClearCall();
	overlayBoxShow();
	actionIndicatorShow('testSmarty');
	setTimeout(function() {
		console.log('Waiting dummy ...');
		actionIndicatorHide('testSmarty');
		ClearCall();
	}, 2000);
});
