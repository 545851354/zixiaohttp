<?php
namespace app\admin\controller;
use think\Db;
use think\Session;

class Index extends \think\Controller
{
    public function index($proid=1)
    {
        if(!session('username'))
        {
            $this->redirect('login/login');
        }
        $user_id = session('user_id');
        $pro = Db::table('zixiao_project')
            ->where("id=$proid")
            ->find();
        $this->assign('pro',$pro);
        //所有大目标
        $project = Db::name('Project')->select();
        $this->assign('project',$project);
        //成员用户
        $user = Db::name('User')->select();
        $this->assign('user',$user);

        //中目标
        $join = [
            ["zixiao_project p","p.id=t.project_id"]
        ];
        $field = "t.id,t.task_name,t.user_id,t.start_time,t.stop_time";
        $task = Db::table('zixiao_task')
            ->alias('t')
            ->where("t.project_id=$proid")
            ->join($join)
            ->field($field)
            ->order('t.start_time desc')
            ->select();
        $this->assign('task',$task);

        //小目标
        $join = [
            ["zixiao_task t","t.id=i.task_id"],
            ["zixiao_user u","u.id=i.user_id"]
        ];
        $field = "u.username,i.*";
        $list = Db::table('zixiao_item')
            ->alias('i')
            ->where("t.project_id=$proid")
            ->join($join)
            ->field($field)
            ->order('i.id desc')
            ->select();
        $this->assign('list',$list);

        //追加小目标
        $addto = Db::name('Addto')->order('id desc')->select();
        $this->assign('addto',$addto);

        //项目附件
        $join = [
            ["zixiao_task t","t.id=f.task_id"]
        ];
        $field = "f.task_id,f.file_name,f.des,f.file_path";
        $files = Db::table('zixiao_files')
            ->alias('f')
            ->where("t.project_id=$proid")
            ->join($join)
            ->field($field)
            ->order('f.id desc')
            ->select();
        $this->assign('files',$files);

        // 没有关联大中目标的小目标
        $notask = Db::table('zixiao_item')
            ->where('task_id=0')
            ->where('user_id',session('user_id'))
            ->order('id desc')
            ->select();
        $this->assign('notask',$notask);

//        dump($notask);die;
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

    public function index3()
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
}
