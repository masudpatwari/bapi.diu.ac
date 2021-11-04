<?php

namespace App\Http\Controllers\Api;

use App\Models\O_BATCH;
use App\Models\O_STUDENT;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActiveBatchForAdmissionResource;

class AdmissionStudentRegCodeGenerateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $batch = O_BATCH::find($request->batch_id);


        $admission_year = substr($batch->year,2);
        $program_code = "{$request->university_code}{$admission_year}{$batch->adm_season}{$request->hall_code}{$request->program_code}";

        dump(\Log::error(print_r([$request->all(),$program_code], true)));
    }
}
