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
use Illuminate\Support\Facades\Validator;


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

    public function searchStudent(Request $request)
    {
    
        $regcode = $request->input('registration_code');
        $student = O_STUDENT::query()
            ->with('department:id,name','batch:id,batch_name')
            ->selectRaw("ID,REG_CODE,ROLL_NO,NAME,BATCH_ID,EMAIL,PHONE_NO,DEPARTMENT_ID,GENDER")
            // ->where('REG_CODE', $regcode) 
            ->where('REG_CODE', 'like', '%' . $regcode . '%')
            ->first();

            if($student){
               $account_info = O_CASHIN::get_student_account_info_summary($student->id);
                $data = [
                    "name" => $student->name,
                    "department" => $student->department->name ??'NA',
                    "batch" => $student->batch->batch_name ??'NA',
                    "roll" => $student->roll_no,
                    "current_dues" => $account_info['summary']['total_current_due']  ??'NA',
                    "student_id" => $student->id,
                ];
                return response()->json(['status'=> 'success', 'data'=>$data], 200);
            }else{
                return response()->json(['error'=> 'Student Not Found'], 404);
            }

    }
    public function confirmPayment(Request $request){

        $validator = Validator::make($request->all(), [
            'type_of_fee' => 'required|integer',
            'transaction_id' => 'required',
            'confirm_amount' => 'required|numeric',
            'student_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 400);
        }

        $receipt_no =$request->input('transaction_id');
        $student_id =$request->input('student_id');
        $type_of_fee =$request->input('type_of_fee');
        $confirm_amount =$request->input('confirm_amount');
        $today = date('Y-m-d');

        if (strtotime($receipt_no) !== 'bot' && O_CASHIN::where('receipt_no', $receipt_no)->get()->count() > 0) {
            return response()->json(['error' => 'Duplicate Receipt No. Found!'], 400);
        }

        try {
            $cachsin = new O_CASHIN();
            $cachsin->purpose_pay_id = $type_of_fee;
            $cachsin->amount = $confirm_amount;
            $cachsin->bank_id = 3;
            $cachsin->student_id = $student_id;
            $cachsin->receipt_no = $receipt_no;
            $cachsin->cashorbank = 1; 
            $cachsin->receive_by = 1;
            $cachsin->date_bank = '' . $today . '';
            $cachsin->pay_date = '' . $today . '';
            $cachsin->receive_by = 486;
            $cachsin->varified_by = 486;
            $cachsin->is_varified = 1;
            $cachsin->save();

            return response()->json(['status'=> 'success', 'message'=>'Payment confirmed successfully]'], 200);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['error' => 'Something Went Wrong!'], 400);
        }

    }
    public function transectionInfo($date){
        // return $date;
        
        try {
            $data = O_CASHIN::where(['receive_by'=>486])->where(['pay_date'=>$date])->orderBY('id','desc')->get();

            $data->transform(function ($student) {
                return [
                    'id' => $student->id,
                    'purpose_pay_id' => $student->purpose_pay_id,
                    'amount' => $student->amount,
                    'student_id' => $student->student_id,
                    'receipt_no' => $student->receipt_no,
                    'pay_date' => $student->pay_date,                  
                    
                ];
            });
            
            

            return response()->json(['status'=> 'success', 'data'=>$data], 200);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['error' => 'Something Went Wrong!'], 400);
        }

    }
    public function transectionDelete($receipt_no){
        
        try {
            $data = O_CASHIN::where(['receive_by'=>486,'receipt_no'=>$receipt_no])->first();  
            if(!empty($data)){
                $data->delete();
                return response()->json(['message' => 'Transection Delete Successfully!'], 200);
            }
            else{
                return response()->json(['message' => 'Transection Id Not Found!'], 200);
            }          

        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['error' => 'Something Went Wrong!'], 400);
        }

    }


    public function singleTransectionInfo($receipt_no){

        try {
            $data = O_CASHIN::where(['receive_by'=>486,'receipt_no'=>$receipt_no])->first();  

            if(!empty($data)){
                 $response =  [
                     'id' => $data->id,
                     'purpose_pay_id' => $data->purpose_pay_id,
                     'amount' => $data->amount,
                     'student_id' => $data->student_id,
                     'receipt_no' => $data->receipt_no,
                     'pay_date' => $data->pay_date,                  
                     
                 ];
               return response()->json(['status'=> 'success', 'data'=> $response ], 200);
             }
             else{
                 $response = ['message' =>  'Transection Not Found!'];                    
                 return response()->json(['status'=> 'fail', 'data'=> $response ], 404);
             } 
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['error' => 'Something Went Wrong!'], 400);
        }

    }
}
