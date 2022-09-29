<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\O_STUDENT;
use Carbon\Carbon;
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
            $info[$list->department->name] = array_values(O_STUDENT::
            where('department_id',$list->department_id)
                ->whereBetween('adm_date',[$start_date,$end_date])
                ->get()
                ->groupBy(function($d) { return Carbon::parse($d->adm_date)->format('M');})->map(function ($students, $month) use($list){
                    return [
                        // 'month' => $month. ', '.Carbon::parse($list->adm_date)->format('Y'),
                        'total admission' => count($students),
                    ];
                })->toArray());
        }


        return response()->json(["data" => $info], 200);
    }
}
