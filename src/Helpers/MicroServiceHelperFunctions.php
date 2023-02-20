<?php

use Illuminate\Support\Str;

/**
 * Get class name of passed string with options.
 *
 * @param  string  $class
 * @param  bool  $withNamespace (optional)
 * @param  string  $namespace (optional namespace default to models namespace)
 * @param  string  $trailing (optional trailing class namespace string)
 * @return string class name with/without namespace
 */
if (! function_exists('class_name_of')) {
    function class_name_of
    (
        string $class,
        bool $withNamespace = false,
        string $namespace = 'Solutionplus\MicroService\Models\\',
        $trailing = ''
    ) {
        $class = Str::camel(str_replace('-', '_', $class));
        $class = Str::singular(ucfirst($class));
        $trailing = ucfirst($trailing);
        return (bool) $withNamespace ? $namespace . $class . $trailing : $class . $trailing;
    }
}

if (! function_exists('generate_serial')) {
    function generate_serial($length = 10, $intOnly = false, $prefix = null) {
        $characters = $intOnly ? '0123456789' : '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $serial = '';
        for ($i = 0; $i < $length; $i++) {
            $serial .= $characters[rand(0, $charactersLength - 1)];
        }
        return "{$prefix}{$serial}";
    }
}

if (! function_exists('pagination_length')) {
    function pagination_length($modelName, $length = 20) {
        $paginationLength = request()->pagination;
        $model = class_name_of($modelName, ! \str_contains($modelName, "\\"));
        if ($paginationLength == 'all') {
            $modelRaws = $model::count();
            return $modelRaws <= 500 ? $modelRaws : 500;
        }
        if (is_numeric($paginationLength)) {
            return $paginationLength;
        }
        return (int) $length;
    }
}
