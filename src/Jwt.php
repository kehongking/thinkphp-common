<?php
declare (strict_types=1);

namespace KeHongKing\ThinkphpCommon;

use think\exception\HttpException;
use think\facade\Cache;
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
            //验证单点登录时token是否正常
            if (isset($result['data']['login_type']) && $result['data']['login_type'] == 'sso') {
                $app_name = Config::get('requestLog')['app_name'] ?? '';
                $redis_key = "jwt-token-$app_name:" . $result['data']['source'] . '-' . $result['data']['id'];
                $redis_token = Cache::get($redis_key);
                if ($redis_token != $token) {
                    throw new HttpException(401, '登录失效', null, [], 401);
                }
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
            $user = Db::name('admin_user')->where('id', $data['id'])->find();
            if (empty($user)) {
                throw new HttpException(401, '您的账号已被删除', null, [], 401);
            }
            if (isset($user['delete_time']) && !empty($user['delete_time'])) {
                throw new HttpException(401, '您的账号已被删除', null, [], 401);
            }
            if (isset($user['status']) && $user['status'] != 1) {
                throw new HttpException(401, '您的账号已被禁用', null, [], 401);
            }
            $role = Db::name('auth_group')->where('id', $user['group_id'])->where('status', 1)->find();
            if (empty($role)) {
                throw new HttpException(401, '您的账号已被禁用', null, [], 401);
            }
        }
    }
}
