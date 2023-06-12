<?php
/**
 * Copyright (C), 2023, Chongqing Jumi Network Technology Co., Ltd
 * FileName: AuthGroupAccess.php
 * Description: 说明
 * @author KeHong
 * @Create Date 2023/04/04 14:58
 * @Update Date 2023/04/04 14:58
 * @version v1.0
 */

namespace app\model;

use think\Model;

class AuthGroupAccess extends Model
{
    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'uid' => 'string',
        'group_id' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];
}