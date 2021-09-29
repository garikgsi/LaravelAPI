<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendar extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    // события календаря
    public function events() {
        return $this->hasMany('App\Event');
    }


}
