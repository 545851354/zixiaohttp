<?php
/**
 * Created by PhpStorm.
 * User: le
 * Date: 2017/10/11
 * Time: 19:59
 */

namespace app\admin\controller;
use think\Controller;
use think\Db;

class Files extends Controller
{
    public function upload(){
        if(request()->isPost()) {
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
            //验证重名
            $filename = request()->file('files')->getInfo('name');
            $res = Db::name('files')
                ->where('file_name',$filename)
                ->field('file_name')
                ->select();
            if($res)
            {
                $this->error('文件名重名，请修改后重新上传');
            }
            $data['user_id'] = session('user_id');
            // 获取表单上传文件
            $file = request()->file('files');
            // 移动到服务器的上传目录 并且设置不覆盖
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'admin', '');
            if ($info) {
                // 成功上传后 获取上传信息
//                dump($info);die;
                $data['file_name'] = $info->getinfo()['name'];
                $data['size'] = $info->getinfo()['size'];
                $data['type'] = $info->getinfo()['type'];
//                $data['file_path'] = $info->getpathName();
                $data['file_path'] = UPLOAD_PATH.'admin/'.$info->getsaveName();
                $data['file_root_path'] = $info->getpathName();
                $data['create_time'] = time();

                $up = Db::name('Files')->insert($data);
                if($up)
                {
                    $this->success("上传成功",url('admin/files/upload'));
                }else{
                    $this->error($file->getError());
                }
            } else {
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }else{

            //联表查询
            $join = [
                ['zixiao_task t','f.task_id=t.id'],
                ['zixiao_user u','f.user_id=u.id'],
            ];
            $field = 'f.id,t.task_name,u.username,f.file_name,f.des,f.file_path,f.size,f.type,f.create_time';
            $list = Db::table('zixiao_files')
                ->alias('f')
                ->join($join)
                ->field($field)
                ->select();
            $this->assign('list', $list);

            //关联任务
            $task = Db::name('Task')->select();
            $this->assign('task', $task);

            return $this->fetch();
        }
    }

    //文件下载
    public function download($id)
    {

        if(request()->isGet()) {

            $result = Db::name('files')->where('id',$id)->find();
            $file_dir = $result['file_root_path'];
            $file_name = $result['file_name'];
            $file_size = $result['size'];
            $file = fopen($file_dir, "r");
            $type = $result['type'];
            header("Content-type: $type");
            header("Accept-Ranges: bytes");
            header("Accept-Length: $file_size");
            header("Content-Disposition: attachment; filename=".$file_name);
            echo fread($file, $file_size);
            fclose($file);


            /*$file_dir = $result['file_root_path'];
            $file_name = $result['file_name'];
            //打开文件
            $file = fopen($file_dir, "r");
            //输入文件标签
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . $file_name);
            //输出文件内容
            //读取文件内容并直接输出到浏览器
            echo fread($file, filesize($file_dir));
            fclose($file);
            exit ();*/



/*
            //下载文件$file_name="1.jpg";
            $file_name=$result['file_name'];
            //使用绝对路径
            $file_path="http://www.xboard.cn".$result['file_path'];
//            if(!file_exists($file_path)){
//                dump($file_path);die;
//                $this->error('文件不存在！');
//            }
            $fp=fopen($file_path,"r");
            $file_size=filesize($file_path); //文件大小
            //以下为下载文件所需的http文件头
            $type = $result['type'];
            header("Content-type: $type");
            header("Accept-Ranges: bytes");
            header("Accept-Length: $file_size");
            header("Content-Disposition: attachment; filename=".$file_name);
            //向客户端回送数据
            $buffer=1024; //每次读1024字节
            //为了下载的安全，我们做一个文件字节读取计数器
            $file_count=0;
            while(!feof($fp) && ($file_size-$file_count>0)){ //判断文件是否结束
                $file_data=fread($fp,$buffer);
                //统计读了多少字节
                $file_count+=$buffer;
                //把部分数据回送给游览器
                echo $file_data;
            }
            //关闭文件
            fclose($fp);
            exit ();*/
        }




    }
}