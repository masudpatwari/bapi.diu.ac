<?php
/**
 * Created by PhpStorm.
 * User: lemon
 * Date: 10/22/19
 * Time: 5:50 PM
 */

namespace App\Traits;


use App\Models\O_MARKS;

trait Marks_Encryption_Trait
{

    /**
     * @param O_MARKS $mark
     *
     */
    public function make_ecrypt(O_MARKS $mark)
    {

        $status_mid = $mark->status_mid;
        $mt = $mark->mt;
        $ct = $mark->ct;
        $ap = $mark->ap;
        $ab = $mark->ab;
        $conti_total  = $mark->conti_total ;
        $status_final = $mark->status_final ;
        $m1 = $mark->m1 ;
        $m2 = $mark->m2 ;
        $m3 = $mark->m3 ;
        $m4 = $mark->m4 ;
        $m5 = $mark->m5 ;
        $m6 = $mark->m6 ;
        $m7 = $mark->m7 ;
        $m8 = $mark->m8 ;

        $final_total = $mark->final_total;
        $course_total = $mark->course_total;
        $letter_grade = $mark->letter_grade;
        $grade_point = $mark->grade_point;
        $roll = $mark->roll;

        $plain_text = "status_mid=$status_mid,mt=$mt,ct=$ct,ap=$ap,ab=$ab,conti_total=$conti_total,status_final=$status_final,m1=$m1,m2=$m2,m3=$m3,m4=$m4,m5=$m5,m6=$m6,m7=$m7,m8=$m8,m9=0,m10=0,final_total=$final_total,course_total=$course_total,letter_grade=$letter_grade,grade_point=$grade_point,roll=$roll";

        $encrypted_text = encrypt($plain_text);

//        $decrypt = decrypt($encrypted_text);
        $mark->encrypted_text = $encrypted_text;
        $mark->save();

    }

    /**
     * @param O_MARKS $mark
     */
    public function makeUpdate(O_MARKS &$mark)
    {

        $status_mid = $mark->status_mid;
        $mt = $mark->mt;
        $ct = $mark->ct;
        $ap = $mark->ap;
        $ab = $mark->ab;
        $conti_total  = $mark->conti_total ;
        $status_final = $mark->status_final ;
        $m1 = $mark->m1 ;
        $m2 = $mark->m2 ;
        $m3 = $mark->m3 ;
        $m4 = $mark->m4 ;
        $m5 = $mark->m5 ;
        $m6 = $mark->m6 ;
        $m7 = $mark->m7 ;
        $m8 = $mark->m8 ;

        $final_total = $mark->final_total;
        $course_total = $mark->course_total;
        $letter_grade = $mark->letter_grade;
        $grade_point = $mark->grade_point;
        $roll = $mark->roll;

        $plain_text = "status_mid=$status_mid,mt=$mt,ct=$ct,ap=$ap,ab=$ab,conti_total=$conti_total,status_final=$status_final,m1=$m1,m2=$m2,m3=$m3,m4=$m4,m5=$m5,m6=$m6,m7=$m7,m8=$m8,m9=0,m10=0,final_total=$final_total,course_total=$course_total,letter_grade=$letter_grade,grade_point=$grade_point,roll=$roll";

        $encrypted_text = encrypt($plain_text);

        $mark->encrypted_text = $encrypted_text;
        $mark->save();


    }

    /**
     * @param array $marksTableIdArray
     * @throws \Exception 'No. of Student is not equal Retrieved No. of Marks Rows'
     */
    public function markEncryptionByMarksTableIds(array $marksTableIdArray){

        if( count($marksTableIdArray) == 0 ) {
            throw new \Exception('No Marks Table ID given');
        }
        $marks = \App\Models\O_MARKS::find( $marksTableIdArray );

        if ($marks->count() != count($marksTableIdArray)){

            throw new \Exception('No. of Student is not equal Retrieved No. of Marks Rows');
        }


        foreach ($marks as $mark) {
            $this->makeUpdate($mark);
        }

    }


    public function check_marks_modified( O_MARKS $singel_course_marks )
    {
        $mkv_value = $singel_course_marks;
        $status_mid = $mkv_value['status_mid'];

        $mt = ( $mkv_value['mt'] !== Null )?  $mkv_value['mt']  : "";
        $ct = ( $mkv_value['ct'] !== Null )?  $mkv_value['ct']  : "";
        $ap = ( $mkv_value['ap'] !== Null )?  $mkv_value['ap']  : "";
        $ab = ( $mkv_value['ab'] !== Null )?  $mkv_value['ab']  : "";

        $conti_total = ( $mkv_value['conti_total'] !== Null )?  $mkv_value['conti_total']  : "";
        $status_final = $mkv_value['status_final'];

        $m1 = ($mkv_value['m1'] != NULL) ? $mkv_value['m1'] : "";
        $m2 = ($mkv_value['m2'] != NULL) ? $mkv_value['m2'] : "";
        $m3 = ($mkv_value['m3'] != NULL) ? $mkv_value['m3'] : "";
        $m4 = ($mkv_value['m4'] != NULL) ? $mkv_value['m4'] : "";
        $m5 = ($mkv_value['m5'] != NULL) ? $mkv_value['m5'] : "";
        $m6 = ($mkv_value['m6'] != NULL) ? $mkv_value['m6'] : "";
        $m7 = ($mkv_value['m7'] != NULL) ? $mkv_value['m7'] : "";
        $m8 = ($mkv_value['m8'] != NULL) ? $mkv_value['m8'] : "";

        $final_total = $mkv_value['final_total'];
        $course_total = $mkv_value['course_total'];
        $letter_grade = $mkv_value['letter_grade'];
        $grade_point = $mkv_value['grade_point'];
        $roll = $mkv_value['roll'];

        $plain_text = "status_mid=$status_mid,mt=$mt,ct=$ct,ap=$ap,ab=$ab,conti_total=$conti_total,status_final=$status_final,m1=$m1,m2=$m2,m3=$m3,m4=$m4,m5=$m5,m6=$m6,m7=$m7,m8=$m8,m9=0,m10=0,final_total=$final_total,course_total=$course_total,letter_grade=$letter_grade,grade_point=$grade_point,roll=$roll";

        if($plain_text == $this->getDecryptMarkString($mkv_value)){
            return false;
        }
        return true;

    }

    public function getDecryptMarkString(O_MARKS $markRow)
    {
        if ( ! $markRow ){
            throw new \Exception('No Mark Row Found');
        }

        return decrypt($markRow['encrypted_text']);

    }


}
