<?php
/**
 * 平台
 */

namespace app\models;

use EasyDingTalk\Application;

Class Platform
{
    const CLUB = 1;//俱乐部
    const PIN = 2;//品牌361
    const VLONG = 3;//威龙

    //钉钉相关数据
    const CORP_ID = '';//todo,企业ID
    const CORP_SECRET = '';//todo,企业secret
    const APP_KEY = 'dinggi7qmoiprtlycw4j';//新版需要用到
    const APP_SECRET = '';//新版需要用到
    const AGENT_ID = '';//微应用ID,由于项目中未用到鉴权，所以没用


    protected $corpId;
    protected $corpSecret;
    protected $appKey;
    protected $appSecret;
    protected $adminIds = [''];//todo,管理员ID
    protected $app;

    /**
     * Platform constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->corpId = self::CORP_ID;
        $this->corpSecret = self::CORP_SECRET;
        $this->appKey = self::APP_KEY;
        $this->appSecret = self::APP_SECRET;
        if (empty($config) && is_array($config)) {
            if (isset($config['corp_id'])) {
                $this->corpId = $config['corp_id'];
            }
            if (isset($config['corp_secret'])) {
                $this->corpSecret = $config['corp_secret'];
            }
            if (isset($config['app_key'])) {
                $this->appKey = $config['app_key'];
            }
            if (isset($config['app_secret'])) {
                $this->appSecret = $config['app_secret'];
            }
        }
        //采用新版方式
        $options = [
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret,
        ];
        $this->app = new Application($options);
    }


    /**
     * 获取corp_id
     * @return mixed
     */
    public function getCorpId()
    {
        return $this->corpId;
    }

    /**
     * 获取管理员ID
     */
    public function getAdminIds()
    {
        return $this->adminIds;
    }

    /**
     * 根据code获取用户信息
     * @param $code
     * @return array
     */
    public function getUserInfo($code)
    {
        $user = $this->app->user;
        $info = $user->getUserInfo($code);
        return $info;
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