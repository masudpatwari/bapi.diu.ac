<?php

namespace App\Http\Controllers;

use App\Models\M_WP_EMP;
use App\Models\O_BATCH;
use App\Models\O_GRADE_POINT_SYSTEM_DETAIL;
use App\Models\O_MARKS;
use App\Models\O_SEMESTERS;
use App\Models\O_STUDENT;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $duplicate_values;

    public function folder($method_name)
    {
        $folder_name = explode('\\', $method_name);
        return str_replace('::', '.', strtolower(array_pop($folder_name)));
    }

    public function get_actions_from_route()
    {
        $routes = \Route::getRoutes();
        $actions = [];
        foreach ($routes as $route) {
            $actions[] = $route->getName();
        }

        //remove store option
        $input = preg_quote("store", '~');
        $var = preg_grep('~' . $input . '~', $actions);
        $actions = array_values(array_diff($actions, $var));

        //remove update option
        $input = preg_quote("update", '~');
        $var = preg_grep('~' . $input . '~', $actions);
        $actions = array_values(array_diff($actions, $var));


        $remove_actions = separated_routes_array();

        $final_action = [];
        foreach ($actions as $action) {
            if (!in_array($action, $remove_actions))
                $final_action[] = $action;
        }

        $actions = array_filter($final_action);


        $controllers_key = ['role', 'course', 'department', 'grade_point_system', 'marksheet', 'semester', 'result','tabulationsheet','audit', 'transcript','batch_split','redis'];

        $controller_group = [];

        foreach ($actions as $aname) {
            if (strpos($aname, 'ajax') !== false)
                continue;

            foreach ($controllers_key as $cval) {
                if (strpos($aname, $cval) !== false)
                    $controller_group[$cval][] = $aname;
            }
        }

        return $controller_group;
    }


    public function make_transcript( $student_id )
    {

         $distinct_ids = O_MARKS::select('SIFR_ID')->distinct()->where(['STD_ID' => $student_id])->pluck('sifr_id');

        $semster_with_ids = O_SEMESTERS::select('ID','SEMESTER')->whereIn('id', $distinct_ids)->pluck('id','semester')->toArray();

        // Semester and ID with duplicates

          $semster_and_ids = O_SEMESTERS::whereIn('id', $distinct_ids)
            ->get(['id','semester']);

        $sifr_semesters = $semster_and_ids->groupBy('semester');

        // Find out duplicate values


        $duplicate_ids = $sifr_semesters->filter(function (Collection $groups) {
            return $groups->count() > 1;
        })->toArray();

        if($duplicate_ids){
            $ids = [];

            // Reject null marks if duplicate

            foreach ($duplicate_ids as $key => $value)
            {
                $ids[] = collect($value)->pluck('id')->filter(function ($item) use($student_id){
                    $this->duplicate_values($item);
                    $grade = O_MARKS::where(['STD_ID' => $student_id, 'SIFR_ID' => $item])->value('grade_point');

                    return $grade >0;
                });
            }


            // remove all duplicate sifr ids

            $sifr_without_duplicates = array_diff($distinct_ids->toArray(), $this->duplicate_values);


            // add sifr ids having marks

            $sifr_ids = array_merge(collect($ids)->flatten()->toArray(), $sifr_without_duplicates);

            $distinct_ids = array_values($sifr_ids);
        }else{

            // remove duplicate SEMESTER and remove keys
            $semster_and_ids = O_SEMESTERS::select('ID','SEMESTER')->whereIn('id', $distinct_ids)->pluck('id','semester')->toArray();

            $distinct_ids =  array_values( array_unique( $semster_and_ids)) ;
        }


        // code modification ends
//        dd($semster_with_ids,$distinct_ids);
//        $semster_and_ids = O_SEMESTERS::select('ID','SEMESTER')->whereIn('id', $distinct_ids)->pluck('id','semester')->toArray();


        if (!empty($distinct_ids)) {

            // $semester_info_table_ids = array_column($distinct_ids, 'sifr_id');
            $semester_info_table_ids = $distinct_ids;

            $student_info = O_STUDENT::with('department','batch:id,batch_name')->where('id', $student_id)->first();

            $gps = O_MARKS::select('gps_id')->where(['std_id' => $student_id])->first();

            if ( ! isset($gps->gps_id) ) throw new \Exception('No Marks Found');

            $grade_point_system_details = O_GRADE_POINT_SYSTEM_DETAIL::grade_point_system_details( $gps->gps_id )->get();
            $batch_info = O_BATCH::where(['ID' =>$student_info->batch_id, 'DEPARTMENT_ID' => $student_info->department_id])->first();
            $exempted_semester = O_SEMESTERS::where(['department_id' => $student_info->department_id, 'batch_id' => $student_info->batch_id, 'exempted' => 1])->with(['allocatedCourses.course'])->get()->toArray();
            /**
             * when all marksheet approved by exam committee then resultsheet status will be = 4 and resultsheet will be published on provisional
             * status >=4 means pending in  env('DEPT_CHAIRMAN')
             */
            $semesters = O_SEMESTERS::whereIn('id', $semester_info_table_ids )->where('result_tabulation_status', '>=', env('DEPT_CHAIRMAN'))->with([
                'allocatedCourses.course',
                'allocatedCourses.marks' => function($q) use ($student_id){
                    $q->where(['std_id' => $student_id])->get();
                }
            ])->orderBy('semester', 'asc')->get();

            $creators = M_WP_EMP::select('ID','NAME')->whereIn('id', $semesters->pluck('creator_id'))->get();

            foreach ($semesters as $semester){
                $semester->created_by_user = $creators->where('ID', $semester->creator_id)->first();
            }

            $semesters = $semesters->toArray();


            $merge_semester = array_merge($exempted_semester, $semesters);

            $blank_semester = array_diff(range(1, $batch_info->no_of_semester), array_column($merge_semester, 'semester'));
            foreach ($blank_semester as $k => $v) {
                $blank_semester[$k] = [
                    'semester' => $v,
                    'exempted' => 0,
                    'total_credit' => 0,
                    'total_subject' => 0,
                    'allocated_courses' => NULL,
                ];
            }

            $transcripts = array_merge($blank_semester, $merge_semester);

            usort($transcripts, function ($a, $b) { return (int)$a['semester'] - (int)$b['semester']; });

            $total_credit_required = array_sum(array_column($transcripts, 'total_credit'));
            $exempted_credit = 0;
            $total_credit_earned = 0;
            $total_cgpa = 0;
            $semesters_data= [];
            $count = 0;
            $i = 0;
            $allocated_courses=[];
            $total_semester_sgpa = 0;
            $has_fail_or_incmplete_in_atleast_one_subject = false;

            foreach ($transcripts as $transcripts_key =>  $transcripts_value) {
                if ($transcripts_value['exempted'] == 1) {
                    $exempted_credit = $exempted_credit + $transcripts_value['total_credit'];
                }
                $total_semester_gpa = 0;

                if (!empty($transcripts_value['allocated_courses'])) {
                    foreach ($transcripts_value['allocated_courses'] as $allocated_courses_key => $allocated_courses_value) {
                        $allocated_courses[$transcripts_key][$allocated_courses_key] = [
                           'id' => $allocated_courses_value['course']['id'],
                            'name' => $allocated_courses_value['course']['name'],
                            'code' => $allocated_courses_value['course']['code'],
                            'credit' => $allocated_courses_value['course']['credit'],
                            'improvable_mark' => $allocated_courses_value['course']['improvable_mark'],
                            'total_mark' => $allocated_courses_value['course']['total_mark'],
                            'course_type' => $allocated_courses_value['course']['course_type'],
                        ];

                        if ($transcripts_value['exempted'] == 0) {
                            if (!empty($allocated_courses_value['marks'])) {

                                if ( $allocated_courses_value['marks'][0]['letter_grade'] == '-'
                                    || $allocated_courses_value['marks'][0]['letter_grade'] == 'F'
                                    || $allocated_courses_value['marks'][0]['letter_grade'] == null
                                ){
                                    if ( $has_fail_or_incmplete_in_atleast_one_subject == false  ){
                                        $has_fail_or_incmplete_in_atleast_one_subject = true;
                                    }
                                }

                                $allocated_courses[$transcripts_key][$allocated_courses_key]['marks'] = [
                                    'id' => $allocated_courses_value['marks'][0]['id'],
                                    'conti_total' => $allocated_courses_value['marks'][0]['conti_total'],
                                    'final_total' => $allocated_courses_value['marks'][0]['final_total'],
                                    'course_total' => $allocated_courses_value['marks'][0]['course_total'],
                                    'letter_grade' => $allocated_courses_value['marks'][0]['letter_grade'],
                                    'grade_point' => $allocated_courses_value['marks'][0]['grade_point'],
                                    'status_mid' => $allocated_courses_value['marks'][0]['status_mid'],
                                    'status_final' => $allocated_courses_value['marks'][0]['status_final'],
                                ];

                                if ($allocated_courses_value['marks'][0]['grade_point'] > 0) {
                                    $total_credit_earned = ($total_credit_earned + $allocated_courses_value['course']['credit']);
                                    $total_semester_gpa = ($total_semester_gpa + ($allocated_courses_value['course']['credit'] * $allocated_courses_value['marks'][0]['grade_point']));
                                }
                            }else{
                                $allocated_courses[$transcripts_key][$allocated_courses_key]['marks'] = [
                                    'final_total' => '',
                                    'course_total' => '',
                                    'letter_grade' => '',
                                    'grade_point' => '',
                                    'status_mid' => '',
                                    'status_final' => '',
                                ];
                            }
                        }
                    }

                    if ($transcripts_value['exempted'] == 0) {
                        //$total_semester_sgpa = ($transcripts_value['total_credit'] > 0) ? round(($total_semester_gpa/$transcripts_value['total_credit']), 2) : 0;
                        //$total_cgpa = round(($total_cgpa + round(($transcripts_value['total_credit'] * $total_semester_sgpa), 2)), 2);

                        $total_semester_sgpa = ($transcripts_value['total_credit'] > 0) ? ($total_semester_gpa/$transcripts_value['total_credit']) : 0;
                        $total_cgpa = ($total_cgpa + ($transcripts_value['total_credit'] * $total_semester_sgpa));

                        foreach ($grade_point_system_details as $gps_key => $semester_gps_value) {
                            if ($semester_gps_value->grade_point <= $total_semester_sgpa) {
                                $semester_average_grade = $semester_gps_value->letter;
                                break;
                            }
                        }
                    }
                }

                if(!empty($allocated_courses[$transcripts_key])){
                    $semesters_data = [
                        'semester' => $transcripts_value['semester'],
                        'exempted' => $transcripts_value['exempted'],
                        'total_subject' => $transcripts_value['total_subject'],
                        'total_credit' => $transcripts_value['total_credit'],
                        'total_semester_gpa' => ($transcripts_value['exempted'] == 0) ? $total_semester_sgpa : 'Exempted',
                        'average_grade' => ($transcripts_value['exempted'] == 0) ? $semester_average_grade : 'Exempted',
                        'semester_result' => ($transcripts_value['exempted'] == 0) ? ($semester_average_grade != 'F') ? 'Passed' : '' : 'Exempted',
                        'allocated_courses' =>  $allocated_courses[$transcripts_key],
                    ];
                }else{
                    $semesters_data = [
                        'semester' => $transcripts_value['semester'],
                        'allocated_courses' =>  'Semester or Marks not exists',
                    ];
                }

                if( $count % 2  == 1 ){
                    $transcript_data['semesters'][$i][] = $semesters_data;
                    $i++;
                    $count =0;
                }
                else {
                    $transcript_data['semesters'][$i][] = $semesters_data;
                    $count++;
                }
            }

            $total_earned_cgpa = ($total_cgpa > 0 && $total_credit_earned > 0) ? round(($total_cgpa/$total_credit_earned), 2) : 0;

            foreach ($grade_point_system_details as $gps_key => $total_gps_value) {
                if ($total_gps_value->grade_point <= $total_earned_cgpa) {
                    $total_average_grade = $total_gps_value->letter;
                    break;
                }
            }


            $transcript_data['results'] = [
                'total_credit_required' => $total_credit_required,
                'exempted_credit' => $exempted_credit,
                'total_credit_earned' => $total_credit_earned,
                'cgpa' => $has_fail_or_incmplete_in_atleast_one_subject?'Incomplete':$total_earned_cgpa,
                'grade_letter' => $has_fail_or_incmplete_in_atleast_one_subject?'Incomplete':$total_average_grade,
                'semesters' => $semesters
            ];
            $data['transcript_data'] = $transcript_data;
            $data['student_info'] = $student_info;
            $data['grade_point_system_details'] = $grade_point_system_details;

            return $data;

        }
    }




    public function duplicate_values($item)
    {
        $this->duplicate_values[] = $item;
    }
}
