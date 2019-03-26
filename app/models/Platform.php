<?php
/**
 * 平台
 */

namespace app\models;


Class Platform
{
    const CLUB = 1;//俱乐部
    const PIN = 2;//品牌361
    const VLONG = 3;//威龙

    static protected $adminIds = [''];//todo,管理员ID

    /**
     * 获取管理员ID
     */
    static public function getAdminIds()
    {
        return self::$adminIds;
    }

    /**
     * 获取平台名称列表
     * @param bool $key
     * @return array|string
     */
    static public function getPlatformOptions($key = false)
    {
        $data = [
            self::CLUB => '企业家俱乐部',
            self::PIN => '361平台',
            self::VLONG => '威龙商务'
        ];
        if ($key === false) {
            return $data;
        }
        return isset($data[$key]) ? $data[$key] : '未知';
    }


}