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

use Illuminate\Database\Eloquent\Model;

class M_RMS_ROLES extends Model
{
    public $timestamps = false;
    protected $table = "rms_roles";
    protected $connection = 'mysql';
    protected $fillable = ['role_name', 'is_deleted'];

}

