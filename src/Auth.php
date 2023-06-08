<?php
declare (strict_types=1);

namespace KeHongKing\ThinkphpCommon;

use app\model\AuthRule;
use think\exception\HttpException;

class Auth
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
        //验证当前是否有访问接口的权限
        $auth = \think\wenhainan\Auth::instance();
        $root = request()->root();
        $pathinfo = request()->pathinfo();
        $url = $root . '/' . $pathinfo;
        //查询权限
        $res = AuthRule::where('name', $url)->find();
        if (empty($res)) {
            throw new HttpException(403, '403 Forbidden', null, [], 403);
        } elseif ($res->status == 0) {
            return $next($request);
        }
        if (!$auth->check($url, request()->request_user_id)) {// 第一个参数是规则名称,第二个参数是用户UID
            //没有显示操作按钮的权限
            throw new HttpException(403, '403 Forbidden', null, [], 403);
        }
        return $next($request);
    }
}
