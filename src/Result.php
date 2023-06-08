<?php

namespace KeHongKing\ThinkphpCommon;
/**
 * Copyright (C), 2023, Chongqing Jumi Network Technology Co., Ltd
 * FileName: Result.php
 * Description: 返回数据处理
 * @author KeHong
 * @Create Date 2023/03/22 9:43
 * @Update Date 2023/03/22 9:43 By Administrator
 * @version v1.0
 */
class Result
{
    //success
    static public function success($data)
    {
        $rs = [
            'code' => 0,
            'msg' => "success",
            'data' => $data,
        ];
        return json($rs);
    }

    //error
    static public function error($code, $msg = 'error', $http_code = 200)
    {
        $rs = [
            'code' => $code,
            'msg' => $msg,
            'data' => [],
        ];
        return json($rs, $http_code);
    }
}