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
        //获取基础参数
        $params = $request->all();
        $method = strtoupper($request->method());
        $uri = $request->pathinfo();
        $app_env = env('APP_ENV');
        $requestLogConfig = Config::get('requestLog');
        $apply_name = explode('/', $uri)[0];
        $aes_apply_arr = $requestLogConfig['aes_apply_name'];
        $aes_env = $requestLogConfig['aes_env'];
        $whitelist_encryption_uri = $requestLogConfig['whitelist_encryption_uri'];
        $is_aes = 0;
        $key = '';
        $iv = '';
        //判断是否需要解密
        if ($method != 'OPTIONS' && in_array($apply_name, $aes_apply_arr) && in_array($app_env, $aes_env) && !in_array($uri, $whitelist_encryption_uri)) {
            $aesDecrypt = $this->dataHandle($request);
            $params = $aesDecrypt['params'];
            $key = $aesDecrypt['key'];
            $iv = $aesDecrypt['iv'];
            $is_aes = 1;
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
            '请求request-id' => $request->header('request-id'),
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
        if ($is_aes == 1) {
            return $this->encrypt($return_data, $code, $key, $iv);
        }
        return json($return_data, $code);
    }

    public function dataHandle($request)
    {
        $params = $request->param();
        if (!isset($params['data']) || empty($request->header('data-key')) || empty($request->header('data-iv'))) {
            throw new HttpException(400, '请求有误', null, [], 400);
        }
        //数据aes解密
        $result = $this->aesDecrypt($params['data'], $request);
        $params = $result['params'];
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
        return ['params' => $params, 'key' => $result['key'], 'iv' => $result['iv']];
    }

    public function aesDecrypt($encryptedData, $request)
    {
        $encryptedData = str_replace(' ', '+', $encryptedData);
        $encryptedData = base64_decode($encryptedData);
        if (empty($encryptedData)) {
            throw new HttpException(400, '数据有误', null, [], 400);
        }
        //rsa解密得到aes的key
        $key = $this->rsaDecrypt($request->header('data-key'));
        $iv = $this->rsaDecrypt($request->header('data-iv'));
        $result = openssl_decrypt($encryptedData, "AES-256-CBC", $key, 1, $iv);
        if (empty($result)) {
            throw new HttpException(400, '数据有误', null, [], 400);
        }
        $request_data = json_decode($result, true);
        //验证请求是否过期
        if (empty($request_data['request_time']) || !$this->checkTimestampWithError($request_data['request_time'])) {
            throw new HttpException(400, '请求超时', null, [], 400);
        }
        return [
            'params' => $request_data,
            'key' => $key,
            'iv' => $iv,
        ];
    }

    //传入的毫秒时间戳与当前时间误差是否在60秒内
    public function checkTimestampWithError($msTimestamp)
    {
        // 先判断是不是合法的 13 位毫秒戳
        if (!is_scalar($msTimestamp) || !ctype_digit((string)$msTimestamp) || strlen((string)$msTimestamp) !== 13) {
            return false;
        }
        // 当前时间毫秒戳
        $nowMs = (int)(microtime(true) * 1000);
        // 传入的时间戳
        $targetMs = (int)$msTimestamp;
        // 计算差值的绝对值
        $diff = abs($nowMs - $targetMs);
        // 允许误差：60 秒 = 60000 毫秒
        $time = Config::get('requestLog')['request_time'] * 1000;
        return $diff <= $time;
    }

    public function rsaDecrypt($encryptedData)
    {
        // 关键：捕获所有警告 + 错误
        set_error_handler(function ($errno, $errstr) {
            // 只要是openssl相关的警告/错误，直接抛异常
            if (str_contains($errstr, 'openssl') || str_contains($errstr, 'IV')) {
                throw new \Exception($errstr);
            }
        }, E_ALL); //这里必须E_ALL
        try {
            $private_path = Config::get('requestLog')['rsa_private_path'];
            if (!file_exists($private_path)) {
                throw new HttpException(400, '私钥文件路径不存在', null, [], 400);
            }
            $private = openssl_pkey_get_private(file_get_contents($private_path));
            if (empty($private)) {
                throw new HttpException(400, '私钥有误', null, [], 400);
            }
            $encryptedData = base64_decode($encryptedData);
            if (empty($encryptedData)) {
                throw new HttpException(400, '请求有误', null, [], 400);
            }
            $decrypted = '';
            // RSA解密，IV为空是正常的，但PHP8+会报警告
            $ok = openssl_private_decrypt($encryptedData, $decrypted, $private);
            // 解密失败也抛异常
            if (!$ok) {
                throw new HttpException(400, '请求有误', null, [], 400);
            }
            // 恢复错误处理
            restore_error_handler();
            return $decrypted;
        } catch (\Throwable $e) { //关键：用 Throwable才能接住所有类型
            //恢复错误处理
            restore_error_handler();
            //统一返回异常
            throw new HttpException(400, $e->getMessage(), null, [], 400);
        }
    }

    public function encrypt($return_data, $code, $key, $iv)
    {
        $json = json_encode($return_data['data'], JSON_UNESCAPED_UNICODE);
        $encryptedData = openssl_encrypt($json, 'AES-256-CBC', $key, 1, $iv);
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
