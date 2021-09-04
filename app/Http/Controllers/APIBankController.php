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

namespace App\Http\Controllers;

use App\Exceptions\StudentNotFound;
use App\Exceptions\StudentNotFoundExceptions;
use App\Http\Resources\ApiGetStudentDetailResource;
use App\Http\Resources\ApiGetStudentResource;
use App\Http\Resources\ExamRoutineResource;
use App\Models\O_IMP_EXAM_SCHEDULE;
use App\Models\O_IMP_EXIM_ROUTINE;
use App\Models\O_IMP_EXIM_ROUTINE_DETAIL;
use App\Models\O_IMP_REQUEST;
use App\Models\O_IMP_REQUEST_COURSE;
use App\Models\O_STUDENT;
use App\Models\O_BATCH;
use App\Models\O_DEPARTMENTS;
use App\Models\O_VIEW_S_BATCH;
use App\Models\O_CASHIN;
use App\Models\M_WP_EMP;
use App\Models\O_VIEW_S_BATCH_REGCARD_PRINT;
use App\Models\O_VIEW_S_BATCH_REGCARD_PRINTED;
use App\Models\O_RELIGION;
use App\Models\O_BANK;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class APIBankController extends Controller
{
    public function getStudents()
    {
        return ApiGetStudentResource::collection(O_STUDENT::select('id','reg_code','roll_no')
            ->where('verified',1)
            ->where('year','>', date('Y')-4)
            ->get());
    }

    public function getStudentDetail(Request $request)
    {


        $regcode = $request->input('regcode');

        if ( ! $regcode){
            return response()->json(['error'=> 'Reg. Code is Empty'], 400);
        }

        $student = O_STUDENT::with('department','batch')->where(['reg_code' => $regcode ])->first();

        if ( $student ){
            return new ApiGetStudentDetailResource( $student );
        }else{
            return response()->json(['error'=> 'Student Not Found'], 400);
        }

    }
}
