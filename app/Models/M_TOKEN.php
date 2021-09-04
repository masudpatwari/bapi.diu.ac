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

class M_TOKEN extends Model
{
    public $timestamps = false;
    protected $table = "token";
    protected $connection = 'mysql';
    protected $fillable = ['email', 'code', 'expirationtime'];

    /*
     * Call to authentication controller
    */
    public static function check_token($code)
    {
        return static::where(['email' => session('user.email'), 'code' => $code]);
    }

    /*
     * Call to authentication controller
    */
    public static function delete_token($email_address)
    {
        return static::where(['email' => $email_address]);
    }
}
