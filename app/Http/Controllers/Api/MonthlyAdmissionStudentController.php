<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\O_STUDENT;
use Illuminate\Http\Request;

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
        foreach ($data['dept'] as $list) {
            $data['date'][$list->department_id] =O_STUDENT::        
            where('DEPARTMENT_ID',[$start_date,$end_date])
            ->whereBetween('ADM_DATE',[$start_date,$end_date]])
            ->select(
                DB::raw("(count(id)) as total"),
                DB::raw("(DATE_FORMAT(ADM_DATE, '%y-%m-%d')) as month")
                )
                ->orderBy('ADM_DATE','ASC')
                ->groupBy(DB::raw("DATE_FORMAT(ADM_DATE, '%y-%m-%d')"))
                ->get();
          }
      return $data;
    }
}
