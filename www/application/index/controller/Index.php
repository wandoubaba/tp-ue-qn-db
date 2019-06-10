<?php
namespace app\index\controller;
use app\index\controller\Base;

class Index extends Base
{
    public function index()
    {
    	$this->view->assign('pagetitle', 'Ueditor结合上传图片到七牛云的完整演示');
    	return $this->view->fetch('index/index');
    }
}
