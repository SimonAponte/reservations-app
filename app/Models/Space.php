<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Space extends Model
{

    protected $fillable = ['name', 'capacity', 'price', 'schedule'];
    
    public function users(){

        return $this->belongsToMany(User::class)->withPivot('reservation_date', 'start_hour', 'end_hour');
    
    }
}
