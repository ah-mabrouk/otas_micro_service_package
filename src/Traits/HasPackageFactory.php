<?php

namespace Solutionplus\MicroService\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

trait HasPackageFactory
{
    use HasFactory;

    protected static function newFactory()
    {
        $modelName = Str::after(get_called_class(), 'Models\\');
        $factoryClassFullPath = "Database\\Factories\\{$modelName}Factory";

        return $factoryClassFullPath::new();
    }
}