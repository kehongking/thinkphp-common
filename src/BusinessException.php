<?php
/**
 * Copyright (C), 2023, Chongqing Jumi Network Technology Co., Ltd
 * FileName: HttpException.php
 * Description: 抛出业务异常信息
 * @author KeHong
 * @Create Date 2023/03/22 11:17
 * @Update Date 2023/03/22 11:17
 * @version v1.0
 */

namespace KeHongKing\ThinkphpCommon;

use think\exception\HttpException;

class BusinessException
{
    static public function exception($return_code, $return_message, $http_code = 400)
    {
        throw new HttpException($return_code, $return_message, null, [], $http_code);
    }
}