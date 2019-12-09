<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Endpoint extends Model
{
    protected $table = 'Endpoint';  //table name

    protected $fillable = [
        'nameId', 'name', 'unitId',  // table fields
    ];

    /**
     * Get name of unit table
     * 
     */

    public function unit()
    {
        return $this->belongsTo('App\Unit', 'unitId', 'unitId');
    }

    /**
     * Get row of measure table
     * 
     */

    public function measures()
    {
        return $this->hasMany('App\Measure', 'nameId', 'nameId');
    }
}
