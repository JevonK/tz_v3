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
 * 抽奖指定管理
 * Class Item
 * @package app\admin\controller
 */
class DrawAppoint extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'LcDrawAppoint';

    /**
     * 抽奖指定列表
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
        $this->title = '抽奖指定列表';
        $query = $this->_query($this->table)->alias('i')->field('i.*,d.title_zh_cn as title,img,u.username, d.type as dtype');
        $query->join('lc_draw_prize d','i.draw_prize_id=d.id')->join('lc_user u','i.uid=u.id')->equal('d.type#i_type')->like('u.username#u_username')->dateBetween('i.created_at#i_time')->order('i.id desc')->page();
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
     * 表单数据处理
     * @param array $vo
     * @throws \ReflectionException
     */
    protected function _form_filter(&$vo){
        if ($this->request->isGet()) {
            $vo['users'] = Db::table('lc_user')->field('id, username')->select();
            $vo['prizes'] = Db::table('lc_draw_prize')->field('id, title_zh_cn')->select();
        }
    }
    /**
     * 添加指定
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        // $this->title = '添加奖品';
        $this->_form($this->table, 'form');
    }


    /**
     * 删除抽奖指定
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        $this->applyCsrfToken();
        $this->_delete($this->table);
    }
}
