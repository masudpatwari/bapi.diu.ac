<?php
/**
 * Created by PhpStorm.
 * User: lemon
 * Date: 1/10/19
 * Time: 2:57 PM
 */

namespace App\Traits;

use App\Models\O_BATCH;
use App\Models\O_COURSE;
use App\Models\O_DEPARTMENTS;
use App\Models\O_IMP_EXAM_SCHEDULE;

trait notification_message_generator
{

    /**
     * Generate semester's message
     * e.g.- Semester: 1, Batch: 21, Program: BSC in CSE
     * @param int $batch_id
     * @param int $semester_number
     * @return string
     */
    public static function generate_semester_message(int $batch_id = 1, int $semester_number=11){

        $program_name = O_DEPARTMENTS::find(session('user.selected_department.id'))->name;
        #$program_name = O_DEPARTMENTS::find(15)->name;
        $batch_name= O_BATCH::find($batch_id)->batch_name;

        $message = 'Semester: '. $semester_number.', Batch: '. $batch_name. ', Program: ' . $program_name;

        return $message;
    }

    /**
     * Generate Marksheet's message
     * e.g.- Course Name: Course Name (code), Semester: 1 on Batch: 35A On Program:BSC in CSE
     * @param int $course_id
     * @param int $batch_id
     * @param int $semester_number
     * @return string
     */
    public static function generate_marksheet_message(int $course_id=15,int $batch_id = 1, int $semester_number=11){

        $program_name = O_DEPARTMENTS::find(session('user.selected_department.id'))->name;
        #$program_name = O_DEPARTMENTS::find(15)->name;
        $courseName= O_COURSE::find($course_id)->name;
        $courseCode= O_COURSE::find($course_id)->code;
        $batch_name= O_BATCH::find($batch_id)->batch_name;
        #Course Name: Course Name (code), Semester: 1 on Batch: 35A On Program:BSC in CSE
        $message = 'Course Name: '.$courseName .' (' . $courseCode .'),' . 'Semester: '. $semester_number.', Batch: '. $batch_name. ', Program: ' . $program_name;

        return $message;
    }

    /**
     * Generate Tabulation's message
     * e.g.- Semester: 1 on Batch: 35A On Program:BSC in CSE
     * @param int $batch_id
     * @param int $semester_number
     * @return string
     */
    public static function generate_tabulation_message(int $batch_id = 1, int $semester_number=11){

        $program_name = O_DEPARTMENTS::find(session('user.selected_department.id'))->name;
        #$program_name = O_DEPARTMENTS::find(15)->name;
        $batch_name= O_BATCH::find($batch_id)->batch_name;

        $message = 'Semester: '. $semester_number.', Batch: '. $batch_name. ', Program: ' . $program_name;

        return $message;
    }

    /**
     * Generate Splitted Batch Created message
     * e.g.- Batch- 35A of BSC in CSE is splitted in 2 batches
     * @param int $parentBatch_id
     * @return string
     */
    public static function generate_splitted_batch_onCreated_message($batch_name, $no_of_splitted_batch, $new_batch_names_array){
        $program_name = O_DEPARTMENTS::find(session('user.selected_department.id'))->name;
        $childBatches = implode(',', $new_batch_names_array);
        $message = 'Batch- '. $batch_name. ' of ' . $program_name . ' is splitted in ' . $no_of_splitted_batch .' batches (' . $childBatches . ')';
        return $message;
    }


    /**
     * Generate Splitted Batch Deleted message
     * e.g.- Splitted Batch- 35A On Program: BSC is merged
     * @param int $parentBatch_id
     * @return string
     */
    public static function generate_split_batch_OnDeleted_message(O_BATCH $ParentBatch){

        $program_name = O_DEPARTMENTS::find(session('user.selected_department.id'))->name;
        $batch_name= $ParentBatch->batch_name;

        $message = 'Splitted Batch- ' . $batch_name . ' On Program: '. $program_name.' is merged ';

        return $message;
    }

    /**
     * Generate Splitted Batch Approve message
     * e.g.- Splitted Batch- 35A On Program: BSC is Approved
     * @param O_BATCH $ParentBatch
     * @return string
     */
    public static function generate_split_batch_OnApprove_message(O_BATCH $ParentBatch){

        $program_name = O_DEPARTMENTS::find(session('user.selected_department.id'))->name;
        $batch_name= $ParentBatch->batch_name;

        $message = 'Batch Split of - ' . $batch_name . ' On Program: '. $program_name.' is Approved';

        return $message;
    }


    /**
     * Generate Splitted Batch On Update message
     * e.g.- Splitted Batch- 35A On Program: BSC is updated
     * @param O_BATCH $ParentBatch
     * @return string
     */
    public static function generate_split_batch_OnUpdated_message(O_BATCH $ParentBatch){

        $program_name = O_DEPARTMENTS::find(session('user.selected_department.id'))->name;
        $batch_name= $ParentBatch->batch_name;

        $message = 'Splitted Batch - ' . $batch_name . ' On Program: '. $program_name.' is updated. ';

        return $message;
    }




    /******************************** IMPROVEMENT EXAM RELATED  *************************************/

    /**
     * Generate Improvement Exam Schedule On Create message
     * e.g.- Improvement Exam Schedule Created. Name: Exam 2020 . Please, Approve'
     *@MsgSendTo Exam Controller
     * @param O_IMP_EXAM_SCHEDULE $schedule
     * @return string
     */
    public static function generate_improvementExamScheduleOnCreate_message(O_IMP_EXAM_SCHEDULE $schedule)
    {

        $message = ' Improvement Exam Schedule Created. Name: ' . $schedule->NAME  . '. Please, Approve';

        return $message;
    }

    /**
     * Generate Improvement Exam Schedule On Approved message
     * e.g.- Improvement Exam Schedule Approved. Name: Exam 2020
     * @MsgSendTo Officer Of Exam Controller
     * @param O_IMP_EXAM_SCHEDULE $schedule
     * @return string
     */
    public static function generate_improvementExamScheduleOnApprove_message(O_IMP_EXAM_SCHEDULE $schedule)
    {

        $message = ' Improvement Exam Schedule Approved. Name: ' . $schedule->name  ;

        return $message;
    }

    /**
     * Generate Improvement Exam Schedule On Deleted message
     * e.g.- Improvement Exam Schedule Updated. Name: Exam 2020
     * @MsgSendTo Officer Of Exam Controller
     * @param O_IMP_EXAM_SCHEDULE $schedule
     * @return string
     */
    public static function generate_improvementExamScheduleOnDeleted_message(O_IMP_EXAM_SCHEDULE $schedule)
    {

        $message = ' Improvement Exam Schedule Deleted. Name: ' . $schedule->name  ;

        return $message;
    }

    /**
     * Generate Improvement Exam Schedule On Updated message
     * e.g.- Improvement Exam Schedule Updated. Name: Exam 2020
     * @MsgSendTo Officer Of Exam Controller
     * @param O_IMP_EXAM_SCHEDULE $schedule
     * @return string
     */
    public static function generate_improvementExamScheduleOnUpdated_message(O_IMP_EXAM_SCHEDULE $schedule)
    {

        $message = ' Improvement Exam Schedule Updated. Name: ' . $schedule->name  ;

        return $message;
    }

    /**
     * Generate Improvement Exam Schedule On Deny message
     * e.g.- Improvement Exam Schedule Created. Name: Exam 2020 . Please, Approve'
     * @MsgSendTo Creator Of This Imp Exm Schedule of Officer Of Exam Controller
     * @param O_IMP_EXAM_SCHEDULE $schedule
     * @return string
     */
    public static function generate_improvementExamScheduleOnDeny_message(O_IMP_EXAM_SCHEDULE $schedule)
    {

        $message = ' Improvement Exam Schedule Denied. Name: ' . $schedule->name  . '. Please, Correct and Submit Again';

        return $message;
    }


    /**
     * Generate Teacher Assign by Program Officer on Improvement Exam Schedule On Create message
     * e.g.- Teacher Assigned on Exam Schedule: Exam 2020 . Waiting For Your Approval '
     * @MsgSendTo Department Chairman of Selected Department
     * @param O_IMP_EXAM_SCHEDULE $schedule
     * @return string
     */
    public static function generate_teacherAssignedOnImpXmScheduleOnCreate_message(O_IMP_EXAM_SCHEDULE $schedule, $type)
    {
        $message = 'Teacher Assigned on Exam Schedule: ' . $schedule->name  . '. Type: '. $type .'. Waiting For Your Approval ';

        return $message;
    }

    /**
     * Generate Message for Teacher Assign denied by Department Chairman on Improvement Exam Schedule
     * e.g.- Teacher Assign on Exam Schedule:  $name   is Denied. Please, Review'
     * @MsgSendTo Who assigned teachers Program Officer
     * @param O_IMP_EXAM_SCHEDULE $schedule
     * @return string
     */
    public static function generate_teacherAssignedOnImpXmScheduleOnDeny_message(O_IMP_EXAM_SCHEDULE $schedule, $type)
    {
        $message = 'Teacher Assign on Exam Schedule: ' . $schedule->name  . ' is Denied.. Type: '. $type .' Please, Review';

        return $message;
    }

    /**
     * Generate Message for Teacher Assign Approved by Department Chairman on Improvement Exam Schedule
     * e.g.- Teacher Assign on Exam Schedule:  $name   is Approved'
     * @MsgSendTo Who assigned teachers Program Officer
     * @param O_IMP_EXAM_SCHEDULE $schedule
     * @return string
     */
    public static function generate_teacherAssignedOnImpXmScheduleOnApproved_message(O_IMP_EXAM_SCHEDULE $schedule, $type)
    {
        $message = 'Teacher Assign on Exam Schedule: ' . $schedule->name  . ' is Approved. Type: '. $type ;

        return $message;
    }


}