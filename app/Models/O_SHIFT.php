<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class O_SHIFT extends Model
{
    public $timestamps = false;
    protected $table = "SHIFT";
    protected $connection = 'oracle';
}
