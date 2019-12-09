<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cardinality extends Model
{
    protected $table = 'Cardinality';  // table name

    protected $fillable = [   // table fields
        'cardinalityId', 'name'
    ];
}
