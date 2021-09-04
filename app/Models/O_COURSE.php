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

class O_COURSE extends Eloquent
{
    public $timestamps = false;
    protected $table = "COURSE";
    protected $connection = 'oracle';
    protected $fillable = ['department_id', 'name', 'code', 'credit', 'course_detail', 'incourse_mark', 'final_mark', 'incourse_pass_mark', 'final_pass_mark', 'improvable_mark', 'total_mark', 'creator_id', 'semester_for', 'course_type'];

    const COURSE_TYPE_NON_THEORY = 0;
    const COURSE_TYPE_THEORY = 1;

    /*
     * Called course controller
    */
    public static function courses()
    {
        return static::selectRaw('ID, NAME, CODE, CREDIT, INCOURSE_PASS_MARK, INCOURSE_MARK, FINAL_PASS_MARK, FINAL_MARK, IMPROVABLE_MARK, COURSE_DETAIL, TOTAL_MARK, SEMESTER_FOR')
            ->where(['DEPARTMENT_ID' => session('user.selected_department.id')])
            ->orderby('NAME', 'ASC');
    }

    public function relImprovementRequest()
    {
        return $this->hasOne(O_IMPROVEMENT_REQUEST::class,'course_id', 'id');
    }

}
