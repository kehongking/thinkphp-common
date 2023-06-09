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

    protected static $instance;

    //默认配置
    protected $config = [
        'key' => 'Jumi~!@#$147258',//秘钥key
        'expire_time' => 7200,//过期时间（秒）
        'alg' => 'HS256',//加密方式HS256、HS384、HS512、RS256、ES256等
    ];

    //默认jwt加密基础数据
    protected $data = [
        'id' => 1,//登录账号唯一标识
        'source' => 'admin',//登录账户来源
        'is_verify_account' => 0//每次验证token时,是否需要验证账号状态 1是 0否
    ];

    /**
     * 类架构函数
     * jwt constructor.
     */
    public function __construct()
    {
        //可设置配置项 jwt, 此配置项为数组。
        if ($jwt = Config::get('jwt')) {
            $this->config = array_merge($this->config, $jwt);
        }
    }

    /**
     * 初始化
     * access public
     * @param array $options 参数
     * return \think\Request
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    //生成token
    public function generateToken($data)
    {
        $time = time();
        $token = array(
            "iss" => $this->config['key'],  //签发者 可以为空
            "aud" => '',          //面象的用户，可以为空
            "iat" => $time,      //签发时间
            "nbf" => $time,    //在什么时候jwt开始生效
            "exp" => $time + $this->config['expire_time'], //token 过期时间
            "data" => array_merge($this->data, $data),     //记录的用户的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
        );
        return JWT::encode($token, $this->config['key'], $this->config['alg']);  //根据参数生成了token，可选：HS256、HS384、HS512、RS256、ES256等
    }

    //验证token
    public function checkToken($token)
    {
        $status = array("code" => 400);
        try {
            JWT::$leeway = 60;//当前时间减去60，把时间留点余地
            $decoded = JWT::decode($token, new Key($this->config['key'], $this->config['alg'])); //同上的方式，这里要和签发的时候对应
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