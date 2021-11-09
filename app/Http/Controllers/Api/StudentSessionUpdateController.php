<?php

namespace App\Http\Controllers\Api;

use App\Models\O_SESSION;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentSessionUpdateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        // dump(\Log::error(print_r($request->all(),true)));


        $this->validate($request, [
            'name' => 'required|string|max:15'
        ]);

        $student_session =  O_SESSION::find($request->id);
        $student_session->NAME = trim($request->name);
        $student_session->save();

        return response()->json($student_session, 200);
    }
}
