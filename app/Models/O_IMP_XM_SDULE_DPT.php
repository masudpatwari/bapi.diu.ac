<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class O_IMP_XM_SDULE_DPT extends Model
{
    public $timestamps = false;
    protected $table = "IMP_XM_SDULE_DPT";
    protected $connection = 'oracle';

    protected $fillable = ['IES_ID', 'DEPARTMENT_ID', 'CREATED_BY', 'CREATED_AT', 'UPDATED_AT'];

    public function relExamSchedule()
    {
        return $this->belongsTo(O_IMP_EXAM_SCHEDULE::class,'ies_id', 'id');
    }

    public function relDepartment()
    {
        return $this->belongsTo(O_DEPARTMENTS::class,'department_id', 'id');
    }

    public function relCreateBy()
    {
        return $this->belongsTo(M_WP_EMP::class, 'create_by', 'id');
    }
}
