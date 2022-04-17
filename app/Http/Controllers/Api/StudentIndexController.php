<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\StudentResource;
use App\Models\O_STUDENT;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentIndexController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {

        $student = O_STUDENT::with('department:id,name','batch:id,batch_name','relCampus','shift:id,name','employee:id,emp_name','group','religion','refereed_by_parent')->where('ID',$id)->first();
        unset($student['image']);

        return new StudentResource($student);

    }
}
