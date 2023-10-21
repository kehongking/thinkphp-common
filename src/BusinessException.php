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
    static public function exception($data)
    {
        $code = $data['code'] ?? 400;
        $http_code = $data['http_code'] ?? 400;
        if (is_string($data)) {
            $message = $data;
        } else {
            $message = $data['message'];
        }
        throw new HttpException($code, $message, null, [], $http_code);
    }
}