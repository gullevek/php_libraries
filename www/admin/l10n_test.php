<?php declare(strict_types=1);

// debug for L10n issues in php 7.3

// namespace test
ob_start();

// init language
$lang = 'en_utf8';
// admin class tests
require 'config.php';
$l = new CoreLibs\Language\L10n($lang);
ob_end_flush();

$string = 'INPUT TEST';

echo "LANGUAGE SET: ".$l->__getLang()."<br>";
echo "LANGUAGE FILE: ".$l->__getMoFile()."<br>";
echo "INPUT TEST: ".$string." => ".$l->__($string)."<br>";

// switch to other language
$lang = 'ja_utf8';
$l->l10nReloadMOfile($lang);

echo "LANGUAGE SET: ".$l->__getLang()."<br>";
echo "LANGUAGE FILE: ".$l->__getMoFile()."<br>";
echo "INPUT TEST: ".$string." => ".$l->__($string)."<br>";

// __END__
