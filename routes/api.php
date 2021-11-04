<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Imp_Request;
use App\Http\Controllers\Imp_Marksheet;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ApiController2;
use App\Http\Controllers\APIBankController;
use App\Http\Controllers\Imp_Exam_Schedule;
use App\Http\Controllers\Imp_Eligible_Courses;
use App\Http\Controllers\Imp_Invoice_Generator;
use App\Http\Controllers\Api\GroupIndexController;
use App\Http\Controllers\Api\ShiftIndexController;
use App\Http\Controllers\Api\BatchStoreController;
use App\Http\Controllers\Api\CampussIndexController;
use App\Http\Controllers\Api\CountryIndexController;
use App\Http\Controllers\Api\StudentReportController;
use App\Http\Controllers\Api\BatchWiseStudentsController;
use App\Http\Controllers\Api\PaymentSystemIndexController;
use App\Http\Controllers\Api\RefereedByParentIndexController;
use App\Http\Controllers\Api\ActiveBatchForAdmissionController;
use App\Http\Controllers\Api\ActiveBatchStudentStoreController;
use App\Http\Controllers\Api\StudentDownloadFormInfoController;
use App\Http\Controllers\Api\RefereedChildByParentIndexController;
use App\Http\Controllers\Api\BatchWiseUnVerifiedStudentsController;
use App\Http\Controllers\Api\AdmissionStudentRegCodeGenerateController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['acceptableIpAddressMiddleware']], function () {

    Route::get('get-semester-teacher-list/{student_id}/{semester}', [ApiController2::class, 'getSemesterTeacherListByStudentID'])->name('getSemesterTeacherListByStudentID');
    Route::get('get-semester-list/{student_id}', [ApiController2::class, 'getSemesterListByStudentId'])->name('getSemesterTeacherListByStudentID');
    Route::get('provisional-transcript-marksheet-info/{student_id}', [ApiController::class, 'provisional_transcript_marksheet'])->name('provisional_transcript_marksheet');
    Route::get('latest-foreign-students', [ApiController::class, 'getLatestForeignStudents'])->name('getLatestForeignStudents');
    Route::get('semester-course-list/{student_id}/{semester_number?}', [ApiController::class, 'getSemesterCourseList'])->name('getSemesterCourseList');
    Route::get('/provisional_result/{student_id}', [ApiController::class, 'provisional_result'])->name('provisional_result');

    /*
     *  Reg card print api
     *
     */
    Route::get('/registration_cards_print/{batch_id}/{m_batch_id}/{token}/{token2}/{site_token}', [ApiController::class, 'registration_cards_print'])->name('registration_cards_print');
    Route::get('/show_batch_list_for_reg_card_printing/{site_token}', [ApiController::class, 'show_batch_list_for_reg_card_printing'])->name('show_batch_list_for_reg_card_printing');
    Route::get('/show_batch_list_for_reg_card_printed/{site_token}', [ApiController::class, 'show_batch_list_for_reg_card_printed'])->name('show_batch_list_for_reg_card_printing');
    Route::get('/reg_card_print_done/{batch_id}/{m_batch_id}/{_token}/{site_token}/{token2}', [ApiController::class, 'reg_card_print_done'])->name('reg_card_print_done');


    Route::get('/get_deptartments', [ApiController::class, 'get_deptartments'])->name('get_deptartments');
    Route::get('/get_all_batch', [ApiController::class, 'get_all_batch'])->name('get_all_batch');
    Route::get('/get_single_batch_detail/{batch_id}', [ApiController::class, 'get_single_batch_detail'])->name('get_single_batch_detail');

    Route::get('/get_student_by_id/{id}', [ApiController::class, 'get_student_by_id'])->name('get_student_by_id');
    Route::get('/get_students_by_batch_id/{id}', [ApiController::class, 'get_student_by_batchid'])->name('get_student_by_batchid');
    Route::get('/get_student_by_reg_code/{reg_code}', [ApiController::class, 'get_student_by_reg_code'])->name('get_student_by_reg_code');


    Route::get('/get_batch_id_name/{department_id}', [ApiController::class, 'get_batch_id_name'])->name('get_batch_id_name');
    Route::get('/check_student/{department_id}/{batch_id}/{reg_code}/{roll_no}/{phone_no}', [ApiController::class, 'check_student'])->name('check_student');
    Route::get('/student_account_info/{ora_uid}', [ApiController::class, 'student_account_info'])->name('student_account_info');
    Route::get('/student_account_info_summary/{ora_uid}', [ApiController::class, 'student_account_info_summary'])->name('student_account_info_summary');
    Route::get('/get_all_teacher/{dept_short_code}', [ApiController::class, 'get_all_teacher'])->name('get_all_teacher');

    Route::get('/get_past_foreign_student/{fromPage?}/{noOfRowsPerpage?}', [ApiController::class, 'get_past_foreign_student'])->name('get_past_foreign_student');
    Route::get('/get_present_foreign_student/{fromPage?}/{noOfRowsPerpage?}', [ApiController::class, 'get_present_foreign_student'])->name('get_present_foreign_student');

    Route::get('/src_by_reg/{reg_no}', [ApiController::class, 'src_by_reg'])->name('src_by_reg');
    Route::get('/student_by_id/{id}', [ApiController::class, 'student_by_id'])->name('student_by_id');

    Route::put('/update-students-actual-fee-and-number-of-semester', [ApiController::class, 'updateStudentsActualFeeAndNumberOfSemester'])->name('updateStudentsActualFeeAndNumberOfSemester');
    Route::put('/update-ct-students-actual-fee-and-semester-n-paymenet-from-semseter', [ApiController::class, 'updateCtStudentsActualFeeAndOthers'])->name('updateCtStudentsActualFeeAndOthers');
    Route::put('/apply-extra-fee-on-students', [ApiController::class, 'applyExtraFeeOnStudents'])->name('applyExtraFeeOnStudents');

    /**
     * bellow code commenting after discussion with Mesbaul
     */
    Route::get('/admission_on_going_batch', [ApiController::class, 'admission_on_going_batch'])->name('admission_on_going_batch');
// Route::get('/get_student_by_adm_frm_no/{adm_frm_no}', 'ApiController@get_student_by_adm_frm_no')->name('get_student_by_adm_frm_no');

    /**
     * Bellow route is off to make off admission of student from Admission and Inte'l site. Discussion with misbaul
     */
//Route::POST('/admission', 'ApiController@admission')->name('admission');


    Route::get('religion', [ApiController::class, 'religion'])->name('religion');
    Route::get('all_employees', [ApiController::class, 'all_employees'])->name('all_employees');

    Route::get('/get_deptartment/{id}', [ApiController::class, 'get_deptartment_info_by_id'])->name('get_deptartment');
    Route::get('/get_batch/{id}', [ApiController::class, 'get_batch_info_by_id'])->name('get_batch');
    Route::get('/admission_team/', [ApiController::class, 'get_admission_team'])->name('admission_team');
    Route::get('/batch-mate/{std_id}', [ApiController::class, 'get_batch_mate'])->name('batch_mate');

    Route::get('get_banks', [ApiController::class, 'get_banks'])->name('get_banks');

    Route::get('get-bank/{id}', [ApiController::class, 'get_bank'])->name('get_banks');

    Route::GET('cashin-report', [ApiController::class, 'cashInReport'])->name('cashInReport');


    /* Improvement route */
    Route::get('/eligible_for_incourse/{id}/{examSchedule}', [Imp_Eligible_Courses::class, 'eligible_for_incourse'])->name('eligible_for_incourse');
    Route::get('/eligible_for_final/{id}/{examSchedule}', [Imp_Eligible_Courses::class, 'eligible_for_final'])->name('eligible_for_final');

    Route::POST('/apply_improvement_request', [Imp_Request::class, 'store'])->name('apply_improvement_request');
    Route::POST('/cancel_improvement_request', [Imp_Request::class, 'destroy'])->name('cancel_improvement_request');
    Route::GET('/get_current_improvement_exam_schedule', [Imp_Exam_Schedule::class, 'get_current_improvement_exam_schedule'])->name('get_current_improvement_exam_schedule');

    Route::GET('/get-improvement-application-data/{std_id}/{currentExamScheduleId}/{type}', [Imp_Invoice_Generator::class, 'get_improvement_application_form_data'])->name('get_improvement_application_form_data');

    Route::GET('/get-improvement-application-data-for-cms/{reg_code}/{currentExamScheduleId}/{type}', [Imp_Invoice_Generator::class, 'get_improvement_application_form_data_for_cms'])->name('get_improvement_application_form_data_for_cms');

    Route::POST('/get_student_for_payment', [Imp_Invoice_Generator::class, 'get_student_for_payment'])->name('get_student_for_payment');
    Route::POST('/make_improvement_payment_complete', [Imp_Invoice_Generator::class, 'make_improvement_payment_complete'])->name('make_improvement_payment_complete');
    Route::POST('/get_improvement_admit_card', [Imp_Invoice_Generator::class, 'get_improvement_admit_card'])->name('get_improvement_admit_card');

    Route::POST('/download_regular_admit_card', [ApiController::class, 'download_regular_admit_card'])->name('download_regular_admit_card');


    Route::POST('/get_improvement_marksheet_for_student', [Imp_Marksheet::class, 'get_improvement_marksheet_for_student'])->name('get_improvement_marksheet_for_student');
    Route::GET('/get_applied_improvement_exam_schedule/{std_id}', [Imp_Exam_Schedule::class, 'get_applied_improvement_exam_schedule'])->name('get_applied_improvement_exam_schedule');
    Route::GET('/get_improvement_exam_routine/{std_id}/{examSheduleId?}', [ApiController::class, 'getImprovementExamRoutine'])->name('getImprovementExamRoutine');
    Route::GET('/get_all_improvement_exam_schedule', [ApiController::class, 'getImprovementExamSchedule'])->name('getImprovementExamSchedule');

    Route::GET('/get-student-by-regcode-part/{txid}/{regcodepartin?}', [ApiController::class, 'getStudentByRegcodePart'])->name('getStudentByRegcodePart');
    Route::GET('/get-student-by-regcode-part-for-manual-input/{regcodepartin}', [ApiController::class, 'getStudentByRegcodePartForManualInput'])->name('getStudentByRegcodePartForManualInput');
    Route::post('/mobile-banking/manual-entry', [ApiController::class, 'save_moblie_payment'])->name('save_moblie_payment');
    Route::post('/mobile-banking/import-mobile-banking-transaction', [ApiController::class, 'importTransaction'])->name('importTransactionForMobileBanking');

    Route::get('/covid-discount-as-scholarhip', [ApiController::class, 'save_covid_discount'])->name('save_covid_discount');
    Route::post('/save-student-scholarship-as-liaison-officer', [ApiController::class, 'save_student_scholarship_as_liaison_officer'])->name('save_student_scholarship_as_liaison_officer');
    Route::get('/get-ref-student/{type}', [ApiController::class, 'getRefStudent'])->name('getRefStudent');
    Route::get('/get-ref-single-student/{type}/{stdid}', [ApiController::class, 'getRefSingleStudent'])->name('getRefSingleStudent');

    Route::POST('/rms-get-batch-info-by-ids', [ApiController::class, 'rms_get_batch_info_by_ids'])->name('rms_get_batch_info_by_ids');

    Route::POST('/attendance_departments', [ApiController::class, 'attendance_departments'])->name('attendance_departments');
    Route::POST('/attendance_students', [ApiController::class, 'attendance_students'])->name('attendance_students');

    Route::group(['prefix' => 'bank', 'middleware' => ['bank']], function () {

//    Route::get('/students', 'APIBankController@getStudents');
        Route::post('students', [APIBankController::class, 'getStudentDetail']);

    });


    Route::POST('/get-accounts-info', [ApiController::class, 'students_account_info_summary']);
    Route::GET('/get-batch-wise-account-info/{batchId}', [ApiController::class, 'batchWiseAccountInfo'])->name('batchWiseAccountInfo');
    Route::GET('/get-batch-wise-account-info-non-covid/{batchId}', [ApiController::class, 'batchWiseAccountInfoNonCovid'])->name('batchWiseAccountInfo');
    Route::GET('/get-purpose-pay', [ApiController::class, 'getPurposePay'])->name('getPurposePay');
    Route::GET('/get-purpose-pay/{id}', [ApiController::class, 'getPurposePayById'])->name('getPurposePayById');

    Route::POST('/general-payment', [ApiController::class, 'save_general_payment'])->name('getPurposePay');


    Route::prefix('student')->group(function () {
        Route::get('/{studentId}', StudentDownloadFormInfoController::class);
    });

    Route::prefix('student-report')->group(function () {
        Route::get('/{regCode}', StudentReportController::class);
    });

    Route::prefix('admission')->group(function () {
        Route::get('active-batch-for-admission', ActiveBatchForAdmissionController::class);
        Route::get('batch-wise-students/{batch_id}', BatchWiseStudentsController::class);
        Route::get('batch-wise-unverified-students/{batch_id}', BatchWiseUnVerifiedStudentsController::class);
        Route::get('refereed-by-parent/index', RefereedByParentIndexController::class);
        Route::get('refereed-child-by-parent/{parent_id}', RefereedChildByParentIndexController::class);
        Route::post('active-batch-student-store', ActiveBatchStudentStoreController::class);
        Route::post('batch-store', BatchStoreController::class);
        Route::post('unverified-student-reg-code-generate', AdmissionStudentRegCodeGenerateController::class);
    });

    Route::get('shifts', ShiftIndexController::class);
    Route::get('groups', GroupIndexController::class);
    Route::get('country', CountryIndexController::class);
    Route::get('campuss', CampussIndexController::class);
    Route::get('payment-system', PaymentSystemIndexController::class);


    Route::get('/test', function (Request $request) {
//        return \App\Models\O_COURSE::orderBy('ID', 'asc')->where('department_id',7)->get();
        return \App\Models\O_DEPARTMENTS::get();
    });
});
