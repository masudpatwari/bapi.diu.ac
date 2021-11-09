<?php

namespace App\Http\Controllers\Api;

use App\Models\O_SESSION;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentSessionStoreController extends Controller
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
            'name' => 'required|string|max:15'
        ]);

        $student_session = new O_SESSION();
        $student_session->NAME = trim($request->name);
        $student_session->save();

        return response()->json($student_session, 200);

    }
}
