<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\O_STUDENT;
use App\Models\O_IMP_EXAM_SCHEDULE;
use App\Models\O_IMP_REQUEST;
use App\Models\O_IMP_REQUEST_COURSE;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Imp_Request extends Controller
{
    public function store(Request $request)
    {

        /**
        "student_id": 6555,
        "course_id": 2,
        "campus_id": "2",
        "currentExamSchedule_id": 3,
        "type": "incourse"
         */


        $this->validate($request,
            [
                'student_id' => 'required|integer',
                'course_id' => 'required|integer',
                'campus_id' => 'required|integer',
                'currentExamSchedule_id' => 'required|integer',
                'type' => 'required',
            ]
        );

        $currentExamSchedule_id = $request->currentExamSchedule_id;
        $student_id = $request->student_id;
        $course_id = $request->course_id;
        $type = $request->type;

            $studentObj = O_STUDENT::find( $student_id );
        $schedule = O_IMP_EXAM_SCHEDULE::with('relXMSduleDPT')->find($currentExamSchedule_id);

        $allowedDeparmentArray = O_IMP_EXAM_SCHEDULE::with('relXMSduleDPT')->find($currentExamSchedule_id)->relXMSduleDPT->pluck('department_id')->toArray();

        if ( ! in_array($studentObj->department_id, $allowedDeparmentArray) ){
            return response()->json(['error'=>'Your Department is not allowed to this Improvement Exam Schedule'],400);
        }

        if (empty($schedule)) {
            return response()->json(['error' => 'Exam schedule has been not published'], 400);
        }

        if ( strtotime($schedule->form_fillup_last_date) < Carbon::now()->getTimestamp() ){
            return response()->json(['error' => 'Passed Last Date of Form fill-up'], 400);
        }

        $imp_request = O_IMP_REQUEST::where(['std_id' => $student_id, 'ies_id' => $schedule->id, 'type'=> $type])->first();

        if ( isset($imp_request) && $imp_request->payment_status == O_IMP_REQUEST::PAID){
            return response()->json(['error'=>'You Have cleared payment for this examination. Try Next Examination Please.'],400);
        }

        if (!empty($imp_request)) {
            $imp_rqc = O_IMP_REQUEST_COURSE::where(['course_id' => $course_id, 'imp_rq' => $imp_request->id, 'type'=> $type ])->exists();
            if ($imp_rqc) {
                return response()->json(['error' => 'You are already applied this course'], 400);
            }
        }

        try{
            
            $transaction = DB::transaction(function () use ($imp_request, $schedule, $student_id, $course_id, $type) {

                $lastRequest  = O_IMP_REQUEST::orderBy('id','desc')->first();
                $invoice_digit = '000';

                if ($lastRequest){
                    $invoice_digit = $lastRequest->id;
                }


                if (empty($imp_request)) {
                    $imp_request = O_IMP_REQUEST::create([
                        'STD_ID' => $student_id,
                        'IES_ID' => $schedule->id,
                        'PAYMENT_STATUS' => O_IMP_REQUEST::UNPAID,
                        'INVOICE_NUMBER' =>  $student_id  . '-' .  $invoice_digit,
                        'TYPE' => $type,
                    ]);

                }

                $imp_rqc = O_IMP_REQUEST_COURSE::create([
                    'STD_ID' => $student_id,
                    'COURSE_ID' => $course_id,
                    'IMP_RQ' => $imp_request->id,
                    'TYPE' => $type,
                ]);
            });

            return response()->json(['success' => 'Apply successful'], 200);
        }
        catch (\Exception $exception){
            Log::error($exception);
            return response()->json(['error' => 'Apply failed'], 400);

        }
    }

    public function destroy(Request $request)
    {
        /**
         * course_id	71
        currentExamSchedule_id	4
        type

        $course_array = [

        'student_id' => $id,
        'course_id' => $request->course_id,
        'campus_id' => $std->campus_id,
        'currentExamSchedule_id' => $request->currentExamSchedule_id,
        'type' => $request->type
        ];
         */
        $student_id =$request->student_id;
        $course_id =$request->course_id;
        $currentExamSchedule_id = $request->currentExamSchedule_id;
        $type = $request->type;


        $schedule = O_IMP_EXAM_SCHEDULE::find( $currentExamSchedule_id );

        if (empty($schedule)) {
            return response()->json(['error' => 'Exam schedule has been not published'], 400);
        }

        if ( strtotime($schedule->form_fillup_last_date) < Carbon::now()->getTimestamp() ){
            return response()->json(['error' => 'Passed Last Date of Form fill-up'], 400);
        }


        $impExamScheduleRequestId = O_IMP_REQUEST::with('relImpRequestCourse')->where([
            'std_id' => $student_id,
            'ies_id' => $currentExamSchedule_id,
            'type' => $type
        ])->first();


        if (empty($impExamScheduleRequestId)) {
            return response()->json(['error' => 'No Student Request Course Found in Selected Improvement Exam Schedule'], 400);
        }

        $imp_rqc = O_IMP_REQUEST_COURSE::with('relImpRequest')->where(
            [
                'std_id' => $student_id,
                'course_id' => $course_id,
                'IMP_RQ'=> $impExamScheduleRequestId->id,
                'type'=> $type
            ])->first();

        if (empty($imp_rqc)) {
            return response()->json(['error' => 'Course not found'], 400);
        }


        if ($imp_rqc->relImpRequest->payment_status == O_IMP_REQUEST::PAID) {
            return response()->json(['error'=>'Sorry! You cannot delete course. Because, You Have Paid For This Examination'],400);
        }
        if ($imp_rqc->relImpRequest->payment_status == O_IMP_REQUEST::UNPAID) {

            try{
                $transaction = DB::transaction(function () use ($impExamScheduleRequestId, $currentExamSchedule_id, $student_id, $course_id, $type) {

                    if ( $impExamScheduleRequestId->relImpRequestCourse->count() == 1){
                        O_IMP_REQUEST::where(['std_id' => $student_id, 'ies_id' => $currentExamSchedule_id, 'type' => $type])->delete();
                    }

                    O_IMP_REQUEST_COURSE::where(
                        [
                            'std_id' => $student_id,
                            'course_id' => $course_id,
                            'IMP_RQ'=> $impExamScheduleRequestId->id,
                            'type'=> $type
                        ])->delete();


                });
            }
            catch ( \Exception $exception){
                Log::error($exception);
                return response()->json(['error' => 'Apply failed'], 400);
            }


            return response()->json(['success' => 'Applied course removed successful'], 200);
        }
        return response()->json(['error' => 'Applied course can not be removed'], 400);
    }
}
