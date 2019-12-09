<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'Unit';  // table name

    protected $fillable = [  // table field
        'unitId', 'name'
    ];

    /**
     * Get names of endpoint table
     * 
     */

    public function endpoints()
    {
        return $this->hasMany('App\Endpoint', 'unitId', 'unitId');
    }
}
