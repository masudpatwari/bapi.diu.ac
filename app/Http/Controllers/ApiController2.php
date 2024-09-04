<?php

namespace App\Http\Controllers;

use App\Models\M_WP_EMP;
use App\Models\O_COURSE_ALLOCATION_INFO;
use App\Models\O_MARKS;
use App\Models\O_STUDENT;
use App\Models\O_SEMESTERS;

class ApiController2 extends Controller
{
    public function getSemesterTeacherListByStudentID(int $std_id, int $semester)
    {

        $student = O_STUDENT::where('id', $std_id)->first();

        if ( ! $student ){
            return response()->json(['message'=>'Student Not Found'],400);
        }

        $sifr = O_SEMESTERS::where('batch_id', $student->batch_id)
            ->where('semester', $semester)
            ->first();


        if ( ! $sifr ){
            return response()->json(['message'=>'Student ID and Semester not match'],400);
        }

        $mark_exists = O_MARKS::where('sifr_id', $sifr->id)->exists();

        $courseTeachers = O_COURSE_ALLOCATION_INFO::with('teacher','course')
            ->where('sifr_id', $sifr->id)
            ->get();


        if ($courseTeachers->count()==0){
            return response()->json(['message'=>'No Teacher Allocated'],400);
        }

        $courseAndTeacher = [];
        foreach ($courseTeachers as $courseTeacher){
            $courseAndTeacher[] = [
                'course_id'=>$courseTeacher->course->id,
                'course_code'=>$courseTeacher->course->code,
                'course_name'=>$courseTeacher->course->name,
                'teacher_id'=>$courseTeacher->teacher->id,
                'teacher_name'=>$courseTeacher->teacher->name,
                'teacher_position'=>$courseTeacher->teacher->position,
            ];
        }

        return [
            'courseAndTeacher'=>$courseAndTeacher,
            'is_mark_exists' => $mark_exists
        ];

    }

    public function getSemesterListByStudentId( int $std_id)
    {
        $student = O_STUDENT::where('id', $std_id)->first();

        if ( ! $student ){
            return response()->json(['message'=>'Student Not Found'],400);
        }

        return['current_semester' => O_SEMESTERS::where('batch_id', $student->batch_id)
            ->max('semester')];
//            ->get()->pluck('semester');

    }

    public function test_student(){
        return O_STUDENT::selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ,  PASSWORD ,  DEPARTMENT_ID ,  BATCH_ID, ACTUAL_FEE , NO_OF_SEMESTER, payment_from_semester,SHIFT_ID,GROUP_ID,CAMPUS_ID")->where('reg_code','CE-E-64-23-126370' )->first();
    }
}
