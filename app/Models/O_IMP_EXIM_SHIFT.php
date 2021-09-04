<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class O_IMP_EXIM_SHIFT extends Model
{
    const NOTAPPROVE = 0;
    const DENY = 1;
    const APPROVED = 2;

    public $timestamps = false;
    protected $table = "IMP_EXIM_SHIFT";
    protected $connection = 'oracle';

    protected  $with = ['relImpExamShiftDetail'];

    protected $fillable = ['IES_ID', 'APPROVE_STATUS', 'APPROVE_BY', 'CREATED_BY', 'CREATED_AT', 'UPDATED_AT', 'UPDATED_BY', 'NO_OF_SHIFT'];

    public function isApproved(){
        return $this->approve_status == self::APPROVED;
    }
    public function isDenied(){
        return $this->approve_status == self::DENY;
    }
    public function isNotApproved(){
        return $this->approve_status != self::APPROVED;
    }

    public function relImpExamShiftDetail()
    {
        return $this->hasMany(O_IMP_EXIM_SHIFT_DETAIL::class,'imp_exim_shift_id', 'id');
    }

    public function relImpExamSchedule()
    {
        return $this->belongsTo(O_IMP_EXAM_SCHEDULE::class,'ies_id', 'id');
    }

    public function relApproveBy()
    {
        return $this->belongsTo(M_WP_EMP::class, 'approve_by', 'id');
    }

    public function relCreatedBy()
    {
        return $this->belongsTo(M_WP_EMP::class, 'created_by', 'id');
    }

    public function relUpdatedBy()
    {
        return $this->belongsTo(M_WP_EMP::class, 'updated_by', 'id');
    }

}
