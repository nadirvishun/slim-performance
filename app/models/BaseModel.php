<?php

namespace app\models;

use Illuminate\Database\Eloquent\Model;

Class BaseModel extends model
{
    /**
     * 自动存储格式为时间戳
     * @param mixed $value
     * @return false|int|null|string
     */
    public function fromDateTime($value)
    {
        return strtotime(parent::fromDateTime($value));
    }
}