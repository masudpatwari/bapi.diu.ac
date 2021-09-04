<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class O_CREDIT_TRANSFER_DETAIL extends Model
{
    public $timestamps = false;
    protected $table = "CREDIT_TRANSFER_DETAIL";
    protected $connection = 'oracle';

    
    public function relCourse()
    {
        return $this->belongsTo(O_COURSE::class,'course_id', 'id');
    }

    public function relSifr()
    {
        return $this->belongsTo(O_SEMESTERS::class,'sifr_id', 'id');
    }
}
