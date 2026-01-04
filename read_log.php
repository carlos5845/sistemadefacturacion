<?php
$file = 'storage/logs/laravel.log';
$lines = file($file);
$last_lines = array_slice($lines, -5);
foreach ($last_lines as $line) {
    echo $line;
}
