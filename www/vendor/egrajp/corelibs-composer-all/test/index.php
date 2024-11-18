<?php

require "../vendor/autoload.php";

print "Bytes: " . CoreLibs\Convert\Byte::humanReadableByteFormat(123414) . "<br>";

$curl = new CoreLibs\UrlRequests\Curl();
print "Config: " . print_r($curl->getConfig(), true) . "<br>";

// __END__
