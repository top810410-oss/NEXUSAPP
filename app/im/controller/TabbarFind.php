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
use app\im\model\mysql\TabbarFind as Tabbar;

class TabbarFind
{
    /**
     * 获取发现页面网址
     */
    public function getTabbarFindUrl(){
        $params = Request::param();
        $where =  ['id' => 1];
        $res_obj = Tabbar::where($where)->find();
        if($res_obj){
             $return_data['data'] = $res_obj;
             return json($return_data);
         }else{
             $return_data['data'] = '数据获取失败！';
             return json($return_data);  
        }
    }
}