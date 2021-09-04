<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\rms_notification as rms_notification;

class M_NOTIFICATION extends Model
{
  public $timestamps = true;
  protected $table = "rms_notification";
  protected $connection = 'mysql';

    use rms_notification;

}
