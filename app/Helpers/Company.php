<?php

use App\Models\CompanySetting;

if (!function_exists('company')) {
    function company()
    {
        return CompanySetting::first();
    }
}