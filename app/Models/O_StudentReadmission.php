<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class O_StudentReadmission extends Model
{
    public $timestamps = false;
    protected $table = "STUDENT_READMISSION";
    protected $connection = 'oracle';

    public function employee()
    {
        return $this->belongsTo(O_EMP::class, 'emp_id', 'id');
    }
}
