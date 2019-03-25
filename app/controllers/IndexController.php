<?php

namespace app\controllers;

use app\models\BehaviorRecord;
use app\models\MonthRecord;
use app\models\Platform;

Class IndexController extends BaseController
{
    /**
     * 月展示
     * @param $request
     * @param $response
     * @param $args
     * @return mixed
     */
    public function month($request, $response, $args)
    {
        //当前月份
        $currentMonth = date('Y-m');
        //传递的月份
        $month = $request->getQueryParam('month', $currentMonth);
        //获取列表
        $monthStamp = strtotime($month);
        //获取当月的列表
        $list = MonthRecord::select('id', 'platform_id', 'total_score', 'month')
            ->where('month', $monthStamp)
            ->orderBy('total_score', 'desc')
            ->get()
            ->toArray();
        if (!empty($list)) {
            foreach ($list as $key => &$value) {
                $value['platform_name'] = Platform::getPlatformOptions($value['platform_id']);
                $value['month_at'] = date('Y-m', $value['month']);
            }
            unset($value);
        }
        //获取选择列表
        $platformOptions = Platform::getPlatformOptions();
        $data = [
            'month' => $month,
            'platformOptions' => $platformOptions,
            'list' => $list,
            'corp_id' => $this->corpId
        ];

        return $this->view->render($response, 'index/month.twig', $data);
    }

    /**
     * 每月某平台的详情
     * @param $request
     * @param $response
     * @param $args
     * @return
     */
    public function monthInfo($request, $response, $args)
    {
        $params = $request->getParams();
        $id = isset($params['id']) ? intval($params['id']) : 0;
        $info = MonthRecord::where('id', $id)->first();
        if ($request->isPost()) {
            //如果是post请求的详情页面
            if (empty($info)) {
                $returnData = $this->jsonFormatData(-1, '未获取到详情页面');
                return $response->withJson($returnData);
            }
            $info = $info->toArray();
            $info['month_at'] = date('Y-m', $info['month']);
            $returnData = $this->jsonFormatData(0, '获取详情页面成功', $info);
            return $response->withJson($returnData);
        }
        //如果是页面展示详情页
        if (empty($info)) {
            return $this->view->render($response, 'public/404.twig');
        }
        $behaviorList = $info->behavior->toArray();
        $info = $info->toArray();
        $info['month_at'] = date('Y-m', $info['month']);
        $info['platform_name'] = Platform::getPlatformOptions($info['platform_id']);
        return $this->view->render($response, 'index/month_info.twig', ['info' => $info, 'behavior_list' => $behaviorList, 'corp_id' => $this->corpId]);
    }

    /**
     * 月记录提交
     * @param $request
     * @param $response
     * @param $args
     * @return
     */
    public function monthSubmit($request, $response, $args)
    {
        $post = $request->getParsedBody();
        //只有管理员才能提交
        $userId = isset($post['user_id']) ? $post['user_id'] : '';
        $noAuth = true;
        if (!empty($userId)) {
            $adminIds = $this->ding->getAdminIds();
            if (in_array($userId, $adminIds)) {
                $noAuth = false;
            }
        }
        if ($noAuth) {
            $returnData = $this->jsonFormatData(-1, '只有管理员有权限操作');
            return $response->withJson($returnData);
        }

        $fields = [
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
            'month_at',
        ];
        $data = [];
        foreach ($fields as $field) {
            if (empty($post[$field])) {
                $returnData = $this->jsonFormatData(-1, '请填写全部内容');
                return $response->withJson($returnData);
            }
            if (in_array($field, ['platform_id', 'cooperate_score', 'sale_score', 'team_score'])) {
                $data[$field] = intval($post[$field]);
            } else {
                $data[$field] = trim($post[$field]);
            }
        }

        $platformOptions = Platform::getPlatformOptions();
        if (!in_array($data['platform_id'], array_keys($platformOptions))) {
            $returnData = $this->jsonFormatData(-1, '所选平台不存在');
            return $response->withJson($returnData);
        }
        $data['month'] = strtotime($data['month_at']);//转为时间戳
        //判定是否已经添加过
        $id = isset($post['id']) ? intval($post['id']) : 0;//是新增还是修改
        if ($id <= 0) {
            //如果是新增，判定是否已经新增过了
            $info = MonthRecord::select('id')
                ->where('month', $data['month'])
                ->where('platform_id', $data['platform_id'])
                ->first();
            if (!empty($info)) {
                $returnData = $this->jsonFormatData(-1, '已添加过此月份此平台数据');
                return $response->withJson($returnData);
            }
        } else {
            //判定是否有此数据
            $info = MonthRecord::select('id', 'behavior_score')
                ->where('id', $id)
                ->first();
            if (empty($info)) {
                $returnData = $this->jsonFormatData(-1, '未获取到此数据详情');
                return $response->withJson($returnData);
            }
        }

        if ($data['cooperate_score'] < 0 || $data['team_score'] < 0 || $data['sale_score'] < 0) {
            $returnData = $this->jsonFormatData(-1, '得分不能小于0');
            return $response->withJson($returnData);
        }
        $data['total_score'] = $data['cooperate_score'] + $data['team_score'] + $data['sale_score'];
        if ($data['total_score'] > 100) {
            $returnData = $this->jsonFormatData(-1, '总分不能超过100分');
            return $response->withJson($returnData);
        }

        if ($id > 0) {
            //如果是修改，总分还要考虑扣除的分数
            $data['total_score'] = $data['total_score'] - $info['behavior_score'];
            $result = MonthRecord::where('id', $id)->update($data);
        } else {
            $result = MonthRecord::create($data);
        }
        if (!$result) {
            $returnData = $this->jsonFormatData(-1, '提交失败');
            return $response->withJson($returnData);
        }

        $returnData = $this->jsonFormatData(0, '提交成功');
        return $response->withJson($returnData);
    }

    /**
     * 行为分提交
     * @param $request
     * @param $response
     * @param $args
     */
    public function behaviorSubmit($request, $response, $args)
    {
        $post = $request->getParsedBody();
        //只有管理员才能提交
        $userId = isset($post['user_id']) ? $post['user_id'] : '';
        $noAuth = true;
        if (!empty($userId)) {
            $adminIds = $this->ding->getAdminIds();
            if (in_array($userId, $adminIds)) {
                $noAuth = false;
            }
        }
        if ($noAuth) {
            $returnData = $this->jsonFormatData(-1, '只有管理员有权限操作');
            return $response->withJson($returnData);
        }

        if (empty($post['behavior_reason']) || empty($post['behavior_score'])) {
            $returnData = $this->jsonFormatData(-1, '请填写全部内容');
            return $response->withJson($returnData);
        }
        $reason = trim($post['behavior_reason']);
        $score = intval($post['behavior_score']);
        if ($score < 0) {
            $returnData = $this->jsonFormatData(-1, '扣除分数不能小于0');
            return $response->withJson($returnData);
        }
        $recordId = isset($post['record_id']) ? intval($post['record_id']) : 0;
        //查询记录是否存在
        $info = MonthRecord::select('id', 'behavior_score')
            ->where('id', $recordId)
            ->first();
        if (empty($info)) {
            $returnData = $this->jsonFormatData(-1, '未获取到管理的每月数据信息');
            return $response->withJson($returnData);
        }
        //事务保存信息
        $data = [
            'record_id' => $recordId,
            'reason' => $reason,
            'score' => $score
        ];
        //保存数据
        $behaviorModel = new BehaviorRecord();
        $result = $behaviorModel->saveBehavior($data);
        if (!$result) {
            $returnData = $this->jsonFormatData(-1, '提交失败');
            return $response->withJson($returnData);
        }

        $returnData = $this->jsonFormatData(0, '提交成功');
        return $response->withJson($returnData);
    }

    /**
     * 删除行为记录
     * @param $request
     * @param $response
     * @param $args
     */
    public function behaviorDelete($request, $response, $args)
    {
        $post = $request->getParsedBody();
        //只有管理员才能提交
        $userId = isset($post['user_id']) ? $post['user_id'] : '';
        $noAuth = true;
        if (!empty($userId)) {
            $adminIds = $this->ding->getAdminIds();
            if (in_array($userId, $adminIds)) {
                $noAuth = false;
            }
        }
        if ($noAuth) {
            $returnData = $this->jsonFormatData(-1, '只有管理员有权限操作');
            return $response->withJson($returnData);
        }
        //判定行为记录是否存在
        $id = isset($post['id']) ? intval($post['id']) : 0;
        $info = BehaviorRecord::select('id')
            ->where('id', $id)
            ->first();
        if (empty($info)) {
            $returnData = $this->jsonFormatData(-1, '未获取到此行为信息');
            return $response->withJson($returnData);
        }
        //删除数据
        $behaviorModel = new BehaviorRecord();
        $result = $behaviorModel->deleteBehavior($id);
        if (!$result) {
            $returnData = $this->jsonFormatData(-1, '删除失败');
            return $response->withJson($returnData);
        }

        $returnData = $this->jsonFormatData(0, '删除成功');
        return $response->withJson($returnData);

    }

    /**
     * 年展示
     * @param $request
     * @param $response
     * @param $args
     */
    public function year($request, $response, $args)
    {
        //当前月份
        $currentYear = date('Y');
        //传递的月份(参数仍然为month)
        $year = $request->getQueryParam('month', $currentYear);
        $monthRecordModel = new MonthRecord();
        $list = $monthRecordModel->getYearRecordList($year);
        if (!empty($list)) {
            foreach ($list as $key => &$value) {
                $value['platform_name'] = Platform::getPlatformOptions($value['platform_id']);
            }
            unset($value);
        }
        $data = [
            'year' => $year,
            'list' => $list,
            'active' => 'year',
            'corp_id' => $this->corpId
        ];

        return $this->view->render($response, 'index/year.twig', $data);
    }

    /**
     * 获取钉钉用户信息
     * @param $request
     * @param $response
     * @param $args
     * @return
     */
    public function getUserInfo($request, $response, $args)
    {
        $code = $request->getParsedBody()['code'];
        $userInfo = $this->ding->getUserInfo($code);
        if ($userInfo['errcode'] == 0) {
            $userId = $userInfo['userid'];
            $data = [
                'user_id' => $userId,
                'is_admin' => 0
            ];
            $adminIds = $this->ding->getAdminIds();
            if (in_array($userId, $adminIds)) {
                $data['is_admin'] = 1;
            }
            $returnData = $this->jsonFormatData(0, '获取成功', $data);
        } else {
            $returnData = $this->jsonFormatData(-1, '获取失败');
        }
        return $response->withJson($returnData);
    }
}