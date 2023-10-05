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

namespace App\Models;

use App\Events\db_delete_log_event;
use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class O_CASHIN extends Eloquent
{
    public $timestamps = false;
    protected $table = "CASHIN";
    protected $connection = 'oracle';

    const IS_VARIFIED = 1;
    const NON_VARIFIED = 0;


    public function purposePay()
    {
        return $this->belongsTo(O_PURPOSE_PAY::class, 'purpose_pay_id', 'id');
    }

    /**
     * get student account info summary
     *
     * @param int $ora_uid @as studnet id
     */
    public static function  get_student_account_info_summary( int $ora_uid ){


        $cashinCollection = self::where(['student_id' => $ora_uid])
            //->where('is_varified', self::IS_VARIFIED) // no need to apply because, all account payment is not verified .
            ->with('purposePay')
            ->get();

        $student = O_STUDENT::selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ,  PASSWORD ,  DEPARTMENT_ID ,  BATCH_ID, ACTUAL_FEE , NO_OF_SEMESTER, payment_from_semester")->where(['id' => $ora_uid ])->first();

        $batchObj = O_BATCH::with('paymemtSystem')->find( $student->batch_id );


        $numberOfSemester = $batchObj->paymemtSystem->nos;
        $paymentSystemDetailsCollection = $batchObj->paymemtSystem->payment_system_detail;

        //$noOfEximptedSemester = $numberOfExemptedSemester = $paymentSystemDetailsCollection->where('semestertype', O_PAYMENT_SYSTEM_DETAIL::EXEMPTED)->count();

        $admissionFee = (int) $cashinCollection->where('purpose_pay_id', O_PURPOSE_PAY::ADMISSION_FEE_STATUS_ID)->sum('amount');

        // step: 1
        $actualFee = (int) $student->actual_fee;

        //  step: 2
        $numberOfSemestInStudentTable = (int) $student->no_of_semester;

        //  step: 3
        $persemsterFeeWithoutScholarship = $numberOfSemestInStudentTable == 0 ? 0: ($actualFee / $numberOfSemestInStudentTable);

        //  step: 4
        $sumOfScholarship= (int) $cashinCollection->where('purpose_pay_id', O_PURPOSE_PAY::SCHOLARSHIP_STATUS_ID)->sum('amount');

        // step: 5

        if ( strpos($student->reg_code,'-CT') > 0 ){ // is Credit Transfer Student?
            $semester =  ( $numberOfSemestInStudentTable - $student->payment_from_semester )==0
                ? $numberOfSemestInStudentTable
                :( $numberOfSemestInStudentTable - $student->payment_from_semester);
            $perSemesterScholarship = $numberOfSemestInStudentTable == 0? 0 : ( ($sumOfScholarship + $admissionFee) / $semester);
        }else{
            $perSemesterScholarship = $numberOfSemestInStudentTable == 0? 0 : ( ($sumOfScholarship + $admissionFee) / $numberOfSemestInStudentTable);
        }


        // step: 6
        $sumOfTutionsFee=   (int) $cashinCollection->where('purpose_pay_id', O_PURPOSE_PAY::TUITION_FEE_STATUS_ID)->sum('amount');

	    $sumOfWaiverFee=   (int) $cashinCollection->where('purpose_pay_id', O_PURPOSE_PAY::WAIVER_ID)->sum('amount');

	    $advEngFee=   (int) $cashinCollection->where('purpose_pay_id', 23)->sum('amount'); // adv eng fee

        $internProjectResourceFee =   (int) $cashinCollection->where('purpose_pay_id', 20)->sum('amount'); //Internship Project Resource Fee

	    $totalPaid =  $admissionFee  + $sumOfTutionsFee + $sumOfWaiverFee + $advEngFee + $internProjectResourceFee;

        // step: 7
        $parent_batch_id  = (int) $batchObj->parent_batch_id ;
        if ( $parent_batch_id > 0){
             $semesterCompleted = O_SEMESTERS::where('batch_id', $batchObj->id)
                ->orWhere('batch_id', $batchObj->parent_batch_id )
                ->where('exempted',0)->where('department_id', $batchObj->department_id)->count();
        }
        else{
           $semesterCompleted = O_SEMESTERS::where('batch_id', $batchObj->id)->where('exempted',0)->where('department_id', $batchObj->department_id)->count();
        }
       

        $noOfEximptedSemester = O_SEMESTERS::where('batch_id', $batchObj->id)->where('exempted',1)->where('department_id', $batchObj->department_id)->count();
        $semesterNeedToComplete = ( $semesterCompleted) - $noOfEximptedSemester;

        // step: 8
        $currentPayable = ($persemsterFeeWithoutScholarship * ($semesterCompleted ) ) - ($perSemesterScholarship * $semesterCompleted );

        // step: 10
        $totalDues = $actualFee  - $sumOfScholarship - $totalPaid;


        //step: 11
        if ( strpos($student->reg_code,'-CT') > 0 ){ // is Credit Transfer Student?
            $perSemesterFee = $actualFee / (
                    ( $numberOfSemestInStudentTable - $student->payment_from_semester )==0
                    ? $numberOfSemestInStudentTable
                    :( $numberOfSemestInStudentTable - $student->payment_from_semester)
                );
            $perSemesterFee = $perSemesterFee - $perSemesterScholarship;
        }else{
            $perSemesterFee = $persemsterFeeWithoutScholarship - $perSemesterScholarship;
        }


        // step: 9
        $currentDues = $currentPayable - $totalPaid + $admissionFee;


         if( $totalDues < $currentDues){
                $currentDues = $totalDues;
        }

        return [
            'summary' => [
                'batch'=> $batchObj,
                'said_fee' => 0, // should be removed after student site update
                'nos' => $semesterCompleted ,
                'currentPayable' => $currentPayable ,
                'eximp-sem' => $noOfEximptedSemester ,
                'sum_of_tution_fee' => $sumOfTutionsFee,
                'common_scholarship' => 0, // should be removed after student site update
                'actual_total_fee' => (int) $actualFee,
                'special_scholarship' => $sumOfScholarship,
                'per_semester_fee' => ceil($perSemesterFee),
                'per_semester_fee_without_scholarship' => ceil($persemsterFeeWithoutScholarship),
                'total_paid' => $totalPaid,
                'total_current_due' => $currentDues,
                'Due_Up_to_April' => $currentDues - ceil($perSemesterFee),
                'total_due' => ceil($totalDues),
            ],
        ];
    }

    /**
     * get student account info summary
     *
     * @param int $ora_uid @as studnet id
     */
    public static function  get_student_account_info_summary_covid( int $ora_uid ){


        $cashinCollection = self::where(['student_id' => $ora_uid])
            //->where('is_varified', self::IS_VARIFIED) // no need to apply because, all account payment is not verified .
            ->with('purposePay')
            ->orderBy('id','desc')
            ->get();
        // dd($cashinCollection);
        $lastTransaction = $cashinCollection->where('purpose_pay_id', O_PURPOSE_PAY::TUITION_FEE_STATUS_ID)->first();

        $student = O_STUDENT::selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ,  PASSWORD ,  DEPARTMENT_ID ,  BATCH_ID, ACTUAL_FEE , NO_OF_SEMESTER")->where(['id' => $ora_uid ])->first();

        $batchObj = O_BATCH::with('paymemtSystem')->find( $student->batch_id );


        $numberOfSemester = $batchObj->paymemtSystem->nos;
        $paymentSystemDetailsCollection = $batchObj->paymemtSystem->payment_system_detail;

        //$noOfEximptedSemester = $numberOfExemptedSemester = $paymentSystemDetailsCollection->where('semestertype', O_PAYMENT_SYSTEM_DETAIL::EXEMPTED)->count();

        $admissionFee = (int) $cashinCollection->where('purpose_pay_id', O_PURPOSE_PAY::ADMISSION_FEE_STATUS_ID)->sum('amount');

        // step: 1
        $actualFee = (int) $student->actual_fee;

        //  step: 2
        $numberOfSemestInStudentTable = (int) $student->no_of_semester;

        //  step: 3
        $persemsterFeeWithoutScholarship = $numberOfSemestInStudentTable == 0 ? 0: ($actualFee / $numberOfSemestInStudentTable);

        //  step: 4
        $sumOfScholarship= (int) $cashinCollection->where('purpose_pay_id', O_PURPOSE_PAY::SCHOLARSHIP_STATUS_ID)->sum('amount');

        // step: 5
        $perSemesterScholarship = $numberOfSemestInStudentTable == 0? 0 : ( ($sumOfScholarship + $admissionFee) / $numberOfSemestInStudentTable);

        // step: 6
        $sumOfTutionsFee=   (int) $cashinCollection->where('purpose_pay_id', O_PURPOSE_PAY::TUITION_FEE_STATUS_ID)->sum('amount');

	    $sumOfWaiverFee=   (int) $cashinCollection->where('purpose_pay_id', O_PURPOSE_PAY::WAIVER_ID)->sum('amount');

        $advEngFee =   (int) $cashinCollection->where('purpose_pay_id', 23)->sum('amount'); // adv eng fee

         $internProjectResourceFee =   (int) $cashinCollection->where('purpose_pay_id', 20)->sum('amount'); //Internship Project Resource Fee

        $totalPaid =  $admissionFee  + $sumOfTutionsFee + $sumOfWaiverFee + $advEngFee + $internProjectResourceFee;


        // step: 7
        $parent_batch_id  = (int) $batchObj->parent_batch_id ;
        if ( $parent_batch_id > 0){
            $semesterCompleted = O_SEMESTERS::where('batch_id', $batchObj->id)->orWhere('batch_id', $batchObj->parent_batch_id )->where('exempted',0)->count();
        }
        else{
            $semesterCompleted = O_SEMESTERS::where('batch_id', $batchObj->id)->where('exempted',0)->count();
        }

        $noOfEximptedSemester = O_SEMESTERS::where('batch_id', $batchObj->id)->where('exempted',1)->count();
        $semesterNeedToComplete = ( $semesterCompleted) - $noOfEximptedSemester;

        // step: 8
        $currentPayable = ($persemsterFeeWithoutScholarship * $semesterCompleted ) - ($perSemesterScholarship * $semesterCompleted );


        // step: 10
        $totalDues = $actualFee  - $sumOfScholarship - $totalPaid;


        //step: 11
        $perSemesterFee = $persemsterFeeWithoutScholarship - $perSemesterScholarship;


        // step: 9
        $currentDues = $currentPayable - $totalPaid + $admissionFee;

	 if( $totalDues < $currentDues){
                $currentDues = $totalDues;
        }

	$preSemesterDue = $currentDues - ceil($perSemesterFee);

//		if($preSemesterDue > $currentDues ) $preSemesterDue = $currentDues;

        return
            [
                'lastTransaction' => $lastTransaction==null?'':$lastTransaction,
                'nos' => $semesterCompleted ,
                'currentPayable' => $currentPayable ,
                'eximp-sem' => $noOfEximptedSemester ,
                'sum_of_tution_fee' => $sumOfTutionsFee,
                'actual_total_fee' => (int) $actualFee,
                'special_scholarship' => $sumOfScholarship,
                'per_semester_fee' => ceil($perSemesterFee),
                'per_semester_fee_without_scholarship' => ceil($persemsterFeeWithoutScholarship),
                'total_paid' => $totalPaid,
                'total_current_due' => $currentDues,
                'Due_upto_april' => $preSemesterDue,
                'total_due' => ceil($totalDues),
                'internProjectResourceFee'=>$internProjectResourceFee,
                'advEngFee'=>$advEngFee,
            ];
    }



    public function student()
    {
        return $this->belongsTo(O_STUDENT::class, 'student_id', 'id');
    }

}
