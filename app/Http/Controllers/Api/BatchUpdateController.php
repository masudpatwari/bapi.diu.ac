<?php

namespace App\Http\Controllers\Api;

use App\Models\O_BATCH;
use App\Models\O_EMP;
use App\Models\O_STUDENT;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BatchUpdateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

    	// dump(\Log::error(print_r($request->all(),true)));

        $this->validate($request, [
            'id' => 'required|integer',
            'department_id' => 'required|integer',
            'group_id' => 'required|integer',
            'shift_id' => 'required|integer',
            'campus_id' => 'required|integer',
            'said_fee' => 'required|numeric',
            'common_scholarship' => 'required|numeric',
            'no_of_semester' => 'required|integer',
            'duration_of_sem_m' => 'required|integer',
            'no_seat' => 'required|integer',
            'sess' => 'required',
            'valid_d_idcard' => 'required|date',
            'active_status' => 'required|integer',
            'class_str_date' => 'required|date',
            'last_date_of_adm' => 'required|date',
            'batch_name' => 'required',
            'payment_system_id' => 'required|integer',
            'admission_start_date' => 'required|date',
            'adm_year' => 'required',
            'adm_season' => 'required|integer',
        ]);


        $batch = O_BATCH::find($request->id);

        $batch->DEPARTMENT_ID = $request->department_id;
        $batch->GROUP_ID = $request->group_id;
        $batch->SHIFT_ID = $request->shift_id;
        $batch->CAMPUS_ID = $request->campus_id;
        $batch->SAID_FEE = $request->said_fee;
        $batch->COMMON_SCHOLARSHIP = $request->common_scholarship;
        $batch->NO_OF_SEMESTER = $request->no_of_semester;
        $batch->DURATION_OF_SEM_M = $request->duration_of_sem_m;

        $batch->NO_SEAT  = $request->no_seat;
        $batch->SESS  = trim($request->sess);

        $batch->VALID_D_IDCARD  = $request->valid_d_idcard ? date('Y/m/d', strtotime($request->valid_d_idcard)) : null;

        $batch->ACTIVE_STATUS  = $request->active_status;

        $batch->CLASS_STR_DATE  = $request->class_str_date ? date('Y/m/d', strtotime($request->class_str_date)) : null;
        
        $batch->LAST_DATE_OF_ADM  = $request->last_date_of_adm ? date('Y/m/d', strtotime($request->last_date_of_adm)) : null;

        $batch->BATCH_NAME  = $request->batch_name;
        $batch->PAYMENT_SYSTEM_ID  = $request->payment_system_id;
        $batch->ADMISSION_START_DATE  = $request->admission_start_date ? date('Y/m/d', strtotime($request->admission_start_date)) : null;
        $batch->ADM_YEAR  = $request->adm_year;
        $batch->ADM_SEASON  = $request->adm_season;
        $batch->save();


        return response()->json($batch, 200);

    }
}
