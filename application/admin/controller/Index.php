<?php
namespace app\admin\controller;
use think\Db;
use think\Session;

class Index extends \think\Controller
{
    public function index()
    {
        if(!session('username'))
        {
            $this->redirect('login/login');
        }
        $user = session('user_id');
        //查询
        $join = [
            ['zixiao_task t','i.task_id=t.id'],
            ['zixiao_project p','t.project_id=p.id'],
        ];
        $field = 'i.*,p.project_name,t.task_name';
        $list = Db::table('zixiao_item')
            ->alias('i')
            ->where('i.user_id='.$user)
            ->join($join)
            ->field($field)
            ->order('i.id desc')
            ->select();
        $this->assign('list', $list);
        //我的小目标数
        $count = Db::table('zixiao_item')
            ->where('user_id='.$user)
            ->count();
        $this->assign('count',$count);
        //小目标总数
        $itemcount = Db::table('zixiao_item')->count();
        $this->assign('itemcount',$itemcount);
        //任务总数
        $taskcount = Db::table('zixiao_task')->count();
        $this->assign('taskcount',$taskcount);
        //任务总数
        $filescount = Db::table('zixiao_files')->count();
        $this->assign('filescount',$filescount);

        return $this->fetch();
    }

    public function index2()
    {
        if(!session('username'))
        {
            $this->redirect('login/login');
        }
        $user = session('user_id');
        //查询
        $join = [
            ['zixiao_task t','i.task_id=t.id'],
            ['zixiao_project p','t.project_id=p.id'],
        ];
        $field = 'i.*,p.project_name,t.task_name';
        $list = Db::table('zixiao_item')
            ->alias('i')
            ->where('i.user_id='.$user)
            ->join($join)
            ->field($field)
            ->select();
        $this->assign('list', $list);
        //我的小目标数
        $count = Db::table('zixiao_item')
            ->where('user_id='.$user)
            ->count();
        $this->assign('count',$count);
        //小目标总数
        $itemcount = Db::table('zixiao_item')->count();
        $this->assign('itemcount',$itemcount);
        //任务总数
        $taskcount = Db::table('zixiao_task')->count();
        $this->assign('taskcount',$taskcount);
        //任务总数
        $filescount = Db::table('zixiao_files')->count();
        $this->assign('filescount',$filescount);

        return $this->fetch();
    }
}
