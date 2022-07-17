<?php

namespace App\Http\Controllers\Api;

use App\Models\O_BATCH;
use App\Models\O_STUDENT;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActiveBatchForAdmissionResource;

class AllBatchForAdmissionController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $batch = O_BATCH::with('relDepartment', 'campus', 'relShift', 'group','paymemtSystem')
            ->withCount('activeStudents','unVerifiedStudents')
            ->whereIn('sess', ["2019-2020", "2020-2021", "2021-2022"])
            ->orderAscending()
            ->get();

        $collection = collect($batch);
        $sorted = $collection->sortBy('active_students_count');

        return ActiveBatchForAdmissionResource::collection($sorted->values()->all());
    }
}
