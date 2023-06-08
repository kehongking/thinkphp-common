<?php
/**
 * Copyright (C), 2023, Chongqing Jumi Network Technology Co., Ltd
 * FileName: CrossDomain.php
 * Description: 说明
 * @author KeHong
 * @Create Date 2023/04/08 13:47
 * @Update Date 2023/04/08 13:47
 * @version v1.0
 */

namespace KeHongKing\ThinkphpCommon;

use think\Response;

class CrossDomain
{
    public function handle($request, \Closure $next)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Max-Age: 1800');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, Token');
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        return $next($request);
    }
}