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

class O_BATCH extends Eloquent
{
    public $timestamps = false;
    protected $table = "S_BATCH";
    protected $connection = 'oracle';
    

    public function students()
    {
        return $this->hasMany(O_STUDENT::class, 'batch_id', 'id');
    }

    public function relDepartment()
    {
        return $this->belongsTo(O_DEPARTMENTS::class, 'department_id', 'id');
    }

    public function relShift()
    {
        return $this->belongsTo(O_SHIFT::class, 'shift_id', 'id');
    }

    //each category might have multiple children
    public function childBatch() {
        return $this->hasMany(static::class, 'parent_batch_id','id');
    }

    public function paymemtSystem()
    {
        return $this->hasOne(O_PAYMENT_SYSTEM::class, 'id', 'payment_system_id');
    }
}
