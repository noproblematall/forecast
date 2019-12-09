<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Measure extends Model
{
    protected $table = 'Measure'; // table name

    protected $fillable = [  // table field
        'nameId', 'value', 'locationId', 'forecastDate', 'date', 'localDate'
    ];

    /**
     * Get name of endpoint table
     * 
     */

    public function endpoint()
    {
        return $this->belongsTo('App\Endpoint', 'nameId', 'nameId');
    }

    /**
     * Get name of location table
     * 
     */

    public function location()
    {
        return $this->belongsTo('App\Measure', 'locationId', 'locationId');
    }
}
