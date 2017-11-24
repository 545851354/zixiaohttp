<?php
/**
 * Created by PhpStorm.
 * User: le
 * Date: 2017/10/11
 * Time: 14:30
 */

namespace app\admin\controller;
use think\Controller;
use think\Session;
use think\Cookie;
use think\Db;

class Login extends Controller
{
    //用户登录
    public function login()
    {
        if(request()->isPost())
        {
            $data = input('post.');
            foreach ($data as $value)
            {
                if(empty($value)){
                    $this->error('账号或密码不能为空！');
                }
            }
            $user = $data['username'];
            $password = $data['password'];
            if($user=="admin")
            {
                $password = md5($password);
            }
            $admin_info = Db::name('User')
                ->where([
                    'username' => $user,
                    'password' => $password,
                ])->find();
            if(!$admin_info)
            {
                $admin_info = Db::name('User')
                    ->where([
                        'nickname' => $user,
                        'password' => $password,
                    ])->find();
            }
            if($admin_info)
            {
                $user_id = $admin_info['id'];
                if(!empty($data['check']))
                {
                    if(!Cookie::has('username'))
                    {
                        Cookie::set('username',$admin_info['username']);
                        Cookie::set('password',$password);
                    }
                }else{
                    Cookie::delete('username');
                    Cookie::delete('password');
                }

                Session::set('user_id',$user_id);
                Session::set('username',$admin_info['username']);
                $this->redirect('admin/index/index');
            }else{
                $this->error("用户名或密码错误！");
            }
        }else{
            return view();
        }
    }

    //用户退出
    public function logout()
    {
        session::clear();
        $this->redirect('admin/login/login');
    }
}