<?php
declare (strict_types=1);

namespace KeHongKing\ThinkphpCommon;

use think\exception\HttpException;
use think\facade\Db;

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
        $token = $request->header('Authorization');
        if (empty($token)) throw new HttpException(401, '请先登陆', null, [], 401);
        $token = explode(' ', $token)[1];
        //验证token
        $jwt = JwtCommon::instance();
        $result = $jwt->checkToken($token);
        if ($result['code'] == 200) {
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
        if ($data['is_verify_account']) {
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
