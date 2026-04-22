<?php
declare (strict_types=1);

namespace KeHongKing\ThinkphpCommon;

use think\exception\HttpException;
use think\facade\Db;
use think\facade\Config;

class Jwt
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
        $authorization = $request->header('Authorization');
        if (empty($authorization)) throw new HttpException(401, '请先登陆', null, [], 401);
        $token_arr = explode(' ', $authorization);
        $token = $token_arr[0];
        if (isset($token_arr[1])) {
            $token = $token_arr[1];
        }
        //验证token
        $jwt = JwtCommon::instance();
        $result = $jwt->checkToken($token);
        if ($result['code'] == 200) {
            //验证项目名称是否正常
            $project_name = Config::get('requestLog')['app_name'] ?? '';
            if (!isset($result['data']['app_name']) || $project_name != $result['data']['app_name']) {
                throw new HttpException(401, '登录失效', null, [], 401);
            }
            //验证环境是否正常
            if (!isset($result['data']['app_env']) || env('APP_ENV', '') != $result['data']['app_env']) {
                throw new HttpException(401, '登录失效', null, [], 401);
            }
            //验证来源是否正常
            $getName = app('http')->getName();
            if ($getName != $result['data']['source']) {
                throw new HttpException(401, '登录失效', null, [], 401);
            }
            //验证当前登录账号是否正常
            $this->verifyAccount($result['data']);
            $request->request_user_id = $result['data']['id'];
            $request->request_source = $result['data']['source'];
        } else {
            throw new HttpException(401, $result['msg'], null, [], 401);
        }
        return $next($request);
    }

    public function verifyAccount($data)
    {
        if ($data['is_verify_account'] == 1) {
            //需要验证登录账号
            $user = Db::name($data['table_user'])->where($data['condition_user'])->find();
            if (empty($user)) {
                throw new HttpException(402, '您的账号已被禁用', null, [], 402);
            }
            $role = Db::name($data['table_role'])->where($data['condition_role'])->find();
            if (empty($role)) {
                throw new HttpException(402, '您的账号已被禁用', null, [], 402);
            }
        }
    }
}
