<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'roll_no' => $this->roll_no,
            'reg_code' => $this->reg_code,
            'department_id' => $this->department_id,
            'batch_id' => $this->batch_id,
            'shift_id' => $this->shift_id,
            'year' => $this->year,
            'reg_sl_no' => $this->reg_sl_no,
            'group_id' => $this->group_id,
            'blood_group' => $this->blood_group,
            'email' => $this->email,
            'phone_no' => $this->phone_no,
            'adm_frm_sl' => $this->adm_frm_sl,
            'religion_id' => $this->religion_id,
            'gender' => $this->gender,
            'dob' => date('Y-m-d', strtotime($this->dob)),
            'birth_place' => $this->birth_place,
            'fg_monthly_income' => $this->fg_monthly_income,
            'parmanent_add' => $this->parmanent_add,
            'mailing_add' => $this->mailing_add,
            'f_name' => $this->f_name,
            'f_cellno' => $this->f_cellno,
            'f_occu' => $this->f_occu,
            'm_name' => $this->m_name,
            'm_cellno' => $this->m_cellno,
            'm_occu' => $this->m_occu,
            'g_name' => $this->g_name,
            'g_cellno' => $this->g_cellno,
            'g_occu' => $this->g_occu,
            'e_name' => $this->e_name,
            'e_cellno' => $this->e_cellno,
            'e_occu' => $this->e_occu,
            'e_address' => $this->e_address,
            'e_relation' => $this->e_relation,
            'emp_id' => $this->emp_id,
            'nationality' => $this->nationality ? Str::title($this->nationality) : null,
            'marital_status' => $this->marital_status,
            'verified' => $this->verified,
            'id_card_given' => $this->id_card_given,
            'id_given_date' => $this->id_given_date,
            'id_receiver' => $this->id_receiver,
            'adm_date' => $this->adm_date,
            'campus_id' => $this->campus_id,
            'std_birth_or_nid_no' => $this->std_birth_or_nid_no,
            'father_nid_no' => $this->father_nid_no,
            'mother_nid_no' => $this->mother_nid_no,

            'e_exam_name1' => $this->e_exam_name1,
            'e_group1' => $this->e_group1,
            'e_group1' => $this->e_group1,
            'e_roll_no_1' => $this->e_roll_no_1,
            'e_passing_year1' => $this->e_passing_year1,
            'e_ltr_grd_tmark1' => $this->e_ltr_grd_tmark1,
            'e_div_cls_cgpa1' => $this->e_div_cls_cgpa1,
            'e_board_university1' => $this->e_board_university1,

            'e_exam_name2' => $this->e_exam_name2,
            'e_group2' => $this->e_group2,
            'e_group2' => $this->e_group2,
            'e_roll_no_2' => $this->e_roll_no_2,
            'e_passing_year2' => $this->e_passing_year2,
            'e_ltr_grd_tmark2' => $this->e_ltr_grd_tmark2,
            'e_div_cls_cgpa2' => $this->e_div_cls_cgpa2,
            'e_board_university2' => $this->e_board_university2,

            'e_exam_name3' => $this->e_exam_name3,
            'e_group3' => $this->e_group3,
            'e_group3' => $this->e_group3,
            'e_roll_no_3' => $this->e_roll_no_3,
            'e_passing_year3' => $this->e_passing_year3,
            'e_ltr_grd_tmark3' => $this->e_ltr_grd_tmark3,
            'e_div_cls_cgpa3' => $this->e_div_cls_cgpa3,
            'e_board_university3' => $this->e_board_university3,

            'e_exam_name4' => $this->e_exam_name4,
            'e_group4' => $this->e_group4,
            'e_group4' => $this->e_group4,
            'e_roll_no_4' => $this->e_roll_no_4,
            'e_passing_year4' => $this->e_passing_year4,
            'e_ltr_grd_tmark4' => $this->e_ltr_grd_tmark4,
            'e_div_cls_cgpa4' => $this->e_div_cls_cgpa4,
            'e_board_university4' => $this->e_board_university4,

            'reg_card_sl' => $this->reg_card_sl,
            'session_name' => $this->session_name,
            'cgpa' => $this->cgpa,
            'actual_fee' => $this->actual_fee,
            'no_of_semester' => $this->no_of_semester,
            'refereed_by_parent_id' => $this->refereed_by_parent_id,
            'refe_by_std_type' => $this->refe_by_std_type,
            'ref_val' => $this->ref_val,
            'payment_from_semester' => $this->payment_from_semester,
            'adm_season' => $this->adm_season,
            'department' => $this->department,
            'batch' => $this->batch,
            'rel_campus' => $this->rel_campus,
            'shift' => $this->shift,
            'employee' => $this->employee,
            'group' => $this->group,
            'religion' => $this->religion,
            'refereed_by_parent' => $this->refereed_by_parent,
        ];
    }
}
