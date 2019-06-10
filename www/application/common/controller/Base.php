<?php
namespace app\common\controller;

use think\Controller;

class Base extends Controller
{
    public function _initialize()
	{
		// 继承父类的_initialize()方法
		parent::_initialize();
	}

    /**
     * 申请一个global_id并将现有global_id值增加step
     * @param  integer $section     中间需要加的区代码
     * @param  integer $sn_length   序号字符串长度，左补0，默认为2
     * @param  integer $step        序号需要增长的步长
     * @return string               字符串格式为：[时间戳].[section].[sn]
     */
    protected function apply_full_global_id_str($section=0, $sn_length=2, $step=1)
    {
        /*
        字符串格式为：[时间戳].[section].[sn]
         */
        $section_str = $section==0 ? '' : str_pad($section,6,'0',STR_PAD_LEFT); //section长6个数字
        $sn_str = str_pad($this->apply_global_sn($step),$sn_length,'0',STR_PAD_LEFT);   //序号长6个数字
        $full = time().$section_str.$sn_str;
        return $full;
    }

    /**
     * 取得当前系统全局ID的数值
     * @param  integer $step 增长步长
     * @return [type]        返回ID数值
     */
    protected function apply_global_sn($step=1)
    {
        $config = ConfigModel::get(['config_key'=>'max_global_id']);
        if(!$config) {
            // 没取到，调用set_config创建配置项
            $this->set_config('max_global_id', 0, true, '当前ID序号', 0);
            $config = ConfigModel::get(['config_key'=>'max_global_id']);
        }
        // 根据update_time和当前时间戳的比较判断ID是否需要改变
        if($config->getData()['update_time']==time()) {
            // update_time和当前时间戳在同一秒，需要改变ID
            $config->config_value+=$step;
        } else {
            // update_time和当前时间戳不在同一秒，将序号复位到1
            $config->config_value = 1;
        }
        // 强行更新update_time
        $config->update_time = time();
        $config->save();
        // 返回ID数值
        return $config->config_value;
    }
}
