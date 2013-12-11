/*
 * $HeadURL: svn://svn/development/core_data/php/www/layout/frontend/default/javascript/frontend.js $
 * $LastChangedBy: gullevek $
 * $LastChangedDate: 2010-09-02 11:58:10 +0900 (Thu, 02 Sep 2010) $
 * $LastChangedRevision: 3159 $
 *
 * AUTHOR: Clemens Schwaighofer
 * DATE: 2008/12/29
 * DESC: Javascript functions for the Catalogue frontend
 *
 */

// METHOD: SwitchImage
// PARAMS: front/back -> what image to show
// RETURN: none
// DESC:   ajax call to switch the main image in the detail view
function SwitchImage(image)
{
	if (image != 'front' || image != 'back')
		image = 'front';
	// disable / enable the href for the other side
	x_ajax_afSwitchImage(image, OutputSwitchImage);
}

// METHOD: OutputSwitchImage
// PARAMS: data -> the image full path for the new image
// RETURN: none
// DESC:   replace the image in the product detail with the back image
function OutputSwitchImage(data)
{

}

/* $Id: frontend.js 3159 2010-09-02 02:58:10Z gullevek $ */
