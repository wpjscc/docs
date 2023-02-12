<?php

Route::group([
    'prefix' => Config::get('cms.backendUri', 'backend') . '/docs',
    'middleware' => 'web'
], function () {
    Route::any('{slug?}', 'Wpjscc\Docs\Controllers\Base@run')->where('slug', '(.*)?');
});
