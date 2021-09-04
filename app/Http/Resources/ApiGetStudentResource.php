<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiGetStudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id . '-'.  $this->roll_no * 123 . '-' . md5($this->id . '-'. $this->roll_no),
//            'name' => $this->name,
            'reg_code'=> $this->reg_code
        ];
    }
}
