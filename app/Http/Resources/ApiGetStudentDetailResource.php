<?php

namespace App\Http\Resources;

use App\Models\O_CASHIN;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiGetStudentDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $summery = '';
        try{
            $summery = O_CASHIN::get_student_account_info_summary( $this->id );
        }
        catch (\Exception $exception){
            Log::error($exception->getMessage() . $exception->getTraceAsString());
            return response()->json(['error'=> $exception->getMessage()], 400);
        }

        try{
            return [
                'reg_code'=> $this->reg_code,
                'name' => $this->name,
                'department'=> $this->department->name??'Not Found',
                'batch'=> $this->batch->batch_name??'Not Found',
                'roll_no' =>$this->roll_no,
                'due_amount' => $summery['summary']['total_due']??'N/A',
                'id' => $this->id,
            ];
        }
        catch (\Exception $exception){
            Log::error($exception->getMessage() . $exception->getTraceAsString());
            return response()->json(['error'=> $exception->getMessage()], 400);
        }
    }
}
