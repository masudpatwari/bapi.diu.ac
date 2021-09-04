<?php

namespace App\Http\Controllers;

use App\Models\M_WP_EMP;
use App\Models\O_CASHIN;
use App\Models\O_EMP;
use App\Models\O_IMP_EXAM_SCHEDULE;
use App\Models\O_IMP_REQUEST;
use App\Models\O_IMP_REQUEST_COURSE;
use App\Models\O_PURPOSE_PAY;
use App\Models\O_STUDENT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Imp_Invoice_Generator extends Controller
{
    public function get_improvement_application_form_data($std_id, $currentExamScheduleId, $type)
    {

        $schedule = O_IMP_EXAM_SCHEDULE::find( $currentExamScheduleId);

        if ( ! $schedule ) {
            return response()->json(['error'=>'No Improvement Exam Schedule Found!'] , 400);
        }
        $studentObj = O_STUDENT::with('department','batch')->where('id', $std_id)->first();
        $year =  substr(date('Y'), -2);

        $impRequestObj = O_IMP_REQUEST::with('relImpRequestCourse')->where( [ 'std_id' => $std_id, 'ies_id'=> $currentExamScheduleId, 'type'=>$type ])->first();

        if ( ! $impRequestObj  ) {
            return response()->json(['error'=>'No Improvement Exam Request Found!'] , 400);
        }


        $appliedCourses = [];

        $totalCost = 0;

            foreach ($impRequestObj->relImpRequestCourse as $ImpRequestCourse){

                $cost = O_IMP_EXAM_SCHEDULE::getCourseCost( $schedule, $std_id , $ImpRequestCourse->course_id, $type)[$studentObj->department->program_type] [$type];

                $appliedCourses [] = [
                    'name' => $ImpRequestCourse->relCourse->name,
                    'code' => $ImpRequestCourse->relCourse->code,
                    'cost' => $cost
                ];

                $totalCost += $cost;
            }

        $reciptNo = NULL;
        $pay_date = NULL;
        $paid_amount = '';
        $cashinDetail = O_CASHIN::where('NOTE', 'like', '%'. $impRequestObj->invoice_number .'%')->orderBy('id','desc')->first();

        if ( $cashinDetail ){
            $reciptNo = $cashinDetail->receipt_no;
            $paid_amount = $cashinDetail->amount;
            $pay_date = Carbon::createFromFormat('Y-m-d H:i:s', $cashinDetail->pay_date)->format('d F Y');
        }



        $data['data'] = [
            'id' => $studentObj->id,
            'name' => $studentObj->name,
            'roll' => $studentObj->roll_no,
            'reg_code' => $studentObj->reg_code,
            'father_name' => $studentObj->f_name ?? 'No Father Name',
            'mother_name' => $studentObj->m_name ?? 'No Mother Name',
            'department' => $studentObj->department->name,
            'batch' => $studentObj->batch->batch_name,
            'session' => $studentObj->batch->sess,
            'applied_courses'=> $appliedCourses,
            'total_cost' => $totalCost,
            'invoice_number' => $impRequestObj->invoice_number,
            'receipt_no' => $reciptNo,
            'paid_amount' => $paid_amount,
            'pay_date' => $pay_date,
            'payment_status' => $impRequestObj->payment_status==O_IMP_REQUEST::PAID?'PAID':'UNPAID',
            'improvement_exam_info' => $schedule,
            'admit_sl_no' => $year.$impRequestObj->id.$studentObj->id
        ];

        return response()->json($data , 200 );
    }

    public function get_improvement_application_form_data_for_cms($reg_code, $currentExamScheduleId, $type)
    {
        $student = O_STUDENT::where('reg_code', $reg_code)->first();
        if (!empty($student)) {
            $application_form = $this->get_improvement_application_form_data( $student->id, $currentExamScheduleId, ''.$type.''  );
            return $application_form;
        }
        return response()->json(['error' => 'Reg code not found'], 404);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_student_for_payment(Request $request)
    {
        $student = O_STUDENT::where('reg_code', $request->reg_code)->first();
        if (!empty($student)) {
            $application_form = $this->get_improvement_application_form_data( $student->id, $request->ies_id, ''.$request->type.''  );
            return $application_form;
        }
        return response()->json(['error' => 'Reg code not found'], 404);
    }

    public function make_improvement_payment_complete(Request $request)
    {
        /**
        ID,
        PURPOSE_PAY_ID,
        AMOUNT,
        NOTE,
        STUDENT_ID,
        RECEIPT_NO,
        CASHORBANK,
        RECEIVE_BY,
        BANK_ID,
        VARIFIED_BY,
        IS_VARIFIED,
        DATE_BANK,
        PAY_DATE
        from CASHIN

         */

        /*$wp_emp = M_WP_EMP::where('id',$request->emp_id)->first();

        if ( $wp_emp ){
            return response()->json(['error'=>'Employee is not found in CMS'], 400);
        }*/

        $validator = Validator::make($request->all(), [
            'bank_id' => 'required|integer',
            'bank_payment_date' => 'required|date_format:d-m-Y',
            'receipt_no' => 'required',
            'invoice_number' => 'required',
            'total_cost' => 'required|numeric',
            'discount' => 'required|numeric',
            'total_payable' => 'required|numeric',
            'ies_id' => 'required',
            'reg_code' => 'required',
            'employee_email' => 'required|email',
            'type' => 'required|in:incourse,final',
        ]);

        if($validator->fails()){
            return response($validator->messages(), 400);
        }

        $bank_id = $request->input('bank_id');
        $bank_payment_date = date('Y-m-d', strtotime($request->input('bank_payment_date')));
        $receipt_no = $request->input('receipt_no');
        $invoice_number = $request->input('invoice_number');
        $total_cost = $request->input('total_cost');
        $discount = $request->input('discount');
        $total_payable = $request->input('total_payable');
        $ies_id = $request->input('ies_id');
        $reg_code = $request->input('reg_code');
        $employee_email = $request->input('employee_email');
        $type = $request->input('type');
        $note = $request->input('note');
        $today = date('Y-m-d');

        $employee = O_EMP::where('official_email', $employee_email)->first();
        if ( ! $employee ){
            return response()->json(['error'=> 'Employee is not found in ERP'], 400);
        }
        $receive_by = $employee->id;

        $student = O_STUDENT::where('reg_code', $reg_code)->first();
        if ( ! $student ){
            return response()->json(['error'=>'Student not found in ERP'], 400);
        }

        $student_id = $student->id;

        $o_imp_requestObj = O_IMP_REQUEST::where([
            'std_id' => $student_id,
            'ies_id' => $ies_id,
            'type' => $type,
            'payment_status' => O_IMP_REQUEST::UNPAID
        ])->first();

        if ( ! $o_imp_requestObj){
            return response()->json(['error'=>'Already paid!'], 400);
        }

        try{



            $cachsin = new O_CASHIN();
            $cachsin->purpose_pay_id = O_PURPOSE_PAY::IMPROVEMENT_FEE_STATUS_ID;
            $cachsin->amount = $total_payable;
            $cachsin->bank_id= $bank_id;
            $cachsin->note = "INVOICE NUMBER :" . $invoice_number . ', Total Amount: '. $total_cost.', Discount: '. $discount.' TK. VIA CMS. '. $note;
            $cachsin->student_id = $student_id;
            $cachsin->receipt_no = $receipt_no;
            $cachsin->cashorbank = $bank_id==0?1:0; //  1 = cash, 0 = Bank
            $cachsin->receive_by = $receive_by;
            $cachsin->date_bank = ''.$bank_payment_date.''; // 11/13/2016 = m/d/y format
            $cachsin->pay_date = ''.$today.'';
            $cachsin->varified_by= $receive_by;
            $cachsin->is_varified = 1;
            $cachsin->save();

            $o_imp_requestObj->payment_status = O_IMP_REQUEST::PAID;
            $o_imp_requestObj->save();


            return response()->json($cachsin, 200);
        }
        catch (\Exception $ex){
            Log::error($ex->getMessage());
            return response()->json(['message' => 'Something Went Wrong!'], 400);
        }
    }

    public function get_improvement_admit_card(Request $request)
    {
        $reg_code = $request->input('reg_code');
        $ies_id = $request->input('ies_id');
        $type = $request->input('type');

        $student = O_STUDENT::where('reg_code', $reg_code)->first();
        if (empty($student)){
            return response()->json(['error'=>'Student not found in ERP'], 400);
        }

        $student_id = $student->id;

        $o_imp_req = O_IMP_REQUEST::where([
            'std_id' => $student_id,
            'ies_id' => $ies_id,
            'type' => $type,
            'payment_status' => O_IMP_REQUEST::PAID
        ])->first();

        if (empty($o_imp_req)){
            return response()->json(['error'=>'Payment not complete! Please payment first'], 400);
        }
        $application_form_data = $this->get_improvement_application_form_data($student_id, $ies_id, $type);

        return $application_form_data;
    }
}
