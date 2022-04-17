<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\StudentResource;
use App\Models\O_STUDENT;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class StudentFilterController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($slug)
    {


        $search_keyword = Str::upper($slug);

        $student = O_STUDENT::query()
            ->with('group','department:id,name')
            ->selectRaw("ID,REG_CODE,ROLL_NO,NAME,GROUP_ID,EMAIL,PHONE_NO,F_NAME,E_NAME,E_CELLNO,DEPARTMENT_ID")
            ->where('REG_CODE', 'LIKE', "%{$search_keyword}%") 
            ->orWhere('ROLL_NO', 'LIKE', "%{$search_keyword}%") 
            ->orWhere('NAME', 'LIKE', "%{$search_keyword}%") 
            ->get();


        return $student;

    }
}
