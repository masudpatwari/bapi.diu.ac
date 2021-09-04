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

class O_GRADE_POINT_SYSTEM_DETAIL extends Eloquent
{
    public $timestamps = false;
    protected $table = "GRADE_POINT_SYSTEM_DETAIL";
    protected $connection = 'oracle';

    /*
     * Called grade point system controller
     * Called marksheet parent controller
    */

    public static function grade_point_system_details($gps_id)
    {
        return static::selectRaw('PC_MARK, LETTER, IN_WORD, GRADE_POINT')
            ->where(['GPS_ID' => $gps_id])
            ->orderby('PC_MARK', 'DESC');
    }
}
