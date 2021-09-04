<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class O_RELIGION extends Model
{
    public $timestamps = false;
    protected $table = "RELIGION";
    protected $connection = 'oracle';
}
