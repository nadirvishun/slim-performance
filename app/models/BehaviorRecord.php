<?php

namespace app\models;


use Illuminate\Database\Capsule\Manager as DB;

Class BehaviorRecord extends BaseModel
{
    /**
     * 表名称
     * @var string
     */
    protected $table = "behavior_record";

    protected $fillable = [
        'record_id',
        'reason',
        'score',
    ];

    /**
     * 保存结果
     */
    public function saveBehavior($data)
    {
        DB::beginTransaction();
        try {
            $score = $data['score'];
            $reason = $data['reason'];
            $recordId = $data['record_id'];
            $this->record_id = $recordId;
            $this->reason = $reason;
            $this->score = $score;
            $behaviorRes = $this->save();
            if (!$behaviorRes) {
                throw new \Exception('行为保存失败');
            }
            $monthRecordModel = MonthRecord::find($recordId);
            if (empty($monthRecordModel)) {
                throw new \Exception('获取关联详情失败');
            }
            $monthRecordModel->behavior_score += $score;
            $monthRecordModel->total_score -= $score;
            $monthRes = $monthRecordModel->save();
            if (!$monthRes) {
                throw new \Exception('修改总记录失败');
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    /**
     * 删除记录
     */
    public function deleteBehavior($id)
    {
        DB::beginTransaction();
        try {
            $behaviorModel = $this->find($id);
            $recordId = $behaviorModel->record_id;
            $score = $behaviorModel->score;
            $behaviorRes = $behaviorModel->delete();
            if (!$behaviorRes) {
                throw new \Exception('删除行为记录失败');
            }
            $monthRecordModel = MonthRecord::find($recordId);
            if (empty($monthRecordModel)) {
                throw new \Exception('获取关联详情失败');
            }
            $monthRecordModel->behavior_score -= $score;//删除，所以减去
            $monthRecordModel->total_score += $score;//总分是加上
            $monthRes = $monthRecordModel->save();
            if (!$monthRes) {
                throw new \Exception('修改总记录失败');
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }
}

