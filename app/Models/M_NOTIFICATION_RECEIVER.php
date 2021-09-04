<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_NOTIFICATION_RECEIVER extends Model
{
  public $timestamps = false;
  protected $table = "rms_notification_receiver";
  protected $connection = 'mysql';

  public function get_notifications(){
      return $this->belongsTo( M_NOTIFICATION::class,'rms_notification_id','id');
  }

  public static function markAsRead($receiver_id, $notification_receiver_id)
  {
     return self::where('receiver_id', $receiver_id )
      ->where('id', $notification_receiver_id)
      ->update([
          'read_datetime' => time()
      ]);
  }
}
