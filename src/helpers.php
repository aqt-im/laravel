<?php

use AqtIm\Laravel\Aqtim;

if (! function_exists('aqtim')) {
    function aqtim(): Aqtim
    {
        return app(Aqtim::class);
    }
}
