<?php

namespace App\Models;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class O_SHIFT extends Eloquent
{
    public $timestamps = false;
    protected $table = "SHIFT";
    protected $connection = 'oracle';
}
