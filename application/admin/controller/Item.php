<?php
/**
 * Created by PhpStorm.
 * User: le
 * Date: 2017/10/11
 * Time: 17:38
 */

namespace app\admin\controller;
use think\Controller;
use think\Db;

class Item extends Controller
{
    public function index()
    {
        //查询
        $join = [
            ['zixiao_task t','i.task_id=t.id'],
            ['zixiao_project p','t.project_id=p.id'],
            ['zixiao_user u','i.user_id=u.id']
        ];
        $field = 'i.*,p.project_name,t.task_name,u.username';
        $list = Db::table('zixiao_item')
            ->alias('i')
            ->join($join)
            ->field($field)
            ->select();
        $this->assign('list', $list);
        $count = Db::table('zixiao_item')->count();
        $this->assign('count',$count);

        return $this->fetch();
    }

    //添加任务
    public function add()
    {
        if(request()->isPost())
        {
            if(!session('username'))
            {
                $this->redirect('login/login');
            }
            $data = input('post.');
            foreach ($data as $value)
            {
                if($value == '' || $value == null)
                {
                    $this->error("所有项都为必填项！");
                }
            }
            $data['create_time'] = strtotime($data['create_time']);
            $data['finish_time'] = strtotime($data['finish_time']);
            $db = db('Item');
            $db->insert($data);
            if($db)
            {
                $this->redirect('index/index');
            }else{
                $this->error("添加失败！");
            }

        }else {

            //查询
            $join = [
                ['zixiao_project p','t.project_id=p.id'],
            ];
            $field = 't.id,t.task_name,t.des,p.project_name,t.task_state';
            $list = Db::table('zixiao_task')
                ->alias('t')
                ->where('t.task_state',0)
                ->join($join)
                ->field($field)
                ->order('t.id desc')
                ->select();
            $this->assign('list', $list);
            $time = date("Y-m-d",time());
            $this->assign('time', $time);

            return $this->fetch();
        }
    }

    //任务细节
    public function detail($id)
    {
        if(request()->isGet())
        {
            //查询
            $join = [
                ['zixiao_task t','i.task_id=t.id'],
                ['zixiao_project p','t.project_id=p.id'],
                ['zixiao_user u','i.user_id=u.id'],
            ];
            $field = 't.*,i.*,p.project_name,u.username';
            $list = Db::table('zixiao_item')
                ->alias('i')
                ->where('i.id='.$id)
                ->join($join)
                ->field($field)
                ->order('i.id desc')
                ->find();
            $this->assign('value', $list);
            return $this->fetch();
        }
    }

    //更新小目标
    public function update($id)
    {
        if(!session('username'))
        {
            $this->redirect('login/login');
        }
        //输出数据
        if(request()->isGet())
        {
            //查询中目标
            $result = Db::name('Task')
                ->field('id,task_name,des,start_time,stop_time')
                ->where('task_state=0')
                ->order('id desc')
                ->select();
            $this->assign('task',$result);
            //查询
            $join = [
                ['zixiao_task t','i.task_id=t.id'],
                ['zixiao_project p','t.project_id=p.id'],
                ['zixiao_user u','i.user_id=u.id'],
            ];
            $field = 't.task_name,t.des,t.start_time,t.stop_time,i.*,p.project_name,u.username';
            $list = Db::table('zixiao_item')
                ->alias('i')
                ->where('i.id='.$id)
                ->join($join)
                ->field($field)
                ->find();
            if($list['user_id'] != session('user_id'))
            {
                $this->error('只有负责人才能编辑此小目标');
            }
            $this->assign('value', $list);
            return $this->fetch();
        }
        else if(request()->isPost())
        {
            //更新数据
            $data = input('post.');
            $data['create_time'] = strtotime($data['create_time']);
            $data['finish_time'] = strtotime($data['finish_time']);
            $result = Db::table('zixiao_item')
                ->where('id='.$id)
                ->update($data);
            if($result)
            {
                $this->redirect('admin/index/index');
            }else{
                $this->error("任务没有做任何修改！");
            }
        }
    }

    public function jsonalter()
    {
        $data = input('post.');
        $id = $data['id'];

        $r = Db::table('zixiao_item')
            ->alias('i')
            ->where('i.id='.$id)
            ->field('state')
            ->find();
        if($r['state'] == 0)
        {
            Db::table('zixiao_item')
                ->where('id='.$id)
                ->update(['state' => 1]);
        }else if($r['state'] == 1)
        {
            Db::table('zixiao_item')
                ->where('id='.$id)
                ->update(['state' => 0]);
        }
        $return = array(
            'code' => 10000,
            'msg'  => "状态切换成功"
        );
        return $return;

    }
}