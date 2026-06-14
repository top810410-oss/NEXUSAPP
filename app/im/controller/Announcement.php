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
use \app\im\model\mysql\Announcement as MAnnouncement;

class Announcement extends Controller
{
    /**
     * 获取商家网址列表
     */
    public function getAnnounceMentInfo(){
        $info = MAnnouncement::where(['id'=>1])->select();
        if($info){
             $return_data['data'] = $info;
             return json($return_data);
         }else{
             $return_data['data'] = '数据获取失败！';
             return json($return_data);  
        }
    }
}