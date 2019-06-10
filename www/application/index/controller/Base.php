<?php
namespace app\index\controller;

use app\common\controller\Base as CommonBase;
use think\Session;
use think\Request;

class Base extends CommonBase
{
    public function _initialize()
    {
        // 继承父类的_initialize()方法
        parent::_initialize();
        // 后台标题等参数
        $this->view->assign('title', '关于图片的Demo');
        $this->view->assign('minititle','Demo');
        $this->view->assign('version','0.0.1');
        $this->view->assign('keywords', 'Thinkphp,ueditor,七牛,图片,数据库');
        $this->view->assign('description', '在ThinkPHP5框架下引入Ueditor并实现向七牛云对象存储上传图片同时将图片信息保存到MySQL数据库，同时实现lazyload懒加载');
    }
}