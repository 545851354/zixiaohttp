<?php
/**
 * Created by PhpStorm.
 * User: le
 * Date: 2017/10/11
 * Time: 19:34
 */

namespace app\admin\controller;
use think\Controller;
use think\Db;

class TaskUser extends Controller
{
    public function index()
    {
        //查询关联任务的用户
        $join = [
            ['zixiao_user u','t.user_id=u.id']
        ];
        $field = 'u.username,t.task_name';
        $result = Db::table('zixiao_task')
            ->alias('t')
            ->join($join)
            ->field($field)
            ->select();
        $this->assign('list',$result);
        return $this->fetch();
    }
}