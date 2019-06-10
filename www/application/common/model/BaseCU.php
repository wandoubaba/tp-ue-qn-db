<?php
#
# 开启自动时间戳，不开启软删除的基础模型
# 支持create_time、update_time
# 不支持delete_time
#
namespace app\common\model;
use think\Model;

class BaseCU extends Model
{
	// 开启自动写入时间戳 如果设置为字符串 则表示时间字段的类型
	protected $autoWriteTimestamp = true;
}