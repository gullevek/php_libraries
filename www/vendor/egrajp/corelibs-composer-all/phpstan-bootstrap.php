<?php // phpcs:ignore PSR1.Files.SideEffects

// Boostrap file for PHPstand
// sets the _SERVER['HTTP_HOST'] var so we can have DB detection
$_SERVER['HTTP_HOST'] = 'soba.tokyo.tequila.jp';
// so www/includes/edit_base.php works
// require_once('www/lib/Smarty/SmartyBC.class.php');
// for whatever reason it does not load that from the confing.master.php
// for includes/admin_header.php
define('BASE_NAME', '');
define('CONTENT_PATH', '');

// __END__
