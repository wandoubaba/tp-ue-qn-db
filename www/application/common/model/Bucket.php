<?php
namespace app\common\model;

use app\common\model\Base;

class Bucket extends Base
{
	public function picture()
    {
        return $this->belongsTo('Picture','bucket_name','bucket_name');
    }
}