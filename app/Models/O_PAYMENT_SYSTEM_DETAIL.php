<?php

namespace App\Models;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class O_PAYMENT_SYSTEM_DETAIL extends Eloquent
{
    public $timestamps = false;
    protected $table = "PAYMENT_SYSTEM_DETAIL";
    protected $connection = 'oracle';

    const PAYABLE = 'Payable';
    const EXEMPTED = 'Exempted';
    const NON_PAYABLE = 'Non-Payable';

}

