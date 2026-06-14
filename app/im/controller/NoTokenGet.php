<?php

namespace app\im\controller;

use app\im\model\mongo\HongBaoDetails;
use app\im\model\traits\MongoObj;
use extend\service\ChatService;
use extend\service\FriendService;
use extend\service\HongBaoService;
use extend\service\JsonDataService;
use extend\service\PayMentService;
use extend\service\UserService;
use \Request;
use \app\im\common\controller\NameFirstChar;
/** mongo表 */

use \app\im\model\mongo\Chat;
use \app\im\model\mongo\ChatList;
use \app\im\model\mongo\Friend;
use \app\im\model\mongo\Circle;
use \app\im\model\mongo\CircleComments;
use \app\im\model\mongo\FriendApply;
use \app\im\model\mongo\UserState;
use \app\im\model\mongo\ChatGroup;
use \app\im\model\mongo\ChatMember;
use \app\im\model\mongo\ChatGroupApply;
/** mysql表 */

use \app\im\model\mysql\User;
use \app\im\controller\Message;
use extend\video\TLSSigAPIv2;


class NoTokenGet
{

  /** 获得某用户未读消息数 */
    public function getNoReaderNum()
    {
        $param = Request::param();
        $type = isset($param['type']) ? 1 : -1;
        $no_reader_num = 0;
        $return_data = [
            'err' => '0',
            'msg' => ''
        ];
        
        if(!isset($param['user_id'])){
            $return_data['err'] = 1;
            $return_data['data'] = '请传入用户user_id ！';
            return json($return_data);
        }
        
        $ret = ChatService::chatList((int)$param['user_id'],$type);
        if(!$ret['data']){
            $return_data['err'] = 1;
            $return_data['data'] = '未查询到该用户的消息列表！';
            return json($return_data);
        }
        $ret = $ret['data'];
        for($i = 0; $i<count($ret); $i++ ){
            if ($ret[$i]['no_reader_num'] > 0) {
                 $no_reader_num = $no_reader_num + $ret[$i]['no_reader_num'];
            }
        }
        $return_data['no_reader_num'] = $no_reader_num;
        return json($return_data);
    }
    
    
    
    
    
}