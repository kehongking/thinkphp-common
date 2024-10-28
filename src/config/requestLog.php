<?php
/**
 * Copyright (C), 2023, Chongqing Jumi Network Technology Co., Ltd
 * FileName: requestLog.php
 * Description: 请求日志以及返回数据格式配置
 * @author KeHong
 * @Create Date 2023/11/08 16:02
 * @Update Date 2023/11/08 16:02
 * @version v1.0
 */
return [
    //原样输出数据格式的接口地址如: api/user/login
    'uri' => [

    ],
    //不加入日志接口地址如: api/user/login
    'log_uri' => [

    ],
    //需要加密的应用名称如:admin
    'aes_apply_name' => [

    ],
    //aes加密key
    'aes_key' => '',
    //aes加密iv
    'aes_iv' => '',
    //aes加密的环境
    'aes_env' => [

    ],
];