<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2020-07-07
 * Time: 18:02
 */

namespace app\im\controller;

use think\Controller;
use think\facade\Request;
use app\im\model\mysql\Sign as DB_Sign;
use app\im\model\mysql\User as DB_User;

class Sign
{
    /**
     * 签到数据处理
     */
    public function updateSignData(){
        $params = Request::param();
        
        if(isset($params['user_id']) && $params['user_id']!=''){
            $user_id = $params['user_id'];
            $where =  ['user_id' => $user_id];
            
            $res_obj = DB_Sign::where($where)->find();
        //    echo(json_encode($res_obj));
            //是否存在签到数据
            if($res_obj){
                 if(date('Ymd')==$res_obj['last_sign_date']){
                          $return_data['data'] = [
                              'msg' => '今日已签到',
                              'signdays' => $res_obj['sign_days'],
                              'scores' => $res_obj['sign_scores']
                              ];
                          return json($return_data);
                 }
                 
                 $days = $res_obj['sign_days'];
                 $scores = 100;
                 
                 if(date('Ymd')>($res_obj['last_sign_date']+1)){
                     $days = 1;
                 }else{
                     //签到满7天时处理
                     if($days == 6){
                         $scores = 688;
                      }
                      
                     if($days == 7){
                         $days = 1;
                      }else{
                         $days = $days + 1;
                      }
                 }
                 
                 
                 
                   $upData = [
                       'user_id' => $user_id,
                       'sign_days' => $days,
                       'last_sign_date' => date('Ymd'),
                       'last_sign_time' => time()
                       ]; 
                      $ret  = DB_Sign::where($where)->update($upData);
                      DB_Sign::where($where)->setInc('sign_scores',$scores);
                       
                      if($ret){
                          DB_User::where(['id' => $user_id])->setInc('money',$scores);
                          $res_obj = DB_Sign::where($where)->find();
                          $return_data['data'] = [
                              'msg' => '签到成功',
                              'signdays' => $res_obj['sign_days'],
                              'scores' => $res_obj['sign_scores']
                              ];
                          return json($return_data);
                      }else{
                          $return_data['data'] = '签到失败';
                          return json($return_data);
                      }
            }else{
                $userObj = DB_User::where(['id' => $user_id])->find();
                 if($userObj){
                     $insertData = [
                       'user_id' => $user_id,
                       'sign_scores' => 100,
                       'sign_days' => 1,
                       'last_sign_date' => date('Ymd'),
                       'last_sign_time' => time()
                       ]; 
                      $ret = DB_Sign::insert($insertData);
                      if($ret){
                          DB_User::where(['id' => $user_id])->setInc('money',$scores);
                          $res_obj = DB_Sign::where($where)->find();
                          $return_data['data'] = [
                              'msg' => '签到成功',
                              'signdays' => $res_obj['sign_days'],
                              'scores' => $res_obj['sign_scores']
                              ];
                          return json($return_data);
                      }else{
                          $return_data['data'] = '签到失败';
                          return json($return_data);
                      }
                  }else{
                    $return_data['data'] = '用户不存在！';
                    return json($return_data);  
                }
                 
            }
        }
        
    }
}