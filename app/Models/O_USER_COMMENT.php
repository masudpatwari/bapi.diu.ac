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

class O_USER_COMMENT extends Eloquent
{
    public $timestamps = false;
    protected $table = "USER_COMMENT";
    protected $connection = 'oracle';
    protected $fillable = ['text', 'datetime', 'course_id', 'sifr_id', 'comment_by', 'comment_type'];

    public static function comments_on_marksheet( $semester_info_table_id, $course_id )
    {
        return static::where(['sifr_id' => $semester_info_table_id, 'course_id' => $course_id, 'comment_type' => 'marksheet'])->orderBy('id', 'asc');
    }

    public static function comments_on_tabulation( $semester_info_table_id )
    {
        return static::where(['sifr_id' => $semester_info_table_id])->orderBy('id', 'asc');
    }

    public function commentBy()
    {
        return $this->belongsTo(M_WP_EMP::class, 'comment_by', 'id');
    }
}

