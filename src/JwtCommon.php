<?php
/**
 * Copyright (C), 2023, Chongqing Jumi Network Technology Co., Ltd
 * FileName: common.php
 * Description: jwt公共类
 * @author KeHong
 * @Create Date 2023/03/23 9:27
 * @Update Date 2023/03/23 9:27
 * @version v1.0
 */

namespace KeHongKing\ThinkphpCommon;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Config;

class JwtCommon
{
    //默认配置
    protected $config = [
        'key' => 'Jumi~!@#$147258',//秘钥key
        'expire_time' => 7200,//过期时间（秒）
        'alg' => 'HS256',//加密方式HS256、HS384、HS512、RS256、ES256等
    ];

    /**
     * 类架构函数
     * jwt constructor.
     */
    public function __construct()
    {
        //可设置配置项 auth, 此配置项为数组。
        if ($auth = Config::get('jwt')) {
            $this->config = array_merge($this->config, $auth);
        }
    }

    //生成token
    public static function generateToken($data): string
    {
        $time = time();
        $token = array(
            "iss" => self::$key,  //签发者 可以为空
            "aud" => '',          //面象的用户，可以为空
            "iat" => $time,      //签发时间
            "nbf" => $time,    //在什么时候jwt开始生效
            "exp" => $time + 720000, //token 过期时间
            "data" => $data         //记录的user的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
        );
        return JWT::encode($token, self::$key, "HS256");  //根据参数生成了token，可选：HS256、HS384、HS512、RS256、ES256等
    }

    //验证token
    public static function checkToken($token): array
    {
        $status = array("code" => 400);
        try {
            JWT::$leeway = 60;//当前时间减去60，把时间留点余地
            $decoded = JWT::decode($token, new Key(self::$key, 'HS256')); //同上的方式，这里要和签发的时候对应
            $arr = (array)$decoded;
            $res['code'] = 200;
            $res['data'] = $arr['data'];
            $res['data'] = json_decode(json_encode($res['data']), true);//将stdObj类型转换为array
            return $res;
        } catch (\Firebase\JWT\SignatureInvalidException $e) { //签名不正确
            $status['msg'] = "token签名不正确";
            return $status;
        } catch (\Firebase\JWT\BeforeValidException $e) { // 签名在某个时间点之后才能用
            $status['msg'] = "token失效";
            return $status;
        } catch (\Firebase\JWT\ExpiredException $e) { // token过期
            $status['msg'] = "token失效";
            return $status;
        } catch (\Exception $e) { //其他错误
            $status['msg'] = "token未知错误";
            return $status;
        }
    }
}