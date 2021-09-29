<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInterface extends Model
{
    protected $fillable = [
        'name', 'title'
    ];
    
    public $timestamps = true;

}
