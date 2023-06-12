<?php
/**
 * Copyright (C), 2023, Chongqing Jumi Network Technology Co., Ltd
 * FileName: Permission.php
 * Description: 说明
 * @author KeHong
 * @Create Date 2023/03/30 15:03
 * @Update Date 2023/03/30 15:03
 * @version v1.0
 */

namespace app\admin\controller;

use app\model\AuthRule;

class Permission
{
    public function index()
    {
        $ruleList = request()->rule()->getRouter()->getRuleList();
        foreach ($ruleList as $key => $value) {
            if (!isset($value['option']['append'])) continue;
            //添加一级
            $one = AuthRule::where('title', $value['option']['append']['group_one_name'])->where('pid', 0)->find();
            if (empty($one)) {
                $one_create = [
                    'pid' => 0,
                    'title' => $value['option']['append']['group_one_name'],
                    'name' => $value['option']['append']['group_one_name'],
                ];
                $one = AuthRule::create($one_create);
            }
            //添加二级
            $two = AuthRule::where('title', $value['option']['append']['group_two_name'])->where('pid', $one->id)->find();
            if (empty($two)) {
                $two_create = [
                    'pid' => $one->id,
                    'title' => $value['option']['append']['group_two_name'],
                    'name' => $value['option']['append']['group_two_name'],
                ];
                $two = AuthRule::create($two_create);
            }
            //查询是否存在
            $rule = '/admin/' . $value['rule'];
            $res = AuthRule::where('name', $rule)->find();
            $status = 1;
            //判断当前接口是否不需要权限
            if (isset($value['option']['append']['is_permission']) && $value['option']['append']['is_permission'] === false) {
                $status = 0;
            }
            if (empty($res)) {
                $data = [
                    'title' => $value['name'],
                    'name' => $rule,
                    'pid' => $two->id,
                    'status' => $status
                ];
                AuthRule::create($data);
            } else {
                $res->title = $value['name'];
                $res->status = $status;
                $res->save();
            }
        }
        return [];
    }
}