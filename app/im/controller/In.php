<?php

namespace app\im\controller;

use app\im\model\mongo\UserState;
use app\im\model\mysql\VendorUser;
use app\super\model\BsysConfig;
use extend\service\ConfigService;
use extend\service\JsonDataService;
use extend\service\JwtService;
use extend\service\MsgService;
use extend\service\QueueService;
use extend\service\RedisService;
use extend\service\UserService;
use extend\service\UserStateService;
use \GatewayWorker\Lib\Gateway;
use \Request;
use \app\im\model\mysql\User;
use \app\im\model\mysql\LoginLog;
use \app\common\controller\Jwt;
use \app\common\model\mysql\System;
use \app\im\model\mongo\Chat;
use \app\im\model\mongo\ChatList;
use \app\im\model\mongo\Friend;
use \app\im\model\mongo\ChatMember;

use app\im\model\mysql\Invitation as DB_Invitation;

class In
{
    public function login()
    {
        $post_data = Request::post();
        $return_data = [
            'err' => 1,
            'msg' => 'fail',
        ];
        if (!isset($post_data['username']) || !isset($post_data['password']) || $post_data['username'] == '' || $post_data['password'] == '') {
            $return_data['msg'] = '登陆数据有误';
            return json($return_data);
        }
        $user = User::field('id,password,status')->where('username', $post_data['username'])->find();
        if($user && empty($user['client_id']) && $post_data['client_id']){
            User::where(['id'=>$user['id']])->update(['client_id'=>$post_data['client_id']]);
        }
        if (!$user || $user->password !== md5($post_data['password'])) {
            $return_data['msg'] = '用户名或者密码错误';
            return json($return_data);
        }

        if ($user->status > 0) {
            $return_data['msg'] = '用户已被' . [1=>'锁定',2=>'冻结'][$user->status];
            return json($return_data);
        }
        
              	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
                    	$cip = $_SERVER['HTTP_CLIENT_IP'];
	                }
                    	else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                    	$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                	}
                    	else if(!empty($_SERVER["REMOTE_ADDR"])){
                    	$cip = $_SERVER["REMOTE_ADDR"];
                  	}else{
                    	$cip = '0.0.0.0';
                	}
                    	preg_match("/[\d\.]{7,15}/", $cip, $cips);
                    	$cip = isset($cips[0]) ? $cips[0] : 'unknown';
                    	unset($cips);
        /** 写入登陆日志 */
        LoginLog::create([
            'user_id' => $user->id,
            'ip' => $cip,
            'details' => '账号密码登陆',
            'agent_id' => $post_data['_agent_id'],
        ]);

        $return_data['err'] = 0;
        $return_data['msg'] = '登陆成功';

        $return_data['data'] = [
            'token' => self::createToken($user->id),
        ];
        QueueService::AfterLogin(['user_id'=>$user->id]);
        /** 这里让其他绑定这个user_id的客户端下线 */
        return json($return_data);
    }

    public function reg()
    {
        $post_data = Request::post();
        $is_waistCoat = 0;
        $nick_name = $post_data['username'];
        $return_data = [
            'err' => 1,
            'msg' => 'fail'
        ];
        
        if(isset($post_data['invitationCode']) && $post_data['invitationCode']!= ''){
            $invitationCode = $post_data['invitationCode'];
            $findArr = [];
            $findArr = DB_Invitation::where(['invitation_code'=>$invitationCode])->select();
            
            if(!count($findArr)){
                $return_data['msg'] = '无效邀请码！';
                return json($return_data);
            }
            
            if($findArr[0]['status']==0){
                     $return_data['msg'] = '邀请码已禁用，请更换！';
                     return json($return_data);
            }
            
            DB_Invitation::where(['invitation_code'=>$invitationCode])->setInc('use_num');
        }
        
        if (!isset($post_data['username']) || !isset($post_data['password']) || $post_data['username'] == '' || $post_data['password'] == '') {
            $return_data['msg'] = '注册数据有误';
            return json($return_data);
        }
        if(isset($post_data['isWaistcoat']) && $post_data['isWaistcoat']==1){
            $is_waistCoat = 1;
        }
        if(isset($post_data['nickName']) && $post_data['nickName']!=""){
            $nick_name = $post_data['nickName'];
        }
        // if (!preg_match("/^\w{1,20}$/", $post_data['password'])) {
        //     $return_data['msg'] = '密码只能包括下划线、数字、字母,长度6-20位';
        //     return json($return_data);
        // }
        $db_data = User::where('username', $post_data['username'])->find();
        if ($db_data) {
            $return_data['msg'] = '这个用户名已经存在了';
            return json($return_data);
        }
        //随机头像
        $_agent_id = isset($post_data['_agent_id']) ? $post_data['_agent_id'] : ( isset($post_data['agent_id'])  ? $post_data['agent_id'] : 0);
        $insert = [
            'username' => $post_data['username'],
            'password' => $post_data['password'],
            'nickname' => $nick_name,
            'is_waistcoat' => $is_waistCoat,
            'agent_id' => $_agent_id,
            'client_id'=> $post_data['client_id'] ?? ''
        ];
        //检测是否开了注册
        $config = BsysConfig::getAllVal('basic_config');
        $sms_status = $config['user_regiter_sms_status'] ?? 0;
        if(isset( $post_data['mobileCode']) && $sms_status != $post_data['mobileCode']){return json(JsonDataService::fail('请清除缓存再试!'));}
        if(isset($post_data['mobileCode']) && $post_data['mobileCode'] == 1){
            //判断手机号和验证码
            if(!isMobile($post_data['username'])) return json(JsonDataService::fail('手机号码格式有误!'));
            $key = ConfigService::SMS_CODE.$post_data['type'].':'.$post_data['username'];
            $code = RedisService::get($key);
            if(!$code) return json(JsonDataService::fail('该手机号还未获取短信!'));
            if($post_data['sms_code'] != $code)
                return json(JsonDataService::fail('验证码不正确!'));
            $insert = array_merge($insert,['phone'=>$post_data['username']]);
        }else{
            // if (!preg_match("/^\w{1,20}$/", $post_data['username'])) {
            //     $return_data['msg'] = '社群号只能包括下划线、数字、字母,并且不能超过20个';
            //     return json($return_data);
            // }
        }

        if ($user = User::create($insert)) {
           if(isset($key)) RedisService::del($key);
            UserStateService::setRandPhoto(['user_id'=>$user->id]); //默认头像
            
            $nowkefu = User::where('nowpollingkefuid',1)->find();
            if($nowkefu){
                $where['is_customer_service'] = 1; 
                $allkefu = User::where($where)->order('id asc')->select();
                $setIndex = 0;
                for($i=0;$i<count($allkefu);$i++){
                    if($allkefu[$i]['nowpollingkefuid']==1){
                        if($i==count($allkefu)-1){
                            $setIndex = 0;
                        }else{
                            $setIndex = $i+1;
                        }
                    }
                }
                $ret  = User::where(['id'=>$nowkefu['id']])->update(['nowpollingkefuid'=>0]);
                if($ret){
                     User::where(['id'=>$allkefu[$setIndex]['id']])->update(['nowpollingkefuid'=>1]);
                }
            }
            /** 这里注册成功后，自动添加客服为好友 */
            /** 客服id */
          //  $friend_ids = BsysConfig::getVal('basic_config', 'user_default_friend');
          //将客服id写入用户客服字段
          $where = [
              'id' => $user->id
           ];
          $update = [
              'my_kefuid' => $nowkefu['id']
          ];
          User::where($where)->update($update);
          $friend_ids = $nowkefu['id'];
            $user->id *= 1;
            $friend_ids = explode('|',$friend_ids);

            if($friend_ids){
                foreach ($friend_ids as $friend_id){
                    if (!Friend::field('id')->where([
                        'user_id' => $user->id,
                        'friend_id' => $friend_id,
                    ])->find()) {
                        $chat_user_ids = [$user->id, $friend_id];
                        sort($chat_user_ids);
                        $chat_user_ids = json_encode($chat_user_ids);
                        $list_id = create_guid();
                        /** 增加会话列表 */
                        ChatList::create([
                            'user_id' => $user->id,
                            'list_id' => $list_id,
                            'user_ids' => $chat_user_ids,
                            'type' => 0,
                            'top' => 1,
                            'top_time' => time(),
//                            'last_chat_time'=>time(),
                        ]);
                        ChatList::create([
                            'user_id' => $friend_id,
                            'list_id' => $list_id,
                            'user_ids' => $chat_user_ids,
                            'type' => 0,
                            'top' => 1,
                            'top_time' => time(),
                        ]);

                        /** 增加到成员表 */
                        ChatMember::create([
                            'list_id' => $list_id,
                            'user_id' => $user->id,
                        ]);
                        ChatMember::create([
                            'list_id' => $list_id,
                            'user_id' => $friend_id,
                        ]);

                        /** 增加到好友表 */
                        Friend::create([
                            'user_id' => $user->id,
                            'friend_id' => $friend_id,
                            'from' => 4,
                        ]);
                        Friend::create([
                            'user_id' => $friend_id,
                            'friend_id' => $user->id,
                            'from' => 4,
                        ]);

                        /** 增加到对话表 */
                        //user_default_friend_speak
                        $text = BsysConfig::getVal('basic_config', 'user_default_friend_speak');
                        $text = $text ? $text : '欢迎来到白菜';
                        $chat = Chat::createChatMsg([
                            'list_id' => $list_id,
                            'user_id' => $friend_id,
                            'content_type' => 0,
                            'msg_type' => 0,
                            'content' => [
                                'text' => $text,
                            ],
                            'time' => time(),
                        ]);
                        //发消息给客服
                        $user_info = UserService::getUserInfo($user->id);
//                        ChatList::where(['list_id'=>$list_id,'user_id'=>$user->id])->update(['last_chat_time'=>time()]);
                        MsgService::senNormalMsgToUid($friend_id,'chatData',[
                            'list_id' => $list_id,
                            'data' => [
                                'type' => 0,
                                'msg' => [
                                    'id' => $chat->id,
                                    'type' => 0,
                                    'time' => time(),
                                    'user_info' => [
                                        'uid' => $user_info['id'],
                                        'name' => $user_info['nickname'],
                                        'face' => $user_info['face'],
                                    ],
                                    'content' => ['text'=>$text]
                                ],
                            ]
                        ]);
                    }
                }
            }
            
            if(!empty($_SERVER['HTTP_CLIENT_IP'])){
                    	$cip = $_SERVER['HTTP_CLIENT_IP'];
	                }
                    	else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                    	$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                	}
                    	else if(!empty($_SERVER["REMOTE_ADDR"])){
                    	$cip = $_SERVER["REMOTE_ADDR"];
                  	}else{
                    	$cip = '0.0.0.0';
                	}
                    	preg_match("/[\d\.]{7,15}/", $cip, $cips);
                    	$cip = isset($cips[0]) ? $cips[0] : 'unknown';
                    	unset($cips);
            
            /** 写入登陆日志 */
            LoginLog::create([
                'user_id' => $user->id,
                'ip' => $cip,
                'details' => '注册登陆',
                'agent_id' => $_agent_id,
            ]);

            $return_data['err'] = 0;
            $return_data['msg'] = '注册成功';
            $return_data['data'] = [
                'token' => self::createToken($user->id),
            ];
            
            $return_data['nowkefuid'] = $nowkefu['id'];
        }
        return json($return_data);
    }

    private static function createToken($user_id)
    {
        $jwt = new Jwt;
        $db_data = System::where('key', 'JWT')->select()->toArray();
        Jwt::$key = $db_data[0]['value']['key']['value'];
        Jwt::$timeNum = $db_data[0]['value']['time']['value'];
        $payload = [
            'user_id' => $user_id,
        ];
        return $jwt->getToken($payload);
    }


    /**
     * 群主登录
     */
    public function vendorLogin(){
        $post_data = Request::post();
        $return_data = [
            'err' => 1,
            'msg' => 'fail',
        ];
        if (!isset($post_data['username']) || !isset($post_data['password']) || $post_data['username'] == '' || $post_data['password'] == '') {
            $return_data['msg'] = '登陆数据有误';
            return json($return_data);
        }
        //检测是否有开通插件
        $user = User::field('id,password,status')->where('username', $post_data['username'])->find();

        if (!$user || $user->password !== md5($post_data['password'])) {
            $return_data['msg'] = '用户名或者密码错误';
            return json($return_data);
        }
        $info = VendorUser::where(['user_id'=>$user['id']])->find();
        if(empty($info)){
            $return_data['msg'] = '您暂未开通插件，请联系客服开通';
            return json($return_data);
        }
        if ($user->status > 0) {
            $return_data['msg'] = '用户已被' . [1=>'锁定',2=>'冻结'][$user->status];
            return json($return_data);
        }
        //
        $return_data['err'] = 0;
        $return_data['msg'] = '登陆成功';

        $return_data['data'] = [
            'token' => JwtService::createToken($user->id),
        ];
        /** 这里让其他绑定这个user_id的客户端下线 */
        return json($return_data);
    }


    /**
     * 忘记交易密码
     */
    public function forgetPassword(){
        $post_data = Request::post();
        $phone = $post_data['username'];
        $user = User::where(['username'=>$phone])->find();
        if(!$user) return json(JsonDataService::fail('该手机号尚未注册!'));
        $key = ConfigService::SMS_CODE.$post_data['type'].':'.$phone;
        $code = RedisService::get($key);
        if(!$code) return json(JsonDataService::fail('该手机号还未获取短信!'));
        if($post_data['sms_code'] != $code) return json(JsonDataService::fail('验证码不正确!'));
        $ret = $user->save(['password'=>$post_data['password']]);
        if($ret === false) return json(JsonDataService::fail('操作失败'));
        RedisService::del($key);
        return json(JsonDataService::success('操作成功'));

    }

}
