<?php

namespace App\Models;

use App\Exceptions\NoCourseFoundException;
use Illuminate\Database\Eloquent\Model;

class O_IMP_EXAM_SCHEDULE extends Model
{
    const NOTAPPROVE = 0;
    const DENYED = 1;
    const APPROVED = 2;

    public $timestamps = false;
    protected $table = "IMP_EXAM_SCHEDULE";
    protected $connection = 'oracle';

    protected $fillable = ['NAME', 'EXAM_START_DATE', 'FORM_FILLUP_LAST_DATE', 'H_INON_FEE', 'H_FION_FEE', 'H_INTW_FEE', 'H_FITW_FEE', 'H_INTH_FEE', 'H_FITH_FEE', 'M_INON_FEE', 'M_FION_FEE', 'M_INTW_FEE', 'M_FITW_FEE', 'M_INTH_FEE', 'M_FITH_FEE', 'APPROVE_STATUS', 'APPROVE_BY', 'CREATED_BY', 'CREATED_AT', 'UPDATED_AT'];

    public function getExamStartDateAttribute($value)
    {
        return date('Y-m-d h:i:s A', $value);
    }

    public function scopeCurrentSchedule( $query )
    {
        return $query->latest('exam_start_date')->first();
    }

    /**
     * Is Exam Schedule Approved
     * @return bool
     */
    public function isApproved(){
        return $this->approve_status == O_IMP_EXAM_SCHEDULE::APPROVED;
    }

    /**
     * Is Exam Schedule Denied
     * @return bool
     */
    public function isDenied(){
        return $this->approve_status == O_IMP_EXAM_SCHEDULE::DENYED;
    }

    /**
     * Is Exam Schedule Not Approved
     * @return bool
     */
    public function isNotApproved(){
        return $this->approve_status != O_IMP_EXAM_SCHEDULE::APPROVED;
    }

    public function scopeCurrentScheduleCollection( $query )
    {
        return $query
            ->where('APPROVE_STATUS', self::APPROVED)
//            ->where('EXAM_START_DATE', '<', time() )
            ->where('FORM_FILLUP_LAST_DATE', '>', time() )
            ->get();
    }

    public function getFormFillupLastDateAttribute($value)
    {
        return date('Y-m-d h:i:s A', $value);
    }

    public function relApproveBy()
    {
        return $this->belongsTo(M_WP_EMP::class, 'approve_by', 'id');
    }

    public function relCreateBy()
    {
        return $this->belongsTo(M_WP_EMP::class, 'create_by', 'id');
    }

    public function relXMSduleDPT()
    {
        return $this->hasMany(O_IMP_XM_SDULE_DPT::class, 'ies_id', 'id');
    }

    public static function getCourseCost( O_IMP_EXAM_SCHEDULE $schedule, int $std_id, int $course_id, string $type){

        $course = O_COURSE::find($course_id )->first();

        if ( ! $course ) throw new NoCourseFoundException;

        $impRequest = O_IMP_REQUEST::where(['ies_id'=>$schedule->id, 'std_id'=> $std_id,'type'=>$type])->first();


        $request_course_query = O_IMP_REQUEST_COURSE::where(['std_id' => $std_id, 'course_id' => $course_id, 'type'=> $type ]);

        if ( $impRequest)
            $request_course_query->where('imp_rq','!=', $impRequest->id);



        $count_request_course = $request_course_query->count();

        $course_fee = [
            'hons' => [
                'incourse' => $schedule->h_fith_fee,
                'final' => $schedule->h_fith_fee
            ],
            'masters' => [
                'incourse' => $schedule->m_inth_fee,
                'final' => $schedule->m_fith_fee
            ]
        ];

        if ($count_request_course == 0)
        {
            $course_fee = [
                'hons' => [
                    'incourse' => $schedule->h_inon_fee,
                    'final' => $schedule->h_fion_fee
                ],
                'masters' => [
                    'incourse' => $schedule->m_inon_fee,
                    'final' => $schedule->m_fion_fee
                ]
            ];
        }
        else if ($count_request_course == 1)
        {
            $course_fee = [
                'hons' => [
                    'incourse' => $schedule->h_intw_fee,
                    'final' => $schedule->h_fitw_fee
                ],
                'masters' => [
                    'incourse' => $schedule->m_intw_fee,
                    'final' => $schedule->m_fitw_fee
                ]
            ];
        }
        else if ($count_request_course == 2)
        {
            $course_fee = [
                'hons' => [
                    'incourse' => $schedule->h_inth_fee,
                    'final' => $schedule->h_fith_fee
                ],
                'masters' => [
                    'incourse' => $schedule->m_inth_fee,
                    'final' => $schedule->m_fith_fee
                ]
            ];
        }

        return $course_fee;

    }


    public function relImpExamShift()
    {
        return $this->hasOne(O_IMP_EXIM_SHIFT::class, 'ies_id', 'id');
    }


    public function relExamRoutine()
    {
        return $this->hasMany(O_IMP_EXIM_ROUTINE::class, 'ies_id', 'id');
        
    }
}
