<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchShowResource extends JsonResource
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
            'valid_d_idcard' => $this->valid_d_idcard ? date('Y-m-d', strtotime($this->valid_d_idcard)) : null,
            'active_status' => $this->active_status,
            'class_str_date' => $this->class_str_date ? date('Y-m-d', strtotime($this->class_str_date)) : null,
            'last_date_of_adm' => $this->last_date_of_adm ? date('Y-m-d', strtotime($this->last_date_of_adm)) : null,
            'batch_name' => $this->batch_name,
            'payment_system_id' => $this->payment_system_id,
            'admission_start_date' => $this->admission_start_date ? date('Y-m-d', strtotime($this->admission_start_date)) : null,
            'adm_year' => $this->adm_year,
            'adm_season' => $this->adm_season,
        ];
    }
}
