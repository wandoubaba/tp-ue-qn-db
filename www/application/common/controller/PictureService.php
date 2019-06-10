<?php
namespace app\common\controller;

use app\common\controller\Base as CommonBase;
use app\common\model\Picture;
use app\common\model\Bucket;
use app\common\controller\QiniuService;
use app\common\model\Res;

class PictureService extends CommonBase
{
	/**
	 * 从数据库中找到第1个默认bucket
	 * @return [type] [description]
	 */
	private function default_bucket()
	{
		$bucket = new Bucket;
		// 向数据库查询bucket_default为1的记录
		$default_bucket = $bucket->where(['bucket_default'=>1])->find();
		// 如果没有bucket_default为1的记录，再尝试取第1条bucket记录
		if(!$default_bucket) {
			$default_bucket = $bucket->where('1=1')->find();
		}
		// 如果实在取不到，这里就算了，返回吧
		return $default_bucket;
	}

	public function up_picture($file, $picture)
	{
		$res = new Res;
		if(empty($picture->toArray()['bucket_name'])) {
			$bucket = $this->default_bucket();
			if($bucket) {
        		$picture->bucket_name = $this->default_bucket()->bucket_name;
        	} else {
        		$res->failed('无法获取bucket信息');
        		return $res;
        	}
		}
		if(empty($picture->toArray()['picture_name'])) {
	        $picture->picture_name = $file->getInfo('name');
	    }
	    if(empty($picture->toArray()['picture_description'])) {
        	$picture->picture_description = $picture->picture_name;
        }
        if($file) {
        	// 创建QiniuService对象实例
            $qservice = new QiniuService;
            try {
            	// 调用up_file方法向指定空间上传图片
                $res = $qservice->up_file($picture->bucket_name, $file);
                if($res->status) {
                	// 上传成功，写入数据库
                	$picture->picture_key = $res->result['key'];
                	//在我的项目中有一个自动生成全局唯一且递增ID的方法，但是demo中没做相关配置部分
                	//demo中将picture_id直接设置成自增ID了
                	//$picture->picture_id = $this->apply_full_global_id_str();
                	$res_db = new Res;
                	$res_db->data_row_count = $picture->isUpdate(false)->allowField(true)->save();
                	if($res_db->data_row_count) {
                		// 写入数据库成功
						$res_db->success();
						$res_db->data = $picture;
					}
					// 将写入数据库的结果作为返回结果的一个属性
					$res->result["db"] = $res_db;
                }
            } catch(\Exception $e) {
                $res->failed($e->getMessage());
            }
        }
        return $res;
	}

	public function up_scrawl($ext = null, $content = null, $path = null)
	{
		// 保存图片到服务器，取得服务器路径
		$file_path = $this->save_picture($ext, $content, $path);
		// 传输服务器图片到七牛云，取得返回的url
		$url = $file_path;
		$res = new Res;
		$picture = new Picture;
		$picture->bucket_name = $this->default_bucket()->bucket_name;
		$picture->picture_name = pathinfo($file_path, PATHINFO_BASENAME);
		$picture->picture_description = $picture->picture_name;
		try {
			$qservice = new QiniuService;
			// $url = $qservice->transfer_file($picture->bucket_name, $file_path);
			$res = $qservice->transfer_file($picture->bucket_name, $file_path);
			if($res->status) {
				// 保存数据库信息
				$picture->picture_key = $res->result['key'];
				//在我的项目中有一个自动生成全局唯一且递增ID的方法，但是demo中没做相关配置部分
                //demo中将picture_id直接设置成自增ID了
                // $picture->picture_id = $this->apply_full_global_id_str();
                $res_db = new Res;
            	$res_db->data_row_count = $picture->isUpdate(false)->allowField(true)->save();
            	if($res_db->data_row_count) {
            		// 写入数据库成功
					$res_db->success();
					$res_db->data = $picture;
				}
				// 将写入数据库的结果作为返回结果的一个属性
				$res->result["db"] = $res_db;
				// 准备url
				// bucket对应的域名				
				$url = $res->result['domain'];
				// 图片在bucket中的key
				$url .= $res->result['key'];
				// 默认插入水印板式
				$url .= '-'.Bucket::get(['bucket_name'=>$res->result['bucket']])->bucket_style_water;
			}
		} catch(\Exception $e) {
			$res->failed($e->getMessage());
			$url = '';
		}
		// 删除服务器图片
		unlink('.'.$file_path);
		// 返回的是七牛云上的url
		return $url;
	}
	
	/**
	 * 在服务器保存图片文件
	 * @param  [type] $ext     [description]
	 * @param  [type] $content [description]
	 * @param  [type] $path    [description]
	 * @return [type]          [description]
	 */
	private function save_picture($ext = null, $content = null, $path = null)
	{
		$full_path = '';
		if ($ext && $content) {
		    do {
		        $full_path = $path . uniqid() . '.' . $ext;
		    } while (file_exists($full_path));
		    $dir = dirname($full_path);
		    if (!is_dir($_SERVER['DOCUMENT_ROOT'].$dir)) {
		        mkdir($_SERVER['DOCUMENT_ROOT'].$dir, 0777, true);
		    }
		    file_put_contents($_SERVER['DOCUMENT_ROOT'].$full_path, $content);
		}
		return $full_path;
	}
}