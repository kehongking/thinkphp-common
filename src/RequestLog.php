<?php
declare (strict_types=1);

namespace KeHongKing\ThinkphpCommon;

use think\exception\HttpException;
use think\facade\Log;
use think\Response;
use think\facade\Config;

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
        $aes_apply_name = explode('/', $uri)['0'];
        $app_env = env('APP_ENV');
        $params = $request->all();
        //获取配置
        $requestLogConfig = Config::get('requestLog');
        //判断是否需要解密
        $aes_apply_arr = $requestLogConfig['aes_apply_name'];
        $aes_env = $requestLogConfig['aes_env'];
        if (in_array($aes_apply_name, $aes_apply_arr) && $method != 'OPTIONS' && in_array($app_env, $aes_env)) {
            $params = $this->aesDecrypt($request);
        }
        $request_data = $this->array_mb_convert_encoding($params);
        $request_json = json_encode($request_data, JSON_UNESCAPED_UNICODE);
        $request_time = $this->msectime();
        $log_data = [
            '请求IP' => request()->ip(),
            '请求方式' => $method,
            '请求地址' => $uri,
            '请求参数' => $request_json,
            '请求token' => $request->header('Authorization'),
        ];
        $response = $next($request);
        if ($method == 'OPTIONS') {
            return json(['code' => 0, 'msg' => 'success', 'data' => []]);
        }
        //添加接口返回日志
        $response_time = $this->msectime();
        $code = $response->getCode();
        $data = $response->getData();
        $log_data['响应code'] = $code;
        $log_data['响应数据'] = stripslashes(json_encode($data, JSON_UNESCAPED_UNICODE));
        $log_data['响应时间'] = $response_time - $request_time . 'ms';
        //判断是否需要添加日志
        if (!in_array($uri, $requestLogConfig['log_uri'])) {
            Log::info(stripslashes(json_encode($log_data, JSON_UNESCAPED_UNICODE)));
        }
        //获取配置判断是否需要原样数据返回
        if (!empty($requestLogConfig['uri'])) {
            foreach ($requestLogConfig['uri'] as $key => $value) {
                if (strpos($uri, $value) !== false) {
                    return $response;//数据格式原样返回
                }
            }
        }
        //返回统一处理数据
        $return_data = [
            'code' => $code == 200 ? 0 : $data['code'],
            'msg' => $data['msg'] ?? 'success',
            'data' => $code == 200 ? $data : null,
        ];
        $return_data = serialize($return_data);
        $return_data = mb_convert_encoding($return_data, 'UTF-8');
        $return_data = unserialize($return_data);
        //返回数据加密
        if (in_array($aes_apply_name, $aes_apply_arr) && $return_data['code'] == 0 && in_array($app_env, $aes_env)) {
            return $this->encrypt($return_data, $code);
        }
        return json($return_data, $code);
    }

    public function aesDecrypt($request)
    {
        $params = $request->param();
        if (empty($params['data'])) return $params;
        $params = $this->decrypt($params['data']);
        foreach ($params as $kk => $vv) {
            //处理参数为null的，不然auth包会报错
            if ($vv === null) {
                $params[$kk] = '';
            }
        }
        //重新设置参数
        if ($request->isGet()) {
            $request->withGet($params);
        } elseif ($request->isPost()) {
            $request->withPost($params);
        } else {
            throw new HttpException(400, '加密暂只支持GET、POST', null, [], 400);
        }
        return $params;
    }

    public function decrypt($encryptedData)
    {
        $encryptedData = str_replace(' ', '+', $encryptedData);
        $encryptedData = base64_decode($encryptedData);
        $requestLogConfig = Config::get('requestLog');
        $result = openssl_decrypt($encryptedData, "AES-256-CBC", $requestLogConfig['aes_key'], 1, $requestLogConfig['aes_iv']);
        if (!$result) throw new HttpException(402, '解密数据有误!', null, [], 402);
        return json_decode($result, true);
    }

    public function encrypt($return_data, $code)
    {
        $requestLogConfig = Config::get('requestLog');
        $json = json_encode($return_data['data'], JSON_UNESCAPED_UNICODE);
        $encryptedData = openssl_encrypt($json, 'AES-256-CBC', $requestLogConfig['aes_key'], 1, $requestLogConfig['aes_iv']);
        $encryptedData = base64_encode($encryptedData);
        $return_data['data'] = $encryptedData;
        return json($return_data, $code);
    }

    public function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**
     * FunctionName: array_mb_convert_encoding
     * Description:将数组转为utf-8编码
     * CreateTime:2023/06/09 14:14
     * UpdateTime:2023/06/09 14:14
     * Author: KeHong
     * @param $array
     * @return mixed
     */
    public function array_mb_convert_encoding($array)
    {
        if (($_FILES)) return $array;
        return eval('return ' . mb_convert_encoding(var_export($array, true) . ';', 'UTF-8', 'UTF-8,GBK,GB2312'));
    }
}
