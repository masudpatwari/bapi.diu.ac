<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class O_CREDIT_TRANSFER extends Model
{
	const APPROVE = 3;
    const DENY = 2;
    const FINAL = 1;
    const DRAFT = 0;

    public $timestamps = false;
    protected $table = "CREDIT_TRANSFER";
    protected $connection = 'oracle';
    protected $fillable = ['student_id', 'teacher_id', 'approve_status', 'created_at', 'created_by'];

    public function relCreditTransferDetails()
    {
        return $this->hasMany(O_CREDIT_TRANSFER_DETAIL::class, 'credit_transfer_id', 'id');
    }

    public function relTeacher()
    {
        return $this->belongsTo(\App\Models\M_WP_EMP::class, 'teacher_id', 'id');
    }

    public function relStudent()
    {
        return $this->belongsTo(O_STUDENT::class, 'student_id', 'id');
    }
}
