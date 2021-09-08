<?php

namespace App\Traits;

use App\Models\O_CASHIN;
use App\Models\O_STUDENT;

trait bapiTrait
{
    public function studentProvisionalResult($student_id)
    {
        try {
            $std = O_STUDENT::find($student_id);

            $currentSemester = $std->getMaxAsCurrentSemester();

            if (!empty($std)) {
                $transcript = $this->make_transcript($student_id);
                if (!empty($transcript)) {
                    unset($transcript['student_info']->image);
                    unset($transcript['student_info']->password);
                    $semesters = $transcript['transcript_data']['results']['semesters'];

                    $totalCurrentDue = O_CASHIN::get_student_account_info_summary($student_id);

                    $max_due_amount_to_show_result = env("MAX_DUE_AMOUNT_TO_SHOW_RESULT", 5000);

                    if (isset($totalCurrentDue ['summary']['total_current_due']) && $totalCurrentDue ['summary']['total_current_due'] > $max_due_amount_to_show_result) {
                        if (count($semesters) > 0) {
                            $semesterOnRemoveAllocated_courses = $currentSemester;

                            foreach ($transcript['transcript_data']['semesters'] as &$rowHas2Semester) {

                                if (isset($rowHas2Semester[0])) {
                                    if ($rowHas2Semester[0]['semester'] == $semesterOnRemoveAllocated_courses) {
                                        $rowHas2Semester[0]['allocated_courses'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[0]['total_semester_gpa'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[0]['average_grade'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[0]['semester_result'] = 'Please, clear Due to show result';
                                    }
                                }

                                if (isset($rowHas2Semester[1])) {
                                    if ($rowHas2Semester[1]['semester'] == $semesterOnRemoveAllocated_courses) {
                                        $rowHas2Semester[1]['allocated_courses'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[1]['total_semester_gpa'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[1]['average_grade'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[1]['semester_result'] = 'Please, clear Due to show result';
                                    }
                                }
                            }

                        }
                    }


                    return $transcript;
                } else {
                    return response()->json(['message' => 'Transcript not complete yet.'], 400);
                }

            } else {
                return response()->json(['message' => 'Student Not Found'], 400);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }

    public function semesterResult($totalSemesterSingleArray, $semester)
    {

        $data = [];

        foreach ($totalSemesterSingleArray as $singleResult) {
            if ($singleResult['semester'] == $semester) {

                $code = [];
                foreach ($singleResult['allocated_courses'] as $row) {

                    if ($row['marks']['letter_grade'] == 'F') {
                        $code[] = $row['code'];
                    }

                }

                $data['semester_gpa'] = number_format($singleResult['total_semester_gpa'], 2) ?? 'N/A';
                $data['semester_result'] = $singleResult['semester_result'] ?? 'N/A';
                $data['incomplete_subject_code'] = implode(',',$code);
                break;
            }
        }

        return $data;
    }
}
