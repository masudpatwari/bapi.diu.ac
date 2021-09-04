<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class O_IMP_COURSE_TEACHER_ASSING extends Model
{
    const NOTAPPROVE = 0;
    const DENY = 1;
    const APPROVED = 2;
    const MARKS_FINAL_SUBMIT = 1;

    public $timestamps = false;
    protected $table = "IMP_COURSE_TEACHER_ASSING";
    protected $connection = 'oracle';

    protected $fillable = ['COURSE_ID', 'CAMPUS_ID', 'APPROVE_STATUS', 'ASSIGN_TEACHER_ID', 'IES_ID', 'APPROVE_BY', 'ASSIGNED_BY', 'CREATED_AT', 'UPDATED_AT', 'UPDATED_BY', 'TYPE', 'MARKS_FINAL_SUBMITTED', 'DEPARTMENT_ID'];

    public function isApproved(){
        $this->approve_status == self::APPROVED;
    }

    public function getSelectedTeacherAttribute()
    {
        return $this->campus_id . '' . $this->course_id . '' . $this->assign_teacher_id;
    }

    public function relCourse()
    {
        return $this->belongsTo(O_COURSE::class, 'course_id', 'id');
    }

    public function relCampus()
    {
        return $this->belongsTo(O_CAMPUS::class, 'campus_id', 'id');
    }

    public function relAssignTeacher()
    {
        return $this->belongsTo(M_WP_EMP::class, 'assign_teacher_id', 'id');
    }

    public function relImpExamSchedule()
    {
        return $this->belongsTo(O_IMP_EXAM_SCHEDULE::class,'ies_id', 'id');
    }

    public function relApproveBy()
    {
        return $this->belongsTo(M_WP_EMP::class, 'approve_by', 'id');
    }

    public function relAssignBy()
    {
        return $this->belongsTo(M_WP_EMP::class, 'assigned_by', 'id');
    }

    public function relUpdatedBy()
    {
        return $this->belongsTo(M_WP_EMP::class, 'updated_by', 'id');
    }

    public function relImpMarksDraft()
    {
        return $this->hasMany(O_IMP_MARKS_DRAFT::class, 'icta_id', 'id');
    }
}
