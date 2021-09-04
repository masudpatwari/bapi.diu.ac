<?php

namespace App\Http\Resources;

use App\Models\O_DEPARTMENTS;
use Illuminate\Http\Resources\Json\JsonResource;

class currentImprovementExamScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $deparmentIdArray = $this->relXMSduleDPT->pluck('department_id')->toArray();
        $deparmentNameArray = O_DEPARTMENTS::select('id', 'name')->whereIn('id', $deparmentIdArray)->pluck('name','id')->toArray();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'exam_start_date' => $this->exam_start_date,
            'form_fillup_last_date' => $this->form_fillup_last_date,
            'departmentNames' => $deparmentNameArray
        ];
    }
}
