<?php
/**
 * Copyright (C), 2023, Chongqing Jumi Network Technology Co., Ltd
 * FileName: AuthRule.php
 * Description: 说明
 * @author KeHong
 * @Create Date 2023/04/04 14:08
 * @Update Date 2023/04/04 14:08
 * @version v1.0
 */

namespace app\model;

use think\Model;

class AuthRule extends Model
{
    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'name' => 'string',
        'title' => 'string',
        'status' => 'tinyint',
        'condition' => 'string',
        'pid' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];
}