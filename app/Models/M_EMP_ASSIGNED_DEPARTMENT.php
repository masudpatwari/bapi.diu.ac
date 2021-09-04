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

class M_EMP_ASSIGNED_DEPARTMENT extends Model
{
    public $timestamps = false;
    protected $table = "rms_emp_assigned_department";
    protected $connection = 'mysql';
    protected $fillable = ['dept_id', 'emp_id'];

    /*
     * Call to assignment department
     */
    public static function emp_assigned_department($employee_id)
    {
        return static::selectRaw('dept_id')
            ->where(['is_deleted' => 0, 'emp_id' => $employee_id]);
    }

    public function department()
    {
        return $this->belongsTo(O_DEPARTMENTS::class, 'dept_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(M_WP_EMP::class, 'emp_id', 'id');
    }
}
