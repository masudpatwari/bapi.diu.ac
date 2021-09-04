<?php

namespace App\Models;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class O_PAYMENT_SYSTEM extends Eloquent
{
    public $timestamps = false;
    protected $table = "PAYMENT_SYSTEM";
    protected $connection = 'oracle';


    public function payment_system_detail()
    {
        return $this->hasMany(O_PAYMENT_SYSTEM_DETAIL::class, 'paymentsystem_id', 'id');
    }
}

