<?php
declare (strict_types=1);

namespace KeHongKing\ThinkphpCommon;

use think\exception\HttpException;

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
        $result = JwtCommon::checkToken($token);
        if ($result['code'] == 200) {
            //验证当前登录账号是否正常
           // $this->verifyAccount($result['data']);
            $request->request_user_id = $result['data']['id'];
            $request->request_source = $result['data']['source'];
        } else {
            throw new HttpException(401, $result['msg'], null, [], 401);
        }
        return $next($request);
    }

    /*public function verifyAccount($data)
    {
        $user = [];
        if ($data['source'] == 'admin') {
            //总后台账户
            $user = AdminUser::where('id', $data['id'])->where('status', StatusConstant::TABLE_STATUS_NORMAL)->find();
        } else {
            BusinessException::exception(CodeMessageConstant::SOURCE_ACCOUNT_ERROR);
        }
        if (empty($user)) throw new HttpException(401, '请先登陆', null, [], 401);
    }*/
}
