<?php

namespace App\Http\Controllers\Api;


use App\Models\O_STUDENT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StudentEmailUpdateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function __invoke(Request $request): JsonResponse
    {

        // dump(\Log::error(print_r($request->all(),true)));

        $this->validate($request,[
            'email' => 'required|email'
        ]);
//        $validate =  Validator::make($data, [
//            'email' => 'required|email'
//        ]);

        $student = O_STUDENT::where('ID',$request->id)->first();

        try {
            $student->update([
                'EMAIL' => $request->email
            ]);

            return response()->json(['Student Email updated'], 200);

        }catch (\Exception $e)
        {
            return response()->json(['Error' => $e->getMessage()], 401);
        }

    }
}
