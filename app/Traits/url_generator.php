<?php
/**
 * Created by PhpStorm.
 * User: lemon
 * Date: 1/10/19
 * Time: 2:55 PM
 */

namespace App\Traits;


trait url_generator
{

    public static function get_semester_detail_url($batch_id , $semester_info_for_resutl_tabale_id)
    {
        $param = [ 'batch_id' => $batch_id, 'semester_id'=> $semester_info_for_resutl_tabale_id, '_token' => md5($batch_id . $semester_info_for_resutl_tabale_id)];

        return route('semester_details' , $param) ;

    }

    /*
    public static function get_semester_edit_url($batch_id , $semester_id)
    {
        $param = [ 'batch_id' => $batch_id, 'semester_id'=> $semester_id, '_token' => md5($batch_id . $semester_id)];

        return route('semester_edit', $param);
    }

    public static function get_exempted_semester_edit_url($batch_id , $semester_id)
    {
        $param = [ 'batch_id' => $batch_id, 'semester_id'=> $semester_id, '_token' => md5($batch_id . $semester_id)];

        return route('exempted_semester_edit', $param);
    }*/

    public static function get_marksheet_detail_url($sifr_id , $course_id)
    {
        $param = ['sifr_id' => $sifr_id, 'course_id' => $course_id, '_token' => md5($sifr_id . $course_id)];

        return route('marksheet_view', $param);
    }
    public static function get_marksheet_edit_url($sifr_id , $course_id)
    {
        $param = ['sifr_id' => $sifr_id, 'course_id' => $course_id, '_token' => md5($sifr_id . $course_id . 'notification')];

        return route('marksheet_edit', $param);

    }

    /*
    public static function get_transcript_detail_url($sifr_id , $course_id)
    {
        $param = ['sifr_id' => $sifr_id, 'course_id' => $course_id, '_token' => md5($sifr_id . $course_id)];

        return route('transcript_view', ['student_id' => $students_value->id, '_token' => md5($students_value->id)])
    }*/

    public static function get_tabulationsheet_detail_url($batch_id , $semester_info_table_id )
    {
        $param = ['batch_id' => $batch_id, 'semester_info_table_id' => $semester_info_table_id, '_token' => md5($batch_id.$semester_info_table_id)];

        return route('tabulationsheet_view', $param);
    }


    public static function get_splittedBatch_detail_url(int $parentBatchId)
    {
        $param = ['id' => $parentBatchId, 'key' => md5($parentBatchId . $parentBatchId )];

        return route('batch_split_detail', $param);
    }

    public static function getImprovementExamScheduleDetail_url(int $impExamSchedule_id)
    {
        return route('schedules.show',['id'=>$impExamSchedule_id , '_token'=> md5($impExamSchedule_id)]);
    }

    /**
     * get Teacher Assign On Improvement Exam Schedule url
     *
     * @return string
     */
    public static function getTeacherAssignOnImprovementExamSchedulOnCreateUrl()
    {
        return route('assign_teacher.pending');
    }

    public static function getTeacherAssignOnImprovementExamScheduleOnDeny_url( $schedule_id , $type )
    {
        return route('assign_teacher.create', ['id' => $schedule_id, 'type' => $type, '_token' => md5($schedule_id . $type)]);
    }



}