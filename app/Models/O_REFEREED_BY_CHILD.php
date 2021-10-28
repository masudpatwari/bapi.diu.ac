<?php

namespace App\Models;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class O_REFEREED_BY_CHILD extends Eloquent
{
    public $timestamps = false;
    protected $table = "REFEREED_BY_CHILD";
    protected $connection = 'oracle';
}
