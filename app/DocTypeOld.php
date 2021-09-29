<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class DocTypeOld extends Model
{
    use SoftDeletes;

    protected $connection = 'db1';
    protected $table = 'doc_types';
    protected $hasFolders = false;
    protected $tableName1C = 'Catalog_ВидыНоменклатуры';
    protected $dates = ['deleted_at'];
    protected $fillable = ['uuid','name','comment','created_at','created_by','updated_at','updated_by',
        'deleted_by','deleted_at'
    ];
    private $model = [];

    public function getModel() {
        return $this->model;
    }

    public function nomenklatura()
    {
        return $this->hasMany('App\Nomenklatura');
    }

    public function name_1c() {
        return $this->tableName1C;
    }

    public function has_folder() {
        return $this->hasFolders;
    }
}
