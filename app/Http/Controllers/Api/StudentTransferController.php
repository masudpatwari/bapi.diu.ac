<?php

namespace App\Http\Controllers\Api;

use App\Models\O_BATCH;
use App\Models\O_EMP;
use App\Models\O_STUDENT;
use Illuminate\Http\Request;
use App\Models\O_StudentTransfer;
use App\Http\Controllers\Controller;

class StudentTransferController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {


        /*$this->validate($request, [
            'student_id' => ['required', 'integer'],
            'department_id' => ['required', 'integer'],
            'batch_id' => ['required', 'integer'],
            'shift_id' => ['required', 'integer'],
            'group_id' => ['required', 'integer'],
            'campus_id' => ['required', 'integer'],
            'created_by_email' => ['required', 'email'],
            'university_code' => ['required', 'integer'],
            'hall_code' => ['required', 'integer'],
            'program_code' => ['required', 'integer'],
        ]);*/


        // dump(\Log::error(print_r($request->all(),true)));



		$batch = O_BATCH::find($request->batch_id);


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


        // dump(\Log::error(print_r([$student,$batch],true)));



        $newRoll = str_pad($roll ?? 1, 3, '0', STR_PAD_LEFT);
        $admission_year = substr($batch->adm_year, 2);
        $new_reg_code = "{$request->university_code}{$admission_year}{$batch->adm_season}{$request->hall_code}{$request->program_code}{$newRoll}";


		$employee = O_EMP::selectRaw("ID,OFFICIAL_EMAIL,EMP_NAME")
            ->where('OFFICIAL_EMAIL', $request->created_by_email)
            ->first();





		try {

			\DB::beginTransaction();

	        $student = O_STUDENT::selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ,  PASSWORD ,  DEPARTMENT_ID ,  BATCH_ID ,  SHIFT_ID ,  YEAR ,  REG_SL_NO ,  GROUP_ID ,  BLOOD_GROUP ,  EMAIL ,  PHONE_NO ,  ADM_FRM_SL ,  RELIGION_ID ,  GENDER ,  DOB ,  BIRTH_PLACE ,  FG_MONTHLY_INCOME ,  PARMANENT_ADD ,  MAILING_ADD ,  F_NAME ,  F_CELLNO ,  F_OCCU ,  M_NAME ,  M_CELLNO ,  M_OCCU ,  G_NAME ,  G_CELLNO ,  G_OCCU ,  E_NAME ,  E_CELLNO ,  E_OCCU ,  E_ADDRESS ,  E_RELATION ,  EMP_ID ,  NATIONALITY ,  MARITAL_STATUS ,  ADM_DATE ,  CAMPUS_ID ,  STD_BIRTH_OR_NID_NO ,  FATHER_NID_NO ,  MOTHER_NID_NO")->where(['ID' => $request->student_id])->first();


	        $studentTransfer = new O_StudentTransfer();

	        $studentTransfer->O_BATCH_ID = $student->batch_id;
	        $studentTransfer->O_SHIFT_ID = $student->shift_id;
	        $studentTransfer->O_GROUP_ID = $student->group_id;
	        $studentTransfer->O_CAMPUS_ID = $student->campus_id;
	        $studentTransfer->OLD_ROLL = $student->roll_no;

	        $studentTransfer->STD_ID = $student->id;
	        $studentTransfer->DEPARTMENT_ID = $student->department_id;

	        $studentTransfer->N_BATCH_ID = $request->batch_id;
	        $studentTransfer->N_SHIFT_ID = $request->shift_id;
	        $studentTransfer->N_GROUP_ID = $request->group_id;
	        $studentTransfer->N_CAMPUS_ID = $request->campus_id;
	        $studentTransfer->N_ROLL = $newRoll;

	        $studentTransfer->EMP_ID = $employee->id;
	        $studentTransfer->DATE_ = date('Y-m-d');
	        $studentTransfer->save();



	        $student->DEPARTMENT_ID = $request->department_id;
	        $student->BATCH_ID = $request->batch_id;
	        $student->SHIFT_ID = $request->shift_id;
	        $student->GROUP_ID = $request->group_id;
	        $student->CAMPUS_ID = $request->campus_id;

	        $student->ROLL_NO = $newRoll;
	        $student->REG_CODE = $new_reg_code;
	        $student->save();

	        \DB::commit();

         	return $student;

        } catch (\Exception $e) {
            dump(\Log::error(print_r($e->getMessage(), true)));
        	\DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }
}
