<?php
$file = 'vendor/robrichards/xmlseclibs/src/XMLSecurityDSig.php';
$lines = file($file);
for ($i = 1090; $i < 1150; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}
