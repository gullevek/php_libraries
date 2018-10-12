<?php
$ENABLE_ERROR_HANDLING = 0;
$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;
$LOG_PER_RUN = 1;

define('USE_DATABASE', true);
define('USE_HEADER', true);
require 'config.php';
require BASE.INCLUDES.'admin_header.inc';
$MASTER_TEMPLATE_NAME = 'main_body.tpl';
$TEMPLATE_NAME = 'smarty_test.tpl';
$PAGE_WIDTH = 750;
require BASE.INCLUDES.'admin_set_paths.inc';

// smarty test
$cms->DATA['SMARTY_TEST'] = 'Test Data';

require BASE.INCLUDES.'admin_smarty.inc';
require BASE.INCLUDES.'admin_footer.inc';
