<?php
/**
 * Copyright (C), 2023, Chongqing Jumi Network Technology Co., Ltd
 * FileName: HandleException.php
 * Description: 异常错误信息处理
 * @author KeHong
 * @Create Date 2023/03/22 9:55
 * @Update Date 2023/03/22 9:55 By Administrator
 * @version v1.0
 */

namespace KeHongKing\ThinkphpCommon;

use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Response;
use Throwable;
use ErrorException;
use Exception;
use InvalidArgumentException;
use ParseError;
use think\exception\ClassNotFoundException;
use think\exception\RouteNotFoundException;
use TypeError;

class HandleException extends Handle
{
    public function render($request, Throwable $e): Response
    {
        //如果处于调试模式
        if (env('app_debug')) {
            return Result::error(500, $e->getMessage() . $e->getTraceAsString());
        }
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return Result::error(422, $e->getError(), 422);
        }
        // 请求404异常 , 不返回错误页面
        if (($e instanceof ClassNotFoundException || $e instanceof RouteNotFoundException) || ($e instanceof HttpException && $e->getStatusCode() == 404)) {
            return Result::error(404, '当前请求资源不存在，请稍后再试', 404);
        }
        if ($e instanceof HttpException) {
            return Result::error($e->getStatusCode(), $e->getMessage(), $e->getCode());
        }
        //请求500异常, 不返回错误页面
        //$e instanceof PDOException ||
        if ($e instanceof Exception || $e instanceof InvalidArgumentException || $e instanceof ErrorException || $e instanceof ParseError || $e instanceof TypeError) {
            $this->reportException($request, $e);
            dump($e);
            return Result::error(500, '系统异常，请稍后再试', 500);
        }
        //其他错误
        $this->reportException($request, $e);
        return Result::error(500, "应用发生错误", 500);
    }

    //记录exception到日志
    private function reportException($request, Throwable $e): void
    {
        $errorStr = "url:" . $request->host() . $request->url() . "\n";
        $errorStr .= "code:" . $e->getCode() . "\n";
        $errorStr .= "file:" . $e->getFile() . "\n";
        $errorStr .= "line:" . $e->getLine() . "\n";
        $errorStr .= "message:" . $e->getMessage() . "\n";
        $errorStr .= $e->getTraceAsString();
        trace($errorStr, 'error');
    }
}