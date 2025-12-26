<?php

use Illuminate\Support\Facades\Artisan;

Artisan::call('tinker', [], function ($line) {
    echo $line;
});
