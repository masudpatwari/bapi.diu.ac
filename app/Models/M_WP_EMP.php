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

class M_WP_EMP extends Model
{
    public $timestamps = false;
    protected $table = "wp_emp";
    protected $connection = 'mysql';

    protected $hidden = ['pass'];

    /*
     * Call to authentication controller
    */

    public static function check_user($email_address, $password)
    {
        return static::where(['email1' => $email_address, 'pass' => md5($password)])->first();
    }

    public static function lockout_user_and_deny_ip($user_id)
    {

    }

    public static function employees()
    {
        return static::where(['activestatus' => 1])->orderBy('name', 'ASC');
    }

    public static function teachers()
    {
        return static::selectRaw('wp_emp.id, name, position, dept')->join('sort_position_allocation', 'sort_position_allocation.emp_short_position', '=', 'wp_emp.emp_short_position')->where(['activestatus' => 1, 'sort_position_allocation.emp_type' => 'teacher'])->orderBy('name', 'ASC');
    }

    public static function deptChairman()
    {
        return static::selectRaw('wp_emp.id, name, position, dept')->join('sort_position_allocation', 'sort_position_allocation.emp_short_position', '=', 'wp_emp.emp_short_position')->where(['activestatus' => 1, 'sort_position_allocation.emp_type' => 'dept_chairman'])->orderBy('name', 'ASC');
    }

    public function roles()
    {
        return $this->belongsToMany(M_RMS_ROLES::class, 'rms_emp_roles', 'emp_id', 'role_id');
    }
}
