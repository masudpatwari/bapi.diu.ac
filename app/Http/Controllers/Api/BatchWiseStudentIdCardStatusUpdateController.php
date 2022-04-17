<?php

namespace App\Http\Controllers\Api;

use App\Models\O_BATCH;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BatchWiseStudentIdCardStatusUpdateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function __invoke(Request $request)
    {

		$this->validate($request, [
            'batch_id' => 'required|integer'
        ]);

        $batch = O_BATCH::selectRaw("ID,CAMPUS_ID,REG_CARD_PRINTED")->find($request->batch_id);
        $batch->REG_CARD_PRINTED = 1;
        $batch->save();

        return $batch;

        
    }
}
