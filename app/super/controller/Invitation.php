<?php

namespace app\super\controller;

use think\Controller;
use app\im\model\mongo\Find as DB_Find;
use app\im\model\mysql\User;
use app\im\model\mysql\Invitation as DB_Invitation;
use app\im\model\mysql\System as DB_System;
use think\facade\Request;

const PAGE_RECORDS = 15;

class Invitation extends Controller
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
        
      

        $key = Request::param();
        $where = [];
        $where1 = [];
        if(isset($key['act']) && $key['act'] == 'check')
        {
            if(isset($key['line_invitation_code']))
            {
            $key['key'] = $key['line_invitation_code'];
            $val = (String)$key['key'];

                $where[] = ['invitation_code','like','%'.$val.'%'];
              if(isset($key['line_remark']) && $key['line_remark'])
                $where1[] = ['remark','like','%'.$val.'%'];
            }

       if(isset($key['line_remark']))
            {
            $key['key'] = $key['line_remark'];
            $val = (String)$key['key'];

                $where[] = ['invitation_code','like','%'.$val.'%'];
              if(isset($key['line_remark']) && $key['line_remark'])
                $where1[] = ['remark','like','%'.$val.'%'];
            }
        }
        //获取邀请码列表
        $list = DB_Invitation::where(function ($q)use($where){
                    $q->where($where);
                })->where(function ($q1)use($where1){
                    $q1->whereOr($where1);
                })
            ->field('*,invitation_code as invitation_code')->order('id', 'desc')
            ->paginate(PAGE_RECORDS,false,[
                    'query'=>Request::param()
                ])->each(function ($v)use($key){
                    if(isset($key['key']) && $key['key'])
                    {
                        if(preg_match('/'.$key['key'].'/',$v['invitation_code']))
                        {
                            $v['invitation_code'] = preg_replace('/'.$key['key'].'/','<span style="color: red">'.$key['key'].'</span>',$v['invitation_code']);
                        }
                        if(preg_match('/'.$key['key'].'/',$v['remark']))
                        {
                            $v['remark'] = preg_replace('/'.$key['key'].'/','<span style="color: red">'.$key['key'].'</span>',$v['remark']);
                        }
                    }
                    return $v;
                });
        $ret = DB_System::where(['key' => 'action_invitation'])->find();
        if($ret){
            $action['invitationAction'] = $ret['value'];
        }        
       // echo json_encode($key);
        $this->assign('action',$action);
        $this->assign('list',$list);
        $this->assign('key',$key);
        return $this->fetch();
    }

    public function getAgentList()
    {
      $where[] =  ['agent_id','<>',''];
        $findArr =  DB_Find::where($where)->order('agent_id', 'desc')->paginate(1000);
        $list = array();
        foreach($findArr as $key => $val){
            array_push($list,$val);
        }
        return json(array('err' => 0,  'msg' => "数据获取成功！", 'data' =>   $list ));
    }

    public function addGame(){
          // $key = Request::param();
          $post_data = Request::post();
          $return_data = [
            'err' => 1,
            'msg' => 'error',
          ];
         // $find_id = md5(uniqid('JWT',true) . rand(1, 100000));
          $game_obj = DB_Find::create([
            'agent_id'  => $post_data['agent_id'],
            'appName' => $post_data['appName'],
            'url' => $post_data['url'],
            'port' => $post_data['port'],
            'status' => 0,
            'is_customer_service'=>[],
           // 'appIcon_url' => $post_data['appIcon_url'],
            'logo_url' => $post_data['logo_url'],
            'create_time' => time(),
          ]);

       /*
          $list = array();         
          $this->assign('list',  $post_data);
          echo "<pre>";
          var_dump( $post_data);
          echo "</pre>";
          return $this->fetch();
         */
         return json(array('err' => 0,  'msg' => "数据添加成功！", 'data' => $post_data));
        
    }

public function updateActionInvitation(){
       $post_data = Request::post();
       $update = [];
       $return_data = [
          'err' => 1,
          'msg' => 'fail',
         
        ];
         
         
         $where =  ['key' => 'action_invitation'];
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

    public function updateGame(){
       $post_data = Request::post();
       $update = [];
       $return_data = [
          'err' => 1,
          'msg' => 'fail',
         
        ];
        
       $where =  ['_id' => $post_data['id'] ];
       $game_obj = DB_Find::where($where)->find();

      
       
      if( $game_obj){
            $update = [
            'agent_id'  => $post_data['agent_id'],
            'appName' => $post_data['appName'],
            'url' => $post_data['url'],
            'port' => $post_data['port'],
           // 'status' => $post_data['status'],
           'logo_url' => $post_data['logo_url'],
           // 'create_time' => time(),
            // 'update_time' => time(),
          ];
          if( DB_Find::where($where)->update( $update )){
             $return_data['err'] = 0;
             $return_data['msg'] = '数据修改成功！';
          }
         
      }
     
      return json($return_data);     
    }

    public function edit()
    {
        $id = Request::param('id');
        $where =  ['id' => $id ];
        $game_obj = DB_Invitation::where($where)->find();
        $return_data['err'] = 0;
        $return_data['msg'] = '获取数据成功！';
        $return_data['data'] = $game_obj;

        return $return_data;    
    }

    public function checkAgentId(){
        $post_data = Request::post();
        $agent_id =  $post_data['agent_id'];
      
        $return_data = [
          'err' => 1,
          'msg' => 'fail',
          
        ];

        $where =  ['agent_id' => $agent_id ];
        $game_obj = DB_Find::where($where)->find();
        if($game_obj){
          $return_data['err'] = 0;
          $return_data['msg'] = '获取数据成功！';
          $return_data['data'] = $game_obj;
        }
       

        return $return_data;    
    }

    /**
     * Undocumented function
     * 上传图片
     * @return void
     */
    public function imgupload() {//图片上传
        $file = request()->file('file');
        $info = $file->validate(['size' => 1024 * 1024 * 2, 'ext' => 'jpg,png,gif,jpeg,svg'])->move('uploads');
        if ($info) {
          // 成功上传后 获取上传信息
          $path = '/uploads/' . $info->getSaveName();
          return json(array('error' => 0, 'msg' => $path));
        } else {
          // 上传失败获取错误信息
          return json(array('error' => 1, 'msg' => $file->getError()));
        }
    }
  
    /**
     * Undocumented function
     *
     * @return void
     */
    public function changeInvitationStatus(){
       $post_data = Request::post();
       $return_data = [
        'err' => 1,
        'msg' => 'fail',
       ];
       $where =  ['id' => $post_data['id'] ];
       $res_obj = DB_Invitation::where($where)->find();
      
       $num = 1;
       if($post_data['act']){
          $num = 0;
       }

       $update = [];
      if( $res_obj){
          $update = ['status' => $num];
          if( DB_Invitation::where($where)->update( $update )){
             $return_data['err'] = 0;
             $return_data['msg'] = '数据修改成功！';
          }
         
      }
      return json($return_data);      
    }

public function addInvitation(){
         $post_data = Request::post();
       $update = [];
       $return_data = [
          'err' => 1,
          'msg' => 'fail',
         
        ];
        
       $data = [
            'invitation_code' => $post_data['line_invitation_code'],
            'remark' => $post_data['line_remark'],
           // 'status' => $post_data['status'],
            'create_time' => time()
          ];
       $ret = DB_Invitation::insert($data);
       if($ret){
             $return_data['err'] = 0;
             $return_data['msg'] = '新增数据

成功！';
             return json($return_data);  
     }
        else{
             $return_data['err'] = 0;
             $return_data['msg'] = '新增数据
失败！';
             return json($return_data);  
         }
       
    }

    public function update(){
         $post_data = Request::post();
       $update = [];
       $return_data = [
          'err' => 1,
          'msg' => 'fail',
         
        ];
        
       $where =  ['id' => $post_data['id'] ];
       $res_obj = DB_Invitation::where($where)->find();
       if($res_obj){
           $update = [
            'invitation_code' => $post_data['invitation_code'],
            'remark' => $post_data['remark'],
           // 'status' => $post_data['status'],
            'create_time' => time()
          ];
          
          $ret  = DB_Invitation::where($where)->update($update);
           if($ret !== false)
        { 
             $return_data['err'] = 0;
             $return_data['msg'] = '数据修改成功！';
             return json($return_data);  
        }
        else{
             $return_data['err'] = 0;
             $return_data['msg'] = '数据修改失败！';
             return json($return_data);  
         }
         
       }else{
           $return_data['err'] = 0;
             $return_data['msg'] = '数据修改失败！';
             return json($return_data);  
       }
       
    }

    public function delInvitation(){
        $post_data = Request::post();
        $return_data = [
          'err' => 1,
          'msg' => 'fail',
        ];
        $where =  ['id' => $post_data['id'] ];
        $res_obj = DB_Invitation::where($where)->delete();
        if($res_obj ){
          $return_data['err'] = 0;
          $return_data['msg'] = '数据删除成功！';
          $return_data['data'] = $res_obj;
       }

        return $return_data; 
    }


}