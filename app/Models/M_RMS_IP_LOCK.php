<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_RMS_IP_LOCK extends Model
{
    protected $table = 'rms_ip_lock';
    protected $fillable = ['ip', 'detail', 'time', 'detail', 'lock_unlock'];

}
