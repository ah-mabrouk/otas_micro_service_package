<?php

namespace Solutionplus\MicroService\Models;

use Illuminate\Database\Eloquent\Model;
use Solutionplus\MicroService\Traits\HasPackageFactory;
use Solutionplus\MicroService\Traits\HasTimezoneFields;

class MicroServiceMap extends Model
{
    use HasPackageFactory, HasTimezoneFields;

    protected $fillable = [
        'name',
        'display_name',

        'origin',

        'destination_key',

        'created_at',
        'updated_at',
    ];

    ## Relations

    ## Getters & Setters

    ## Query Scope Methods

    ## Other Methods

}
