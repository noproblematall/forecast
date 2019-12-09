<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'Location'; // table name

    protected $fillable = [ // table field
        'locationId', 'name'
    ];

    /**
     * Get row of measure table
     * 
     */

    public function measures()
    {
        return $this->hasMany('App\Measure', 'locationId', 'locationId');
    }
}
