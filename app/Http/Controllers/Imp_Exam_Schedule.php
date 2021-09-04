<?php

namespace App\Http\Controllers;

use App\Events\examScheduleApproved_event;
use App\Events\examScheduleCreated_event;
use App\Events\examScheduleDeleted_event;
use App\Events\examScheduleDenied_event;
use App\Events\examScheduleUpdated_event;
use App\Http\Resources\currentImprovementExamScheduleResource;
use App\Models\O_IMP_REQUEST;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\O_IMP_EXAM_SCHEDULE;
use App\Models\O_IMP_XM_SDULE_DPT;
use App\Models\O_DEPARTMENTS;
use App\Rules\CheckHonsProgramExists;
use App\Rules\CheckMastersProgramExists;
use App\Rules\CheckExamScheduleRange;
use Illuminate\Support\Facades\DB;

class Imp_Exam_Schedule extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['schedules'] = O_IMP_EXAM_SCHEDULE::orderby('id', 'desc')->get();
        return view($this->folder(__METHOD__), $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if ( ! isOfficerOfControllerOfExamination() ){
            return back()->withErrors(['message' => 'You are not Officer Of Controller Of Examination']);
        }
        $data['hons_programs'] = O_DEPARTMENTS::where('PROGRAM_TYPE', 'hons')->orderby('id', 'asc')->get();
        $data['masters_programs'] = O_DEPARTMENTS::where('PROGRAM_TYPE', 'masters')->orderby('id', 'asc')->get();
        return view($this->folder(__METHOD__), $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if ( ! isOfficerOfControllerOfExamination() ){
            return back()->withErrors(['message' => 'You are not Officer Of Controller Of Examination']);
        }

        $this->validate($request,
            [
                'name' => 'required|string|max:100',
                'form_fillup_date' => ['required', 'date_format:Y-m-d', 'after:'.date('Y-m-d').'', new CheckExamScheduleRange()],
                'exam_start_date' => ['required', 'date_format:Y-m-d', 'after:form_fillup_date', new CheckExamScheduleRange()],
                'programs.hons' => ['required', 'array', new CheckHonsProgramExists()],
                'fee.hons.1.in' => 'required|numeric',
                'fee.hons.1.final' => 'required|numeric',
                'fee.hons.2.in' => 'required|numeric',
                'fee.hons.2.final' => 'required|numeric',
                'fee.hons.3.in' => 'required|numeric',
                'fee.hons.3.final' => 'required|numeric',
                'programs.masters' => ['required', 'array', new CheckMastersProgramExists()],
                'fee.masters.1.in' => 'required|numeric',
                'fee.masters.1.final' => 'required|numeric',
                'fee.masters.2.in' => 'required|numeric',
                'fee.masters.2.final' => 'required|numeric',
                'fee.masters.3.in' => 'required|numeric',
                'fee.masters.3.final' => 'required|numeric',
            ],
            [
                'name' => 'Name field is required',
                'fee.hons.1.in.required' => 'Field is required',
                'fee.hons.1.in.numeric' => 'Field must be a number',
                'fee.hons.1.final.required' => 'Field is required',
                'fee.hons.1.final.numeric' => 'Field must be a number',
                'fee.hons.2.in.required' => 'Field is required',
                'fee.hons.2.in.numeric' => 'Field must be a number',
                'fee.hons.2.final.required' => 'Field is required',
                'fee.hons.2.final.numeric' => 'Field must be a number',
                'fee.hons.3.in.required' => 'Field is required',
                'fee.hons.3.in.numeric' => 'Field must be a number',
                'fee.hons.3.final.required' => 'Field is required',
                'fee.hons.3.final.numeric' => 'Field must be a number',
                'fee.masters.1.in.required' => 'Field is required',
                'fee.masters.1.in.numeric' => 'Field must be a number',
                'fee.masters.1.final.required' => 'Field is required',
                'fee.masters.1.final.numeric' => 'Field must be a number',
                'fee.masters.2.in.required' => 'Field is required',
                'fee.masters.2.in.numeric' => 'Field must be a number',
                'fee.masters.2.final.required' => 'Field is required',
                'fee.masters.2.final.numeric' => 'Field must be a number',
                'fee.masters.3.in.required' => 'Field is required',
                'fee.masters.3.in.numeric' => 'Field must be a number',
                'fee.masters.3.final.required' => 'Field is required',
                'fee.masters.3.final.numeric' => 'Field must be a number',
            ]
        );

        $transaction = DB::transaction(function () use ($request) {
            $name = $request->name;
            $exam_start_date = $request->exam_start_date;
            $form_fillup_date = $request->form_fillup_date;
            $department_ids = $request->programs;
            $fee = $request->fee;


            $exam_start_date =strtotime($exam_start_date);
            $form_fillup_date = Carbon::createFromTimestamp(strtotime($form_fillup_date))->addDay()->subSecond()->getTimestamp();

            $schedule = O_IMP_EXAM_SCHEDULE::create([
                'NAME' => $name,
                'EXAM_START_DATE' => $exam_start_date,
                'FORM_FILLUP_LAST_DATE' => $form_fillup_date,
                'H_INON_FEE' => $fee['hons'][1]['in'],
                'H_FION_FEE' => $fee['hons'][1]['final'],
                'H_INTW_FEE' => $fee['hons'][2]['in'],
                'H_FITW_FEE' => $fee['hons'][2]['final'],
                'H_INTH_FEE' => $fee['hons'][3]['in'],
                'H_FITH_FEE' => $fee['hons'][3]['final'],
                'M_INON_FEE' => $fee['masters'][1]['in'],
                'M_FION_FEE' => $fee['masters'][1]['final'],
                'M_INTW_FEE' => $fee['masters'][2]['in'],
                'M_FITW_FEE' => $fee['masters'][2]['final'],
                'M_INTH_FEE' => $fee['masters'][3]['in'],
                'M_FITH_FEE' => $fee['masters'][3]['final'],
                'APPROVE_STATUS' => O_IMP_EXAM_SCHEDULE::NOTAPPROVE,
                'CREATED_BY' => session('user.id'),
                'CREATED_AT' => time(),
                'UPDATED_AT' => time(),
            ]);

            $department_array = [];
            if (!empty($department_ids)) {
                $departments = array_merge($department_ids['hons'], $department_ids['masters']);
                foreach ($departments as $key => $id) {
                    $department_array[] = [
                        'IES_ID' => $schedule->id,
                        'DEPARTMENT_ID' => $id,
                        'CREATED_BY' => session('user.id'),
                        'CREATED_AT' => time(),
                        'UPDATED_AT' => time(),
                    ];
                }
            }
            O_IMP_XM_SDULE_DPT::insert($department_array);


            /**
             * EVENT: examScheduleCreated_event
             */

            event(new examScheduleCreated_event($schedule));


        });

        if (empty($transaction))
        {
            return redirect()->route('schedules.index')->withErrors(['message' => 'Exam Schedule Created successfully.']);
        }
        return redirect()->back()->withErrors(['message' => 'Exam Schedule Created failed.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, $token)
    {
        if (md5($id) == $token)
        {
            $data['schedule'] = O_IMP_EXAM_SCHEDULE::where('id', $id)->first();
            return view($this->folder(__METHOD__), $data);
        }
        return redirect()->route('schedules.index')->withErrors(['message' => 'Invalid Request']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, $token)
    {


        if (  ! isControllerOfExamination() || ! isOfficerOfControllerOfExamination() ){
            return redirect()->route('schedules.index')->withErrors(['message' => 'You are not Officer Of Controller Of Examination']);
        }

        if (md5($id) == $token)
        {

            $schedule = O_IMP_EXAM_SCHEDULE::with('relXMSduleDPT:id,ies_id,department_id')->where('id', $id)->first();

            if ($schedule->isApproved()) {
                return redirect()->route('schedules.index')->withErrors(['message' => 'Exam Schedule already has been approved']);
            }


            $data['hons_programs'] = O_DEPARTMENTS::where('PROGRAM_TYPE', 'hons')->orderby('id', 'asc')->get();
            $data['masters_programs'] = O_DEPARTMENTS::where('PROGRAM_TYPE', 'masters')->orderby('id', 'asc')->get();
            $data['schedule'] = $schedule;
            $data['schedule_departments'] = (!empty($schedule->relXMSduleDPT)) ? $schedule->relXMSduleDPT->pluck('department_id')->toArray() : [];
            return view($this->folder(__METHOD__), $data);

        }
        return redirect()->route('schedules.index')->withErrors(['message' => 'Invalid Request']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, $token)
    {


        if (  ! isControllerOfExamination() || ! isOfficerOfControllerOfExamination() ){
            return redirect()->route('schedules.index')->withErrors(['message' => 'You are not Officer Of Controller Of Examination']);
        }


        if (md5($id) == $token)
        {
            $this->validate($request,
                [
                    'name' => 'required|string|max:100',
                    'form_fillup_date' => ['required', 'date_format:Y-m-d', 'after:'.date('Y-m-d').'', new CheckExamScheduleRange($id)],
                    'exam_start_date' => ['required', 'date_format:Y-m-d', 'after:form_fillup_date', new CheckExamScheduleRange($id)],
                    'programs.hons' => ['required', 'array', new CheckHonsProgramExists()],
                    'fee.hons.1.in' => 'required|numeric',
                    'fee.hons.1.final' => 'required|numeric',
                    'fee.hons.2.in' => 'required|numeric',
                    'fee.hons.2.final' => 'required|numeric',
                    'fee.hons.3.in' => 'required|numeric',
                    'fee.hons.3.final' => 'required|numeric',
                    'programs.masters' => ['required', 'array', new CheckMastersProgramExists()],
                    'fee.masters.1.in' => 'required|numeric',
                    'fee.masters.1.final' => 'required|numeric',
                    'fee.masters.2.in' => 'required|numeric',
                    'fee.masters.2.final' => 'required|numeric',
                    'fee.masters.3.in' => 'required|numeric',
                    'fee.masters.3.final' => 'required|numeric',
                ],
                [
                    'name' => 'Name field is required',
                    'fee.hons.1.in.required' => 'Field is required',
                    'fee.hons.1.in.numeric' => 'Field must be a number',
                    'fee.hons.1.final.required' => 'Field is required',
                    'fee.hons.1.final.numeric' => 'Field must be a number',
                    'fee.hons.2.in.required' => 'Field is required',
                    'fee.hons.2.in.numeric' => 'Field must be a number',
                    'fee.hons.2.final.required' => 'Field is required',
                    'fee.hons.2.final.numeric' => 'Field must be a number',
                    'fee.hons.3.in.required' => 'Field is required',
                    'fee.hons.3.in.numeric' => 'Field must be a number',
                    'fee.hons.3.final.required' => 'Field is required',
                    'fee.hons.3.final.numeric' => 'Field must be a number',
                    'fee.masters.1.in.required' => 'Field is required',
                    'fee.masters.1.in.numeric' => 'Field must be a number',
                    'fee.masters.1.final.required' => 'Field is required',
                    'fee.masters.1.final.numeric' => 'Field must be a number',
                    'fee.masters.2.in.required' => 'Field is required',
                    'fee.masters.2.in.numeric' => 'Field must be a number',
                    'fee.masters.2.final.required' => 'Field is required',
                    'fee.masters.2.final.numeric' => 'Field must be a number',
                    'fee.masters.3.in.required' => 'Field is required',
                    'fee.masters.3.in.numeric' => 'Field must be a number',
                    'fee.masters.3.final.required' => 'Field is required',
                    'fee.masters.3.final.numeric' => 'Field must be a number',
                ]
            );

            $schedule = O_IMP_EXAM_SCHEDULE::where('id', $id)->first();
            if( $schedule->isApproved() ){
                return redirect()->back()->withErrors('message', 'Approved Exam Schedule cannot be edit');
            }

            if( $schedule->isApproved() ){
                return redirect()->route('schedules.index')->withErrors('message', 'Approved Exam Schedule cannot be deleted');
            }

            try{
                DB::transaction(function () use ($request, $id) {
                    $name = $request->name;
                    $exam_start_date = $request->exam_start_date;
                    $form_fillup_date = $request->form_fillup_date;
                    $department_ids = $request->programs;
                    $fee = $request->fee;


                    $exam_start_date = strtotime($exam_start_date);
                    $form_fillup_date = Carbon::createFromTimestamp(strtotime($form_fillup_date))->addDay()->subSecond()->getTimestamp();

                    $schedulestatus = O_IMP_EXAM_SCHEDULE::where('id', $id)->update([
                        'NAME' => $name,
                        'EXAM_START_DATE' => $exam_start_date,
                        'FORM_FILLUP_LAST_DATE' => $form_fillup_date,
                        'H_INON_FEE' => $fee['hons'][1]['in'],
                        'H_FION_FEE' => $fee['hons'][1]['final'],
                        'H_INTW_FEE' => $fee['hons'][2]['in'],
                        'H_FITW_FEE' => $fee['hons'][2]['final'],
                        'H_INTH_FEE' => $fee['hons'][3]['in'],
                        'H_FITH_FEE' => $fee['hons'][3]['final'],
                        'M_INON_FEE' => $fee['masters'][1]['in'],
                        'M_FION_FEE' => $fee['masters'][1]['final'],
                        'M_INTW_FEE' => $fee['masters'][2]['in'],
                        'M_FITW_FEE' => $fee['masters'][2]['final'],
                        'M_INTH_FEE' => $fee['masters'][3]['in'],
                        'M_FITH_FEE' => $fee['masters'][3]['final'],
                        'APPROVE_STATUS' => O_IMP_EXAM_SCHEDULE::NOTAPPROVE,
                        'UPDATED_AT' => time(),
                    ]);

                    $department_array = [];
                    if (!empty($department_ids)) {
                        $departments = array_merge($department_ids['hons'], $department_ids['masters']);
                        foreach ($departments as $key => $department_id) {
                            $department_array[] = [
                                'IES_ID' => $id,
                                'DEPARTMENT_ID' => $department_id,
                                'CREATED_BY' => session('user.id'),
                                'CREATED_AT' => time(),
                                'UPDATED_AT' => time(),
                            ];
                        }
                    }
                    O_IMP_XM_SDULE_DPT::where('ies_id', $id)->delete();
                    O_IMP_XM_SDULE_DPT::insert($department_array);

                    /**
                     * EVENT: examScheduleUpdated_event
                     */

                    event(new examScheduleUpdated_event($schedule));
                });
                return redirect()->back()->withErrors(['message' => 'Exam Schedule Update successfully.']);
            }
            catch (\Exception $exception){
                return redirect()->back()->withErrors(['message' => 'Exam Schedule Update failed.']);
            }
        }
        return redirect()->back()->withErrors(['message' => 'Invalid Url']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $token)
    {
        if (md5($id) == $token && (isOfficerOfControllerOfExamination() || isControllerOfExamination() ))
        {
            $examSchedule = O_IMP_EXAM_SCHEDULE::where('id', $id)->first();

            if (! $examSchedule){
                return back()->withErrors('message', 'No Improvement Exam Schedule Found!');

            }
            if( $examSchedule->isApproved() ){
                return back()->withErrors('message', 'Approved Exam Schedule cannot be deleted');
            }


            /**
             * EVENT: examScheduleDeleted_event
             */
            event(new examScheduleDeleted_event($examSchedule));

            $examSchedule->delete();

            O_IMP_XM_SDULE_DPT::where('ies_id', $id)->delete();

            return redirect()->route('schedules.index')->withErrors(['message' => 'Exam Schedule Delete successfully.']);
        }
        return redirect()->route('schedules.index')->withErrors(['message' => 'Exam Schedule Delete failed.']);
    }

    public function schedules_approve($id, $token)
    {
        if (md5($id) == $token)
        {
            if( isControllerOfExamination() )
            {
                $schedule = O_IMP_EXAM_SCHEDULE::where('id', $id)->first();

                if ( $schedule->isNotApproved())
                {
                    O_IMP_EXAM_SCHEDULE::where('id', $id)->update([
                        'approve_status' => O_IMP_EXAM_SCHEDULE::APPROVED,
                        'approve_by' => session('user.id'),
                    ]);


                    /**
                     * EVENT: examScheduleApproved_event
                     */
                    event(new examScheduleApproved_event($schedule));


                    return redirect()->back()->withErrors(['message' => 'Exam Schedule Approve successfully.']);
                }
                return redirect()->back()->withErrors(['message' => 'Already Approved.']);
            }
        }
        return redirect()->back()->withErrors(['message' => 'Exam Schedule Approve failed.']);
    }



    public function schedules_deny($id, $token)
    {

        if (md5($id) == $token )
        {
            if( isControllerOfExamination() )
            {
                $schedule = O_IMP_EXAM_SCHEDULE::where('id', $id)->first();
                if ( $schedule->isNotApproved())
                {
                    O_IMP_EXAM_SCHEDULE::where('id', $id)->update([
                        'approve_status' => O_IMP_EXAM_SCHEDULE::DENYED,
                        'approve_by' => session('user.id'),
                    ]);


                    /**
                     * EVENT: examScheduleDenied_event
                     */
                    event(new examScheduleDenied_event($schedule));

                    return redirect()->back()->withErrors(['message' => 'Exam Schedule Denied successfully.']);
                }
                return redirect()->back()->withErrors(['message' => 'Already Approved.']);
            }
        }
        return redirect()->back()->withErrors(['message' => 'Exam Schedule Approve failed.']);
    }

    public function get_current_improvement_exam_schedule()
    {
        $currentSchedule = O_IMP_EXAM_SCHEDULE::with('relXMSduleDPT')->CurrentScheduleCollection();


        if ( $currentSchedule ){
            return currentImprovementExamScheduleResource::collection( $currentSchedule );
        }
        else{
            return response()->json(['error'=>'No Current Schedule', 400]);
        }

    }

    public function get_applied_improvement_exam_schedule($std_id)
    {

        $xm_requests = O_IMP_REQUEST::distinct('relExamSchedule')->with('relExamSchedule')->where('std_id', $std_id)->get();


        if (! $xm_requests){
            return response()->json(['error'=>'You Have not Applied Any Improvement Exam'], 400);
        }

        $relExamSchedules = $xm_requests->pluck('relExamSchedule');

        $examSchedules = $relExamSchedules->unique();



        $response_xm_schedule = [];

        //'NAME', 'EXAM_START_DATE', 'FORM_FILLUP_LAST_DATE',
        // 'H_INON_FEE', 'H_FION_FEE', 'H_INTW_FEE', 'H_FITW_FEE', 'H_INTH_FEE', 'H_FITH_FEE', 'M_INON_FEE', 'M_FION_FEE', 'M_INTW_FEE', 'M_FITW_FEE', 'M_INTH_FEE', 'M_FITH_FEE',
        // 'APPROVE_STATUS', 'APPROVE_BY', 'CREATED_BY', 'CREATED_AT', 'UPDATED_AT'

        foreach ( $examSchedules as $examSchedule){
            $response_xm_schedule [] = [
                'id'=> $examSchedule->id,
                'name'=> $examSchedule->name,
                'exam_start_date'=> $examSchedule->exam_start_date,
                'form_fillup_last_date'=> $examSchedule->form_fillup_last_date,
            ];
        }

        return response()->json(['data'=>$response_xm_schedule] , 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function extend_show($id, $token)
    {
        if (md5($id) == $token)
        {
            $schedule = O_IMP_EXAM_SCHEDULE::with('relXMSduleDPT:id,ies_id,department_id')->where('id', $id)->first();

            if ( ! $schedule->isApproved()) {
                return redirect()->route('schedules.index')->withErrors(['message' => 'Exam Schedule NOT approved']);
            }

            $data['hons_programs'] = O_DEPARTMENTS::where('PROGRAM_TYPE', 'hons')->orderby('id', 'asc')->get();
            $data['masters_programs'] = O_DEPARTMENTS::where('PROGRAM_TYPE', 'masters')->orderby('id', 'asc')->get();
            $data['schedule'] = $schedule;
            $data['schedule_departments'] = (!empty($schedule->relXMSduleDPT)) ? $schedule->relXMSduleDPT->pluck('department_id')->toArray() : [];
            return view($this->folder(__METHOD__), $data);
        }
        return redirect()->route('schedules.index')->withErrors(['message' => 'Invalid Request']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function extend(Request $request, $id, $token)
    {


        if (  ! is_super_user() ){
            return redirect()->route('schedules.index')->withErrors(['message' => 'You are not Super Admin']);
        }


        if (md5($id) == $token)
        {
            $this->validate($request,
                [
                    'form_fillup_date' => ['required', 'date_format:Y-m-d', 'after:'.date('Y-m-d').'', new CheckExamScheduleRange($id)],
                    'exam_start_date' => ['required', 'date_format:Y-m-d', 'after:form_fillup_date', new CheckExamScheduleRange($id)],
                ]
            );

            $schedule = O_IMP_EXAM_SCHEDULE::where('id', $id)->first();

            try{
                DB::transaction(function () use ($request, $id, $schedule) {

                    $exam_start_date = $request->exam_start_date;
                    $form_fillup_date = $request->form_fillup_date;

                    $exam_start_date = strtotime($exam_start_date);
                    $form_fillup_date = Carbon::createFromTimestamp(strtotime($form_fillup_date))->addDay()->subSecond()->getTimestamp();

                    O_IMP_EXAM_SCHEDULE::where('id', $id)->update([
                        'EXAM_START_DATE' => $exam_start_date,
                        'FORM_FILLUP_LAST_DATE' => $form_fillup_date,
                        'UPDATED_AT' => time(),
                    ]);


                    /**
                     * EVENT: examScheduleUpdated_event
                     */

                    event(new examScheduleUpdated_event($schedule));
                });
                return redirect()->route('schedules.index')->withErrors(['message' => 'Exam Schedule Update successfully.']);
            }
            catch (\Exception $exception){
                return redirect()->route('schedules.index')->withErrors(['message' => 'Exam Schedule Update failed.']);
            }
        }
        return redirect()->back()->withErrors(['message' => 'Invalid Url']);
    }

}
