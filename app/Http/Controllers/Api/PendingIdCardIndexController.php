<?php

namespace App\Http\Controllers\Api;

use App\Models\O_STUDENT;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PendingIdCardIndexController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $students = O_STUDENT::selectRaw("ID,REG_CODE,ROLL_NO,NAME,VERIFIED")
            ->where('ID_CARD_GIVEN','!=','1')
            ->where('verified',1)
            ->get();

        return $students;
    }
}
