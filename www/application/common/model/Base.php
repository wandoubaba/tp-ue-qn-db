<?php
#
# 关闭自动时间戳，不开启软删除的基础模型
# 不支持create_time、update_time、delete_time
#
namespace app\common\model;
use think\Model;

class Base extends Model
{
	// 关闭自动写入时间戳
	protected $autoWriteTimestamp = false;
}