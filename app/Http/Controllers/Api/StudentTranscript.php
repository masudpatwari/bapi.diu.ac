<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\bapiTrait;
use Illuminate\Http\Request;

class StudentTranscript extends Controller
{
    use bapiTrait;
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request,$studentId)
    {

        $studentProvisionalResult = $this->studentProvisionalResult($studentId);
        return $studentProvisionalResult;
    }
}
