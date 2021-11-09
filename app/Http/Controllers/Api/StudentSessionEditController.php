<?php

namespace App\Http\Controllers\Api;

use App\Models\O_SESSION;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentSessionEditController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request,$id)
    {
        return O_SESSION::find($id);
    }
}
