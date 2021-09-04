<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RELIGION extends Model
{
    public $timestamps = false;
    protected $table = "RELIGION";
    protected $connection = 'oracle';

    public static function test()
    {
        $dateShiftArray = [
            'code1-incourse' => '20-10-2020-s1',
            'code1-final' => '20-10-2020-s1',
            'code2-incourse' => '20-10-2020-s2',
        ];

        /**
         * get course list with type from O_IMP_REQUEST_COURSE by eis_id
         *
         * get date and shift
         * get course ids from date_shift
         * get student ids by course ids
         * if any student id found more than one time then student exam is overlaps
         */


        $courseAllocation = ['73_incourse' => '20-10-2020-s1','73_final' => '20-10-2020-s3', '76_incourse'=>'20-10-2020-s3'];

        $courseIdNTypesArray = array_keys($courseAllocation, '20-10-2020-s3');

        $impRequestArray = O_IMP_REQUEST::where('ies_id', 66)->get()->pluck('id')->toArray();
        dump($courseIdNTypesArray, $impRequestArray);

        $studentArray = [];
        foreach ($courseIdNTypesArray as $item){
            $exArray = explode('_', $item);
            $courseId = $exArray[0];
            $courseType = $exArray[1];

            $getStdIdArray = O_IMP_REQUEST_COURSE::whereIn('imp_rq', $impRequestArray)
                ->where('course_id', $courseId)
                ->where('type', $courseType)
                ->get()
                ->pluck('std_id')
                ->toArray();


            $studentArray = array_merge($studentArray, $getStdIdArray);

        }


        $stdCount = array_count_values($studentArray);
        $overlapStdIds = array_filter($stdCount, function ($i){
            return $i > 1;
        });
        dd($stdCount, $overlapStdIds);

    }
}
