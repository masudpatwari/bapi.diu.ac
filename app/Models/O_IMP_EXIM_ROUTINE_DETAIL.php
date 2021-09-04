<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class O_IMP_EXIM_ROUTINE_DETAIL extends Model
{

    public $timestamps = false;
    protected $table = "IMP_EXIM_ROUTINE_DETAIL";
    protected $connection = 'oracle';
    
    public function relCourse()
    {
        return $this->belongsTo(O_COURSE::class, 'course_id', 'id');
    }

    public function relShiftDetail()
    {
        return $this->hasOne(O_IMP_EXIM_SHIFT_DETAIL::class, 'id', 'imp_exim_shift_detail_id');
    }

    public function relRoutineParent()
    {
        return $this->belongsTo(O_IMP_EXIM_ROUTINE::class, 'imp_xm_routine_id', 'id');
    }

}
