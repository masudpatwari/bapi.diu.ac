<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class M_SHORT_EVENT_LOG extends Model
{
    public $timestamps = false;
    protected $table = 'RMS_SHORT_EVENT_LOG';
    protected $connection = 'mysql';
    protected $fillable = ['uid', 'event_type', 'message', 'time'];

    public static function add_warning_log($message)
    {
        static::add_entry('warning', $message);
    }

    private static function add_entry($event_type, $message = '')
    {
        static::create([
            'event_type' => $event_type,
            'message' => $message,
            'uid' => session('user.id'),
            'time' => time()
        ]);
    }



    public static function addtrace_url_log($message)
    {
        static::add_entry('trace url', $message);
    }

    public static function add_success_log($message)
    {
        static::add_entry('success', $message);
    }

    public static function add_error_log($message)
    {
        static::add_entry('error', $message);
    }

    public static function add_info_log($message)
    {
        static::add_entry('info', $message);
    }

    public static function add_danger_log($message)
    {
        static::add_entry('danger', $message);
    }

}
