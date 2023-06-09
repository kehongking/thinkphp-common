<?php
/**
 * Copyright (C), 2023, Chongqing Jumi Network Technology Co., Ltd
 * FileName: jwt.php
 * Description: jwt配置
 * @author KeHong
 * @Create Date 2023/06/09 15:22
 * @Update Date 2023/06/09 15:22
 * @version v1.0
 */
return [
    'key' => 'Jumi~!@#$147258',//秘钥key
    'expire_time' => 7200,//过期时间（秒）
    'alg' => 'HS256',//加密方式HS256、HS384、HS512、RS256、ES256等
];