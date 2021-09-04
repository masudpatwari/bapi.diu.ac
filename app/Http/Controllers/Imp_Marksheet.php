<?php

namespace App\Http\Controllers;

use App\Models\O_COURSE;
use App\Models\O_IMP_REQUEST;
use App\Traits\Marks_Encryption_Trait;
use Illuminate\Http\Request;
use App\Models\O_IMP_REQUEST_COURSE;
use App\Models\O_IMP_MARKS_DRAFT;
use App\Models\O_IMP_EXAM_SCHEDULE;
use App\Models\O_MARKS;
use App\Models\O_MARKS_EDIT_HISTORY;
use App\Models\O_IMP_COURSE_TEACHER_ASSING;
use App\Models\O_BATCH;
use App\Models\O_SEMESTERS;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Imp_Marksheet extends Imp_Marksheet_Settings
{
    use Marks_Encryption_Trait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function batch_view($course_id, $campus_id, $type, $ies_id, $batch_id, $token)
    {
        if (md5($course_id.$campus_id.$type.$ies_id.$batch_id) == $token)
        {
            $batch = O_BATCH::with('relDepartment', 'relShift')->where('id', $batch_id)->first();
            $data['type'] = $type;
            $data['campus_id'] = $campus_id;
            $data['ies_id'] = $ies_id;
            $data['batch_id'] = $batch_id;
            $data['mk_info'] = O_COURSE::where('id', $course_id)->first();
            $students = O_IMP_REQUEST_COURSE::imp_requested_students_via_batch( $course_id, $campus_id, $type, $ies_id, $batch_id );
            $marks = O_IMP_REQUEST_COURSE::imp_requested_marks_via_batch( $course_id, $campus_id, $type, $ies_id, $batch_id );
            $data['marksheet_table'] = $this->view_marksheet_table($students, $marks);
            $data['batch'] = $batch;
            $data['semester_info'] = O_IMP_REQUEST_COURSE::imp_semester_info( $course_id, $batch->department_id, $batch_id );
            return view($this->folder(__METHOD__), $data);
        }
        else
        {
            return redirect()->back()->withErrors(['message' => 'Invalid Marksheet']);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($course_id, $campus_id, $ies_id, $type, $token)
    {
        if (md5($course_id . $campus_id. $ies_id . $type) == $token)
        {
            O_IMP_REQUEST::removeUnpaidRequest($ies_id);

            $data['type'] = $type;
            $data['campus_id'] = $campus_id;
            $data['ies_id'] = $ies_id;
            $data['mk_info'] = O_COURSE::where('id', $course_id)->first();
            $students = O_IMP_REQUEST_COURSE::imp_requested_students($course_id, $campus_id, $ies_id, $type);

            $marks = O_IMP_COURSE_TEACHER_ASSING::with('relImpMarksDraft')->where(['course_id' => $course_id, 'campus_id' => $campus_id, 'ies_id' => $ies_id, 'type' => $type, 'assign_teacher_id' => session('user.id')])->first();
            /*
             * this terms not apply. because when mark final submit and redirect back here there is no marks logicaly.
             *
            if (!empty($marks) && $marks->marks_final_submitted == O_IMP_COURSE_TEACHER_ASSING::MARKS_FINAL_SUBMIT) {
                return redirect()->back()->withErrors(['message' => 'Marksheet already final submitted']);
            }
            */

            if (!empty($marks->relImpMarksDraft) && ($marks->relImpMarksDraft->count() > 0)) {
                $data['marksheet_table'] = $this->view_marksheet_table($students, $marks->relImpMarksDraft->toArray());
            }
            else
            {
                $marks = O_IMP_REQUEST_COURSE::imp_requested_marks($course_id, $ies_id, $type);
                $data['marksheet_table'] = $this->view_marksheet_table($students, $marks);
            }
        }
        else
        {
            return redirect()->back()->withErrors(['message' => 'Invalid Marksheet']);
        }
        return view($this->folder(__METHOD__), $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_improvement_marksheet_for_student(Request $request)
    {
        $validator = Validator::make($request->all(),  [
            'student_id' => 'required|integer',
            'ies_id' => 'required|integer',
        ]);

        if($validator->fails()){
            return response($validator->messages(), 400);
        }

        $ies_id = $request->input('ies_id');
        $student_id = $request->input('student_id');

        try{
            $marks = O_IMP_REQUEST_COURSE::imp_student_marksheet( $ies_id, $student_id );

            return response()->json(['data'=>$marks], 200);

        }
        catch (\Exception $exception){
            return response()->json(['error'=>$exception->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($course_id, $campus_id, $ies_id, $type, $token)
    {
        if (md5($course_id . $campus_id. $ies_id . $type) == $token)
        {
            $data['type'] = $type;
            $data['mk_info'] = O_COURSE::where('id', $course_id)->first();
            $students = O_IMP_REQUEST_COURSE::imp_requested_students($course_id, $campus_id, $ies_id, $type);
            $marks = O_IMP_REQUEST_COURSE::imp_requested_marks($course_id, $ies_id, $type);
            $data['marksheet_table'] = $this->view_marksheet_table($students, $marks);
        }
        else
        {
            return redirect()->back()->withErrors(['message' => 'Invalid Marksheet']);
        }
        return view($this->folder(__METHOD__), $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $course_id, $campus_id, $ies_id, $type, $token)
    {
        if (md5($course_id.$campus_id.$ies_id.$type) == $token)
        {
            $mk_info = O_COURSE::where('id', $course_id)->first();
            $this->marksheet_validation( $request, $mk_info, $type );
            $icta_id = O_IMP_COURSE_TEACHER_ASSING::where(['course_id' => $course_id, 'campus_id' => $campus_id, 'ies_id' => $ies_id, 'type' => $type, 'assign_teacher_id' => session('user.id')])->first()->id;
            $students = O_IMP_REQUEST_COURSE::imp_requested_students($course_id, $campus_id, $ies_id, $type);

//            $marks = O_IMP_REQUEST_COURSE::imp_requested_marks($course_id, $ies_id, $type);
//            $marksheet_table = $this->view_marksheet_table($students, $marks);
//            $replace_marks_old2new = $this->replace_marks_old2new($mk_info, $marksheet_table, $request->mk_value, $type, TRUE);
//            if (count($replace_marks_old2new)) {
//                if ($request->save_as == \RMS_status::FINAL_STATUS) {
//                    $request_marks_array = $this->marksheet_entry_array_for_final($replace_marks_old2new, $mk_info, $type, $icta_id);
//                    if (!empty($request_marks_array)){
//                        foreach ($request_marks_array as $marks_key => $marks_value) {
//                            O_MARKS::where('id', $marks_key)->update($marks_value);
//                        }
//                    }
//                }
//            }
//            else
//            {
//                return redirect()->route('imp.marksheet_edit',[
//                    'course_id'=> $course_id,
//                    'campus_id'=> $campus_id,
//                    'ies_id'=> $ies_id,
//                    'type' =>$type,
//                    '_token'=> $token
//                ])->with(['message' => 'Marksheet has not be diffrent values - !']);
//            }

            $marks = O_IMP_REQUEST_COURSE::imp_requested_marks($course_id, $ies_id, $type);
            $marksheet_table = $this->view_marksheet_table($students, $marks);
            $replace_marks_old2new = $this->replace_marks_old2new($mk_info, $marksheet_table, $request->mk_value, $type, FALSE);

            if (count($replace_marks_old2new)) {

                O_IMP_MARKS_DRAFT::where('icta_id', $icta_id)->delete();

                if ($request->save_as == \RMS_status::DRAFT_STATUS) {
                    $request_marks_array = $this->marksheet_entry_array_for_draft($replace_marks_old2new, $mk_info, $type, $icta_id);
                    O_IMP_MARKS_DRAFT::insert($request_marks_array);
                }

                if ($request->save_as == \RMS_status::FINAL_STATUS) {
                    $request_marks_array = $this->marksheet_entry_array_for_final($replace_marks_old2new, $mk_info, $type, $icta_id);
                    if (!empty($request_marks_array)){
                        try {
                            DB::beginTransaction();

                            $ies = O_IMP_EXAM_SCHEDULE::where('id', $ies_id)->first();
                            foreach ($request_marks_array as $marks_key => $marks_value) {
                                O_MARKS::where('id', $marks_key)->update($marks_value);

                                $mark = O_MARKS::where('id', $marks_key)->first();
                                $this->makeUpdate($mark);

                                $marks_new = O_IMP_REQUEST_COURSE::imp_requested_marks($course_id, $ies_id, $type);
                                $marksheet_table_new = $this->view_marksheet_table($students, $marks_new);
                                $row[] = $this->create_mark_history( $marksheet_table[$marks_key], $marksheet_table_new[$marks_key], $ies->name .' IMP XM. mark_Id:' . $mark->id. ' TYPE:' . $type);
                            }
                            O_MARKS_EDIT_HISTORY::insert($row);
                            O_IMP_COURSE_TEACHER_ASSING::where(['course_id' => $course_id, 'campus_id' => $campus_id, 'ies_id' => $ies_id, 'type' => $type, 'assign_teacher_id' => session('user.id')])->update([
                                'MARKS_FINAL_SUBMITTED' => O_IMP_COURSE_TEACHER_ASSING::MARKS_FINAL_SUBMIT
                            ]);
                            DB::commit();

                            Redis::flushAll();

                        }
                        catch ( \Exception $exception){
                            Log::emergency( $exception);
                            DB::rollBack();

                        }

                    }

                }
                //Route::get('/imp_marksheet_view/{course_id}/{campus_id}/{ies_id}/{type}/{_token}', 'Imp_Marksheet@show')->name('imp.marksheet_view');
                return redirect()->route('imp.assigned_marksheet.index')->with(['message' => 'Marksheet has been updated successfully.']);
            }
            return redirect()->route('imp.marksheet_edit',[
                'course_id'=> $course_id,
                'campus_id'=> $campus_id,
                'ies_id'=> $ies_id,
                'type' =>$type,
                '_token'=> $token
            ])->with(['message' => 'Marksheet has not be diffrent values ..']);
        }
        else
        {
            return redirect()->route('imp.marksheet_edit',[
                'course_id'=> $course_id,
                'campus_id'=> $campus_id,
                'ies_id'=> $ies_id,
                'type' =>$type,
                '_token'=> $token
            ])->with(['message' => 'Invalid Marksheet']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function pdf( $course_id, $campus_id, $type, $ies_id, $batch_id, $token ){
        if (md5($course_id.$campus_id.$type.$ies_id.$batch_id) == $token)
        {
            $file_path = storage_path(env('PDF_FILE_STORAGE_PATH')).'/improvement_marksheet_'.$token.'.pdf';
            if (file_exists($file_path) && env('PDF_CACHE_ENABLE') == 1) {
                download_pdf($file_path, 'improvement_marksheet_'.$token.'.pdf');
                return false;
            }

            $batch = O_BATCH::with('relDepartment', 'relShift')->where('id', $batch_id)->first();
            $header = O_COURSE::where('id', $course_id)->first();
            $students = O_IMP_REQUEST_COURSE::imp_requested_students_via_batch( $course_id, $campus_id, $type, $ies_id, $batch_id );
            $marks = O_IMP_REQUEST_COURSE::imp_requested_marks_via_batch( $course_id, $campus_id, $type, $ies_id, $batch_id );
            $marksheet_table = $this->view_marksheet_table($students, $marks);
            $semester_info = O_IMP_REQUEST_COURSE::imp_semester_info( $course_id, $batch->department_id, $batch_id );

            if($type == 'incourse' && ($header->course_type == \RMS_COURSE_TYPE::THEORY))
            {
                $view = \View::make('imp_marksheet.component.pdf_incourse_theory', compact('batch', 'header', 'semester_info', 'marksheet_table'));
            }

            if($type == 'final' && ($header->course_type == \RMS_COURSE_TYPE::THEORY))
            {
                $view = \View::make('imp_marksheet.component.pdf_final_theory', compact('batch', 'header', 'semester_info', 'marksheet_table'));
            }

            if ($type == 'final' && ($header->course_type == \RMS_COURSE_TYPE::NON_THEORY))
            {
                $view = \View::make('imp_marksheet.component.pdf_final_non_theory', compact('batch', 'header', 'semester_info', 'marksheet_table'));
            }

            $mpdf = new \Mpdf\Mpdf(['tempDir' => storage_path('temp'), 'mode' => 'utf-8', 'format' => 'A4-P', 'orientation' => 'P']);
            $mpdf->SetTitle('Improvement Mark Sheet '.$token.'');
            $mpdf->WriteHTML(file_get_contents( public_path('pdf_style.css') ), 1);
            $mpdf->WriteHTML($view, 2);
            $mpdf->Output($file_path, 'F');
            return $mpdf->Output('improvement_marksheet_'.$token.'', 'I');
        }
        else
        {
            return redirect()->back()->withErrors(['message' => 'Invalid Marksheet']);
        }
    }

    public function getImpromentMarks($impExamScheduleId, $stdId)
    {

    }
}
