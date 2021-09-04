<?php

/**
 * Date : 2018-Jun-20;
 * Developer Name : Md. Mesbaul Islam || Arif Bin A. Aziz;
 * Contact : 01738120411;
 * E-mail : rony.max24@gmail.com;
 * Theme Name: Result Management System;
 * Theme URI: N/A;
 * Author: Dhaka International University;
 * Author URI: N/A;
 * Version: 1.1.0
 */

namespace App\Models;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class O_DESIGNATION extends Eloquent
{
    public $timestamps = false;
    protected $table = "EMP_DESIGNATION";
    protected $connection = 'oracle';

    /*
     * Called assign department controller
    */

    public static function departments()
    {
        return static::selectRaw('ID, NAME, SHORT_CODE, FACULTY, DEPARTMENT');
    }
}
