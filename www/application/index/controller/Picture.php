<?php
namespace app\index\controller;

use app\index\controller\Base;
use app\common\model\Bucket as BucketModel;
use app\common\model\Picture as PictureModel;
use app\common\model\Res;
use think\Loader;
use think\Session;

class Picture extends Base
{
	public function index()
	{
		$this->view->assign('pagetitle', '图片管理');
		// 加载bucket列表
		$bucketlist = BucketModel::all(function($query) {
        	$query->order(['bucket_default'=>'desc', 'bucket_name'=>'asc']);
        });
        // 加载bucket里的图片
        $picturelist;
        // 遍历bucket
        foreach($bucketlist as $n=>$bucket) {
        	$picture = new PictureModel;
        	$picturelist = $picture
        		->where(['bucket_name'=>$bucket->bucket_name])
        		->order(['create_time'=>'desc'])
        		->select();
        	$bucketlist[$n]['child'] = $picturelist;
        }
        $this->view->assign('bucketlist', $bucketlist);
        return $this->view->fetch('picture/picture');
	}

	/**
	 * 加载添加图片页面
	 */
	public function add()
	{
		$this->view->assign('pagetitle', '上传图片');
		$bucket = input('?bucket') ? input('bucket') : '';
		$this->view->assign('bucket', $bucket);
		$bucketlist = BucketModel::all(function($query) {
        	$query->order(['bucket_default'=>'desc', 'bucket_name'=>'asc']);
        });
		$this->view->assign('bucketlist', $bucketlist);
		return $this->view->fetch('picture/picture_add');
	}

	/**
	 * 加载编辑图片页面
	 * @return [type] [description]
	 */
	public function edit()
	{
		$this->view->assign('pagetitle', '编辑图片信息');
		if(!input('?id')) {
			$this->error('参数错误');
			return;
		}
		$id = input('id');
		$picture = PictureModel::get($id);
		if(!$picture) {
			$this->error('参数错误');
			return;
		}
		$this->view->assign('picture', $picture);
		return $this->view->fetch('picture/picture_edit');
	}

	/**
	 * 执行编辑图片操作
	 * @return [type] [description]
	 */
	public function do_picture_edit()
	{
		$res = new Res;
		$res->data = input();
		$res->data['picture_protected'] = input('?picture_protected') ? 1 : 0;
		try {
			$picture = new PictureModel;
			$res->data_row_count = $picture->isUpdate(true)->allowField(true)->save([
				'picture_name'=>$res->data['picture_name'],
				'picture_description'=>$res->data['picture_description'],
				'picture_protected'=>$res->data['picture_protected']
			],['picture_id'=>$res->data['picture_id']]);
			if($res->data_row_count) {
				$res->success();
			}
		} catch (\Exception $e) {
			$res->faild($e->getMessage());
		}
		return $res;
	}

	/**
	 * 执行添加图片操作
	 * @return [type] [description]
	 */
	public function do_picture_add()
	{
		$res = new Res;
        $picture_file = request()->file('picture_file');
        $picture = new PictureModel;
        $picture->bucket_name = input('bucket_name');
        $picture->picture_name = input('picture_name')?:$picture_file->getInfo('name');
        $picture->picture_description = input('picture_description')?:$picture->picture_name;
        $picture->picture_protected = input('picture_protected');
        // 由于demo中没做登录部分，所以这里获取不到值
        // $picture->admin_id = Session::has('admin_infor')?Session::get('admin_infor')->admin_id:'';
        if($picture_file) {
        	// 创建PictureService对象实例
        	$pservice = new \app\common\controller\PictureService;
            try {
            	// 调用up_file方法向指定空间上传图片
                $res = $pservice->up_picture($picture_file, $picture);
            } catch(\Exception $e) {
                $res->failed($e->getMessage());
            }
        }
        return $res;
	}

	/**
	 * 执行删除图片的操作
	 * @return [type] [description]
	 */
	public function do_picture_delete()
	{
		$res = new Res;
		if(!input('?id')) {
			// 未取到id参数
			$res->failed('参数错误');
			return $res;
		}
		$id = input('id');
    	try {
    		$res->data = PictureModel::get($id);
    		if(!$res->data) {
    			// 取到的id参数没有对应的记录
    			$res->failed('参错错误');
    			return $res;
    		}
    		if($res->data['picture_protected']) {
    			$res->failed('不能删除受保护的图片');
    			return $res;
    		}
    		// 创建QiniuService对象实例
    		$qservice = new \app\common\controller\QiniuService;
    		// 调用delete_file方法删除指定bucket和指定key的文件
    		$res = $qservice->delete_file($res->data['bucket_name'], $res->data['picture_key']);
    		if($res->status) {
    			// 文件删除成功，开始删除数据
	    		PictureModel::where(['picture_id'=>$id])->delete();
	    		$res->append_message('<li>数据库记录删除成功</li>');
	    	}
    	} catch(\Exception $e) {
    		$res->failed($e->getMessage());
    	}
    	return $res;
	}

	/**
	 * 加载空间管理页面
	 * @return [type] [description]
	 */
    public function bucket()
    {
        $this->view->assign('pagetitle','存储空间');
        $bucketlist = BucketModel::all(function($query) {
        	$query->order(['bucket_default'=>'desc', 'bucket_name'=>'asc']);
        });
        $this->view->assign('list', $bucketlist);
        $this->view->assign('count', count($bucketlist));
        return $this->view->fetch('picture/bucket');
    }

    /**
     * 加载添加空间页面
     * @return [type] [description]
     */
    public function bucket_add()
    {
    	$this->view->assign('pagetitle', '添加存储空间');
    	return $this->view->fetch('picture/bucket_add');
    }

    /**
     * 执行添加空间操作
     * @return [type] [description]
     */
    public function do_bucket_add()
    {
		$res = new Res;
		$res->data = input();
		$res->data['bucket_default'] = input('?bucket_default') ? 1 : 0;
		$bucket = new BucketModel;
		$validate = Loader::validate('Bucket');
		if(!$validate->check($res->data)) {
			$res->failed($validate->getError());
			return $res;
		}
		if($res->data['bucket_default']) {
		$default = BucketModel::get(['bucket_default'=>1]);
			// 单独验证只可以有一条默认空间
			if($default) {
				$res->failed('只能有1个默认空间：已经存在默认空间'.$default->bucket_name);
				return $res;
			}
		}
		try {
			$res->data_row_count = $bucket->isUpdate(false)->allowField(true)->save([
				'bucket_name'		=>	$res->data['bucket_name'],
				'bucket_domain'		=>	$res->data['bucket_domain'],
				'bucket_description'=>	$res->data['bucket_description'],
				'bucket_default'=>	$res->data['bucket_default'],
				'bucket_style_thumb'=>	$res->data['bucket_style_thumb'],
				'bucket_style_original'=>	$res->data['bucket_style_original'],
				'bucket_style_water'=>	$res->data['bucket_style_water'],
				'bucket_style_fixwidth'=>	$res->data['bucket_style_fixwidth'],
				'bucket_style_fixheight'=>	$res->data['bucket_style_fixheight'],
			]);
			if($res->data_row_count) {
				$res->success();
			}
		} catch(\Exception $e) {
			$res->failed($e->getMessage());
		}
		return $res;
    }

    /**
     * 加载编辑空间页面
     * @return [type] [description]
     */
    public function bucket_edit()
    {
		$this->view->assign('pagetitle', '编辑存储空间');
		if(!input('?name')) {
			$this->error('参数错误');
			return;
		}
		$name = input('name');
		$bucket = BucketModel::get(['bucket_name'=>$name]);
		if(!$bucket) {
			$this->error('参数错误');
			return;
		}
		$this->view->assign('bucket', $bucket);
		return $this->view->fetch('picture/bucket_edit');
    }

    /**
     * 执行修改空间（描述）操作
     * @return [type] [description]
     */
    public function do_bucket_edit()
    {
    	$res = new Res;
		$res->data = input();
		$res->data['bucket_default'] = input('?bucket_default') ? 1 : 0;
		$validate = Loader::validate('Bucket');
		if(!$validate->scene('edit')->check($res->data)) {
			$res->failed($validate->getError());
			return $res;
		}
		$bucket = new BucketModel;
		if($res->data['bucket_default']) {
			$default = $bucket->where('bucket_default', 'eq', 1)->where('bucket_name','neq',$res->data['bucket_name'])->find();
			if($default) {
				$res->failed('只能有1个默认空间：已经存在默认空间'.$default->bucket_name);
				return $res;
			}
		}
		try {
			$res->data_row_count = $bucket->isUpdate(true)->allowField(true)->save([
				'bucket_domain'=>$res->data['bucket_domain'],
				'bucket_description'=>$res->data['bucket_description'],
				'bucket_default'=>$res->data['bucket_default'],
				'bucket_style_thumb'=>$res->data['bucket_style_thumb'],
				'bucket_style_original'=>$res->data['bucket_style_original'],
				'bucket_style_water'=>$res->data['bucket_style_water'],
				'bucket_style_fixwidth'=>$res->data['bucket_style_fixwidth'],
				'bucket_style_fixheight'=>$res->data['bucket_style_fixheight'],
			], ['bucket_name'=>$res->data['bucket_name']]);
			if($res->data_row_count) {
				$res->success();
			} else {
				$res->failed('未更改任何数据');
			}
		} catch(\Exception $e) {
			$res->failed($e->getMessage());
		}
		return $res;
    }

    /**
     * 执行删除空间（非默认）操作
     * @return [type] [description]
     */
    public function do_bucket_delete()
    {
    	$res = new Res;
		$name = input('?name') ? input('name') : '';
		$bucket = BucketModel::get(['bucket_name'=>$name]);
		$res->data = $bucket;
		if(empty($bucket)) {
			$res->failed("参数错误");
			return $res;
		}
		if($bucket->bucket_default==1) {
			$res->failed("默认空间不允许删除");
			return $res;
		}
		try {
			$res->data_row_count = BucketModel::where(['bucket_name'=>$name])->delete();	// 执行真删除
			$res->success();
		} catch(\Exception $e) {
			$res->failed($e->getMessage());
		}
		return $res;
    }
}
