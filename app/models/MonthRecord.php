<?php

namespace app\models;

use Illuminate\Database\Capsule\Manager as DB;

Class MonthRecord extends BaseModel
{
    /**
     * 表名称
     * @var string
     */
    protected $table = "month_record";

    protected $fillable = [
        'cooperate_fact',
        'cooperate_plan',
        'cooperate_score',
        'platform_id',
        'sale_fact',
        'sale_plan',
        'sale_score',
        'team_fact',
        'team_plan',
        'team_score',
        'month',
        'total_score'
    ];

    /**
     * 行为记录
     */
    public function behavior()
    {
        return $this->hasMany('app\models\BehaviorRecord', 'record_id', 'id');
    }

    /**
     * 获取年记录列表
     * @param $year
     * @return
     */
    public function getYearRecordList($year)
    {
        //获取列表
        $yearStamp = strtotime($year.'-01-01');
        $nextYearStamp = strtotime($year . '-01-01 +1 year');
        //获取全年的
        $list = $this->select(DB::raw('sum(total_score) as year_total_score,platform_id'))
            ->where('month', '>=', $yearStamp)
            ->where('month', '<', $nextYearStamp)
            ->orderBy('year_total_score', 'desc')
            ->groupBy('platform_id')
            ->get()
            ->toArray();
        return $list;
    }
}

