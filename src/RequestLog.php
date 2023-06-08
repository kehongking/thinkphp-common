<?php
declare (strict_types=1);

namespace KeHongKing\ThinkphpCommon;

use think\facade\Log;
use think\Response;

class RequestLog
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        //添加接口请求日志
        $method = strtoupper($request->method());
        $uri = $request->pathinfo();
        $bodyAsJson = json_encode($request->all(), JSON_UNESCAPED_UNICODE);
        $request_time = $this->msectime();
        $log_data = [
            '请求方式' => $method,
            '请求地址' => $uri,
            '请求参数' => $bodyAsJson,
            '请求token' => $request->header('Authorization'),
        ];
        $response = $next($request);
        //添加接口返回日志
        $response_time = $this->msectime();
        $code = $response->getCode();
        $data = $response->getData();
        $log_data['响应code'] = $code;
        $log_data['响应数据'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        $log_data['响应时间'] = $response_time - $request_time . 'ms';
        Log::info($log_data);
        $return_data = [
            'code' => $code == 200 ? 0 : $data['code'],
            'msg' => $data['msg'] ?? 'success',
            'data' => $code == 200 ? $data : [],
        ];
        if ($uri == 'core/test') {
            return $response;
        } else {
            $return_data = serialize($return_data);
            $return_data = mb_convert_encoding($return_data, 'UTF-8');
            $return_data = unserialize($return_data);
            return json($return_data, $code);
        }
    }

    public function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }
}
