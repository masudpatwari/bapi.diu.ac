<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class O_CAMPUS extends Model
{
    public $timestamps = false;
    protected $table = "CAMPUS";
    protected $connection = 'oracle';
}
