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

class O_VIEW_S_BATCH extends Eloquent
{
    public $timestamps = false;
    protected $table = "VIEW_S_BATCH";
    protected $connection = 'oracle';

    /*
     * Call default controller
    */

    public static function batch( $department_id )
    {
        return static::where(['DEPARTMENT_ID' => $department_id])
            ->orderby('BATCH_NAME', 'ASC');
    }
}
