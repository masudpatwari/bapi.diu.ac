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
            $data['date'][$list->department_id] =Students::        
            where('department_id',[$start_date,$end_date])
            ->whereBetween('adm_date',['2022-05-01','2022-09-01'])
            ->select(
                DB::raw("(count(id)) as total"),
                DB::raw("(DATE_FORMAT(adm_date, '%y-%m-%d')) as month")
                )
                ->orderBy('adm_date','ASC')
                ->groupBy(DB::raw("DATE_FORMAT(adm_date, '%y-%m-%d')"))
                ->get();
          }
      return $data;
    }
}
