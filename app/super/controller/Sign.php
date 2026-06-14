<?php

namespace app\super\controller;

use think\Controller;
use app\im\model\mysql\Sign as DB_Sign;
use app\im\model\mysql\System as DB_System;
use app\im\model\mysql\User;
use think\facade\Request;
const PAGE_RECORDS = 15;
class Sign extends Controller
{
    public function initialize()
    {
        $super_id = session('super_id');
        if(!$super_id)
        {
            $this->error('请先登录');
        }
    }

    public function index()
    {
        $post_data = Request::post();
        $key = Request::param();
        $where = [];
        $where1 = [];
        if(isset($key['act']) && $key['act'] == 'check')
        {
            if($key['start_time'] && !$key['end_time'])
            {
                $where[] = ['create_time','>=',strtotime($key['start_time'].' 00:00:00')];
            }
            else if(!$key['start_time'] && $key['end_time'])
            {
                $where[] = ['create_time','<=',strtotime($key['end_time'].' 23:59:59')];
            }
            else if($key['start_time'] && $key['end_time'])
            {
                if(strtotime($key['start_time'].' 00:00:00') < strtotime($key['end_time'].' 00:00:00'))
                {
                    $where[] = ['create_time','>=',strtotime($key['start_time'].' 00:00:00')];
                    $where[] = ['create_time','<=',strtotime($key['end_time'].' 23:59:59')];
                }
                else
                {
                    $where[] = ['create_time','>=',strtotime($key['end_time'].' 00:00:00')];
                    $where[] = ['create_time','<=',strtotime($key['start_time'].' 23:59:59')];
                }
            }
            $userids = User::getUserIdByNickname($key['key']);
            if($key['key'])
            {
                $where1[] =  ['u.id','in', $userids];
            }
        }
       
        $chatArr =  DB_Sign::where('user_id','>',0)->order('id', 'desc')->paginate(PAGE_RECORDS);
        

        $list = array();
        foreach($chatArr as $key => $val){
            $user = User::getUserByUserId($val->user_id);
            if(empty($user)){
                unset($chatArr[$key]);
                continue;
            }
            $val->nick_name = $user->nickname ;
            array_push($list,$val);
          
        }
        // echo(date('Ymd'));
        for($i = 0;$i<count($list);$i++){
        //    $user = User::getUserByUserId($val->user_id);
            $last_date = $list[$i]->last_sign_date;
            if(date('Ymd')>$last_date){
                $list[$i]['today_sign'] = 0;
            }else{
                $list[$i]['today_sign'] = 1;
            }
        }
       // echo(json_encode($chatArr));
        
        $ret = DB_System::where(['key' => 'action_sign'])->find();
        if($ret){
            $action['signAction'] = $ret['value'];
        }  
        $this->assign('action',$action);
        
        $this->assign('chatlist',  $chatArr);
        $this->assign('list',  $list);
        $this->assign('key',$key);
        return $this->fetch();
    }

    public function memberChatList()
    {
        $user_id = (int)Request::param('user_id');
        $parmas = Request::param();
        $where[] =  ['user_id','=',$user_id];
        $chatArr =  DB_Sign::where('user_id','=', $user_id)->order('time', 'desc')->paginate(PAGE_RECORDS);
        $list = array();
        foreach($chatArr as $key => $val){
           // $user = User::getUserByUserId($val->user_id);
           // $val->nickname = $user->nickname ;
            array_push($list,$val);
          
        }
        $this->assign('chatlist',  $chatArr);
        $this->assign('list',  $list);
        $this->assign('user_id',$user_id);

        return $this->fetch();
     
    }
    
    public function updateActionSign(){
       $post_data = Request::post();
       $update = [];
       $return_data = [
          'err' => 1,
          'msg' => 'fail',
         
        ];
         
         
         $where =  ['key' => 'action_sign'];
         $update = [
            'value' => $post_data['checkVal'],
            'explain' => '全局邀请码状态',
            'update_time' => time()
          ];
          
        $ret  = DB_System::where($where)->update($update);
           if($ret !== false)
        { 
             $return_data['err'] = 0;
             $return_data['msg'] = '数据修改成功！';
             return json($return_data);  
        }
        else{
             $return_data['err'] = 1;
             $return_data['msg'] = '数据修改失败！';
             return $return_data;  
         }
       
    }

}