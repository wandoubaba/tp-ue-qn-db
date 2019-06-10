<?php
namespace app\common\model;
use app\common\model\BaseCU;

class Picture extends BaseCU
{
	public function bucket()
    {
        return $this->hasOne('Bucket','bucket_name','bucket_name');
    }
}