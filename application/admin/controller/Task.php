<?php
/**
 * Created by PhpStorm.
 * User: le
 * Date: 2017/10/11
 * Time: 15:44
 */

namespace app\admin\controller;
use think\Controller;
use think\Db;

class Task extends Controller
{
    public function index($mytask=0)
    {
        //我的中目标
        if($mytask==1)
        {
            $userid = session('user_id');
            $condition = "t.user_id=$userid";
        }else
        //任务验收
        if($mytask==2)
        {
            //验收人检测
            $accs = Db::name('Task')
                ->field('id,task_acceptance')
                ->select();
            $str = "";
            $userid = session('user_id');
            foreach ($accs as $v)
            {
                $acc = explode(",",$v['task_acceptance']);

                if(in_array($userid,$acc))
                {
                    $str .= "{$v['id']},";
                }
            }
            $str = rtrim($str, ",");
            $condition = "t.id in ($str)";
        }else{
            $condition = '';
        }

        //查询中目标
        $join = [
            ['zixiao_project p','t.project_id=p.id'],
            ['zixiao_user u','t.user_id=u.id']
        ];
        $field = 'p.project_name,u.username,u.nickname,t.*';
        $result = Db::table('zixiao_task')
            ->alias('t')
            ->where($condition)
            ->join($join)
            ->field($field)
            ->select();

        //查询关联任务的用户
        $join2 = [
            ['zixiao_task t','i.task_id=t.id'],
            ['zixiao_user u','i.user_id=u.id']
        ];
        $field2 = 'u.username,t.id';
        $result2 = Db::table('zixiao_item')
            ->alias('i')
            ->join($join2)
            ->field($field2)
            ->select();

        $result3 = Db::name('Item')
            ->field('task_id,item_name')
            ->where('state',0)
            ->select();

        $user = Db::name('User')
            ->field('id,username')
            ->select();

        //用户id计数
        /*$number = array();
        foreach ($user as $kn => $vn)
        {
            $uid = $vn['id'];
            $res = Db::name('Task')
                ->where("user_id=$uid")
                ->field('id')
                ->select();
            $ress = array();
            foreach ($res as $vr)
            {
                $ress[] = $vr['id'];
            }
            $number[$uid] = $ress;
//            $usercount = Db::name('Task')->where('user_id',$id)->count();

        }*/
        //组合HTML标签内容
        $str = "";
        foreach ($result as $key => $value)
        {

            $times = $value['stop_time']/86400-$value['start_time']/86400+1;
            $start_time = date('Y-m-d',$value['start_time']);
            $stop_time = date('Y-m-d',$value['stop_time']);
            $str .= "<tr ";
            if($value['task_state'] == 1)
            {
                $str .= "style='color: #aaa'";
            }
            //遍历用户对应的任务序号
            /*foreach ($number as $nk => $nv)
            {
                if($value['user_id'] == $nk)
                {
                    $usernum = array_search($value['id'],$nv)+1;
                }
            }*/

            $str .= ">
                    <td>{$value['nickname']}</td>
                    <td><input type='checkbox' onclick='checkid({$value['id']})' name='checkbox' ";
//            $number[$value['user_id']]++;

            if($value['task_state'] == 1)
            {
                $str .= "checked";
            }
            $str .= "></td>
                    <td>{$value['project_name']}（{$value['task_name']}）</td>
                    <td>{$start_time}</td>
                    <td>{$stop_time}</td>
                    <td>$times 天</td>
                    <td>{$value['username']}</td>
                    <td>";
            //任务成员-去重
            $taskuser = array();
            foreach ($result2 as $v)
            {
                if($v['id'] == $value['id'])
                {
                    $taskuser[] = $v['username'];
                }
            }
            $taskuser = array_unique($taskuser);
            $str .= join("/",$taskuser);
            $str .= "</td><td>";
            //验收人
            $acc = explode(",",$value['task_acceptance']);
            foreach ($user as $v)
            {
                if(in_array($v['id'],$acc))
                {
                    $str .= $v['username'].' ';
                }
            }
            $str .= "</td><td>";

            if($value['task_state'] == 1){
                $str .= "<small class='label label-success'>已完成</small>";
            }else if($value['stop_time'] + 86400 < time() ){
                $str .= "<small class='label label-danger'>未完成</small>";
            }else if($value['start_time'] < time() ){
                $str .= "<small class='label label-warning'>进行中</small>";
            }else{
                $str .= "<small class='label label-default'>未开始</small>";
            }
            $str .= "
                    </td>
                    <td>
                        <select class='form-control select2' name='project_id' style='width: 100%;'>";
            //筛选未完成中目标的小目标
            if($value['task_state'] == 0 && $value['stop_time'] + 86400 < time())
            {
                foreach ($result3 as $v)
                {
                    if($value['id'] == $v['task_id'])
                    {
                        $str .= "<option>{$v['item_name']}</option>";
                    }
                }
            }

            $str .= "
                        </select>
                    </td>
                    <td>{$value['task_cause']}</td>
                    <td>";
            if($value['task_operate'] == 1){
                $str .= "<small class='label label-purple'>延期</small>";
            }else if($value['task_operate'] == 2){
                $str .= "<small class='label label-purple'>变更</small>";
            }else if($value['task_operate'] == 3){
                $str .= "<small class='label label-purple'>完成</small>";
            }
            $str .= "</td><td><a href='update/id/{$value["id"]}.html'>编辑</a></td></tr>";
        }

        $this->assign('item',$str);


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
                if($value == '' || $value == null || empty($data['task_acceptance']))
                {
                    $this->error("所有项都为必填项！");
                }
            }
            $data['start_time'] = strtotime($data['start_time']);
            $data['stop_time'] = strtotime($data['stop_time']);
            $data['task_acceptance'] = join(",",$data['task_acceptance']);
            $result = Db::name('Task')->insert($data);
            if($result)
            {
                $this->success("添加成功",url('admin/task/index'));
            }else{
                $this->error("添加失败！");
            }

        }else{
            $result = Db::name('Project')->select();
            $this->assign('list',$result);
            $user = Db::name('User')
                ->field('id,username')
                ->select();
            $this->assign('user',$user);

            $time = date("Y-m-d",time());
            $this->assign('time', $time);
            return $this->fetch();
        }
    }


    //修改任务
    public function update($id)
    {
        if(!session('username'))
        {
            $this->redirect('login/login');
        }
        if(request()->isGet())
        {
            //查询
            $join = [
                ['zixiao_project p','t.project_id=p.id'],
                ['zixiao_user u','t.user_id=u.id'],
            ];
            $field = 't.*,p.project_name,u.username';
            $list = Db::table('zixiao_task')
                ->alias('t')
                ->where('t.id='.$id)
                ->join($join)
                ->field($field)
                ->find();
            if($list['user_id'] != session('user_id'))
            {
                $this->error('只有负责人才能编辑此任务');
            }
            $this->assign('value', $list);
            return $this->fetch();
        }
        else if(request()->isPost())
        {
            //更新数据
            $data = input('post.');
            $data['start_time'] = strtotime($data['start_time']);
            $data['stop_time'] = strtotime($data['stop_time']);
            $result = Db::table('zixiao_task')
                ->where('id='.$id)
                ->update($data);
            if($result)
            {
                $this->redirect('admin/task/index');
            }else{
                $this->error("修改失败！");
            }
        }
    }

    public function jsonalter()
    {
        $data = input('post.');
        $id = $data['id'];

        $r = Db::table('zixiao_task')
            ->alias('t')
            ->where('t.id='.$id)
            ->field('task_state,task_acceptance')
            ->find();

        $user = session('user_id');
        //验收人
        $acc = explode(",",$r['task_acceptance']);
        if(in_array($user,$acc))
        {
            if($r['task_state'] == 0)
            {
                Db::table('zixiao_task')
                    ->where('id='.$id)
                    ->update(['task_state' => 1]);
            }else if($r['task_state'] == 1)
            {
                Db::table('zixiao_task')
                    ->where('id='.$id)
                    ->update(['task_state' => 0]);
            }
            $return = array(
                'code' => 10000,
                'msg'  => "ok"
            );
            echo json_encode($return);
        }else{
            $return = array(
                'code' => 20000,
                'msg'  => "请联系【验收人】操作此任务！"
            );
            echo json_encode($return);
        }
    }
}