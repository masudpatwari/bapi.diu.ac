<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_RMS_USER_ACCESS_DURATION extends Model
{
    public $timestamps = false;
    protected $table = 'RMS_USER_ACCESS_DURATION';
    protected $connection = 'mysql';
    protected $fillable = ['uid', 'login_time', 'last_access_time'];

    public static function insert_login_info()
    {
        // get session data for user
        $user_id = session('user.id');
        $data = [
            'uid' => $user_id,
            'login_time' => time(),
            'last_access_time' => time()
        ];

        static::create($data);
    }
}
