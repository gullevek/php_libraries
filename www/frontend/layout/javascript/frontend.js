/*
 * AUTHOR: Clemens Schwaighofer
 * DATE: 2008/12/29
 * DESC: Javascript functions for the Catalogue frontend
*/

// METHOD: SwitchImage
// PARAMS: front/back -> what image to show
// RETURN: none
// DESC:   ajax call to switch the main image in the detail view
function SwitchImage(image) {
	if (image != 'front' || image != 'back') {
		image = 'front';
	}
	// disable / enable the href for the other side
	x_ajax_afSwitchImage(image, OutputSwitchImage);
}

// METHOD: OutputSwitchImage
// PARAMS: data -> the image full path for the new image
// RETURN: none
// DESC:   replace the image in the product detail with the back image
function OutputSwitchImage(data) {

}
