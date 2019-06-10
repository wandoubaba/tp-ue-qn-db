<?php
#
# 操作结果模型，有状态，有结果，有信息，有行数，有数据
#
namespace app\common\model;

class Res
{
	
	/**
	 * 结果状态
	 * @var bool
	 */
	public $status;
	/**
	 * 结果标题，如“执行成功”
	 * @var [type]
	 */
	public $message_title;
	/**
	 * 结果内容，一般是错误信息
	 * @var [type]
	 */
	public $message;
	/**
	 * 采影响的数据行数
	 * @var int
	 */
	public $data_row_count;
	/**
	 * 输入的数据
	 * @var [type]
	 */
	public $data;

	public $result;

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		$this->status = false;
		$this->message_title = '';
		$this->message = '';
		$this->data_row_count = 0;
		$this->data = null;
		$this->result = [];
	}
	/**
	 * 设置失败结果
	 * @param  string $title [结果标题，默认为“操作失败”]
	 * @param  string $msg   [结果信息，默认为空字符]
	 * @return [type]        [description]
	 */
	public function failed($title='操作失败',$msg='')
	{
		$this->status = false;
		$this->message_title = $title;
		$this->message = $msg ? $msg : $title;
	}

	/**
	 * 设置成功结果
	 * @param  string $title [结果标题，默认为“操作成功”]
	 * @param  string $msg   [结果信息，默认为空字符]
	 * @return [type]        [description]
	 */
	public function success($title='操作成功',$msg='')
	{
		$this->status = true;
		$this->message_title = $title;
		$this->message = $msg ? $msg : $title;
	}

	/**
	 * 设置完整的结果
	 * @param [type] $status         [状态]
	 * @param [type] $message_title  [标题]
	 * @param [type] $message        [内容]
	 * @param [type] $data_row_count [记录数]
	 * @param [type] $data           [操作数据]
	 */
	public function set_res($status, $message_title, $message='', $data_row_count=0, $data=null)
	{
		$this->status = $status;
		$this->message_title = $message_title;
		$this->message = $message;
		$this->data_row_count = $data_row_count;
		$this->data = $data;
	}

	/**
	 * 向信息中添加内容
	 * @param  [type] $msg [要添加的内容]
	 * @return [type]      [description]
	 */
	public function append_message($msg)
	{
		$this->message .= $msg;
	}
}