<?php

namespace App\Http\Controllers\Api;

use App\Models\O_EMP;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActiveBatchForAdmissionResource;

class CmsEmployeeSyncToErpController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        // dump(\Log::error(print_r($request->rel_attendance_ids['att_data_id'],true)));
        

        $emp = O_EMP::where('OFFICIAL_EMAIL',trim($request->office_email))->first();



        if (!$emp) {

           $o_emp = new O_EMP();
           $o_emp->emp_name = $request->name;
           $o_emp->position_id = $request->designation_id;
           $o_emp->department_id = $request->department_id;
           $o_emp->date_of_birth = date('Y-m-d', $request->date_of_birth);
           $o_emp->date_of_join = date('Y-m-d', $request->date_of_join);
           $o_emp->campus_id = $request->campus_id;
           $o_emp->official_email = $request->office_email;
           $o_emp->personal_email = $request->private_email;
           $o_emp->office_address = $request->office_address;
           $o_emp->office_phone = $request->office_phone_no;
           $o_emp->home_phone = $request->home_phone_no;
           $o_emp->mobile_no_1 = $request->personal_phone_no;
           $o_emp->mobile_no_2 = $request->alternative_phone_no;
           $o_emp->spous_mobile_no = $request->spous_phone_no;
           $o_emp->parents_mobile_no = $request->parents_phone_no;
           $o_emp->others_mobile_no = $request->other_phone_no;
           $o_emp->merit = $request->merit;
           $o_emp->activestatus = $request->activestatus;
           $o_emp->empshort_position_id = $request->shortPosition_id;
           $o_emp->idno = $request->id;
           $o_emp->preidno = $request->rel_attendance_ids['att_card_id'];
           $o_emp->attendance_id = $request->rel_attendance_ids['att_data_id'];
           $o_emp->save();

           return response()->json(['message' => 'Employee sync to ERP successfully'], 200);

        }

        
        return response()->json(['message' => 'Employee already sync to ERP'], 202);
    }
}
