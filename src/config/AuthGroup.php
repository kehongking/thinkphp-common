<?php
/**
 * Copyright (C), 2023, Chongqing Jumi Network Technology Co., Ltd
 * FileName: AuthGroup.php
 * Description: 说明
 * @author KeHong
 * @Create Date 2023/04/04 10:28
 * @Update Date 2023/04/04 10:28
 * @version v1.0
 */

namespace app\model;

use think\Model;

class AuthGroup extends Model
{
    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'title' => 'string',
        'status' => 'tinyint',
        'rules' => 'text',
        'son_rules' => 'text',
        'describe' => 'string',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    const STATUS_NORMAL = 1;//正常
    const STATUS_DISABLE = 0;//禁用
    const STATUS_SELECT = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用',
    ];

    protected $append = ['status_text'];

    public function getStatusTextAttr($value, $data)
    {
        return self::STATUS_SELECT[$data['status']];
    }
}