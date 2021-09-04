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

class O_EXAM_TABULATOR extends Eloquent
{
    public $timestamps = false;
    protected $table = "EXAM_TABULATOR";
    protected $connection = 'oracle';

    /*
     * Called semsesters controller
    */

    public static function tabulators($semester_id)
    {
        return static::selectRaw('EMP_ID, SIFR_ID')
            ->where(['SIFR_ID' => $semester_id, 'IS_DELETED' => 0])
            ->with('tabulator')
            ->orderby('ID', 'ASC');
    }

    public static function tabulator_ids($semester_id)
    {
        return static::where(['SIFR_ID' => $semester_id, 'IS_DELETED' => 0])
            ->pluck('emp_id')->toArray();
    }

    /*
     * Called marksheet parent controller
    */
    public static function tabulator_semesters_id( $department_id, $employee_id )
    {
        return static::selectRaw('S_BATCH.BATCH_NAME, SEMESTER_INFO_FOR_RESULT.ID, SEMESTER')->distinct()
            ->join('SEMESTER_INFO_FOR_RESULT', 'SEMESTER_INFO_FOR_RESULT.ID', '=', 'EXAM_TABULATOR.SIFR_ID')
            ->join('S_BATCH', 'S_BATCH.ID', '=', 'SEMESTER_INFO_FOR_RESULT.BATCH_ID')
            ->where(['EMP_ID' => $employee_id, 'SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID' => $department_id, 'SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0, 'EXAM_TABULATOR.IS_DELETED' => 0])
            ->orderby('SEMESTER', 'ASC');
    }

    /*
     * Called marksheet parent controller
    */
    public static function get_all_semesters( $department_id)
    {
        return static::selectRaw('S_BATCH.BATCH_NAME, SEMESTER_INFO_FOR_RESULT.ID, SEMESTER')->distinct()
            ->join('SEMESTER_INFO_FOR_RESULT', 'SEMESTER_INFO_FOR_RESULT.ID', '=', 'EXAM_TABULATOR.SIFR_ID')
            ->join('S_BATCH', 'S_BATCH.ID', '=', 'SEMESTER_INFO_FOR_RESULT.BATCH_ID')
            ->where(['SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID' => $department_id, 'SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0, 'EXAM_TABULATOR.IS_DELETED' => 0])
            ->orderby('SEMESTER', 'ASC');
    }

    public function tabulator()
    {
        return $this->belongsTo(M_WP_EMP::class, 'emp_id', 'id');
    }
}

