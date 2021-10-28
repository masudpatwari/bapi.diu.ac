<?php

namespace App\Models;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class O_COUNTRY extends Eloquent
{
    public $timestamps = false;
    protected $table = "COUNTRY";
    protected $connection = 'oracle';
}
