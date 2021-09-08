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

class O_STUDENT extends Eloquent
{
    public $timestamps = false;
    protected $table = "STUDENT";
    protected $connection = 'oracle';
    protected $fillable = ['NAME','PASSWORD','DEPARTMENT_ID','BATCH_ID','SHIFT_ID','YEAR','GROUP_ID','BLOOD_GROUP','EMAIL','PHONE_NO',
                            'ADM_FRM_SL','RELIGION_ID','GENDER','DOB','BIRTH_PLACE','FG_MONTHLY_INCOME','PARMANENT_ADD','MAILING_ADD',
                            'F_NAME','F_CELLNO','M_NAME','M_CELLNO','G_NAME','G_CELLNO','E_NAME','E_CELLNO','EMP_ID','NATIONALITY',
                            'MARITAL_STATUS','IMAGE','FILENAME','ADM_DATE','CAMPUS_ID','STD_BIRTH_OR_NID_NO','E_EXAM_NAME1','E_GROUP1',
                            'E_ROLL_NO_1','E_PASSING_YEAR1','E_LTR_GRD_TMARK1','E_DIV_CLS_CGPA1','E_BOARD_UNIVERSITY1','ACTUAL_FEE',
                            'NO_OF_SEMESTER'];
    protected $hidden = [
      'PASSWORD'
    ];

    /*
     * Called marksheet parent controller
    */

    public static function students($department_id, $batch_id)
    {
//      $childBatchIdArray = O_BATCH::where('PARENT_BATCH_ID', $batch_id)->get()->pluck('id')->toArray();
//      $childBatchIdArray [] = $batch_id;
//
        return static::where(['DEPARTMENT_ID' => $department_id, 'VERIFIED' => 1])
          ->whereIn('BATCH_ID' ,  (array)$batch_id )
//          ->whereIn('BATCH_ID' ,  $childBatchIdArray )
            ->orderby('ROLL_NO', 'ASC');
    }

    /*
	 * Called edit marksheet controller
	*/
    public static function student_by_roll($department_id, $batch_id, $roll_no)
    {
        return static::where(['DEPARTMENT_ID' => $department_id, 'BATCH_ID' => $batch_id, 'ROLL_NO' => $roll_no, 'VERIFIED' => 1]);
    }

    /*
	 * Called edit marksheet controller
	*/
    public static function student_by_registration($department_id, $batch_id, $registration_no)
    {
        return static::where(['DEPARTMENT_ID' => $department_id, 'BATCH_ID' => $batch_id, 'REG_CODE' => $registration_no, 'VERIFIED' => 1]);
    }

    /*
	 * Called edit marksheet controller
	*/
    public static function student_by_student_id($department_id, $batch_id, $student_id)
    {
        return static::where(['DEPARTMENT_ID' => $department_id, 'BATCH_ID' => $batch_id, 'ID' => $student_id, 'VERIFIED' => 1]);
    }

    /*
     * Call transcript controller
     * */

    public static function students_by_registration_no( $registration_no )
    {
        return static::where(['REG_CODE' => $registration_no, 'VERIFIED' => 1]);
    }

    public function studentMarks()
    {
        return $this->hasMany(O_MARKS::class, 'std_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(O_DEPARTMENTS::class, 'department_id', 'id');
    }

    public function batch()
    {
        return $this->belongsTo(O_BATCH::class, 'batch_id', 'id');
    }

    public function relCampus()
    {
        return $this->belongsTo(O_CAMPUS::class, 'campus_id', 'id');
    }


    public function shift()
    {
        return $this->belongsTo(O_SHIFT::class, 'shift_id', 'id');
    }

    public function relCreditTransfer()
    {
        return $this->hasOne(O_CREDIT_TRANSFER::class, 'student_id', 'id');
    }

    public static function credit_transfer_assigned_courses( $student_id, $type )
    {

        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT COURSE.ID, COURSE.NAME, COURSE.CODE, COURSE.COURSE_TYPE, COURSE_ALLOCATION_INFO.COL1_TITLE, COURSE_ALLOCATION_INFO.COL1_MARK, COURSE_ALLOCATION_INFO.COL2_TITLE, COURSE_ALLOCATION_INFO.COL2_MARK, COURSE_ALLOCATION_INFO.COL3_TITLE, COURSE_ALLOCATION_INFO.COL3_MARK, COURSE_ALLOCATION_INFO.COL4_TITLE, COURSE_ALLOCATION_INFO.COL4_MARK, COURSE.INCOURSE_MARK, COURSE.FINAL_MARK, COURSE.TOTAL_MARK, SEMESTER_INFO_FOR_RESULT.GPS_ID FROM '.$db_prefix.'.STUDENT
            JOIN '.$db_prefix.'.SEMESTER_INFO_FOR_RESULT ON SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID = STUDENT.DEPARTMENT_ID AND SEMESTER_INFO_FOR_RESULT.BATCH_ID = STUDENT.BATCH_ID
            JOIN '.$db_prefix.'.COURSE_ALLOCATION_INFO ON COURSE_ALLOCATION_INFO.SIFR_ID = SEMESTER_INFO_FOR_RESULT.ID
            JOIN '.$db_prefix.'.COURSE ON COURSE.ID = COURSE_ALLOCATION_INFO.COURSE_ID
            JOIN '.$db_prefix.'.CREDIT_TRANSFER_DETAIL ON CREDIT_TRANSFER_DETAIL.COURSE_ID = COURSE.ID
            WHERE STUDENT.ID = '.$student_id.' AND SEMESTER_INFO_FOR_RESULT.IS_VERIFIED = 1 AND SEMESTER_INFO_FOR_RESULT.IS_DELETED = 0 AND COURSE.COURSE_TYPE = '.$type.' AND COURSE_ALLOCATION_INFO.IS_DELETED = 0
        ');
    }

    public function getMaxAsCurrentSemester()
    {

        return O_SEMESTERS::where('batch_id', $this->batch_id)->max('SEMESTER');

    }

    public function employee()
    {
        return $this->belongsTo(O_EMP::class, 'emp_id', 'id');
    }

}

