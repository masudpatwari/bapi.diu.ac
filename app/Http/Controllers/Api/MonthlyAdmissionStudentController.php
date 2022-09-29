<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\O_STUDENT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class MonthlyAdmissionStudentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index($start_date, $end_date)
    {
        // $students = O_STUDENT::selectRaw("ID,NAME,DEPARTMENT_ID,ADM_DATE")
        //     ->with('department')
        //     ->whereBetween('adm_date', [$start_date,$end_date])
        //     ->get()
        //     ->groupBy('department.name');

        // return $students;

        // $data['dept'] = O_STUDENT::with('department')->whereBetween('adm_date',[$start_date,$end_date])->select('department_id')->distinct()->get(); 
        // foreach ($data['dept'] as $list) {
        //     $data['date'][$list->department_id] = O_STUDENT::        
        //     where('department_id',[$list->department_id])
        //     ->whereBetween('adm_date',[$start_date,$end_date])
        //     ->select(
        //         DB::raw("(count(id)) as total"),                
        //         )                
        //         ->get()
        //         ->groupBy('adm_date');
        //   }


        $data['dept'] = O_STUDENT::with('department')->whereBetween('adm_date',[$start_date,$end_date])->select('department_id')->distinct()->get(); 
        foreach ($data['dept'] as $list) {
            $data['month'][$list->department_id] = O_STUDENT::        
            where('department_id',[$list->department_id])
            ->whereBetween('adm_date',[$start_date,$end_date])
            ->select(
                DB::raw("(count(id)) as total"),
                DB::raw("(DATE_FORMAT(adm_date, 'mm')) as month")                  
                )                
                ->orderBy('month','ASC')
                ->groupBy('month')        
                ->get();
          }
      return $data;
    }
}
