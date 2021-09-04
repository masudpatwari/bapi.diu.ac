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

class O_MARKS_EDIT_HISTORY extends Eloquent
{
    public $timestamps = false;
    protected $table = "MARKS_EDIT_HISTORY";
    protected $connection = 'oracle';
    protected $fillable = ['marks_id', 'note_no', 'edited_by', 'ip', 'history', 'datetime', 'std_id', 'sifr_id'];


    public function entryBy()
    {
        return $this->belongsTo(M_WP_EMP::class,'creator_id', 'id');
    }
}

