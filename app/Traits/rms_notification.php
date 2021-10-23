<?php
/**
 * User: Arif
 * Date: 12/2/18
 * Time: 12:52 PM
 */

namespace App\Traits;

use App\Models\M_EMP_ASSIGNED_DEPARTMENT;
use App\Models\M_RMS_EMP_ROLES;
use App\Models\M_SORT_POSITION_ALLOCATION;
use App\Models\M_WP_EMP;
use App\Models\O_BATCH;
use App\Models\O_COURSE;
use App\Models\O_COURSE_ALLOCATION_INFO;
use App\Models\O_DEPARTMENTS;
use App\Models\O_IMP_EXAM_SCHEDULE;
use App\Models\O_MARKS;
use App\Models\O_SEMESTERS;
use App\Models\M_NOTIFICATION;
use App\Models\M_NOTIFICATION_RECEIVER;
use function foo\func;


/**
 * Trait rms_notification
 * @package App\Traits
 */
trait rms_notification
{

    use notification_message_generator;
    use url_generator;

    /**
     * Insert Notificatoin
     * @param string $notification_type
     * @param array $data_array
     * @param string $anchor_link
     * @param int|null $sifr_id
     * @return integer
     */
    public static function insertNotification(string $notification_type, array $data_array, string $anchor_link, $sifr_id = null){

        $notification = new M_NOTIFICATION();
        $notification->sender_id = session('user.id');
        $notification->type = $notification_type;
        $notification->data = json_encode($data_array);
        $notification->action_link= $anchor_link;
        $notification->program_id= session('user.selected_department.id');
        $notification->sifr_id= $sifr_id;
        $notification->save();

        return $notification->id;
    }

    /**
     * Insert Notification Receiver
     * @param int $notification_id
     * @param int $receiver_id
     * @return integer
     */
    public static function insertNotificationReceiver(int $notification_id, int $receiver_id){

        $notification_reciver = new M_NOTIFICATION_RECEIVER();
        $notification_reciver->rms_notification_id= $notification_id;
        $notification_reciver->receiver_id = $receiver_id;
        $notification_reciver->save();

        return $notification_reciver->id;
    }


    /**
     * SEMESTER On create
     *
     * On create send notification to Dpt. Chairman
     *
     * @param O_SEMESTERS $semester
     */
    public static function semester_create_nf_receiver_generator(O_SEMESTERS $semester)
    {

        $batch_id = $semester->batch_id;
        $semester_number=$semester->semester;
        $sifr_id = $semester->id;
        $dept_chairman_id = $semester->dcid ;

        $anchor_link = self::get_semester_detail_url($batch_id , $sifr_id);

        $message = self::generate_semester_message($batch_id , $semester_number);

        $notification_id = self::insertNotification('semester_create', ['message'=>$message],$anchor_link, $sifr_id);

        if( $notification_id){
            self::insertNotificationReceiver($notification_id,$dept_chairman_id);
        }
        else{
            M_NOTIFICATION::find($notification_id)->delete();
            // send message to Admin
        }


    }
    /**
     * SEMESTER On Delete
     *
     * On create send notification to program officer ( also Dpt. Chairman if Deleted by Others)
     *
     * @param O_SEMESTERS $semester
     */
    public static function semester_delete_nf_receiver_generator(O_SEMESTERS $semester)
    {

        $batch_id = $semester->batch_id;
        $semester_number=$semester->semester;
        $sifr_id = $semester->id;

        $dept_chairman_id = $semester->dcid ;
        $program_officer_id = $semester->program_officer_id;

        $receiverIdsArray = array_unique([ $dept_chairman_id, $program_officer_id ]);

        if( $key = array_search(session('user.id'),$receiverIdsArray))
        unset($receiverIdsArray[$key]);

        /**
         * if no receiver of this message then no need to send message. :)
         */
        if(count($receiverIdsArray)==0) {
            return;
        }

        $message = self::generate_semester_message($batch_id , $semester_number);

        /**
         * no link will send as anchorLink only #
         **/
        $notification_id = self::insertNotification('semester_delete', ['message'=>$message],'#', $sifr_id);


        if( ! $notification_id ){
            //send message to admin
            return;
        }

        $countReciverIds = count($receiverIdsArray);
        $notificationReceiverTableRowIdArray = [];

        foreach ($receiverIdsArray as $receiverId){
            $notificationReceiverTableRowIdArray[] = self::insertNotificationReceiver($notification_id, $receiverId);
        }

        if($countReciverIds != count($notificationReceiverTableRowIdArray)){
            M_NOTIFICATION::find($notification_id)->delete();
            // send message to Admin

        }



    }


    /**
     * on semester approve
     *
     * send notification to teachers, tabulators, program officer, exam-committee members
     *
     * @param O_SEMESTERS $semestersObj
     */
    public static function semester_approve_nf_receiver_generator(O_SEMESTERS $semestersObj)
    {
        $batch_id = $semestersObj->batch_id;
        $semester_number=$semestersObj->semester;
        $semester_info_for_result_id = $semestersObj->id;
        $is_exempted = $semestersObj->exempted==0?false:true;

        $anchor_link = self::get_semester_detail_url($batch_id , $semester_info_for_result_id);
        $message = self::generate_semester_message($batch_id , $semester_number);

        $programOfficerIdArray = (array) O_SEMESTERS::getProgramOfficerId($semester_info_for_result_id);

        $teacherIdArray = O_SEMESTERS::getAllocatedTeachersId($semester_info_for_result_id)->toArray();
        $examTabulatorIdsArray = O_SEMESTERS::getAllocatedExamTabulatorsId($semester_info_for_result_id)->toArray();
        $examCommitteeIdsArray = O_SEMESTERS::getAllocatedExamCommitteeMembersId($semester_info_for_result_id)->toArray();

        /**
         * if approve except department chairman like Super User then dept charman should get notification
         */
        $deptChairmanId= $semestersObj->dcid;

        if($is_exempted)
        $receiverIdsArray = $programOfficerIdArray;
        else
        $receiverIdsArray = array_unique( array_merge($teacherIdArray,$examTabulatorIdsArray,$examCommitteeIdsArray,$programOfficerIdArray) );

        if( $deptChairmanId != session('user.id'))
            $receiverIdsArray = array_unique( array_merge($receiverIdsArray, (array) $deptChairmanId) );

        $notification_id = self::insertNotification('semester_approve', ['message'=>$message],$anchor_link,$semester_info_for_result_id);

        if( ! $notification_id ){
            //send message to admin
            return;
        }
        $countReciverIds = count($receiverIdsArray);
        $notificationReceiverTableRowIdArray = [];

        foreach ($receiverIdsArray as $receiverId){
            $notificationReceiverTableRowIdArray[] = self::insertNotificationReceiver($notification_id, $receiverId);
        }

        if($countReciverIds != count($notificationReceiverTableRowIdArray)){
            M_NOTIFICATION::find($notification_id)->delete();
            // send message to Admin

        }

    }


    /**
     * on semester update
     *
     * send notification to program officer
     *
     * @param O_SEMESTERS $semesterObj
     * @param string $notification_type
     */
    private static function semester_update_nf_receiver_generator(O_SEMESTERS $semesterObj, string $notification_type='')
    {
        $batch_id = $semesterObj->batch_id;
        $semester_info_for_result_id = $semesterObj->id;
        $semester_number= $semesterObj->semester;

        $anchor_link = self::get_semester_detail_url($batch_id , $semester_info_for_result_id);
        $message = self::generate_semester_message($batch_id , $semester_number);
        $programOfficerId = O_SEMESTERS::getProgramOfficerId($semester_info_for_result_id);

        $notificationTableRowId = self::insertNotification($notification_type, ['message'=>$message],$anchor_link, $semester_info_for_result_id);

        $notificationReceiverRowId = null;

        if( $notificationTableRowId != null ){

            $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $programOfficerId);

            if( ! $notificationReceiverRowId ){
                M_NOTIFICATION::find($notificationTableRowId)->delete();
                // send message to Admin
            }
        }
        else{
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            // send message to Admin
        }

    }

    /**
     * semester update on semester_settings page . That means it is first step of semester update process.
     *
     * @param O_SEMESTERS $semesterObj
     *
     */
    public static function semester_update_on_semester_settings(O_SEMESTERS $semesterObj)
    {
        self::semester_update_nf_receiver_generator($semesterObj,'semester_update_on_semester_settings');
    }

    /**
     * semester update on course_allocation page . That means it is second step of semester update process.
     *
     * @param O_SEMESTERS $semesterObj
     *
     */
    public static function semester_update_on_course_allocation(O_SEMESTERS $semesterObj)
    {
        self::semester_update_nf_receiver_generator($semesterObj,'semester_update_on_course_allocation');
    }



    /***********************************************
     *
     * MARKSHEET ENTRY
     *
     ********************************************/

    /**
     * on final Submit by Course Teacher or Tabulator
     *
     * send notification to exam committee.
     *
     * @param int $course_id
     * @param int $semester_info_for_result_id
     */
    public static function marksheet_save_as_final_nf_receiver_generator(int $course_id, int $semester_info_for_result_id)
    {
        $semesterObj = O_SEMESTERS::find($semester_info_for_result_id);
        $batch_id = $semesterObj->batch_id;
        $semester_number = $semesterObj->semester;

        $anchor_link = self::get_marksheet_detail_url($semester_info_for_result_id, $course_id);
        $message = self::generate_marksheet_message($course_id, $batch_id, $semester_number);
        $examCommitteeMembersIdArray = O_SEMESTERS::getAllocatedExamCommitteeMembersId($semester_info_for_result_id)->toArray();

        $notificationTableRowId = self::insertNotification('marksheet_save', ['message'=>$message],$anchor_link, $semester_info_for_result_id);

        $notificationReceiverRowIdArray = [];

        if( $notificationTableRowId != null ){

            foreach ($examCommitteeMembersIdArray as $t_id) {
                $notificationReceiverRowIdArray[] = self::insertNotificationReceiver($notificationTableRowId, $t_id);
            }


            if( count($examCommitteeMembersIdArray) != count($notificationReceiverRowIdArray)){
                M_NOTIFICATION::find($notificationTableRowId)->delete();

                // send message to Admin
            }

        }
        else{
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            // send message to Admin
        }

    }


    /*
     * on markssheet deny (Deny by exam-committee only)
     *
     * send notification to marksheet Creator, Dept.chairman, program officer, exam-committee members except approved by
     *
     * here creator will get marksheet edit link, other will not get any link.
     */

    /**
     * on markssheet approve (approve by exam-committee only) /  on Approve / on forward.
     * exam committee => dept. chairman
     *
     * send notification to marksheet Creator, Dept.chairman, program officer, exam-committee members except approved by
     *
     * @param O_SEMESTERS $semesterObj
     * @param int $course_id
     */
    public static function marksheet_approve_nf_receiver_generator(O_SEMESTERS $semesterObj , int $course_id=15)
    {
        $semester_info_table_id=$semesterObj->id;
        $batch_id = $semesterObj->batch_id;
        $semester_number=$semesterObj->semester;

        $message = self::generate_marksheet_message($course_id, $batch_id, $semester_number);
        $anchor_link = self::get_marksheet_detail_url($semester_info_table_id,$course_id);

        $marksheetCreatorId = O_MARKS::where(['sifr_id'=>$semester_info_table_id , 'course_id'=>$course_id])->first()->creator_id;
        $programOfficerId = O_SEMESTERS::getProgramOfficerId($semester_info_table_id);
        $deptChairmanID = O_SEMESTERS::getDeptChairmanId($semester_info_table_id);
        $examCommitteeMemberIDArray = O_SEMESTERS::getAllocatedExamCommitteeMembersId($semester_info_table_id)->toArray();

        $sendToIdArray = array_unique( array_merge( (array) $marksheetCreatorId , (array) $programOfficerId, (array) $deptChairmanID, $examCommitteeMemberIDArray) );

        /*
         * if No. of sender is more than 1, then remove id who is approving the marksheet if exists.
         * */
        if(count($sendToIdArray) != 1 ){
            if (($key = array_search(session('user.id'), $sendToIdArray)) !== false) {
                unset($sendToIdArray[$key]);
            }
        }

        $notificationTableRowId = self::insertNotification('marksheet_approve', ['message'=>$message], $anchor_link, $semester_info_table_id);

        $notificationReceiverRowIdArray = [];

        if( $notificationTableRowId != null ){

            foreach ($sendToIdArray as $t_id) {
                $notificationReceiverRowIdArray[] = self::insertNotificationReceiver($notificationTableRowId, $t_id);
            }


            if( count($sendToIdArray) != count($notificationReceiverRowIdArray)){
                M_NOTIFICATION::find($notificationTableRowId)->delete();

                // send message to Admin
            }

        }
        else{
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            // send message to Admin
        }


    }

    /**
     * On marksheet deny
     * Notification send to creator (with link course marksheet edit), # link to program officer, dept chairman, exam committee members
     *
     * @param O_SEMESTERS $semesterObj
     * @param int $course_id
     */
    public static function marksheet_deny_nf_receiver_generator(O_SEMESTERS $semesterObj, int $course_id = 15)
    {

        $semester_info_table_id = $semesterObj->id;
        $batch_id = $semesterObj->batch_id;
        $semester_number = $semesterObj->semester;

        $message = self::generate_marksheet_message($course_id, $batch_id, $semester_number);

        $marksheetCreatorId = O_MARKS::where(['sifr_id'=>$semester_info_table_id , 'course_id'=>$course_id])->first()->creator_id;



        /**
         * ### if current user is creator of marksheet then no need to send notification to creator
         */
        if(session('user.id') != $marksheetCreatorId){
            $anchor_link = self::get_marksheet_edit_url($semester_info_table_id,$course_id);
            $notificationTableRowId = self::insertNotification('marksheet_deny', ['message'=>$message],$anchor_link, $semester_info_table_id);
            $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $marksheetCreatorId);

            if(! $notificationReceiverRowId) {
                M_NOTIFICATION::find($notificationTableRowId)->delete();
                // send message to Admin
                return;
            }

        }



        $programOfficerId = O_SEMESTERS::getProgramOfficerId($semester_info_table_id);
        $deptChairmanID = O_SEMESTERS::getDeptChairmanId($semester_info_table_id);
        $examCommitteeMemberIDArray = O_SEMESTERS::getAllocatedExamCommitteeMembersId($semester_info_table_id)->toArray();

        $sendToIdArray = array_unique( array_merge( (array) $programOfficerId, (array) $deptChairmanID, $examCommitteeMemberIDArray) );

        /**
         * ### if No. of sender is more than 1, then remove id who is approving the marksheet if exists.
         */
        if(count($sendToIdArray) > 1 ){
            if (($key = array_search(session('user.id'), $sendToIdArray)) !== false) {
                unset($sendToIdArray[$key]);
            }
        }

        // only creator get link, other people not get any link, so setting #.
        $notificationTableRowId = self::insertNotification('marksheet_deny', ['message'=>$message],'#', $semester_info_table_id);

        $notificationReceiverRowIdArray = [];

        if( $notificationTableRowId != null ){

            foreach ($sendToIdArray as $t_id) {
                $notificationReceiverRowIdArray[] = self::insertNotificationReceiver($notificationTableRowId, $t_id);
            }


            if( count($sendToIdArray) != count($notificationReceiverRowIdArray)){
                M_NOTIFICATION::find($notificationTableRowId)->delete();

                // send message to Admin
            }

        }
        else{
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            // send message to Admin
        }

    }

    /**
     * on markssheet update
     *
     * No notification send because it on `draft` sate and marksheet not submitted as `final`
     */
    private function marksheet_update_nf_receiver_generator()
    {
        return false;
    }

    /**
     * ### FOR FUTURE USE :)
     * On marksheet delete
     *
     * marksheet delete happen only by Admin. so, only creator will notified.
     *
     * No notification send because only creator can delete????
     */
    public static function marksheet_delete_nf_receiver_generator(int $semester_info_table_id = 10, int $course_id = 15, int $batch_id = 1, int $semester_number = 11)
    {
        if( is_super_user()) {

            $message = self::generate_marksheet_message($course_id, $batch_id, $semester_number);

            #creator will only notify that super user has deleted marksheet, creator will not get any link, go setting #
            $notificationTableRowId = self::insertNotification('marksheet_delete', ['message' => $message], '#', $semester_info_table_id);

            if (!$notificationTableRowId) {
                // send admin message
            } else {

                $marksheetCreatorId = O_MARKS::where(['sifr_id' => $semester_info_table_id, 'course_id' => $course_id])->first()->creator_id;

                $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $marksheetCreatorId);

                if (!$notificationReceiverRowId) {
                    M_NOTIFICATION::find($notificationTableRowId)->delete();
                    // send message to Admin
                    return;
                }
            }
        }
    }


    /**
     * on Tabulationsheet Approve / forward
     *
     * send notification to upper level users
     *
     * @param int $batch_id
     * @param int $semester_info_table_id
     * @param int $next_status
     */
    public static function tabulationsheet_frwd_nf_receiver_generator(int $batch_id = 10, int $semester_info_table_id = 10, int $next_status = 0)
    {
        $sendToIdArray = [];

        $semester_number = O_SEMESTERS::find($semester_info_table_id)->semester;

        $message = self::generate_tabulation_message($batch_id, $semester_number);
        $anchor_link = self::get_tabulationsheet_detail_url( $batch_id , $semester_info_table_id);


        $office_of_controllers_role_table_id = env('OFFICE_OF_CONTROLLERS_ROLE_TABLE_ID');
        $controllers_role_table_id = env('CONTROLLERS_ROLE_TABLE_ID ');
        $bot_role_table_id = env('BOT_ROLE_TABLE_ID');

        $roleIdToPick = null;

        if( ! $office_of_controllers_role_table_id || ! $controllers_role_table_id || ! $bot_role_table_id)
        {
            #sent mail to admin

            return "OFFICE_OF_CONTROLLERS_ROLE_TABLE_ID or CONTROLLERS_ROLE_TABLE_ID or BOT_ROLE_TABLE_ID not found in env";
        }

        if($next_status == 6){

            # get ids of 'Officer of the Controller of Examination' roles

            $roleIdToPick = $office_of_controllers_role_table_id;

        }elseif ($next_status == 7){

            # get ids of 'Controller of Examination' roles

            $roleIdToPick = $controllers_role_table_id;
        }
        elseif($next_status==8){

            # get ids of 'BOT' roles

            $roleIdToPick = $bot_role_table_id;

        }else{
            #   not code for execution
        }

        $sendToIdArray = M_RMS_EMP_ROLES::select('emp_id')->where('role_id',$roleIdToPick)->pluck('emp_id');

        if( count($sendToIdArray) == 0 ){
            #sent mail to admin
            return;
        }


        $notificationTableRowId = self::insertNotification('tabulationsheet_approve', ['message'=>$message], $anchor_link, $semester_info_table_id);

        $notificationReceiverRowIdArray = [];

        if( $notificationTableRowId != null ){

            foreach ($sendToIdArray as $e_id) {
                $notificationReceiverRowIdArray[] = self::insertNotificationReceiver($notificationTableRowId, $e_id);
            }


            if( count($sendToIdArray) != count($notificationReceiverRowIdArray)){
                M_NOTIFICATION::find($notificationTableRowId)->delete();

                // send message to Admin
            }

        }
        else{
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            // send message to Admin
        }

    }


    /**
     * on Tabulationsheet deny
     *
     * send notification to dept.charman, programofficer,exam-committee with marksheet view link .
     *
     * @param int $batch_id
     * @param int $semester_info_table_id
     */
    public static function tabulationsheet_deny_nf_receiver_generator(int $batch_id , int $semester_info_table_id )
    {
        $semester_number = O_SEMESTERS::find($semester_info_table_id)->semester;

        /**
         * send notification to dept.charman, programofficer,exam-committee
         */
        $message_for_no_link = self::generate_tabulation_message($batch_id, $semester_number);
        $message_for_subject = '';

        $allocatedCourseIdArray = O_COURSE_ALLOCATION_INFO::where(['sifr_id'=> $semester_info_table_id])->pluck('course_id')->toArray();

        foreach ( $allocatedCourseIdArray as $course_id) {

            $courseObj = O_COURSE::find($course_id);
            $courseName = $courseObj->name;
            $courseCode = $courseObj->code;
            $makrsheet_detail_view_link = self::get_marksheet_detail_url( $semester_info_table_id, $course_id );
            $message_for_subject .= " <a class='text-info' href='$makrsheet_detail_view_link'> $courseName ( $courseCode ) </a> <br> ";
        }


        $deptCharmanIdArray = (array) O_SEMESTERS::getDeptChairmanId($semester_info_table_id);
        $examCommitteeIdsArray = O_SEMESTERS::getAllocatedExamCommitteeMembersId($semester_info_table_id)->toArray();
        $programOfficerIdArray = (array) O_SEMESTERS::getProgramOfficerId($semester_info_table_id);

        $sendingWithNoLinkToArray = array_unique( array_merge($deptCharmanIdArray , $examCommitteeIdsArray, $programOfficerIdArray) );

        $notificationTableRowId = self::insertNotification('tabulationsheet_deny', ['message'=>$message_for_no_link . "<br><b>Course List:</b><br>" . $message_for_subject],'#', $semester_info_table_id);

        if( ! $notificationTableRowId) {
            // send message to admin
            return;
        }


        /**
         * ### if No. of sender is more than 1, then remove id who is approving the marksheet if exists.
         */
        if(count($sendingWithNoLinkToArray) > 1 ){
            if (($key = array_search(session('user.id'), $sendingWithNoLinkToArray)) !== false) {
                unset($sendingWithNoLinkToArray[$key]);
            }
        }

        foreach ($sendingWithNoLinkToArray as $e_id){
            $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $e_id);

            if( ! $notificationReceiverRowId ) {
                // send message to admin
                M_NOTIFICATION::find($notificationTableRowId)->delete();
                return;
            }
        }
        /**
         * end
         */

    }

    /**
     * on comment on marksheet
     *
     * send notification to creator , program officer and exam committee
     *
     * @param int $course_id
     * @param int $semester_info_table_id
     * @param string $comment_text
     */
    public static function send_marksheet_comment_notificatoin(int $course_id , int $semester_info_table_id , $comment_text)
    {
        $semesterObj = O_SEMESTERS::find($semester_info_table_id);
        $batch_id = $semesterObj->batch_id;
        $semester_number = $semesterObj->semester;

        $marksheetCreatorId = (array) O_MARKS::where(['sifr_id'=>$semester_info_table_id , 'course_id'=>$course_id])->first()->creator_id;
        $programOfficerIdArray = (array) O_SEMESTERS::getProgramOfficerId($semester_info_table_id);
        $examCommitteeIdsArray = O_SEMESTERS::getAllocatedExamCommitteeMembersId($semester_info_table_id)->toArray();

        $sendToIdArray = array_unique(array_merge($marksheetCreatorId, $examCommitteeIdsArray, $programOfficerIdArray));
        $message = self::generate_marksheet_message($course_id,$batch_id,$semester_number);
        $anchor_link = self::get_marksheet_detail_url($semester_info_table_id,$course_id);

        $notificationTableRowId = self::insertNotification('marksheet_comment_submitted', ['message'=>$message, 'comment'=>$comment_text],$anchor_link, $semester_info_table_id);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }

        /**
         * ### if No. of receiver is more than 1, then remove user id who is commenting if exists.
         */
        if(count($sendToIdArray) > 1 ){
            if (($key = array_search(session('user.id'), $sendToIdArray)) !== false) {
                unset($sendToIdArray[$key]);
            }
        }


        $notificationReceiverRowIdArray = [];

        foreach ($sendToIdArray as $id) {
            $notificationReceiverRowIdArray[] = self::insertNotificationReceiver($notificationTableRowId, $id);
        }


        if ( count($sendToIdArray) != count($notificationReceiverRowIdArray)){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }


    /**
     * on comment on Tabulationsheet
     *
     * send notification to creator, program officer and exam committee
     *
     * @param int $semester_info_table_id
     * @param string $comment_text
     */
    public static function send_tabulationsheet_comment_notificatoin(int $semester_info_table_id ,string $comment_text )
    {
        $marksheetCreatorIdArray = [];

        $semesterObj = O_SEMESTERS::find($semester_info_table_id)->batch_id;
        $batch_id = $semesterObj->batch_id;
        $semester_number = $semesterObj->semester;

        $courseIdArray = O_COURSE_ALLOCATION_INFO::where('sifr_id',$semester_info_table_id)->pluck('course_id');
        $programOfficerIdArray = (array) O_SEMESTERS::getProgramOfficerId($semester_info_table_id);
        $examCommitteeIdsArray = O_SEMESTERS::getAllocatedExamCommitteeMembersId($semester_info_table_id)->toArray();

        foreach ( $courseIdArray as $course_id) {
            $marksheetCreatorIdArray[] =  O_MARKS::where(['sifr_id'=>$semester_info_table_id , 'course_id'=>$course_id])->first()->creator_id;
        }

        $sendToIdArray = array_unique(array_merge($marksheetCreatorIdArray, $examCommitteeIdsArray, $programOfficerIdArray));

        $message = self::generate_tabulation_message($batch_id, $semester_number);
        $anchor_link = self::get_tabulationsheet_detail_url($batch_id, $semester_info_table_id);

        $notificationTableRowId = self::insertNotification('tabulationsheet_comment_submitted', ['message'=>$message, 'comment'=>$comment_text],$anchor_link, $semester_info_table_id);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }

        /**
         * ### if No. of receiver is more than 1, then remove user id who is commenting if exists.
         */
        if(count($sendToIdArray) > 1 ){
            if (($key = array_search(session('user.id'), $sendToIdArray)) !== false) {
                unset($sendToIdArray[$key]);
            }
        }

        $notificationReceiverRowIdArray = [];
        foreach ($sendToIdArray as $id) {
            $notificationReceiverRowId[] = self::insertNotificationReceiver($notificationTableRowId, $id);
        }

        if ( count($sendToIdArray) != count($notificationReceiverRowIdArray)){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }


    /******************************************************************************************
     *
     * batch split related notification
     *
     * like - split_batch_created, split_batch_deleted, split_batch_approved, split_batch_updated.
     *
     */


    /**
     * split_batch_created
     *
     * send notification to chairman
     *
     * @param O_BATCH $selected_batch_info
     * @param int $no_of_splitted_batch
     * @param array $new_batch_names_array
     */
    public static function split_batch_created( O_BATCH $selected_batch_info, $no_of_splitted_batch, $new_batch_names_array)
    {
        $notificatoinReciverIdArray = getDeptChaimanIdsOnSelectedProgram(session('user.selected_department.id'));

        $message = self::generate_splitted_batch_onCreated_message($selected_batch_info->batch_name, $no_of_splitted_batch, $new_batch_names_array);
        $anchor_link = self::get_splittedBatch_detail_url($selected_batch_info->id);

        $notificationTableRowId = self::insertNotification('split_batch_created', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowIdArray = [];
        foreach ($notificatoinReciverIdArray as $id) {
            $notificationReceiverRowIdArray[] = self::insertNotificationReceiver($notificationTableRowId, $id);
        }

        if ( count($notificatoinReciverIdArray) != count($notificationReceiverRowIdArray)){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }



    /**
     * split_batch_deleted
     *
     * send notification to employee who Splitted parent batch.  
     *
     * @param O_BATCH $selected_batch_info
     */
    public static function split_batch_deleted( O_BATCH $selected_batch_info)
    {

        $notificatoinReciverId= $selected_batch_info->splited_by;

        $message = self::generate_split_batch_OnDeleted_message($selected_batch_info);
        $anchor_link = '#';

        $notificationTableRowId = self::insertNotification('split_batch_deleted', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $notificatoinReciverId);

        if ( ! $notificationReceiverRowId ){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }


    /**
     * split_batch_approved
     *
     * send notification to employee who Splitted parent batch.
     *
     * @param O_BATCH $selected_batch_info
     */
    public static function split_batch_approved(O_BATCH $selected_batch_info)
    {

        $notificatoinReciverId= $selected_batch_info->splited_by;

        $message = self::generate_split_batch_OnDeleted_message( $selected_batch_info );
        $anchor_link = self::get_splittedBatch_detail_url( $selected_batch_info->id );

        $notificationTableRowId = self::insertNotification('split_batch_approved', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $notificatoinReciverId);

        if ( ! $notificationReceiverRowId ){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }
    /**
     * split_batch_updated
     *
     * send notification to employee who Splitted parent batch.
     *
     * @param O_BATCH $selected_batch_info
     */
    public static function split_batch_updated( O_BATCH $selected_batch_info)
    {

        $notificatoinReciverId= $selected_batch_info->splited_by;

        $message = self::generate_split_batch_OnUpdated_message( $selected_batch_info );
        $anchor_link = self::get_splittedBatch_detail_url( $selected_batch_info->id );

        $notificationTableRowId = self::insertNotification('split_batch_updated', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $notificatoinReciverId);

        if ( ! $notificationReceiverRowId ){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }


    /**
     * **********************************       IMPROVEMENT EXAM PART   *********************************
     */

    /**
     * improvementExamScheduleOnCreate
     *
     * send notification to Exm Controller.
     *
     * @param O_IMP_EXAM_SCHEDULE $schedule
     */
    public static function improvementExamScheduleOnCreate( O_IMP_EXAM_SCHEDULE $schedule)
    {
    // getDeptChaimanIdsOnSelectedProgram
        $notificatoinReciverId = getExamControllerId();



        $message = self::generate_improvementExamScheduleOnCreate_message( $schedule );
        $anchor_link = self::getImprovementExamScheduleDetail_url( $schedule->id);

        $notificationTableRowId = self::insertNotification('impXmSedulCreate', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $notificatoinReciverId);

        if ( ! $notificationReceiverRowId ){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }


    /**
     * improvementExamScheduleOnApprove
     *
     * send notification to Creator(ProgramOfficer).
     *
     * @param O_IMP_EXAM_SCHEDULE $schedule
     */
    public static function improvementExamScheduleOnApprove( O_IMP_EXAM_SCHEDULE $schedule)
    {
        $notificatoinReciverId = $schedule->created_by;


        $message = self::generate_improvementExamScheduleOnApprove_message( $schedule );
        $anchor_link = self::getImprovementExamScheduleDetail_url( $schedule->id);

        $notificationTableRowId = self::insertNotification('impXmSedulApprove', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $notificatoinReciverId);

        if ( ! $notificationReceiverRowId ){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }

    /**
     * improvementExamScheduleOnUpdated
     *
     * send notification to Exam Controller
     *
     * @param O_IMP_EXAM_SCHEDULE $schedule
     */
    public static function improvementExamScheduleOnUpdated( O_IMP_EXAM_SCHEDULE $schedule)
    {
        $notificatoinReciverId = getExamControllerId();


        $message = self::generate_improvementExamScheduleOnApprove_message( $schedule );
        $anchor_link = self::getImprovementExamScheduleDetail_url( $schedule->id);

        $notificationTableRowId = self::insertNotification('impXmSedulUpdated', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $notificatoinReciverId);

        if ( ! $notificationReceiverRowId ){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }
    /**
     * improvementExamScheduleOnDeleted
     *
     * send notification to Exam Controller
     *
     * @param O_IMP_EXAM_SCHEDULE $schedule
     */
    public static function improvementExamScheduleOnDeleted( O_IMP_EXAM_SCHEDULE $schedule)
    {
        $notificatoinReciverId = getExamControllerId();


        $message = self::generate_improvementExamScheduleOnDeleted_message( $schedule );
        $anchor_link = '#';

        $notificationTableRowId = self::insertNotification('impXmSedulDelete', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $notificatoinReciverId);

        if ( ! $notificationReceiverRowId ){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }

    /**
     * improvementExamScheduleOnDeny
     *
     * send notification to Creator(ProgramOfficer).
     *
     * @param O_IMP_EXAM_SCHEDULE $schedule
     */
    public static function improvementExamScheduleOnDeny( O_IMP_EXAM_SCHEDULE $schedule)
    {
        $notificatoinReciverId = $schedule->created_by;


        $message = self::generate_improvementExamScheduleOnDeny_message( $schedule );
        $anchor_link = self::getImprovementExamScheduleDetail_url( $schedule->id);

        $notificationTableRowId = self::insertNotification('impXmSedulDeny', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $notificatoinReciverId);

        if ( ! $notificationReceiverRowId ){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }

    /**
     * teacherAssignedOnImpXmScheduleOnCreate
     *
     * send notification to Department Chairman
     *
     * @param O_IMP_EXAM_SCHEDULE $schedule
     */
    public static function teacherAssignedOnImpXmScheduleOnCreate( O_IMP_EXAM_SCHEDULE $schedule, string $type)
    {
        $notificatoinReciverId = getDeptChaimanIdsOnSelectedProgram(session('user.selected_department.id'));

        if (is_array($notificatoinReciverId))
        {
            $notificatoinReciverId = $notificatoinReciverId[0];
        }

        $message = self::generate_teacherAssignedOnImpXmScheduleOnCreate_message( $schedule,$type );
        $anchor_link = self::getTeacherAssignOnImprovementExamSchedulOnCreateUrl();

        $notificationTableRowId = self::insertNotification('impXmSedulTeacherAssignCreate', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $notificatoinReciverId);

        if ( ! $notificationReceiverRowId ){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }

    /**
     * teacherAssignedOnImpXmScheduleOnDeny
     *
     * send notification to Creator(ProgramOfficer)
     *
     * @param O_IMP_EXAM_SCHEDULE $schedule
     */
    public static function teacherAssignedOnImpXmScheduleOnDeny( O_IMP_EXAM_SCHEDULE $schedule,$type, $programOfficerId)
    {
        $notificatoinReciverId = $programOfficerId;


        $message = self::generate_teacherAssignedOnImpXmScheduleOnDeny_message( $schedule, $type );
        $anchor_link = self::getTeacherAssignOnImprovementExamScheduleOnDeny_url($schedule->id, $type);

        $notificationTableRowId = self::insertNotification('impXmSedulTeacherAssignDeny', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $notificatoinReciverId);

        if ( ! $notificationReceiverRowId ){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }


    /**
     * teacherAssignedOnImpXmScheduleOnApproved
     *
     * send notification to Creator(ProgramOfficer)
     *
     * @param O_IMP_EXAM_SCHEDULE $schedule
     */
    public static function teacherAssignedOnImpXmScheduleOnApproved( O_IMP_EXAM_SCHEDULE $schedule,$type, $programOfficerId)
    {
        $notificatoinReciverId = $programOfficerId;


        $message = self::generate_teacherAssignedOnImpXmScheduleOnApproved_message( $schedule, $type );
        $anchor_link = self::getTeacherAssignOnImprovementExamSchedulOnCreateUrl();

        $notificationTableRowId = self::insertNotification('impXmSedulTeacherAssignApprove', ['message'=>$message, ],$anchor_link);

        if ( ! $notificationTableRowId){
            // send message to admin
            return;
        }


        $notificationReceiverRowId = self::insertNotificationReceiver($notificationTableRowId, $notificatoinReciverId);

        if ( ! $notificationReceiverRowId ){
            // send message to admin
            M_NOTIFICATION::find($notificationTableRowId)->delete();
            return;
        }
    }




}