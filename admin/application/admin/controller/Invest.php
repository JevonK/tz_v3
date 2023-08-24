<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2019 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://demo.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use library\Controller;
use think\Db;

/**
 * 已投项目管理
 * Class Item
 * @package app\admin\controller
 */
class Invest extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'LcInvest';

    /**
     * 已投项目管理
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '已投项目管理';
        $auth = $this->app->session->get('user');
        $status = $this->request->param('status', '');
        $where = '1 ';
        if (isset($auth['username']) and $auth['username'] != 'admin') {
            $where = "(u.system_user_id in (select uid from system_user_relation where parentid={$auth['id']}) or u.system_user_id={$auth['id']} )";
        }
        $query = $this->_query($this->table)->alias('i')->field('i.*,u.username,it.title_en_us as title');
        $query->where($where)->join('lc_user u','i.uid=u.id');
        $query->join('lc_item it','i.itemid=it.id')->like('it.title_zh_cn#it_title,u.username#u_username,i.status#status')->dateBetween('i.time#i_time')->order('i.id desc')->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data)
    {
    }

    /**
     * 投资暂停
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function set_pause() {
        $id = $this->request->param('id');
        $curr_time = time();
        $invest = Db::name($this->table)->where('id',$id)->find();
        if ($invest['pause_time'] > 0) {
            $diff_time = $curr_time - $invest['pause_time'];
            $time = strtotime($invest['time']) + $diff_time;
            $time2 = strtotime($invest['time2']) + $diff_time;
            $time2_actual = strtotime($invest['time2_actual']) + $diff_time;
            $update_data = [
                'pause_time' => 0,
                'time2_actual' => date('Y-m-d H:i:s', $time2_actual),
                'time2' => date('Y-m-d H:i:s', $time2),
            ];
            if ($invest['type'] == 1) {
                $update_data['time'] = date('Y-m-d H:i:s', $time);
            }
            Db::name($this->table)->where('id',$id)->update($update_data);
        }else {
            Db::name($this->table)->where('id',$id)->update(['pause_time' => $curr_time]);
        }
        $this->success(lang('think_library_form_success'), '');

    }
}
