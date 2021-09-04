<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class O_IMP_REQUEST extends Model
{
    /**
     * PAYMENT_STATUS
     *
     * UNPAID = 0
     * PAID = 1
     */

    const UNPAID = 0;
    const PAID = 1;
    
    public $timestamps = false;
    protected $table = "IMP_REQUEST";
    protected $connection = 'oracle';

    protected $fillable = ['STD_ID', 'IES_ID', 'PAYMENT_STATUS', 'INVOICE_NUMBER','TYPE'];

    public static function imp_requested_courses_for_assign_teacher($schedule_id, $campus_id, $type)
    {
        $department_id = session('user.selected_department.id');
        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT IMP_REQUEST_COURSE.COURSE_ID, COURSE.NAME AS COURSE_NAME, COURSE.CODE, COURSE.COURSE_TYPE, CAMPUS.ID AS CAMPUS_ID, CAMPUS.NAME AS CAMPUS_NAME FROM '.$db_prefix.'.IMP_REQUEST 
            JOIN '.$db_prefix.'.IMP_REQUEST_COURSE ON IMP_REQUEST_COURSE.IMP_RQ = IMP_REQUEST.ID 
            JOIN '.$db_prefix.'.STUDENT ON STUDENT.ID = IMP_REQUEST_COURSE.STD_ID 
            JOIN '.$db_prefix.'.CAMPUS ON CAMPUS.ID = STUDENT.CAMPUS_ID 
            JOIN '.$db_prefix.'.COURSE ON COURSE.ID = IMP_REQUEST_COURSE.COURSE_ID 

            WHERE IMP_REQUEST.IES_ID = '.$schedule_id.' AND CAMPUS.ID = '.$campus_id.' AND STUDENT.DEPARTMENT_ID = '.$department_id.' AND IMP_REQUEST_COURSE.TYPE = '."'".$type."'".'
            GROUP BY IMP_REQUEST_COURSE.COURSE_ID, STUDENT.CAMPUS_ID, CAMPUS.ID, CAMPUS.NAME, COURSE.NAME, COURSE.CODE, COURSE.COURSE_TYPE ORDER BY COURSE.CODE ASC
        ');
    }

    public static function imp_requested_courses_for_exam_routine($schedule_id, $campus_id)
    {
        $department_id = session('user.selected_department.id');
        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT COUNT(*) AS count, IMP_REQUEST_COURSE.TYPE AS EXAM_TYPE, IMP_REQUEST_COURSE.COURSE_ID, COURSE.NAME AS COURSE_NAME, COURSE.CODE, COURSE.COURSE_TYPE, COURSE.DEPARTMENT_ID, COURSE.SEMESTER_FOR AS SEMESTER FROM '.$db_prefix.'.IMP_REQUEST 
            JOIN '.$db_prefix.'.IMP_REQUEST_COURSE ON IMP_REQUEST_COURSE.IMP_RQ = IMP_REQUEST.ID 
            JOIN '.$db_prefix.'.COURSE ON COURSE.ID = IMP_REQUEST_COURSE.COURSE_ID
            JOIN '.$db_prefix.'.STUDENT ON STUDENT.ID = IMP_REQUEST.STD_ID 

            WHERE IMP_REQUEST.IES_ID = '.$schedule_id.' AND STUDENT.CAMPUS_ID = '.$campus_id.' AND STUDENT.DEPARTMENT_ID = '.$department_id.'
            GROUP BY IMP_REQUEST_COURSE.TYPE, IMP_REQUEST_COURSE.COURSE_ID, COURSE.NAME, COURSE.CODE, COURSE.COURSE_TYPE, COURSE.DEPARTMENT_ID, COURSE.SEMESTER_FOR ORDER BY IMP_REQUEST_COURSE.TYPE, COURSE.CODE ASC
        ');
    }

    public static function imp_applided_reg_code($schedule_id, $campus_id, $course_id, $type)
    {
        $department_id = session('user.selected_department.id');
        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT STUDENT.ID, STUDENT.REG_CODE, STUDENT.ROLL_NO, STUDENT.NAME, STUDENT.PHONE_NO FROM  '.$db_prefix.'.IMP_REQUEST 
            JOIN '.$db_prefix.'.IMP_REQUEST_COURSE ON IMP_REQUEST_COURSE.IMP_RQ = IMP_REQUEST.ID 
            JOIN '.$db_prefix.'.STUDENT ON STUDENT.ID = IMP_REQUEST.STD_ID 

            WHERE IMP_REQUEST.IES_ID = '.$schedule_id.' AND STUDENT.CAMPUS_ID = '.$campus_id.' AND STUDENT.DEPARTMENT_ID = '.$department_id.' AND IMP_REQUEST_COURSE.COURSE_ID = '.$course_id.' AND IMP_REQUEST_COURSE.TYPE = '."'".$type."'".' ORDER BY STUDENT.ID ASC
        ');
    }

    public static function imp_requested_students($schedule_id, $campus_id)
    {
        $department_id = session('user.selected_department.id');
        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT IMP_REQUEST.IES_ID, IMP_REQUEST_COURSE.STD_ID, IMP_REQUEST_COURSE.COURSE_ID, IMP_REQUEST_COURSE.TYPE FROM '.$db_prefix.'.IMP_REQUEST 
            JOIN '.$db_prefix.'.IMP_REQUEST_COURSE ON IMP_REQUEST_COURSE.IMP_RQ = IMP_REQUEST.ID
            JOIN '.$db_prefix.'.COURSE ON COURSE.ID = IMP_REQUEST_COURSE.COURSE_ID 
            
            JOIN '.$db_prefix.'.STUDENT ON STUDENT.ID = IMP_REQUEST.STD_ID 

            WHERE IMP_REQUEST.IES_ID = '.$schedule_id.' AND STUDENT.CAMPUS_ID = '.$campus_id.' AND STUDENT.DEPARTMENT_ID = '.$department_id.' ORDER BY IMP_REQUEST_COURSE.STD_ID ASC
        ');

        return DB::connection('oracle')->select('
            SELECT IMP_REQUEST.IES_ID, IMP_REQUEST_COURSE.STD_ID, IMP_REQUEST_COURSE.COURSE_ID, IMP_REQUEST_COURSE.TYPE FROM '.$db_prefix.'.IMP_REQUEST 
            JOIN '.$db_prefix.'.IMP_REQUEST_COURSE ON IMP_REQUEST_COURSE.IMP_RQ = IMP_REQUEST.ID 
            JOIN '.$db_prefix.'.COURSE ON COURSE.ID = IMP_REQUEST_COURSE.COURSE_ID 
            
            JOIN '.$db_prefix.'.STUDENT ON STUDENT.ID = IMP_REQUEST.STD_ID 

            WHERE IMP_REQUEST.IES_ID = '.$schedule_id.' AND STUDENT.CAMPUS_ID = '.$campus_id.' AND COURSE.DEPARTMENT_ID = '.$department_id.' ORDER BY IMP_REQUEST_COURSE.STD_ID ASC
        ');
    }

    public function relStudent()
    {
        return $this->belongsTo(O_STUDENT::class,'std_id', 'id');
    }

    public function relImpRequestCourse()
    {
        return $this->hasMany(O_IMP_REQUEST_COURSE::class, 'imp_rq', 'id');
    }

    public function relExamSchedule()
    {
        return $this->belongsTo(O_IMP_EXAM_SCHEDULE::class,'ies_id', 'id');
    }

    public static function removeUnpaidRequest($iesId)
    {
        $impReqIdArray = O_IMP_REQUEST::select('id')
            ->where('payment_status', O_IMP_REQUEST::UNPAID)
            ->where('ies_id', '<=',$iesId)
            ->get()->pluck('id')->toArray();
        O_IMP_REQUEST::whereIn('id', $impReqIdArray)->delete();
        O_IMP_REQUEST_COURSE::whereIn('imp_rq', $impReqIdArray)->delete();

    }
}
