<?php

// debug for L10n issues in php 7.3

// namespace test
ob_start();

$lang = 'en_utf8';

// admin class tests
require 'config.php';
$l = new CoreLibs\Language\L10n($lang);

echo "OK<br>";

ob_end_flush();
// __END__
