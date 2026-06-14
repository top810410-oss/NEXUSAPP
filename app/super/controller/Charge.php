<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2020-08-01
 * Time: 4:29
 */

namespace app\super\controller;


use app\im\model\mysql\CapitalLog;
use app\im\model\mysql\User;
use app\im\model\mysql\UserBank;
use app\im\model\mysql\Charge as DB_Charge;
use extend\service\JsonDataService;
use extend\service\MsgService;
use extend\service\UserService;
use extend\service\UtilService;
use think\App;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Request;
use app\super\model\Admin as Model_Admin;

class Charge extends Controller
{

    public function __construct(App $app = null)
    {

        parent::__construct($app);
        $this->assign('menu', ['财务操作', '充值管理']);

    }

    public function index()
    {
        $params = UtilService::getMore([
            ['key',''],
            ['time',''],
            ['status','-1'],
            ['page',1],
            ['limit',10],
        ]);
        if (Request::isAjax()) {
            $model = (new DB_Charge());
            $count = $model->count(1);
            $model = $model->alias('w');
            $model = $model->leftJoin('user u','w.user_id = u.id');
            $model = $model->leftJoin('user_bank b','w.user_bank_id = b.id');
            $model = $model->field('w.*,w.status as v_status,u.nickname,b.account,b.fullname');
            if($params['key']){
                $map['u.nickname|b.fullname|b.account'] = ['like','%'.$params['key']];
                $model = $model->where("u.nickname like '%{$params['key']}%' or b.fullname like '%{$params['key']}%' or b.account like '%{$params['key']}%'");
            }
            if($params['status'] > -1){
                $model = $model->where(['w.status'=>$params['status']]);
            }
            if($params['time'] > 0){
                $model = $model->where(['w.status'=>$params['status']]);
            }
            $list = $model->page($params['page'], $params['limit'])->select();
           // echo($list);
            if ($list) {
                foreach ($list as &$v) {
                    $v['bank_code'] = $v['account'];
                    $v['status_msg'] = $v['v_status'] ? '已审核' : '待审核';
                 //   $v['true_amount'] = bcsub($v['draw_money'], $v['fee'], 2);
                    $v['update_time'] = $v['update_time'] ? date('Y-m-d H:i:s', $v['update_time']) : '';
                }
            }
            return json(['code' => 0, 'data' => $list->toArray(), 'count' => $count, 'msg' => 'success']);
        }

        return $this->fetch();
    }

    //充值列表
    public function updateStatus()
    {
        $params = Request::param();
        $common_params = [
            'audit_admin_id' => session('super_id'),
            'audit_user_name' => session('super_name'),
            'update_time' => time()
        ];
        $DB_Charge = DB_Charge::get($params['id']);
        if (!$DB_Charge) return json(JsonDataService::fail('操作失败!'));
        if ($params['type'] == 0) {
            //解冻 + 返钱
            if (mb_strlen($params['remark']) <= 0) return json(JsonDataService::fail('请输入不通过原因'));
            $remark = mb_substr($params['remark'], 0, 50);
            try {
                DB_Charge::startTrans();
                $ret = DB_Charge::where(['id' => $params['id']])->update(array_merge(['remark' => $remark,
                    'status' => 2], $common_params));
                if ($ret === false) throw new Exception('操作失败!');
                // $ret = User::where(['id' => $DB_Charge['user_id']])
                //     ->setField(['money' => Db::raw('money +' . $DB_Charge['draw_money']), 'freeze_money' => Db::raw('freeze_money-' . $DB_Charge['draw_money'])]);
                if ($ret === false) throw new Exception('操作失败!');
               // $user_info = UserService::getUserInfo($DB_Charge['user_id']);
                // $ret = CapitalLog::create([
                //     'user_id' => $DB_Charge['user_id'],
                //     'money' => $DB_Charge['draw_money'],
                //     'user_money' => $user_info['money'],
                //     'explain' => '充值失败解冻',
                //     'capital_type' => 4,
                //     'record_type' => 1
                // ]);
               // if ($ret === false) throw new Exception('操作失败!');
            } catch (Exception $e) {
                DB_Charge::rollback();
                return json(JsonDataService::fail($e->getMessage()));
            }
            DB_Charge::commit();
        } else {
            //解冻
            try {
                DB_Charge::startTrans();
                $ret = DB_Charge::where(['id' => $params['id']])->update(array_merge(['remark' => '充值审核通过!',
                    'status' => 1], $common_params));
                if ($ret === false) throw new Exception('操作失败!');
                // $ret = User::where(['id' => $DB_Charge['user_id']])
                //     ->setDec('freeze_money', $DB_Charge['amount']);
                // if ($ret === false) throw new Exception('操作失败!');
            } catch (Exception $e) {
                DB_Charge::rollback();
                return json(JsonDataService::fail($e->getMessage()));
            }
            DB_Charge::commit();
        }
       // echo(json_encode($DB_Charge));
        User::where(['id' => $DB_Charge['user_id']])->setInc('money',$DB_Charge['amount']);
        $user_info = UserService::getUserInfo($DB_Charge['user_id']);
        MsgService::senNormalMsgToUid($DB_Charge['user_id'],'payAmount',$user_info);
        return json(JsonDataService::success('操作成功!'));
    }
}