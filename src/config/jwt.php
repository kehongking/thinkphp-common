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
    'private_key' => root_path() . 'app/rsa/token_private_key.pem',//rsa私钥路径
    'public_key' => root_path() . 'app/rsa/token_public_key.pem',//rsa公钥路径
];