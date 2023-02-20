<?php

if (! function_exists('generate_local_secret')) {
    function generate_local_secret($length = 10, $intOnly = false, $prefix = null) {
        $characters = $intOnly ? '0123456789' : '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $serial = '';
        for ($i = 0; $i < $length; $i++) {
            $serial .= $characters[rand(0, $charactersLength - 1)];
        }
        return "{$prefix}{$serial}";
    }
}
