<?php

namespace App\Http\Controllers\Api;

use App\Models\O_BATCH;
use App\Models\O_EMP;
use App\Models\O_STUDENT;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BatchStoreController extends Controller
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
            'department_id' => 'required|integer',
            'group_id' => 'required|integer',
            'shift_id' => 'required|integer',
            'campus_id' => 'required|integer',
            'said_fee' => 'required|numeric',
            'batch_name' => 'required',
            'common_scholarship' => 'required|numeric',
            'number_of_semester' => 'required|integer',
            'duration_of_semester' => 'required|integer',
            'no_of_seat' => 'required|integer',
            'year' => 'required|integer',
            'session' => 'required',
            'admission_season' => 'required|integer',
            'active' => 'required|in:0,1',
            'id_card_expiration_date' => 'required|date',
            'class_start_date' => 'required|date',
            'last_data_of_admission' => 'required|date',
            'payment_system' => 'required|integer',
            'admission_start_date' => 'required|date',
            'created_by_email' => 'required|email',
        ]);

        $employee = O_EMP::selectRaw("ID,OFFICIAL_EMAIL,EMP_NAME")
                    ->where('OFFICIAL_EMAIL', $request->created_by_email)
                    ->first();


        $batch = new O_BATCH();

        $batch->DEPARTMENT_ID = $request->department_id;
        $batch->GROUP_ID = $request->group_id;
        $batch->SHIFT_ID = $request->shift_id;
        $batch->CAMPUS_ID = $request->campus_id;
        $batch->SAID_FEE = $request->said_fee;
        $batch->COMMON_SCHOLARSHIP = $request->common_scholarship;
        $batch->NO_OF_SEMESTER = $request->number_of_semester;
        $batch->DURATION_OF_SEM_M = $request->duration_of_semester;

        $batch->NO_SEAT  = $request->no_of_seat;
        $batch->SESS  = trim($request->session);

        $batch->VALID_D_IDCARD  = date('Y/m/d', strtotime($request->id_card_expiration_date));
        $batch->ACTIVE_STATUS  = $request->active;

        $batch->CLASS_STR_DATE  = date('Y/m/d', strtotime($request->class_start_date));
        $batch->CREATOR_ID  = $employee->id;
        $batch->LAST_DATE_OF_ADM  = date('Y/m/d', strtotime($request->last_data_of_admission));
        $batch->BATCH_NAME  = $request->batch_name;
        $batch->PAYMENT_SYSTEM_ID  = $request->payment_system;
        $batch->ADMISSION_START_DATE  = date('Y/m/d', strtotime($request->admission_start_date));
        $batch->ADM_YEAR  = $request->year;
        $batch->ADM_SEASON  = $request->admission_season;
        $batch->save();


        return response()->json($batch, 200);

    }
}
