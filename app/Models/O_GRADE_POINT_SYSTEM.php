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

class O_GRADE_POINT_SYSTEM extends Eloquent
{
    public $timestamps = false;
    protected $table = "GRADE_POINT_SYSTEM";
    protected $connection = 'oracle';
    protected $fillable = ['session_for', 'meta', 'mark_diff_grade_to_grade', 'fail_mark_bellow', 'creator_id'];
}
