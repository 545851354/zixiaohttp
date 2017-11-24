<?php
/**
 * Created by PhpStorm.
 * User: le
 * Date: 2017/10/11
 * Time: 11:19
 */

namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Session;

class User extends Controller
{

    public function index()
    {
        if(Session::has('administrator'))
        {
            $result = Db::name('User')->select();
            $this->assign('list',$result);
            return $this->fetch();
        }else{
            $this->error("请登录管理员！",url('admin/login/login'));
        }
    }

    //添加用户
    public function add()
    {
        if(Session::has('administrator'))
        {
            if(request()->isPost())
            {

                $data = input('post.');
                foreach ($data as $value)
                {
                    if(empty($value)){
                        $this->error('所有项都为必填项！');
                    }
                }
                // 验证重名
                $result = Db::name('User')->where('username',$data['username'])->find();
                if($result) $this->error('用户名重名');
                $result = Db::name('User')->where('nickname',$data['nickname'])->find();
                if($result) $this->error('简称重名');
                $data['create_time'] = time();
                $db = db('User');
                $db->insert($data);
                if($db)
                {
                    $this->success("添加成功",url('admin/user/index'));
                }else{
                    $this->error("添加失败！");
                }
            }else{
                return view();
            }
        }else{
            $this->error("请登录管理员！",url('admin/login/login'));
        }

    }

    //注册登录
    public function logon()
    {
        if(request()->isPost())
        {
            $data = input('post.');
            $administrator = md5($data['administrator']);
            $admin = Db::name('User')
                ->where('id=1')
                ->field('password')
                ->find();
            if($administrator == $admin['password'])
            {
                Session::set('administrator',$administrator);
                $this->success("登录成功，请谨慎操作！",url("admin/user/add"));
            }else{
                $this->error("密码错误！");
            }
        }else{
            $this->error("访问错误！");
        }
    }

    //退出
    public function logout()
    {
        session::clear();
        $this->redirect('admin/login/login');
    }
}