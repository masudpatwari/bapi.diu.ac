<?php

namespace App\Http\Controllers\Api;

use App\Models\O_STUDENT;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PendingIdCardUpdateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $this->validate($request, [
            'student_id' => 'required|integer',
            'receiver_id' => 'required|integer',
        ]);


    // dump(\Log::error(print_r($request->all(),true)));


        $student = O_STUDENT::selectRaw("ID,ID_CARD_GIVEN,ID_GIVEN_DATE,NAME,ID_RECEIVER")
            ->where('ID', $request->student_id)
            ->first();

        $student->ID_CARD_GIVEN = 1;
        $student->ID_GIVEN_DATE = date('Y-m-d');
        $student->ID_RECEIVER = $request->receiver_id; //campus id
        $student->save();

        return $student;
    }
}
