<?php

namespace App\Models;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;
use Illuminate\Support\Facades\DB;

class O_IMP_MARKS_DRAFT extends Eloquent
{
	public $timestamps = false;
    protected $table = "IMP_MARKS_DRAFT";
    protected $connection = 'oracle';
}
