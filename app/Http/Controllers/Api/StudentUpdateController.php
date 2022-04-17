<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\StudentResource;
use App\Models\O_STUDENT;
use App\Models\O_BATCH;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentUpdateController extends Controller
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
            'id' => 'required|integer',
            'name' => 'required|string|max:80',
            'department_id' => 'required|integer',
            'batch_id' => 'required|integer',
            'shift_id' => 'required|integer',
            'year' => 'required|integer',
            'group_id' => 'required|integer',
            'blood_group' => 'required|max:4',
            'email' => 'required|email',
            'phone_no' => 'required|max:15',
            'adm_frm_sl' => 'required|max:20',
            'religion_id' => 'required|integer',
            'gender' => 'required|max:1',
            'dob' => 'required',
            'birth_place' => 'nullable',
            'fg_monthly_income' => 'nullable|numeric',
            'parmanent_add' => 'required|string|max:200',
            'mailing_add' => 'required|string|max:100',
            'f_name' => 'required|string|max:80',
            'f_cellno' => 'required|string|max:15',
            'f_occu' => 'nullable|string|max:30',
            'father_nid_no' => 'required|max:50',
            'm_name' => 'required|string|max:80',
            'm_cellno' => 'nullable|max:15',
            'm_occu' => 'nullable|string|max:30',
            'mother_nid_no' => 'nullable|max:50',
            'g_name' => 'nullable|string|max:30',
            'g_cellno' => 'nullable|max:15',
            'g_occu' => 'nullable|string|max:30',
            'e_name' => 'required|string|max:30',
            'e_cellno' => 'required|max:15',
            'e_occu' => 'nullable|string|max:30',
            'e_relation' => 'nullable|string|max:20',
            'emp_id' => 'required|integer',
            'nationality' => 'required|max:30',
            'marital_status' => 'required|max:20',
            'std_birth_or_nid_no' => 'nullable|max:50',
            'adm_season' => 'required|integer',
            'e_exam_name1' => 'required|string|max:40',
            'e_group1' => 'required|string|max:20',
            'e_roll_no_1' => 'required|max:10',
            'e_passing_year1' => 'required|max:4',
            'e_ltr_grd_tmark1' => 'required|max:10',
            'e_div_cls_cgpa1' => 'required|max:10',
            'e_board_university1' => 'required|max:50',
            'e_exam_name2' => 'required|string|max:40',
            'e_group2' => 'required|string|max:20',
            'e_roll_no_2' => 'required|max:10',
            'e_passing_year2' => 'required|max:4',
            'e_ltr_grd_tmark2' => 'required|max:10',
            'e_div_cls_cgpa2' => 'required|max:10',
            'e_board_university2' => 'required|max:50',
            'e_exam_name3' => 'nullable|string|max:40',
            'e_group3' => 'nullable|string|max:20',
            'e_roll_no_3' => 'nullable|max:10',
            'e_passing_year3' => 'nullable|max:4',
            'e_ltr_grd_tmark3' => 'nullable|max:10',
            'e_div_cls_cgpa3' => 'nullable|max:10',
            'e_board_university3' => 'nullable|max:50',
            'e_exam_name4' => 'nullable|string|max:40',
            'e_group4' => 'nullable|string|max:20',
            'e_roll_no_4' => 'nullable|max:10',
            'e_passing_year4' => 'nullable|max:4',
            'e_ltr_grd_tmark4' => 'nullable|max:10',
            'e_div_cls_cgpa4' => 'nullable|max:10',
            'e_board_university4' => 'nullable|max:50',
            'refereed_by_parent_id' => 'nullable|integer',
            'refe_by_std_type' => 'nullable|max:50',
            'ref_val' => 'nullable|max:50',
        ]);

        
        $batch = O_BATCH::find($request->batch_id);

        $student = O_STUDENT::where('ID',$request->id)->first();
        unset($student['image']);

        $student->NAME = trim($request->name);
        $student->DEPARTMENT_ID = $request->department_id;
        $student->BATCH_ID = $request->batch_id;
        $student->SHIFT_ID = $request->shift_id;
        $student->BATCH_ID = $request->batch_id;
        $student->GROUP_ID = $request->group_id;
        $student->BLOOD_GROUP = $request->blood_group;
        $student->EMAIL = $request->email;
        $student->PHONE_NO = trim($request->phone_no);
        $student->ADM_FRM_SL = trim($request->adm_frm_sl);
        $student->ADM_SEASON = trim($request->adm_season);
        $student->RELIGION_ID = $request->religion_id;
        $student->GENDER = $request->gender;
        $student->DOB = date('Y-m-d', strtotime($request->dob));
        $student->BIRTH_PLACE = $request->birth_place;
        $student->FG_MONTHLY_INCOME = $request->fg_monthly_income;
        $student->PARMANENT_ADD = $request->parmanent_add;
        $student->MAILING_ADD = $request->mailing_add;
        $student->F_NAME = $request->f_name;
        $student->F_CELLNO = $request->f_cellno;
        $student->F_OCCU = $request->f_occu;
        $student->M_NAME = $request->m_name;
        $student->M_CELLNO = $request->m_cellno;
        $student->M_OCCU = $request->m_occu;
        $student->G_NAME = $request->g_name;
        $student->G_CELLNO = $request->g_cellno;
        $student->G_OCCU = $request->g_occu;
        $student->E_NAME = $request->e_name;
        $student->E_CELLNO = $request->e_cellno;
        $student->E_OCCU = $request->e_occu;
        $student->E_RELATION = $request->e_relation;
        $student->NATIONALITY = $request->nationality;
        $student->MARITAL_STATUS = $request->marital_status;
        $student->YEAR = $batch->adm_year;
        $student->CAMPUS_ID = $batch->campus_id;
        $student->STD_BIRTH_OR_NID_NO = $request->std_birth_or_nid_no;
        $student->FATHER_NID_NO = $request->father_nid_no;
        $student->MOTHER_NID_NO = $request->mother_nid_no;
        $student->E_EXAM_NAME1 = $request->e_exam_name1;
        $student->E_GROUP1 = $request->e_group1;
        $student->E_ROLL_NO_1 = $request->e_roll_no_1;
        $student->E_PASSING_YEAR1 = $request->e_passing_year1;
        $student->E_LTR_GRD_TMARK1 = $request->e_ltr_grd_tmark1;
        $student->E_DIV_CLS_CGPA1 = $request->e_div_cls_cgpa1;
        $student->E_BOARD_UNIVERSITY1 = $request->e_board_university1;
        $student->E_EXAM_NAME2 = $request->e_exam_name2;
        $student->E_GROUP2 = $request->e_group2;
        $student->E_ROLL_NO_2 = $request->e_roll_no_2;
        $student->E_PASSING_YEAR2 = $request->e_passing_year2;
        $student->E_LTR_GRD_TMARK2 = $request->e_ltr_grd_tmark2;
        $student->E_DIV_CLS_CGPA2 = $request->e_div_cls_cgpa2;
        $student->E_BOARD_UNIVERSITY2 = $request->e_board_university2;
        $student->E_EXAM_NAME3 = $request->e_exam_name3;
        $student->E_GROUP3 = $request->e_group3;
        $student->E_ROLL_NO_3 = $request->e_roll_no_3;
        $student->E_PASSING_YEAR3 = $request->e_passing_year3;
        $student->E_LTR_GRD_TMARK3 = $request->e_ltr_grd_tmark3;
        $student->E_DIV_CLS_CGPA3 = $request->e_div_cls_cgpa3;
        $student->E_BOARD_UNIVERSITY3 = $request->e_board_university3;
        $student->E_EXAM_NAME4 = $request->e_exam_name4;
        $student->E_GROUP4 = $request->e_group4;
        $student->E_ROLL_NO_4 = $request->e_roll_no_4;
        $student->E_PASSING_YEAR4 = $request->e_passing_year4;
        $student->E_LTR_GRD_TMARK4 = $request->e_ltr_grd_tmark4;
        $student->E_DIV_CLS_CGPA4 = $request->e_div_cls_cgpa4;
        $student->E_BOARD_UNIVERSITY4 = $request->e_board_university4;
        $student->SESSION_NAME = trim($batch->sess);
        $student->ACTUAL_FEE = $batch->said_fee - $batch->common_scholarship;
        $student->NO_OF_SEMESTER = $batch->no_of_semester;
        $student->REFEREED_BY_PARENT_ID = $request->refereed_by_parent_id;
        $student->REFE_BY_STD_TYPE = $request->refe_by_std_type;
        $student->REF_VAL = $request->ref_val;
        $student->save();

        return response()->json($student, 200);

    }
}
