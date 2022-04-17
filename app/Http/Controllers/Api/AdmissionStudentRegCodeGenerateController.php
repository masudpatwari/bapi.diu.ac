<?php

namespace App\Http\Controllers\Api;

use App\Models\O_DEPARTMENTS;
use App\Models\O_BATCH;
use App\Models\O_STUDENT;
use App\Models\O_EMP;
use App\Models\O_CASHIN;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActiveBatchForAdmissionResource;

class AdmissionStudentRegCodeGenerateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {


        $this->validate($request, [
            'receipt_no' => 'required',
            'admission_fee' => 'required',
            'bank_status' => 'required',
            'bank_id' => 'required',
            'note' => 'nullable|string|max:100',
            'student_id' => 'required|integer',
            'department_id' => 'required|integer',
            'created_by_email' => 'required|email',
            'student_type' => 'nullable',
        ]);


        




        $batch = O_BATCH::find($request->batch_id);
        $department = O_DEPARTMENTS::find($request->department_id);

        $employee = O_EMP::selectRaw("ID,OFFICIAL_EMAIL,EMP_NAME")
            ->where('OFFICIAL_EMAIL', $request->created_by_email)
            ->first();

        $student = O_STUDENT::selectRaw("ID,NAME,ROLL_NO,REG_CODE,PHONE_NO,ADM_FRM_SL,EMP_ID,VERIFIED,DEPARTMENT_ID,YEAR,ADM_SEASON")
            ->with('employee:id,emp_name')
            ->where([
                'DEPARTMENT_ID' => $batch->department_id,
                'YEAR' => $batch->adm_year,
                'ADM_SEASON' => $batch->adm_season,
                'verified' => 1
            ])
            ->orderBy('ROLL_NO', 'desc')
            ->first();

        if ($student) {
            $roll = $student->roll_no + 1;
        }

        $newRoll = str_pad($roll ?? 1, 3, '0', STR_PAD_LEFT);
        $admission_year = substr($batch->adm_year, 2);
        $new_reg_code = "{$request->university_code}{$admission_year}{$batch->adm_season}{$request->hall_code}{$request->program_code}{$newRoll}";

        $new_reg_code = $request->student_type == 'CT' ? "{$new_reg_code}-CT" : $new_reg_code;


        try {

            \DB::beginTransaction();

            $cachsin = new O_CASHIN();
            $cachsin->purpose_pay_id = 4; // admission fee
            $cachsin->amount = $request->admission_fee;
            $cachsin->bank_id = $request->bank_id;
            $cachsin->note = $request->note;
            $cachsin->student_id = $request->student_id;
            $cachsin->receipt_no = trim($request->receipt_no);
            $cachsin->cashorbank = $request->bank_status;
            $cachsin->receive_by = $employee->id;
            $cachsin->date_bank = date('m/d/Y'); // 11/13/2016 = m/d/y format
            $cachsin->pay_date = date('m/d/Y');
            $cachsin->varified_by = $employee->id;
            $cachsin->is_varified = 1;
            $cachsin->save();

            $updateStudent = O_STUDENT::where('ID', $request->student_id)->first();
            $updateStudent->REG_CODE = $new_reg_code;
            $updateStudent->ROLL_NO = $newRoll;
            $updateStudent->VERIFIED = 1;
            $updateStudent->save();

            \DB::commit();

            $updateStudent2 = O_STUDENT::where('ID', $request->student_id)->first();

            $updateStudent2['batch_name']= $batch->batch_name;
            $updateStudent2['department_name']= $department->name;

            return $updateStudent2;

        } catch (\Exception $ex) {
            \DB::rollBack();
            \Log::error($ex->getMessage());
            return response()->json(['error' => 'Something Went Wrong!'], 400);
        }

    }
}
