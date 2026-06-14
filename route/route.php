<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Route;

/*
Route::get('manageAdmin','admin/index/index');//后台首页
Route::get('manageLogin','admin/login/loginPage');//后台登录页面
Route::post('loginAction','admin/login/loginAction');//后台登录操作
Route::get('welcome','admin/index/welcome');//欢迎页面
Route::get('memberList','admin/member/memberList');//注册会员列表
Route::get('memberShow','admin/member/memberShow');//展示单个会员信息
Route::post('changeUserStatus','admin/member/changeUserStatus');//更改会员状态
*/

Route::get('yzmimg','super/login/yzmimg');//验证码图片

Route::get('/admin_login','super/login/loginPage');//后台超级用户登录页面
Route::get('super_signout','super/login/signout');//后台超级用户退出页面

Route::get('super_adminList','super/admin/index');//后台管理员列表
Route::get('super_changepass','super/login/changepass');//后台超级用户修改密码页面
Route::post('super_updatepass','super/login/updatepass');//后台超级用户修改密码页面

Route::post('superloginAction','super/login/loginAction');//后台超级用户登录操作
Route::get('/admin_index','super/index/index');//后台超级用户首页
Route::get('welcomeSuper','super/index/welcome');//欢迎页面
Route::get('super_memberList','super/member/memberList');//注册会员列表
Route::get('super_pollingKefuList','super/pollingkefu/pollingKefuList');//轮询客服列表
Route::get('super_memberShow','super/member/memberShow');//展示单个会员信息
Route::get('super_getMemberByagent','super/member/memberByagent');//通过agent_id获取会员列表



Route::get('super_groupList','super/group/index');//群列表

Route::post('changeUserService','super/member/changeUserService');//更改会员为客服人员
Route::post('super_changeUserStatus','super/member/changeUserStatus');//更改会员状态
Route::post('super_delMember','super/member/delMember');//删除用户





Route::get('super_configList','super/baseconf/index');//基础配置列表

Route::get('super_findList','super/find/index');//探索商家自定义网站管理

Route::get('super_tabbarFind','super/tabbarfind/index');//发现页管理
Route::get('super_tabbarFindEdit','super/tabbarfind/edit');//tabbar发现页面编辑
Route::post('super_updateTabbarFind','super/tabbarfind/updateTabbarFind');//Tabbar发现页内容更新
Route::post('super_changeTabbarFindStatus','super/tabbarfind/changeTabbarFindStatus');//修改Tabbar发现页状态
Route::post('super_updateActionTabbarfind','super/tabbarfind/updateActionTabbarfind');//修改Tabbarfind发现页开启/关闭

Route::post('super_checkAgentId','super/find/checkAgentId');//检查客户标识
Route::get('super_getAgentList','super/find/getAgentList');//获取客户(商家)列表
Route::post('super_updateService','super/find/updateService');

Route::post('super_addGame','super/find/addGame');//添加发现中的游戏
Route::post('super_imgupload','super/find/imgupload');//添加发现中的游戏上传图片
Route::post('super_changeGameStatus','super/find/changeGameStatus');//修改记录状态
Route::post('super_gameDel','super/find/gameDel');//删除记录状态
Route::get('super_gameShow','super/find/show');//编辑记录状态
Route::post('super_updateGame','super/find/updateGame');//更新记录

Route::get('super_pollingKefuShow','super/pollingkefu/show');//编辑客服轮询id
Route::post('super_updateKefuId','super/pollingkefu/updatePollingKefuId');//更新记录



Route::get('super_friendlist','super/friendlist/index');//通讯录管理
Route::get('super_membermaillist','super/friendlist/index');//会员通讯录

Route::get('super_chatList','super/chatlist/index');//会话管理
Route::get('super_MemberChatList','super/chatlist/memberChatList');

Route::get('super_memberlogList','super/memberlog/index');//日志管理


Route::get('super_setconfig','super/system/setData');       //系统配置
Route::post('admin_saveConfig','super/system/saveConfig');  //保存配置
Route::get('super_contactList','super/contact/contactList'); //手机通讯录

Route::get('super_finance','super/finance/index'); //提现
Route::get('super_financeList','super/finance/financeList'); //提现
Route::post('super_finance_status','super/finance/updateStatus');  //手动操作


Route::get('super_charge','super/charge/index'); //充值
Route::post('super_charge_status','super/charge/updateStatus');  //手动操作充值状态

Route::get('super_manual','super/manual/index');  //手动操作
Route::get('super_manualList','super/manual/manualList');  //手动操作

Route::get('super_userInfo','super/member/getUserInfo');  //手动操作
Route::post('super_update_amount','super/member/updateAmount');  //手动操作

Route::get('super_capital_getList','super/capital/index');  //资金流水

Route::get('super_vendorList','super/vendor/index');                    //资金流水
Route::get('super_vendoruserlist','super/vendor/userList');             //资金流水
Route::get('super_createUserVendor','super/vendor/createUserVendor');   //资金流水
Route::get('super_editUserVendor','super/vendor/editUserVendor');       //资金流水
Route::any('super_vendorSave','super/vendor/vendorSave');               //保存插件
Route::any('super_setRebot','super/member/setRebot');               //保存插件
Route::any('super_robotList','super/vendor/robotList');               //保存插件
Route::any('super_addRobot','super/vendor/addRobot');               //保存插件
Route::any('super_uploadOne','super/upload/uploadOne');               //保存插件
Route::any('super_vendorAddRobot','super/vendor/saveRobot');               //保存插件

Route::any('super_updateUserPassword','super/member/updateUserPassword');               //重置密码
Route::any('super_saveUserPassword','super/member/saveUserPassword');               //重置密码
Route::any('super_updatePollingKefuId','super/pollingkefu/updatePollingKefuId');   //设置客服轮询ID
Route::any('super_updateKefuTerm','super/pollingkefu/updatePollingKefuTerm');   //设置客服术语
Route::any('super_savePollingKefuId','super/pollingkefu/savePollingKefuId');   //保存客服轮询ID
Route::any('super_updateTradeUserPassword','super/member/updateTradeUserPassword');   //交易密码            //重置密码
Route::any('super_saveUserTradePassword','super/member/saveUserTradePassword');   //交易密码            //重置密码
Route::any('super_articleList','super/article/articleList');   //交易密码            //重置密码
Route::any('super_addArticle','super/article/addArticle');   //交易密码            //重置密码

Route::any('super_announcementList','super/announcement/addAnnouncement'); //公告列表
Route::any('super_addAnnouncement','super/announcement/addAnnouncement'); //添加公告

Route::post('super_updateActionInvitation','super/invitation/updateActionInvitation');//更新全局邀请码状态
Route::get('super_invitationCodeList','super/invitation/index');//邀请码列表页面
Route::get('super_invitationEdit','super/invitation/edit');//邀请码编辑
Route::post('super_updateInvitation','super/invitation/update');//邀请码更新
Route::post('super_changeInvitationStatus','super/invitation/changeInvitationStatus');//更改邀请码状态
Route::post('super_addInvitation','super/invitation/addInvitation');//新增邀请码
Route::post('super_delInvitation','super/invitation/delInvitation');//删除邀请码

Route::get('super_waistcoatMemberList','super/waistcoatmember/waistcoatMemberList');//马甲用户列表
Route::get('super_addWaistcoatMember','super/waistcoatmember/addWaistcoatMember');//添加马甲用户


Route::get('super_signList','super/sign/index');//签到管理
Route::post('super_updateActionSign','super/sign/updateActionSign');//修改系统签到开启/关闭


Route::get('super_qiangHongBao','super/qianghongbao/qiangHongBaoList');//抢红包配置列表
Route::post('super_updateHongBaoPercent','super/qianghongbao/updatePercent');//更新抢红包百分比
Route::post('super_updateHongBaoMethod','super/qianghongbao/updateHongBaoMethod');//更新抢红包百分比
Route::post('super_changeUserQiang','super/member/changeUserQiang');//更改用户抢红包状态
Route::post('super_updateActionQiangHongBao','super/qianghongbao/updateActionQaingHongBao');//更新全局抢红包状态

Route::get('super_autoReg','super/autoreg/index');//自动注册配置页面
Route::post('super_updateAutoreg','super/autoreg/updateAutoReg');//自动注册/登录配置更新
Route::get('super_autoRegEdit','super/autoreg/edit');//自动注册/登录页面编辑

Route::post('super_changeUserFanyi','super/member/changeUserFanyi');//更改用户翻译状态

Route::post('super_changeUserLanguage','super/member/changeUserLanguage');//更改用户翻译状态

return [

];
