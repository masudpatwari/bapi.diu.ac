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

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class O_PURPOSE_PAY extends Eloquent
{
    public $timestamps = false;
    protected $table = "PURPOSE_PAY";
    protected $connection = 'oracle';

    const	OTHERS_STATUS_ID = 1;
    const	SCHOLARSHIP_STATUS_ID = 2;
    const	REGISTRATION_FEE_STATUS_ID = 3;
    const	ADMISSION_FEE_STATUS_ID = 4;
    const	TUITION_FEE_STATUS_ID = 5;
    const	EXAMINATION_FEE_STATUS_ID = 6;
    const	IMPROVEMENT_FEE_STATUS_ID = 7;
    const	MARKS_SHEET_FEE_STATUS_ID = 8;
    const	PROVISIONAL_CERTIFICATE_FEE_STATUS_ID = 9;
    const	LIBRARY_FEE_STATUS_ID = 10;
    const	FINE_OR_LATE_FEE_STATUS_ID = 11;
    const	CONVOCATION_FEE_STATUS_ID = 12;
    const	ID_CARD_FEE_STATUS_ID = 13;
    const	RE_ADMISSION_FEE_STATUS_ID = 14;
    const	SESSION_FEE_STATUS_ID = 15;
    const	WAIVER_ID = 25;
}

