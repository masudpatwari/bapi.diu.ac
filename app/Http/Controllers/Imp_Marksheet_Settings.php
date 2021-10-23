<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use App\Rules\GreaterThanZeroRule;
use App\Rules\LessThanEqualAnotherFieldRule;
use App\Models\O_GRADE_POINT_SYSTEM_DETAIL;

class Imp_Marksheet_Settings extends Controller
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {

    }

    public function view_marksheet_table($students, $marks)
    {
        $data = [];
        foreach ($students as $students_key => $students_value) {
            if (!empty($marks)) {
                foreach ($marks as $marks_key => $marks_value) {
                    $marks_value = (array)$marks_value;
                    if ($students_value->id == $marks_value['std_id']) {

                        $data[$marks_value['id']] = [
                            'student_id' => base64_encode($students_value->id),
                            'name' => $students_value->name,
                            'roll' => $students_value->roll_no,
                            'reg_code' => $students_value->reg_code,
                            'batch_name' => $students_value->batch_name,
                            'marks_value' => [
                                'marks_id' => $marks_value['id'],
                                'status_mid' => $marks_value['status_mid'],
                                'status_mid_text' => attendance( $marks_value['status_mid'] ),
                                'continuous_assessment' => [
                                    'mt' => ($marks_value['mt'] != NULL) ? $marks_value['mt'] : "",
                                    'ct' => ($marks_value['ct'] != NULL) ? $marks_value['ct'] : "",
                                    'ap' => ($marks_value['ap'] != NULL) ? $marks_value['ap'] : "",
                                    'ab' => ($marks_value['ab'] != NULL) ? $marks_value['ab'] : "",
                                ],
                                'continuous_assessment_total' => ($marks_value['status_mid'] == 'p') ? $marks_value['conti_total'] : attendance( $marks_value['status_mid'] ),
                                'status_final' => $marks_value['status_final'],
                                'status_final_text' => attendance( $marks_value['status_final'] ),
                                'final_examination' => [
                                    'm1' => ($marks_value['m1'] != NULL) ? $marks_value['m1'] : "",
                                    'm2' => ($marks_value['m2'] != NULL) ? $marks_value['m2'] : "",
                                    'm3' => ($marks_value['m3'] != NULL) ? $marks_value['m3'] : "",
                                    'm4' => ($marks_value['m4'] != NULL) ? $marks_value['m4'] : "",
                                    'm5' => ($marks_value['m5'] != NULL) ? $marks_value['m5'] : "",
                                    'm6' => ($marks_value['m6'] != NULL) ? $marks_value['m6'] : "",
                                    'm7' => ($marks_value['m7'] != NULL) ? $marks_value['m7'] : "",
                                    'm8' => ($marks_value['m8'] != NULL) ? $marks_value['m8'] : "",
                                ],
                                'final_examination_total' => ($marks_value['status_final'] == 'p') ? $marks_value['final_total'] : attendance( $marks_value['status_final'] ),
                                'course_total' => ($marks_value['course_total'] != NULL) ? $marks_value['course_total'] : NULL,
                                'letter_grade' => ($marks_value['letter_grade'] != NULL) ? $marks_value['letter_grade'] : NULL,
                                'grade_point' => ($marks_value['grade_point'] != NULL) ? number_format($marks_value['grade_point'], 2) : NULL,
                                'datetime' => NULL,
                                'gps_id' => $marks_value['gps_id'],
                                'is_modified' => NULL,
                            ]
                        ];
                    }
                }
            }
        }
        return $data;
    }

    public function marksheet_entry_array_for_draft($marks_value, $mk_info, $type, $icta_id)
    {
        $ctype = $mk_info->course_type;
        $course_id = $mk_info->id;
        $theory = \RMS_COURSE_TYPE::THEORY;
        $insert_array = [];
        foreach ($marks_value as $mkv_key => $mkv_value) {
            $continuous_assessment_total = ($ctype == $theory) ? array_sum($mkv_value['continuous_assessment']) : 0;
            $final_examination_total = array_sum($mkv_value['final_examination']);

            $status_mid = ($ctype == $theory) ? $mkv_value['status_mid'] : 'p';
            $status_final = $mkv_value['status_final'];

            $mt = ($status_mid == 'p') ? ($ctype == $theory) ? $mkv_value['continuous_assessment']['mt'] : NULL : NULL;
            $ct = (isset($mkv_value['continuous_assessment']['ct'])) ? $mkv_value['continuous_assessment']['ct'] : NULL;
            $ap = (isset($mkv_value['continuous_assessment']['ap'])) ? $mkv_value['continuous_assessment']['ap'] : NULL;
            $ab = (isset($mkv_value['continuous_assessment']['ab'])) ? $mkv_value['continuous_assessment']['ab'] : NULL;
            $conti_total = ($status_mid == 'p') ? $continuous_assessment_total : NULL;

            $m1 = ($status_final == 'p') ? $mkv_value['final_examination']['m1'] : NULL;
            $m2 = ($status_final == 'p') ? $mkv_value['final_examination']['m2'] : NULL;
            $m3 = ($status_final == 'p') ? $mkv_value['final_examination']['m3'] : NULL;
            $m4 = ($status_final == 'p') ? $mkv_value['final_examination']['m4'] : NULL;
            $m5 = ($status_final == 'p') ? ($ctype == $theory) ? $mkv_value['final_examination']['m5'] : NULL : NULL;
            $m6 = ($status_final == 'p') ? ($ctype == $theory) ? $mkv_value['final_examination']['m6'] : NULL : NULL;
            $m7 = ($status_final == 'p') ? ($ctype == $theory) ? $mkv_value['final_examination']['m7'] : NULL : NULL;
            $m8 = ($status_final == 'p') ? ($ctype == $theory) ? $mkv_value['final_examination']['m8'] : NULL : NULL;

            $final_total = ($status_final == 'p') ? $final_examination_total : NULL;
            $course_total = NULL;
            $letter_grade = NULL;
            $grade_point = NULL;
            $roll = $mkv_value['roll'];
            $gps_id = $mkv_value['gps_id'];

            $insert_array[$mkv_key] = array(
                'id' => $mkv_key,
                'status_mid' => $status_mid,
                'mt' => $mt,
                'ct' => $ct,
                'ap' => $ap,
                'ab' => $ab,
                'conti_total' => $conti_total,
                'status_final' => $status_final,
                'm1' => $m1,
                'm2' => $m2,
                'm3' => $m3,
                'm4' => $m4,
                'm5' => $m5,
                'm6' => $m6,
                'm7' => $m7,
                'm8' => $m8,
                'm9' => 0,
                'm10' => 0,
                'final_total' => $final_total,
                'course_total' => $course_total ,
                'letter_grade' => $letter_grade,
                'grade_point' => $grade_point,
                'roll' => $roll,
                'gps_id' => $gps_id,
                'course_id' => $course_id,
                'icta_id' => $icta_id,
                'std_id' => base64_decode($mkv_value['student_id']),
                'exam_type' => $type,
            );
        }
        return $insert_array;
    }

    public function marksheet_entry_array_for_final($marks_value, $mk_info, $type, $icta_id)
    {
        $ctype = $mk_info->course_type;
        $non_theory = \RMS_COURSE_TYPE::NON_THEORY;
        $theory = \RMS_COURSE_TYPE::THEORY;
        $use_creator_id = TRUE;
        /*$passed_mark = round($gps_details[0]['pc_mark']);
        $failed_mark = round($gps_details[(sizeof($gps_details) - 1)]['pc_mark']);*/
        $insert_array = [];

        foreach ($marks_value as $mkv_key => $mkv_value) {

            $gps_details = O_GRADE_POINT_SYSTEM_DETAIL::grade_point_system_details($mkv_value['gps_id'])->get();
            $passed_mark = round($gps_details[0]['pc_mark']);
            $failed_mark = round($gps_details[(sizeof($gps_details) - 1)]['pc_mark']);

            $continuous_assessment_total = ($ctype == $theory) ? array_sum($mkv_value['continuous_assessment']) : 0;
            $final_examination_total = array_sum($mkv_value['final_examination']);

            $result_course_total = round(( ($ctype == $theory) ? $continuous_assessment_total + $final_examination_total : $final_examination_total ));
            $unit_course_total = ((100 / $mk_info->total_mark) * $result_course_total);

            $gps_details_count = sizeof($gps_details);

            for ($i = 0; $i < $gps_details_count; $i++) {
                if ($i > 0) {
                    $ckey = ($i - 1);
                } else {
                    $ckey = 0;
                }
                $gpc_marks = round($gps_details[$i]['pc_mark']);
                $lpc_marks = round($gps_details[$ckey]['pc_mark']);

                if ($unit_course_total >= $passed_mark) {
                    $gpl['gl'][$mkv_key] = ($gps_details[0]['letter']);
                    $gpl['gp'][$mkv_key] = ($gps_details[0]['grade_point']);
                } else if ($unit_course_total < $failed_mark) {
                    $gpl['gl'][$mkv_key] = ($gps_details[(sizeof($gps_details) - 1)]['letter']);
                    $gpl['gp'][$mkv_key] = ($gps_details[(sizeof($gps_details) - 1)]['grade_point']);
                } else if ($unit_course_total <= $passed_mark && $unit_course_total >= $failed_mark) {
                    if ($unit_course_total >= $gpc_marks && $unit_course_total < $lpc_marks) {
                        $gpl['gl'][$mkv_key] = ($gps_details[$i]['letter']);
                        $gpl['gp'][$mkv_key] = ($gps_details[$i]['grade_point']);
                    }
                }
            }

            $status_mid = ($ctype == $theory) ? $mkv_value['status_mid'] : 'p';
            $status_final = $mkv_value['status_final'];
            $array = [];

            if ($type == 'incourse' && $ctype == $theory && $status_mid != 'a') {
                $mt = ($status_mid == 'p') ? ($ctype == $theory) ? $mkv_value['continuous_assessment']['mt'] : NULL : NULL;
                $ct = (isset($mkv_value['continuous_assessment']['ct'])) ? $mkv_value['continuous_assessment']['ct'] : NULL;
                $ap = (isset($mkv_value['continuous_assessment']['ap'])) ? $mkv_value['continuous_assessment']['ap'] : NULL;
                $ab = (isset($mkv_value['continuous_assessment']['ab'])) ? $mkv_value['continuous_assessment']['ab'] : NULL;
                $conti_total = ($status_mid == 'p') ? $continuous_assessment_total : NULL;
                $array = [
                    'status_mid' => $status_mid,
                    'mt' => $mt,
                    'ct' => $ct,
                    'ap' => $ap,
                    'ab' => $ab,
                    'conti_total' => $conti_total,
                ];
            }
            if ($type == 'final' && $ctype == $theory && $status_final != 'a') {
                $m1 = ($status_final == 'p') ? $mkv_value['final_examination']['m1'] : NULL;
                $m2 = ($status_final == 'p') ? $mkv_value['final_examination']['m2'] : NULL;
                $m3 = ($status_final == 'p') ? $mkv_value['final_examination']['m3'] : NULL;
                $m4 = ($status_final == 'p') ? $mkv_value['final_examination']['m4'] : NULL;
                $m5 = ($status_final == 'p') ? ($ctype == $theory) ? $mkv_value['final_examination']['m5'] : NULL : NULL;
                $m6 = ($status_final == 'p') ? ($ctype == $theory) ? $mkv_value['final_examination']['m6'] : NULL : NULL;
                $m7 = ($status_final == 'p') ? ($ctype == $theory) ? $mkv_value['final_examination']['m7'] : NULL : NULL;
                $m8 = ($status_final == 'p') ? ($ctype == $theory) ? $mkv_value['final_examination']['m8'] : NULL : NULL;
                $final_total = ($status_final == 'p') ? $final_examination_total : NULL;
                $array = [
                    'status_final' => $status_final,
                    'm1' => $m1,
                    'm2' => $m2,
                    'm3' => $m3,
                    'm4' => $m4,
                    'm5' => $m5,
                    'm6' => $m6,
                    'm7' => $m7,
                    'm8' => $m8,
                    'm9' => 0,
                    'm10' => 0,
                    'final_total' => $final_total,
                ];
            }
            if ($type == 'final' && $ctype == $non_theory && $status_final != 'a') {
                $m1 = ($status_final == 'p') ? $mkv_value['final_examination']['m1'] : NULL;
                $m2 = ($status_final == 'p') ? $mkv_value['final_examination']['m2'] : NULL;
                $m3 = ($status_final == 'p') ? $mkv_value['final_examination']['m3'] : NULL;
                $m4 = ($status_final == 'p') ? $mkv_value['final_examination']['m4'] : NULL;
                $final_total = ($status_final == 'p') ? $final_examination_total : NULL;
                $array = [
                    'status_final' => $status_final,
                    'm1' => $m1,
                    'm2' => $m2,
                    'm3' => $m3,
                    'm4' => $m4,
                    'final_total' => $final_total,
                ];
            }

            $course_total = ($status_mid == 'e' || $status_final == 'e') ? NULL : $result_course_total;
            $letter_grade = ($status_mid == 'e' || $status_final == 'e') ? NULL : $gpl['gl'][$mkv_key];
            $grade_point = ($status_mid == 'e' || $status_final == 'e') ? NULL : $gpl['gp'][$mkv_key];
            $roll = $mkv_value['roll'];
            $gps_id = $mkv_value['gps_id'];
            $datetime = time();

            /*$plain_text = "status_mid=$status_mid,mt=$mt,ct=$ct,ap=$ap,ab=$ab,conti_total=$conti_total,status_final=$status_final,m1=$m1,m2=$m2,m3=$m3,m4=$m4,m5=$m5,m6=$m6,m7=$m7,m8=$m8,m9=0,m10=0,final_total=$final_total,course_total=$course_total,letter_grade=$letter_grade,grade_point=$grade_point,roll=$roll";
            $encrypted_text = encrypt($plain_text);*/

            $merge_array = array_merge($array, [
                'course_total' => $course_total ,
                'letter_grade' => $letter_grade,
                'grade_point' => $grade_point,
                'roll' => $roll,
                'gps_id' => $gps_id,
                'datetime' => $datetime,
                'is_modified' => 1,
            ]);

            $insert_array[$mkv_key] = $merge_array;
        }

        return $insert_array;
    }


    public function create_mark_history( $o, $n, $note_no )
    {
        return [
            'marks_id' => $o['marks_value']['marks_id'],

            'o_status_mid' => $o['marks_value']['status_mid'],
            'o_mt' => $o['marks_value']['continuous_assessment']['mt'],
            'o_ct' => $o['marks_value']['continuous_assessment']['ct'],
            'o_ap' => $o['marks_value']['continuous_assessment']['ap'],
            'o_ab' => $o['marks_value']['continuous_assessment']['ab'],
            'o_conti_total' => ($o['marks_value']['status_mid'] == 'p') ? $o['marks_value']['continuous_assessment_total'] : NULL,
            'o_status_final' => $o['marks_value']['status_final'],
            'o_m1' => $o['marks_value']['final_examination']['m1'],
            'o_m2' => $o['marks_value']['final_examination']['m2'],
            'o_m3' => $o['marks_value']['final_examination']['m3'],
            'o_m4' => $o['marks_value']['final_examination']['m4'],
            'o_m5' => $o['marks_value']['final_examination']['m5'],
            'o_m6' => $o['marks_value']['final_examination']['m6'],
            'o_m7' => $o['marks_value']['final_examination']['m7'],
            'o_m8' => $o['marks_value']['final_examination']['m8'],
            'o_m9' => NULL,
            'o_m10' => NULL,
            'o_final_total' => ($o['marks_value']['status_final'] == 'p') ? $o['marks_value']['final_examination_total'] : NULL,
            'o_course_total' => $o['marks_value']['course_total'],
            'o_letter_grade' => $o['marks_value']['letter_grade'],
            'o_grade_point' => $o['marks_value']['grade_point'],


            'n_status_mid' => $n['marks_value']['status_mid'],
            'n_mt' => $n['marks_value']['continuous_assessment']['mt'],
            'n_ct' => $n['marks_value']['continuous_assessment']['ct'],
            'n_ap' => $n['marks_value']['continuous_assessment']['ap'],
            'n_ab' => $n['marks_value']['continuous_assessment']['ab'],
            'n_conti_total' => ($n['marks_value']['status_mid'] == 'p') ? $n['marks_value']['continuous_assessment_total'] : NULL,
            'n_status_final' => $n['marks_value']['status_final'],
            'n_m1' => $n['marks_value']['final_examination']['m1'],
            'n_m2' => $n['marks_value']['final_examination']['m2'],
            'n_m3' => $n['marks_value']['final_examination']['m3'],
            'n_m4' => $n['marks_value']['final_examination']['m4'],
            'n_m5' => $n['marks_value']['final_examination']['m5'],
            'n_m6' => $n['marks_value']['final_examination']['m6'],
            'n_m7' => $n['marks_value']['final_examination']['m7'],
            'n_m8' => $n['marks_value']['final_examination']['m8'],
            'n_m9' => NULL,
            'n_m10' => NULL,
            'n_final_total' => ($n['marks_value']['status_final'] == 'p') ? $n['marks_value']['final_examination_total'] : NULL,
            'n_course_total' => $n['marks_value']['course_total'],
            'n_letter_grade' => $n['marks_value']['letter_grade'],
            'n_grade_point' => $n['marks_value']['grade_point'],
            'creator_id' => session('user.id'),
            'datetime' => time(),
            'ip' => request()->ip(),
            'note_no' => $note_no,
        ];
    }

    public function replace_marks_old2new($mk_info, $o, $n, $type, $checked )
    {
        $theory = \RMS_COURSE_TYPE::THEORY;
        $non_theory = \RMS_COURSE_TYPE::NON_THEORY;
        $request_marks_array = [];
        foreach ($n as $n_key => $n_value) {
            if ($mk_info->course_type == 0){
                unset($o[$n_key]['marks_value']['final_examination']['m5']);
                unset($o[$n_key]['marks_value']['final_examination']['m6']);
                unset($o[$n_key]['marks_value']['final_examination']['m7']);
                unset($o[$n_key]['marks_value']['final_examination']['m8']);
            }
            $o_string = $o[$n_key]['marks_value']['status_mid'].str_replace(',', '_', implode(',', $o[$n_key]['marks_value']['continuous_assessment'])).''.$o[$n_key]['marks_value']['status_final'].str_replace(',', '_', implode(',', $o[$n_key]['marks_value']['final_examination']));

            if ($type == 'incourse'){
                $n_string = $n_value['status_mid'].str_replace(',', '_', implode(',', $n_value['continuous_assessment'])).''.$o[$n_key]['marks_value']['status_final'].str_replace(',', '_', implode(',', $o[$n_key]['marks_value']['final_examination']));;
                $n_value['status_final'] = $o[$n_key]['marks_value']['status_mid'];
                $n_value['final_examination'] = $o[$n_key]['marks_value']['final_examination'];
                $n_value['final_examination_total'] = $o[$n_key]['marks_value']['final_examination_total'];
            }

            if ($type == 'final'){
                $n_string = $o[$n_key]['marks_value']['status_mid'].str_replace(',', '_', implode(',', $o[$n_key]['marks_value']['continuous_assessment'])).''.$n_value['status_final'].str_replace(',', '_', implode(',', $n_value['final_examination']));
                $n_value['status_mid'] = $o[$n_key]['marks_value']['status_mid'];
                $n_value['continuous_assessment'] = $o[$n_key]['marks_value']['continuous_assessment'];
                $n_value['continuous_assessment_total'] = $o[$n_key]['marks_value']['continuous_assessment_total'];
                $n_value['continuous_assessment_total'] = $o[$n_key]['marks_value']['continuous_assessment_total'];
            }
            if ($checked) {
                if ($o_string != $n_string){
                    $n_value['gps_id'] = $o[$n_key]['marks_value']['gps_id'];
                    $request_marks_array[$n_key] = $n_value;
                }
            }
            else
            {
                $n_value['gps_id'] = $o[$n_key]['marks_value']['gps_id'];
                $request_marks_array[$n_key] = $n_value;
            }
        }
        return $request_marks_array;
    }

    public function marksheet_validation($request, $si, $type )
    {

        /*
         * If javascript validation off user has been blocked.
         *
         * */

        $incourse_mark = $si->incourse_mark;
        $final_mark = $si->final_mark;
        $rules = [];

        if ($type == 'incourse') {
            foreach ($request->mk_value as $key => $value) {
                $rules['mk_value.'.$key.'.continuous_assessment.mt'] = 'nullable|numeric|min:0|max:'.$incourse_mark.'';
                $rules['mk_value.'.$key.'.continuous_assessment.ct'] = 'nullable|numeric|min:0|max:'.$incourse_mark.'';
                $rules['mk_value.'.$key.'.continuous_assessment.ap'] = 'nullable|numeric|min:0|max:'.$incourse_mark.'';
                $rules['mk_value.'.$key.'.continuous_assessment.ab'] = 'nullable|numeric|min:0|max:'.$incourse_mark.'';
                $rules['mk_value.'.$key.'.total_continuous_assessment'] = [
                    'bail',
                    'required_if:mk_value.'.$key.'.status_mid,p',
                    new GreaterThanZeroRule($value['status_mid']),
                    new LessThanEqualAnotherFieldRule($incourse_mark, $value['status_mid']),
                ];
            }
        }

        if ($type == 'final') {
            foreach ($request->mk_value as $key => $value) {
                $rules['mk_value.'.$key.'.final_examination.m1'] = 'nullable|numeric|min:0|max:'.$final_mark.'';
                $rules['mk_value.'.$key.'.final_examination.m2'] = 'nullable|numeric|min:0|max:'.$final_mark.'';
                $rules['mk_value.'.$key.'.final_examination.m3'] = 'nullable|numeric|min:0|max:'.$final_mark.'';
                $rules['mk_value.'.$key.'.final_examination.m4'] = 'nullable|numeric|min:0|max:'.$final_mark.'';
                $rules['mk_value.'.$key.'.final_examination.m5'] = 'nullable|numeric|min:0|max:'.$final_mark.'';
                $rules['mk_value.'.$key.'.final_examination.m6'] = 'nullable|numeric|min:0|max:'.$final_mark.'';
                $rules['mk_value.'.$key.'.final_examination.m7'] = 'nullable|numeric|min:0|max:'.$final_mark.'';
                $rules['mk_value.'.$key.'.final_examination.m8'] = 'nullable|numeric|min:0|max:'.$final_mark.'';
                $rules['mk_value.'.$key.'.total_final_examination'] = [
                    'bail',
                    'required_if:mk_value.'.$key.'.status_final,p',
                    new GreaterThanZeroRule($value['status_final']),
                    new LessThanEqualAnotherFieldRule($final_mark, $value['status_final']),
                ];
            }
        }

        $request->validate($rules);
    }
}
