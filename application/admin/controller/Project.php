<?php
/**
 * Created by PhpStorm.
 * User: le
 * Date: 2017/10/11
 * Time: 15:25
 */

namespace app\admin\controller;

use think\Controller;
use think\Db;

class Project extends Controller
{
    public function index()
    {
        $result = Db::name('Project')->select();
        $this->assign('list',$result);
        return $this->fetch();
    }

    //添加项目
    public function add()
    {
        if(request()->isPost())
        {
            if(!session('username'))
            {
                $this->redirect('admin/login/login');
            }
            $data = input('post.');
            foreach ($data as $value)
            {
                if($value == '' || $value == null)
                {
                    $this->error("所有项都为必填项！");
                }
            }
            $db = db('Project');
            $db->insert($data);
            if($db)
            {
                $this->success("添加成功",url('admin/project/index'));
            }else{
                $this->error("添加失败！");
            }

        }else{
            return view();
        }
    }
}