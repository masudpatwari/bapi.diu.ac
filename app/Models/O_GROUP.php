<?php

namespace App\Models;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class O_GROUP extends Eloquent
{
    public $timestamps = false;
    protected $table = "S_GROUP";
    protected $connection = 'oracle';
}
