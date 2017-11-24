<?php
/**
 * Created by PhpStorm.
 * User: le
 * Date: 2017/11/8
 * Time: 13:08
 */
namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Log;
use think\Request;

class Index extends Controller
{
    // Android/pc微信登录及注册接口
    // 关键字段 unionid
    public function login()
    {
//        return "ok,正在调试……";
        if(request()->isPost()) {
            $data = input('post.');

            // 检测 uin
            if(!empty($data['uin'])){
                $uin = Db::table('xboard_user')->where('uin',$data['uin'])->find();

                if($uin) {
                    $result = Db::table('xboard_user')
                        ->field('nickname,sex,province,city,country,userimg,unionid,uin')
                        ->where('uin',$data['uin'])
                        ->find();
                    $return = [
                        "status" => 1,
                        "message" => "账号连接成功",
                        "result" => $result
                    ];
                    return json($return);
                }else{
                    $res = Db::table('xboard_user')
                        ->field('id')
                        ->where('nickname',$data['nickname'])
                        ->where('sex',$data['sex'])
//                        ->where('city',$data['city'])
//                        ->where('country',$data['country'])
                        ->find();

                    if($res)
                    {
                        Db::table('xboard_user')
                            ->where('id',$res['id'])
                            ->update(['uin' => $data['uin']]);
                        $result = Db::table('xboard_user')
                            ->field('nickname,sex,province,city,country,userimg,unionid,uin')
                            ->where('uin',$data['uin'])
                            ->find();
                        $return = [
                            "status" => 1,
                            "message" => "账号绑定连接成功",
                            "result" => $result
                        ];
                        return json($return);
                    }else{
                        $return = [
                            "status" => 0,
                            "message" => "账号连接失败"
                        ];
                        return json($return);
                    }

                }
            }

            // 检测 unionid
            $unionid = Db::table('xboard_user')->where('unionid',$data['unionid'])->find();
            if(!$unionid)
            {
                $data['create_time'] = time();

                $up = Db::table('xboard_user')->insert($data);
                if($up)
                {
                    // 注册成功返回用户数据
                    $result = Db::table('xboard_user')
                        ->field('nickname,sex,province,city,country,userimg,unionid')
                        ->where('unionid',$data['unionid'])
                        ->find();
                    $return = [
                        "status" => 1,
                        "message" => "登录成功",
                        "result" => $result
                    ];
                    return json($return);
                }else{
                    $return = [
                        "status" => 0,
                        "message" => "注册失败"
                    ];
                    return json($return);
                }



                /*$file = request()->file('userimg');
                if($file)
                {
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'userimg', '');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $data['userimg'] = UPLOAD_PATH.'userimg/'.$info->getsaveName();
                        $data['create_time'] = time();

                        $up = Db::table('xboard_user')->insert($data);
                        if($up)
                        {
                            // 注册成功返回用户数据
                            $result = Db::table('xboard_user')
                                ->field('nickname,sex,province,city,country,userimg,unionid')
                                ->where('unionid',$data['unionid'])
                                ->find();
                            $return = [
                                "status" => 1,
                                "message" => "登录成功",
                                "result" => $result
                            ];
                            return json($return);
                        }else{
                            $return = [
                                "status" => 0,
                                "message" => "注册失败"
                            ];
                            return json($return);
                        }
                    } else {
                        // 返回文件错误信息
                        $return = array(
                            "status" => 0,
                            "message"  => $file->getError()
                        );
                        return json($return);
                    }
                }*/

            }else{
                // 如果 unionid 值存在则直接查询
                $result = Db::table('xboard_user')
                    ->field('nickname,sex,province,city,country,userimg,unionid')
                    ->where('unionid',$data['unionid'])
                    ->find();
                $return = [
                    "status" => 1,
                    "message" => "登录成功",
                    "result" => $result
                ];
                return json($return);
            }

        }else{
            return view();
            /*$return = array(
                "status" => 0,
                "message"  => "Illegal Access"
            );
            return json($return);*/
        }
    }

    // 文件上传
    public function upload()
    {
        if(request()->isPost()) {
            $data = input('post.');
            $file = request()->file('files');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'xboardimg');
                if ($info) {
                    // 成功上传后 获取上传信息
                    $data['file_path'] = UPLOAD_PATH . 'xboardimg/' . $info->getSaveName();
                    $file_name = $info->getinfo()['name'];
                    $data['file_name'] = $file_name;
                    $data['create_time'] = time();
                    $res = Db::table('xboard_files')
                        ->where('create_time','>',time()-60)
                        ->where('file_name',$file_name)
//                        ->where('down_time=0')
                        ->find();
                    if(!$res){
                        Db::table('xboard_files')->insert($data);
                        $return = [
                            "status" => 1,
                            "message" => "上传成功"
                        ];
                        return json($return);
                    }
                } else {
                    // 返回文件错误信息
                    $return = array(
                        "status" => 0,
                        "message"  => $file->getError()
                    );
                    return json($return);
                }
            }else{
                $return = array(
                    "status" => 0,
                    "message"  => "未发现文件"
                );
                return json($return);
            }

        }else {
            return view();
            /*$return = array(
                "status" => 0,
                "message"  => "Illegal Access"
            );
            return json($return);*/
        }

    }

    // 文件下载
    public function download()
    {
        if(request()->isPost()) {
            $data = input('post.');
            $time = time()-600;
            $result = Db::table('xboard_files')
                ->field('id,file_name,unionid,file_path')
                ->where('unionid',$data['unionid'])
                ->where("down_time=0")
                ->where("create_time>$time")
                ->select();
            if($result){
                foreach ($result as $key => $value)
                {
                    Db::table('xboard_files')
                        ->where('id',$value['id'])
                        ->update(['down_time' => time()]);
                }
                $return = [
                    "status" => 1,
                    "message" => "文件数据返回成功",
                    "result" => $result
                ];
                return json($return);
            }else{
                // 未找到文件返回
                $return = [
                    "status" => 0,
                    "message" => "not found"
                ];
                return json($return);
            }
        }else{
            return view();
        }

    }
}