<?php

namespace app\controllers;


use app\models\Platform;
use Psr\Container\ContainerInterface;

Class BaseController
{
    protected $ci;
    protected $renderer;
    protected $view;
    protected $db;
    protected $ding;
    protected $corpId;

    public function __construct(ContainerInterface $ci)
    {
        //容器
        $this->ci = $ci;
        //twig视图
        $this->view = $this->ci->view;
        //orm激活
        $this->db = $this->ci->db;
        $this->ding = new Platform();
        $this->corpId = $this->ding->getCorpId();
    }

    /**
     * 返回的json数据格式
     * @param $code
     * @param $msg
     * @param array $data
     * @return array
     */
    protected function jsonFormatData($code, $msg, $data = [])
    {
        $arr = [
            'code' => $code,
            'msg' => $msg
        ];
        if (!empty($data)) {
            $arr['data'] = $data;
        }
        return $arr;
    }
}