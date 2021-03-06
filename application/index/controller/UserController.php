<?php

namespace app\index\controller;

use think\Request;
use app\index\service\UserService;
use think\Validate;

class UserController extends Controller
{
    /**
     * 显示资源列表
     *
     * @param  Request  $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(Request $request)
    {
        $search['username'] = $request->param('username', '');

        return $this->asJson(UserService::getSingletonInstance()->userList($search, $request->param('limit', 10)));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $userInfo = $request->all();
        $validate = $this->validate($userInfo, [
            'username|用户名' => 'require',
            'password|密码' => 'require|confirm',
            // 'email'    => 'require|email'
        ]);
        if ($validate !== true) {
            return $this->asJson([], $validate, 422);
        }
        unset($userInfo['password_confirm']);
        $res = UserService::getSingletonInstance()->store($userInfo);
        if ($res) {
            return $this->asJson([], '注册成功', 200);
        } else {
            return $this->asJson([], '注册失败,角色名重复', 422);
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function read($id)
    {
        return $this->asJson($this->userService->read($id));
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        if (UserService::getSingletonInstance()->update($id, $request->all())) {
            return $this->asJson([], '修改成功', 200);
        } else {
            return $this->asJson([], '修改失败', 422);
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }

    /**
     * 登录用户
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function login(Request $request)
    {
        $userInfo = $request->all();
        $validate = $this->validate($userInfo, [
            'username|用户名' => 'require',
            'password|密码' => 'require'
        ]);
        if ($validate !== true) {
            return $this->asJson([], $validate, 422);
        }
        $res = UserService::getSingletonInstance()->login($userInfo);
        if ($res) {
            return $this->asJson([], '登陆成功', 200);
        } else {
            return $this->asJson([], '登录失败，帐号或密码错误', 422);
        }
    }

    /**
     * 用户信息
     * @return \think\Response
     */
    public function userInfo()
    {
        $info = json_decode(session('user'), true);
        if ($info) {
            unset($info['password']);
        } else {
            $info = false;
        }
        return $this->asJson($info);
    }

    public function updatePassword(Request $request)
    {
        $validate = $this->validate($request->all(), [
            'old_password|原密码' => 'require',
            'password|新密码' => 'require|different:old_password',
            'confirm_password|确认密码' => 'confirm:password'
        ], [
            'password.different' => '新密码与原密码不能相同',
            'confirm_password.confirm' => '确认密码与新密码需要相同'
        ]);
        if ($validate !== true) {
            return $this->asJson([], $validate, 422);
        }
        $info = json_decode(session('user'), true);
        if (UserService::getSingletonInstance()->updatePassword($info['id'], $request->all())) {
            UserService::getSingletonInstance()->loginOut();
            return $this->asJson([], '修改成功');
        } else {
            return $this->asJson('', '原密码错误', 422);
        }
    }

    /**
     * 登出
     * @return \think\Response
     */
    public function loginOut()
    {
        UserService::getSingletonInstance()->loginOut();
        return $this->asJson('', '退出登录');
    }
}
