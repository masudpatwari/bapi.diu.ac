<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class O_IMP_EXIM_SHIFT_DETAIL extends Model
{

    public $timestamps = false;
    protected $table = "IMP_EXIM_SHIFT_DETAIL";
    protected $connection = 'oracle';

    protected $fillable = ['IMP_EXIM_SHIFT_ID', 'FROM_TIME', 'TO_TIME', 'PLACE', 'SHIFT_NO'];

    public function getToTimeAttribute($value){
        return date('h:i A', $value);
    }
    public function getFromTimeAttribute($value){
        return date('h:i A', $value);
    }
    public function relShiftParent()
    {
        return $this->belongsTo(O_IMP_EXIM_SHIFT::class, 'imp_exim_shift_id', 'id');
    }
}
