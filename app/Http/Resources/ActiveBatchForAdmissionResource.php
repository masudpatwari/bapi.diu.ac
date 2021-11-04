<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ActiveBatchForAdmissionResource extends JsonResource
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
            'department_id' => $this->department_id,
            'group_id' => $this->group_id,
            'shift_id' => $this->shift_id,
            'campus_id' => $this->campus_id,
            'said_fee' => $this->said_fee,
            'common_scholarship' => $this->common_scholarship,
            'no_of_semester' => $this->no_of_semester,
            'duration_of_sem_m' => $this->duration_of_sem_m,
            'no_seat' => $this->no_seat,
            'sess' => $this->sess,
            'year' => $this->year ?? 'N/A',
            'adm_season' => $this->adm_season ?? 'N/A',
            'valid_d_idcard' => $this->valid_d_idcard ? Carbon::parse($this->valid_d_idcard)->format('d M, Y') : '-',
            'active_status' => $this->active_status,
            'class_str_date' => $this->class_str_date ? Carbon::parse($this->class_str_date)->format('d M, Y') : '-',
            'last_date_of_adm' => $this->last_date_of_adm ? Carbon::parse($this->last_date_of_adm)->format('d M, Y') : '-',
            'batch_name' => $this->batch_name,
            'admission_start_date' => $this->admission_start_date ? Carbon::parse($this->admission_start_date)->format('d M, Y') : '-',
            'rel_department' => $this->relDepartment,
            'campus' => $this->campus,
            'shift' => $this->relShift,
            'group' => $this->group,
            'active_students_count' => $this->active_students_count,
            'un_verified_Students' => $this->un_verified_students_count,
            'paymemtSystem' => $this->paymemtSystem,
        ];
    }
}
