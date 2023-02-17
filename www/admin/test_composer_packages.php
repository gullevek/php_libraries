<?php

// Pure test for composer packages

require "../vendor/autoload.php";

$status = CoreLibs\Get\DotEnv::readEnvFile('.', 'test.env');

print "S: " . $status . ", ENV: <pre>" . print_r($_ENV, true) . "</pre><br>";

print "Bytes: " . CoreLibs\Convert\Byte::humanReadableByteFormat(123414) . "<br>";

// __END__
