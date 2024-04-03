<?php

namespace Solutionplus\MicroService\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

trait HasPackageFactory
{
    use HasFactory;

    protected static function newFactory()
    {
        $packageNamespace = Str::before(get_called_class(), 'Models\\');
        $modelName = Str::after(get_called_class(), 'Models\\');
        $factoryClassFullPath = "{$packageNamespace}Database\\Factories\\{$modelName}Factory";

        return $factoryClassFullPath::new();
    }
}