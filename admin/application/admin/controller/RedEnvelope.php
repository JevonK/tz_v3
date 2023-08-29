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
 * 红包管理
 * Class Item
 * @package app\admin\controller
 */
class RedEnvelope extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'LcRedEnvelope';

    /**
     * 红包列表
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
        $this->title = '红包列表';
        $auth = $this->app->session->get('user');
        $where = '';
        if (isset($auth['username']) and $auth['username'] != 'admin') {
            $where = "(f_user_id in (select uid from system_user_relation where parentid={$auth['id']}) or f_user_id={$auth['id']} )";
        }
        $query = $this->_query($this->table)->where($where);
        $query->order('id desc')->page();
    }

    /**
     * 表单数据处理
     * @param array $vo
     * @throws \ReflectionException
     */
    protected function _form_filter(&$vo){
        if ($this->request->isGet()) {
            $user = $this->app->session->get('user');
            if(!isset($vo['type'])) $vo['type'] = '1';
            if(empty($vo['code'])) $vo['code'] = uniqid();
            if(empty($vo['f_user_id'])) $vo['f_user_id'] = $user['id'];
        }
    }

    /**
     * 添加红包
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        // $this->title = '添加红包';
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑红包
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        // $this->title = '编辑红包';
        $this->_form($this->table, 'form');
    }

    /**
     * 删除红包
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        $this->applyCsrfToken();
        $this->_delete($this->table);
    }
    
    /**
     * 转盘配置
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function set()
    {
        $this->title = '转盘配置';
        $this->_form($this->set_table, 'set');
    }
    /**
     * 转盘配置修改
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function set_edit()
    {
        $this->_form($this->set_table, 'set_form');
    }
}
