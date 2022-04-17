<?php

namespace App\Http\Controllers\Api;

use App\Models\O_EMP;
use App\Models\O_STUDENT;
use App\Models\O_StudentReadmission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class StudentReadmissionStoreController extends Controller
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
            'student_id' => ['required', 'integer'],
            'batch_id' => ['required', 'integer'],
            'shift_id' => ['required', 'integer'],
            'group_id' => ['required', 'integer'],
            'campus_id' => ['required', 'integer'],
            'roll' => ['required', 'integer'],
            'created_by_email' => ['required', 'email'],
        ]);


		$employee = O_EMP::selectRaw("ID,OFFICIAL_EMAIL,EMP_NAME")
            ->where('OFFICIAL_EMAIL', $request->created_by_email)
            ->first();


		try {

			\DB::beginTransaction();

	        $student = O_STUDENT::selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ,  PASSWORD ,  DEPARTMENT_ID ,  BATCH_ID ,  SHIFT_ID ,  YEAR ,  REG_SL_NO ,  GROUP_ID ,  BLOOD_GROUP ,  EMAIL ,  PHONE_NO ,  ADM_FRM_SL ,  RELIGION_ID ,  GENDER ,  DOB ,  BIRTH_PLACE ,  FG_MONTHLY_INCOME ,  PARMANENT_ADD ,  MAILING_ADD ,  F_NAME ,  F_CELLNO ,  F_OCCU ,  M_NAME ,  M_CELLNO ,  M_OCCU ,  G_NAME ,  G_CELLNO ,  G_OCCU ,  E_NAME ,  E_CELLNO ,  E_OCCU ,  E_ADDRESS ,  E_RELATION ,  EMP_ID ,  NATIONALITY ,  MARITAL_STATUS ,  ADM_DATE ,  CAMPUS_ID ,  STD_BIRTH_OR_NID_NO ,  FATHER_NID_NO ,  MOTHER_NID_NO")->where(['ID' => $request->student_id])->first();


	        $studentReAdmission = new O_StudentReadmission();

	        $studentReAdmission->O_BATCH_ID = $student->batch_id;
	        $studentReAdmission->O_SHIFT_ID = $student->shift_id;
	        $studentReAdmission->O_GROUP_ID = $student->group_id;
	        $studentReAdmission->O_CAMPUS_ID = $student->campus_id;
	        $studentReAdmission->OLD_ROLL = $student->roll_no;
	        $studentReAdmission->STD_ID = $student->id;
	        $studentReAdmission->DEPARTMENT_ID = $student->department_id;

	        $studentReAdmission->N_BATCH_ID = $request->batch_id;
	        $studentReAdmission->N_SHIFT_ID = $request->shift_id;
	        $studentReAdmission->N_GROUP_ID = $request->group_id;
	        $studentReAdmission->N_CAMPUS_ID = $request->campus_id;
	        $studentReAdmission->N_ROLL = $request->roll;

	        $studentReAdmission->EMP_ID = $employee->id;
	        $studentReAdmission->DATE_ = date('Y-m-d');
	        $studentReAdmission->save();

	        $student->BATCH_ID = $request->batch_id;
	        $student->SHIFT_ID = $request->shift_id;
	        $student->GROUP_ID = $request->group_id;
	        $student->CAMPUS_ID = $request->campus_id;
	        $student->ROLL_NO = $request->roll;
	        $student->save();

	        \DB::commit();

         	return $student;

        } catch (\Exception $e) {
        	\DB::rollBack();
            dump(\Log::error(print_r($e->getMessage(), true)));
            return response()->json(['error' => $e->getMessage()], 401);
        }

    

    }


}
