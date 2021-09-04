<?php


/**
 * Date : 2018-Jun-20;
 * Developer Name : Md. Mesbaul Islam || Arif Bin A. Aziz;
 * Contact : 01738120411;
 * E-mail : rony.max24@gmail.com;
 * Theme Name: Result Management System;
 * Theme URI: N/A;
 * Author: Dhaka International University;
 * Author URI: N/A;
 * Version: 1.1.0
 */

class RMS_status
{
    const EMPTY_STATUS = 0;
    const DRAFT_STATUS = 1;
    const FINAL_STATUS = 3;
    const EXAM_COMMITTEE_CHAIRMAN_APPROVED = 4;
    const DEPT_CHAIRMAN_APPROVED = 5;
    const OFFICER_OF_CONTROLLER_EXAMINITION_APPROVED = 6;  /* Officer of the controller of examination */
    const CONTROLLER_OF_EXAMINITION_APPROVED = 7;  /* Controller of examination */
    const BOT_APPROVED = 8;
    const FINAL_APPROVED = 9;
}

class RMS_COURSE_TYPE
{
    const THEORY = 1;
    const NON_THEORY = 0;
}

class RMS_PROGRAMS_TYPE
{
    const HONS = ['CS-E', 'CS-D', 'EE-D', 'EE-E', 'CE-D', 'CE-E', 'SO-E', 'SO-D', 'PH', 'EN-D', 'EN-E', 'LL-D', 'LL-E', 'BED', 'BS-D', 'BS-E', 'LL-P','EC-D','EC-E','PS', 'DEP-CLOSE', 'DEP-CLOSED'];
    const MASTERS = ['SO-M2', 'CS-M', 'EN-M1', 'EN-M2', 'SO-M1', 'BS-M1', 'BS-M2', 'LL-HR', 'LL-M1', 'LL-M2', 'MCA', 'MED','EC-M1','EC-M2'];
}

class RMS_rolename
{
    const ADMIN = 'admin';
    const CHAIRMAN = 'chairman';
    const SUPER_USER = 'su';
}

function is_super_user(){
    return in_array(\RMS_rolename::SUPER_USER,get_current_user_roles_name_toLowerLetter_array());
}

function is_dept_chairman(){
    return in_array(\RMS_rolename::CHAIRMAN,get_current_user_roles_name_toLowerLetter_array());
}
/*
 * Marksheet and resultsheet approve button
 *
 */
function has_approved_button( $status ){
    if (in_array(env('DEPT_CHAIRMAN'), get_current_user_role_ids()) && ($status == RMS_status::EXAM_COMMITTEE_CHAIRMAN_APPROVED)){
        return true;
    }
    if (in_array(env('OFFICER_OF_CONTROLLER_EXAMINITION'), get_current_user_role_ids()) && ($status == RMS_status::DEPT_CHAIRMAN_APPROVED)){
        return true;
    }
    if (in_array(env('CONTROLLER_OF_EXAMINITION'), get_current_user_role_ids()) && ($status == RMS_status::OFFICER_OF_CONTROLLER_EXAMINITION_APPROVED)){
        return true;
    }
    if (in_array(env('BOT'), get_current_user_role_ids()) && ($status == RMS_status::CONTROLLER_OF_EXAMINITION_APPROVED)){
        return true;
    }
}

function course_type( $type )
{
    return ($type == 0) ? 'Non Theory' : 'Theory';
}

function get_current_user_roles(){
    $roles = \App\Models\M_RMS_EMP_ROLES::where(['emp_id' => session('user.id')])->get();
    $role_array = [];
    foreach ($roles as $role_value){
        $role_array[] = $role_value->role_id;
    }
    return $role_array;
}

/**
 * get current user roles
 *
 * @return array role ids
 */

function get_current_user_role_ids(){
    static $role_ids;
    if( $role_ids ) return $role_ids;

    $role_ids = \App\Models\M_RMS_EMP_ROLES::where(['emp_id' => session('user.id')])->pluck('role_id')->toArray();
    return $role_ids;
}

function get_current_user_roles_name(){
    $role_ids = get_current_user_role_ids();
    $role_names = \App\Models\M_RMS_ROLES::whereIn('id',$role_ids)->pluck('role_name')->toArray();
    return implode(', ', $role_names);
}

function get_current_user_roles_name_toLowerLetter_array(){
    static $role_name_to_lower_letter ;

    if($role_name_to_lower_letter) return $role_name_to_lower_letter;

    $role_ids = get_current_user_role_ids();
    $role_name_array = \App\Models\M_RMS_ROLES::whereIn('id',$role_ids)->pluck('role_name')->toArray();
    $role_name_to_lower_letter =[];

    foreach ($role_name_array as $name )
    $role_name_to_lower_letter []= strtolower($name);

    return $role_name_to_lower_letter;
}



function approve_status( $approve_status )
{
    $flow = [
        0 => [
            'value' => 0,
            'txt' => 'Entry Marksheet',
        ],
        1 => [
            'value' => 1,
            'txt' => 'Draft Submit',
        ],
        2 => [
            'value' => 3,
            'txt' => 'Final Submit',
        ],
        3 => [
            'value' => 3,
            'txt' => 'Final Submit',
        ],
        4 => [
            'value' => 4,
            'txt' => 'Approved by Exam Committee Chairman',
        ],
        5 => [
            'value' => 5,
            'txt' => 'Approved by Dept. Chairman',
        ],
        6 => [
            'value' => 6,
            'txt' => 'Approved by Officer of the Controller of Examination',
        ],
        7 => [
            'value' => 7,
            'txt' => 'Approved by Controller of Examination',
        ],
        8 => [
            'value' => 8,
            'txt' => 'Approved by BOT',
        ]
    ];

    return [
        'prev_status' => ($approve_status > 0) ? $flow[$approve_status-1] : NULL,
        'current_status' => $flow[$approve_status],
        'next_status' => ($approve_status < 7) ? $flow[$approve_status+1] : NULL,
    ];
}

function attendance ( $status ){
    $data = [
        'a' => 'Abs',
        'e' => 'Exp',
        'p' => 'Present',
        'f' => 'Failed',
        'i' => 'Incomplete',
        'r' => 'Readmision',
        'ps' => 'Passed',
        'pa' => 'Incomplete',
        'pe' => 'Expelled',
        'ap' => 'Incomplete',
        'aa' => 'Absent',
        'ae' => 'Expelled',
        'ep' => 'Expelled',
        'ee' => 'Expelled',
        'ea' => 'Expelled',
        'pp' => 'Present',
    ];
    return $data[$status];
}

function remvoe_parentheses( $string ){
    return preg_replace("/\([^)]+\)/","", $string);
}

function generate_pdf( $html, $filename ){
    $mpdf = new \Mpdf\Mpdf();

    $mpdf->SetHTMLHeaderByName('MyHeader1', 'E', true);

    $mpdf->WriteHTML($html);

    return $mpdf->Output();


    /* $mpdf->SetTitle('Download');
     $mpdf->SetHTMLHeader('
     <div style="text-align: right; font-weight: bold;">
         My document
     </div>');
         $mpdf->SetHTMLFooter('
     <table width="100%">
         <tr>
             <td width="33%">{DATE j-m-Y}</td>
             <td width="33%" align="center">{PAGENO}/{nbpg}</td>
             <td width="33%" style="text-align: right;">My document</td>
         </tr>
     </table>');;
     $mpdf->WriteHTML(file_get_contents( public_path('pdf_style.css') ), 1);
     $mpdf->WriteHTML($html, 2);
     return $mpdf->Output('Marksheet', 'I');*/
}


function route_parent_has_permission($route_name ,$routes_array ){

    if( is_super_user()) return true;

    foreach ($routes_array as $key) {
      // var_dump($route_name, $key,strpos($route_name, $key)!==false);
      if(strpos( $key, $route_name)!==false) return true;
    }
}

function route_has_permission($route_name , $routes_array){

    if( is_super_user()) return true;

    if(in_array($route_name, $routes_array)) return true;
}


function separated_routes_array(){
    return [
        'logout',
        'enter_code',
        'check_login',
        'check_code',
        'check_mobile_no',
        'enter_code',
        'lock_user_page',
        'send_code_mobile',
        'sidebar_toggle',
        'topbar_toggle',
        'ajax_load_assigned_department',
        '/',
        'selectDepartment',
        'dashboard',
        'marksheet_view',
        'marksheet_entry',
        'marksheet_edit',
        'marksheet_pdf',
        'semester_details',
        'resultsheet_pdf',
        'resultsheet_view',
        'tabulationsheet_pdf',
        'tabulationsheet_view',
        'course_edit',
        'assign_role_view',
        'grade_point_system',
        'marksheet_approve',
        'approve_semester',
        'semester_update',
        'ajax_is_semester_exists',
        'single_mark_edit_form',
        'single_mark_entry_form',

        'imp_single_mark_edit__marksheet',
        'imp_single_mark_edit_form',
        'imp_single_mark_update',


        'notifications_home',

        'marksheet_comments_store',
        'tabulation_comments_store',
        'allocate_course',
        'allocation_store',
        'allocate_course_edit',
        'allocation_edit_update',
        'tabulationsheet_not_approve',
        'marksheet_not_approve',
        'marksheet_non_theory_pdf',
        'grade_point_system_delete',
        'grade_point_system_edit',
        'employee_access',
        'employee_access_store',
        'emp_sort_position',
        'emp_sort_position_store',
	    'visit_audit_log',
	    'exempted_semester_store',
	    'batch_split_update',
    ];
}


function multiSelected($key, $newArray=null, $dbArray=[])
{
    if(is_array($newArray))
    {
        return in_array($key,$newArray);
    }

    if(is_array($dbArray))
    {
        return in_array($key,$dbArray);
    }
    return false;
}


function isOldValue( $oldVal, $newVal )
{
    if (!empty($oldVal)){
        return $oldVal;
    }
    return $newVal;
}

/**
 * get user information as text. this will send userid, name, position, email and mobile.
 *
 * @return string
 */

function get_user_info($user_id = null){

    if($user_id!==null)
    {
        $user = \App\Models\M_WP_EMP::find($user_id);

        return "ID:". $user->id. "<br>".
            "Name:" . $user->name. "<br>".
            "position:" . $user->position . "<br>".
            "email:" .$user->email1 . "<br>".
            "mobile:" .$user->mobile1 . "<br>";
    }
    return "ID:". session('user.id') . "<br>".
        "Name:" .session('user.name') . "<br>".
        "position:" .session('user.position') . "<br>".
        "email:" .session('user.email') . "<br>".
        "mobile:" .session('user.mobile_no') . "<br>";
}


/**
 * get user info for notification
 *
 * @param int $user_id
 * @return string
 */
function get_user_info_for_notification(int $user_id){

    static $userArray = [];
    static $userData =[];

    if( in_array($user_id, $userArray)){
        return $userData[$user_id];
    }

    $user = \App\Models\M_WP_EMP::find($user_id);

    if($user)
    {
        return $userData[$user_id] = $user->name. ' ( '. $user->position . ' ) '.
            '<div class=\'text-hide\'>Email:' .$user->email1 . ', Mobile:' .$user->mobile1 . '</div>';
    }
    else{
        return $userData[$user_id] = "No User Found!";
    }
}

/**
 * get notification view setting by notification type
 *
 * @param string $notification_type
 * @return array keys: titile, link_title
 */
function get_notification_view_setting(string $notification_type='*'){

    $notification_settings=  [
        'semester_create' =>
            [
                'type_title'=>'New Semester Created',
                'title'=>'New Semester Created',
                'link_title'=>'Show Semester Detail',
                'show_action_link'=>true
            ],

        'semester_update_on_semester_settings' =>
            [
                'type_title'=>'Semester Settings Updated',
                'title'=>'Semester Settings Updated',
                'link_title'=>'Show Semester Detail',
                'show_action_link'=>true
            ],
        'semester_update_on_course_allocation' =>
            [
                'type_title'=>'Course Allocation Update',
                'title'=>'Course Allocation Update',
                'link_title'=>'Show Semester Detail',
                'show_action_link'=>true
            ],
        'semester_delete' =>
            [
                'type_title'=>'Semester Deleted',
                'title'=>'Semester Deleted',
                'link_title'=>'',
                'show_action_link'=>false
            ],
        'semester_approve' =>
            [
                'type_title'=>'Semester Approved',
                'title'=>'Semester Approved',
                'link_title'=>'Show Semester Detail',
                'show_action_link'=>true
            ],


        'marksheet_save' =>
            [
                'type_title'=>'Marksheet Submitted As Final',
                'title'=>'Marksheet Submitted, Waiting for Approval',
                'link_title'=>'Show Marksheet',
                'show_action_link'=>true
            ],
        'marksheet_approve' =>
            [
                'type_title'=>'Marksheet Approved',
                'title'=>'Marksheet Approved',
                'link_title'=>'Show Marksheet',
                'show_action_link'=>true
            ],
        'marksheet_deny' =>
            [
                'type_title'=>'Marksheet Denied',
                'title'=>'Marksheet Denied, Course Marksheet Status: Draft',
                'link_title'=>'Edit Marksheet',
                'show_action_link'=>true
            ],


        'marksheet_comment_submitted' =>
            [
                'type_title'=>'Comment on Marksheet',
                'title'=>'Comment on Marksheet',
                'link_title'=>'Show Marksheet',
                'show_action_link'=>true,
                'has_comment'=>true
            ],
        'tabulationsheet_comment_submitted' =>
            [
                'type_title'=>'Comment on Tabulationsheet',
                'title'=>'Comment on Tabulationsheet',
                'link_title'=>'Show Tabulationsheet',
                'show_action_link'=>true,
                'has_comment'=>true
            ],


        'tabulationsheet_approve' =>
            [
                'type_title'=>'Tabulationsheet Approved',
                'title'=>'Tabulationsheet Approved',
                'link_title'=>'Show Tabulationsheet',
                'show_action_link'=>true
            ],
        'tabulationsheet_deny' =>
            [
                'type_title'=>'Tabulationsheet Denied',
                'title'=>'Tabulationsheet Denied, Course Marks Status: Draft',
                'link_title'=>'Edit Marksheet',
                'show_action_link'=>true
            ],


        // split_batch  split_batch_created, split_batch_deleted, split_batch_approved, split_batch_updated

        'split_batch_created' =>
            [
                'type_title'=>'Batch Splitted ',
                'title'=>'Batch is splitted ',
                'link_title'=>'Show Batch Detail',
                'show_action_link'=>true
            ],

        'split_batch_approved' =>
            [
                'type_title'=>'Splitted Batch Approved',
                'title'=>'Splitted Batch is Approved',
                'link_title'=>'Show Batch Detail',
                'show_action_link'=>true
            ],

        'split_batch_updated' =>
            [
                'type_title'=>'Splitted Batch Updated',
                'title'=>'Splitted Batch is Updated',
                'link_title'=>'Show Batch Detail',
                'show_action_link'=>true
            ],

        'split_batch_deleted' =>
            [
                'type_title'=>'Split Batch Deleted',
                'title'=>'Split Batch Deleted',
                'link_title'=>'#',
                'show_action_link'=>false
            ],

        /**
         * improvemeent exam module
         */

        'impxmsedulcreate' =>
            [
                'type_title'=>'Improvement Exam Created',
                'title'=>'Improvement Exam Created',
                'link_title'=>'Show Improvement Exam',
                'show_action_link'=> true
            ],

        'impxmsedulapprove' =>
            [
                'type_title'=>'Improvement Exam Approved',
                'title'=>'Improvement Exam Approved',
                'link_title'=>'Show Improvement Exam',
                'show_action_link'=> true
            ],
        'impxmsedulupdated' =>
            [
                'type_title'=>'Improvement Exam Updated',
                'title'=>'Improvement Exam Updated',
                'link_title'=>'Show Improvement Exam',
                'show_action_link'=> true
            ],
        'impxmseduldelete' =>
            [
                'type_title'=>'Improvement Exam Deleted',
                'title'=>'Improvement Exam Deleted',
                'link_title'=>'#',
                'show_action_link'=> false
            ],
        'impxmseduldeny' =>
            [
                'type_title'=>'Improvement Exam Denied',
                'title'=>'Improvement Exam Denied',
                'link_title'=>'Show Improvement Exam',
                'show_action_link'=> true
            ],
        'impxmsedulteacherassigncreate' =>
            [
                'type_title'=>'Teacher Assigned on Improvement Exam',
                'title'=>'Approve/Deny Assigned Teacher on Improvement Exam',
                'link_title'=>'Show Assigned Teacher',
                'show_action_link'=> true
            ],
        'impxmsedulteacherassigndeny' =>
            [
                'type_title'=>'Denied Teacher Assigned on Improvement Exam',
                'title'=>'Show Assigned Teacher on Improvement Exam',
                'link_title'=>'Show Assigned Teacher',
                'show_action_link'=> true
            ],
        'impxmsedulteacherassignapprove' =>
            [
                'type_title'=>'Approved Teacher Assigned on Improvement Exam',
                'title'=>'Show Assigned Teacher on Improvement Exam',
                'link_title'=>'Show Assigned Teacher',
                'show_action_link'=> true
            ],
    ];

    if( $notification_type == '*') return $notification_settings;

    return $notification_settings[$notification_type];
}

function get_short_event_type_full_name( $event_type ){
    $event_name = [
        "trace url" => "Visited URL",
        "success" => 'Success',
        "danger" => 'Danger Message',
        "error" => 'Error Message',
        "warning" => 'Warning Message',
        "login" => 'Login Message',
    ];

    return $event_name[$event_type];

}

function semester_array( $selected = NULL )
{
    $semesters = [
        1 => [
            'id' => 1,
            'name' => 'First',
            'sn' => '1<sup>st</sup>'
        ],
        2 => [
            'id' => 2,
            'name' => 'Second',
            'sn' => '2<sup>nd</sup>'
        ],
        3 => [
            'id' => 3,
            'name' => 'Third',
            'sn' => '3<sup>rd</sup>'
        ],
        4 => [
            'id' => 4,
            'name' => 'Fourth',
            'sn' => '4<sup>th</sup>'
        ],
        5 => [
            'id' => 5,
            'name' => 'Fifth',
            'sn' => '5<sup>th</sup>'
        ],
        6 => [
            'id' => 6,
            'name' => 'Sixth',
            'sn' => '6<sup>th</sup>'
        ],
        7 => [
            'id' => 7,
            'name' => 'Seventh',
            'sn' => '7<sup>th</sup>'
        ],
        8 => [
            'id' => 8,
            'name' => 'Eighth',
            'sn' => '8<sup>th</sup>'
        ],
        9 => [
            'id' => 9,
            'name' => 'Nineth',
            'sn' => '9<sup>th</sup>'
        ],
        10 => [
            'id' => 10,
            'name' => 'Tenth',
            'sn' => '10<sup>th</sup>'
        ],
        11 => [
            'id' => 11,
            'name' => 'Eleventh',
            'sn' => '11<sup>th</sup>'
        ],
        12 => [
            'id' => 12,
            'name' => 'Twelveth',
            'sn' => '12<sup>th</sup>'
        ],
    ];
    return (empty($selected)) ? $semesters : $semesters[$selected];
}

/*
 * Marksheet header labels
 *
 * */
function labeling(){
    return [
        'mt' => 'Mid-Term',
        'ct' => 'Class Test',
        'ap' => 'Assignment / Presentation',
        'ab' => 'Attendence and Behaviour',
        'vi' => 'Viva',
        'th' => 'Thesis',
        'pt' => 'Presentation',
        're' => 'Report',
        'lr' => 'Lab Report',
        'lf' => 'Lab Final',
    ];
}




function download_pdf( $path, $filename ){

    header("Content-type:application/pdf");

    // It will be called downloaded.pdf
    header("Content-Disposition:inline;filename=".$filename."");
    header('content-Transfer-Encoding:binary');
    header('Accept-Ranges:bytes');

    // The PDF source is in original.pdf
    readfile($path);
}

function deleted_pdf_files( $token_array ){

    foreach ($token_array as $token_array_key => $token_array_value) {
        $filename = storage_path(env('PDF_FILE_STORAGE_PATH')).'/'.$token_array_key.'_'.$token_array_value.'.pdf';
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
}

function redis_check_key_and_get_data( $redis_key )
{
    /*
     * Default redis permission is empty.
     * If redis permission has empty thna redis worked. Otherwise redis not worked.
    */
    if ( ! session()->has('redis_permission')) {
        if( Illuminate\Support\Facades\Redis::exists($redis_key) ){
            return unserialize( Illuminate\Support\Facades\Redis::get($redis_key) );
        }
    }
    return false;
}

function redis_check_store( $redis_key, $redis_data )
{
    /*
     * Default redis permission is empty.
     * If redis permission has empty thna redis worked. Otherwise redis not worked.
    */
    if ( ! session()->has('redis_permission')) {
        Illuminate\Support\Facades\Redis::set($redis_key, serialize($redis_data));
        return true;
    }
    return false;
}

function cmp($a, $b)
{
return $a['semester'] < $b['semester'];
}


/*
 * This function can print an images in all pdf files.
 *
 * */
function pdfLogo( $logo )
{
    $image = '/var/www/html/images/'.$logo.'';
    $imageData = base64_encode(file_get_contents($image) );
    $mime = mime_content_type($image);

    return 'data:image/'.$mime.';base64,'.$imageData.'';
}

function tabulation_statistics( $students, $resultsheet )
{

    $male = $students->where('gender', 'M')->count();
    $female = $students->where('gender', 'F')->count();
    $total_registered = $students->count();


    $male_array = collect($resultsheet['marks'])->where('gender','M')->toArray();
    $male_attendance = [];
    foreach ($male_array as $absent){
        $male_attendance[] = (int) in_array('Abs', array_column(array_column($absent['course'], "marks"), "final_total"));
    }

    $male_attend = array_count_values($male_attendance);
    $male_absent = array_key_exists('1', $male_attend) ? $male_attend[1] : 0;
    $male_present = array_key_exists('0', $male_attend) ? $male_attend[0] : 0;

    $female_array = collect($resultsheet['marks'])->where('gender','F')->toArray();
    $female_attendance = [];
    foreach ($female_array as $absent){
        $female_attendance[] = (int) in_array('Abs', array_column(array_column($absent['course'], "marks"), "final_total"));
    }
    $female_attend = array_count_values($female_attendance);
    $female_absent = array_key_exists('1', $female_attend) ? $female_attend[1] : 0;
    $female_present = array_key_exists('0', $female_attend) ? $female_attend[0] : 0;


    $total_array = collect($resultsheet['marks'])->toArray();
    $total_attendance = [];
    foreach ($total_array as $absent){
        $total_attendance[] = (int) in_array('Abs', array_column(array_column($absent['course'], "marks"), "final_total"));
    }

    $total_attend = array_count_values($total_attendance);
    $total_absent = array_key_exists('1', $total_attend) ? $total_attend[1] : 0;
    $total_present = array_key_exists('0', $total_attend) ? $total_attend[0] : 0;


    $male_passed = collect($resultsheet['marks'])->where('gender','M')->where('result','Passed')->count();
    $female_passed = collect($resultsheet['marks'])->where('gender','F')->where('result','Passed')->count();
    $total_passed = collect($resultsheet['marks'])->where('result','Passed')->count();

    $male_incomplete = collect($resultsheet['marks'])->where('gender','M')->where('result','Incomplete')->count();
    $female_incomplete = collect($resultsheet['marks'])->where('gender','F')->where('result','Incomplete')->count();
    $total_incomplete = collect($resultsheet['marks'])->where('result','Incomplete')->count();

    $male_readmision = collect($resultsheet['marks'])->where('gender','M')->where('result','Readmision')->count();
    $female_readmision =collect($resultsheet['marks'])->where('gender','F')->where('result','Readmision')->count();
    $total_readmision =collect($resultsheet['marks'])->where('result','Readmision')->count();


    $male_aPlus = collect($resultsheet['marks'])->where('gender','M')->where('average_grade','A+')->count();
    $female_aPlus = collect($resultsheet['marks'])->where('gender','F')->where('average_grade','A+')->count();
    $total_aPlus = collect($resultsheet['marks'])->where('average_grade','A+')->count();

    $male_passed_percentage = ($male > 0) ? round( (($male_passed * 100 ) / $male), 2 ) : 0;
    $female_passed_percentage = ($female > 0) ? round( (($female_passed * 100 ) / $female), 2 ) : 0;
    $total_passed_percentage = ($total_registered > 0) ? round( (($total_passed * 100 ) / $total_registered), 2 ) : 0;
    return [
        'statistics' => [
            'male' => [
                'registered' => $male,
                'absent' => $male_absent,
                'present' => $male_present,
                'passed' => $male_passed,
                'passed_percentage' => $male_passed_percentage,
                'incomplete' => $male_incomplete,
                'readmision' => $male_readmision,
                'aPlus' => $male_aPlus,
            ],
            'female' => [
                'registered' => $female,
                'absent' => $female_absent,
                'present' => $female_present,
                'passed' => $female_passed,
                'passed_percentage' => $female_passed_percentage,
                'incomplete' => $female_incomplete,
                'readmision' => $female_readmision,
                'aPlus' => $female_aPlus,
            ],
            'total' => [
                'registered' => $total_registered,
                'absent' => $total_absent,
                'present' => $total_present,
                'passed' => $total_passed,
                'passed_percentage' => $total_passed_percentage,
                'incomplete' => $total_incomplete,
                'readmision' => $total_readmision,
                'aPlus' => $total_aPlus,
            ],
        ]
    ];
}

/** get Dept Chaiman/ responsible (persion who is acting as chairman) on SelectedProgram
 * 1. Get all shortPosition from M_SORT_POSITION_ALLOCATION table
 * 2. find perfect shortPositon for selected department
 * 3. sent perfect id(s)
 *
 * @param  int|null departmentId
 * @return array
 */

function getDeptChaimanIdsOnSelectedProgram($departmentId = null){


//    if(is_super_user()) {
//        return [session('user.id')];
//    }

    /*
     *  1	MSS IN SOCIOLOGY (2 YEARS)
        2/3/4	B.SC. IN CSE (DAY)
        5/6	B.SC. IN EETE (DAY)

        7/8	B.SC. IN CIVIL ENGG. (DAY)
        9	B.PHARM
        10/11/12/13	BA HONS. IN ENGLISH (DAY)
        14/15/16	BSS HONS. IN SOCIOLOGY (DAY)
        17/18/19/20/21/22	LL.B HONS. (DAY)

        22	MHRL (2 YEARS)
        23/24/25/26/	BBA (DAY)

        27	B.ED **
        28	M.ED **
        29	MCA **
     * */


    if( !$departmentId ){
        $departmentId = session('user.selected_department.id');
    }

    $relProgram_shortPosition =[
        1=>'SOC',

        2=>'CSE',
        3=>'CSE',
        4=>'CSE',

        5=>'EETE',
        6=>'EETE',

        7=>'CIVIL',
        8=>'CIVIL',

        9=>'PHARM',

        10=>'ENG',
        11=>'ENG',
        12=>'ENG',
        13=>'ENG',

        14=>'SOC',
        15=>'SOC',
        16=>'SOC',

        17=>'LAW',
        18=>'LAW',
        19=>'LAW',
        20=>'LAW',
        21=>'LAW',
        22=>'LAW',

        23=>'BBA',
        24=>'BBA',
        25=>'BBA',
        26=>'BBA',
    ];

    $CharmanShortListArray  = \App\Models\M_SORT_POSITION_ALLOCATION::select('emp_short_position')->where('emp_type','dept_chairman')->pluck('emp_short_position');

    $find = $relProgram_shortPosition[$departmentId];
    $shortPosition = [];

    foreach($CharmanShortListArray as $val){
        if(strpos(strtolower($val),strtolower($find))>-1)
        {
            $shortPosition []= $val;
        }
    }

    return \App\Models\M_WP_EMP::whereIn('emp_short_position',$shortPosition)->pluck('id')->toArray();
}


/**
 * get Exam Controller ID
 * @return int|false
 */
function getExamControllerId(){

    $empRole = \App\Models\M_RMS_EMP_ROLES::where('role_id', env('CONTROLLER_OF_EXAMINITION'))->get();

    if( $empRole->count() != 1 ){

        $message = $empRole->count() . ' - Exam Controller Assigned. Should have only one';
        \Illuminate\Support\Facades\Log::error($message);
        return false;
    }
    if ( ! $empRole ){
        $message = 'No Exam Controller Assigned.';
        \Illuminate\Support\Facades\Log::error($message);
        return false;
    }

    return $empRole->first()->emp_id;
}

/**
 * get current loggedin user id.
 * @return int
 */
function getCurrentUserId():int{
    return session('user.id');
}

/**
 * is current user Dept. Chairman
 * @return bool
 */
function isDeptChairman(){

    if(is_super_user()) {
        return true;
    }

    $CharmanShortListArray  = \App\Models\M_SORT_POSITION_ALLOCATION::select('emp_short_position')->where('emp_type','dept_chairman')->pluck('emp_short_position')->toArray();

    return in_array(session('user.short_position') , $CharmanShortListArray);
}

/**
 * check current user is Controller of Examination
 *
 * @return bool
 */
function isControllerOfExamination(){

    if(is_super_user()) {
        return true;
    }

    return in_array(env('CONTROLLER_OF_EXAMINITION'), get_current_user_role_ids());
}


/**
 * check current user is Officer Of Controller of Examination
 *
 * @return bool
 */
function isOfficerOfControllerOfExamination(){

    if(is_super_user()) {
        return true;
    }

    return in_array(env('OFFICER_OF_CONTROLLER_EXAMINITION'), get_current_user_role_ids());
}

/**
 * check current user is Program Officer of a Department
 *
 * @return bool
 */
function isProgramOfficer(){

    if(is_super_user()) {
        return true;
    }

    return in_array(env('PROGRAM_OFFICER'), get_current_user_role_ids());
}
