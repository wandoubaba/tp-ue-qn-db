<?php
namespace app\common\controller;

use app\common\model\Res;
use think\Env;
use app\common\model\Bucket;

class QiniuService
{
	/**
	 * 向七牛云存储获取指定bucket的token
	 * @param  string $bucket [指定bucket名称]
	 * @return [type]         [description]
	 */
	private function get_token($bucket)
    {
    	$access_key = Env::get('qiniu.access_key');
    	$secret_key = Env::get('qiniu.secret_key');

    	$auth = new \Qiniu\Auth($access_key, $secret_key);
    	$upload_token = $auth->uploadToken($bucket);
    	return $upload_token;
    }

    private function generate_auth()
    {
    	$access_key = Env::get('qiniu.access_key');
    	$secret_key = Env::get('qiniu.secret_key');
    	$auth = new \Qiniu\Auth($access_key, $secret_key);
    	return $auth;
    }

    public function delete_file($bucket, $key)
    {
    	$res = new Res;

    	try {
			$auth = $this->generate_auth();
			$bucketManager = new \Qiniu\Storage\BucketManager($auth);

			$config = new \Qiniu\Config();
			$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
			$err = $bucketManager->delete($bucket, $key);
			// dump($err->getResponse('statusCode')->statusCode);
			/*
			HTTP状态码	说明
			298	部分操作执行成功
			400	请求报文格式错误
			包括上传时，上传表单格式错误。例如incorrect region表示上传域名与上传空间的区域不符，此时需要升级 SDK 版本。
			401	认证授权失败
			错误信息包括密钥信息不正确；数字签名错误；授权已超时，例如token not specified表示上传请求中没有带 token ，可以抓包验证后排查代码逻辑; token out of date表示 token 过期，推荐 token 过期时间设置为 3600 秒（1 小时），如果是客户端上传，建议每次上传从服务端获取新的 token；bad token表示 token 错误，说明生成 token 的算法有问题，建议直接使用七牛服务端 SDK 生成 token。
			403	权限不足，拒绝访问。
			例如key doesn't match scope表示上传文件指定的 key 和上传 token 中，putPolicy 的 scope 字段不符。上传指定的 key 必须跟 scope 里的 key 完全匹配或者前缀匹配；ExpUser can only upload image/audio/video/plaintext表示账号是体验用户，体验用户只能上传文本、图片、音频、视频类型的文件，完成实名认证即可解决；not allowed表示您是体验用户，若想继续操作，请先前往实名认证。
			404	资源不存在
			包括空间资源不存在；镜像源资源不存在。
			405	请求方式错误
			主要指非预期的请求方式。
			406	上传的数据 CRC32 校验错误
			413	请求资源大小大于指定的最大值
			419	用户账号被冻结
			478	镜像回源失败
			主要指镜像源服务器出现异常。
			502	错误网关
			503	服务端不可用
			504	服务端操作超时
			573	单个资源访问频率过高
			579	上传成功但是回调失败
			包括业务服务器异常；七牛服务器异常；服务器间网络异常。需要确认回调服务器接受 POST 请求，并可以给出 200 的响应。
			599	服务端操作失败
			608	资源内容被修改
			612	指定资源不存在或已被删除
			614	目标资源已存在
			630	已创建的空间数量达到上限，无法创建新空间。
			631	指定空间不存在
			640	调用列举资源(list)接口时，指定非法的marker参数。
			701	在断点续上传过程中，后续上传接收地址不正确或ctx信息已过期。
			 */
			if($err) {
				if($err->getResponse('statusCode')->statusCode==612) {
					// 指定资源不存在或已被删除
					$res->success('目标文件已不存在');
				} else {
					$res->failed($err->message());
				}
			} else {
				$res->success();
			}
		} catch (\Exception $e) {
			$res->failed($e->getMessage());
		}
		return $res;
    }

    /**
     * 向指定七牛云存储空间上传文件
     * @param  [type] $bucket [指定存储空间bucket名称]
     * @param  [type] $file   [需上传的文件]
     * @return [type]         [Res对象实例]
     */
    public function up_file($bucket, $file = null)
    {
    	$token = $this->get_token($bucket);
    	$res = new Res;
    	$res->data = '';
    	$res->result = ['token'=>$token];
    	if($file) {
	    	// 要上传图片的本地路径
	    	$file_path = $file->getRealPath();
	    	// 文件名后缀
	    	$ext = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);
	    	// 文件前缀（类似文件夹）
	    	$prefix = str_replace("-","",date('Y-m-d/'));
	    	// 上传后保存的文件名（无后缀）
	    	$file_name = uniqid();
	    	// 上传后的完整文件名（含前缀后缀）
	    	$key = $prefix.$file_name.'.'.$ext;
	    	// 域名
	    	$domain = Bucket::get(['bucket_name'=>$bucket])->bucket_domain;
	    	// 初始化UploadManager对象并进行文件上传
	    	$upload_manager = new \Qiniu\Storage\UploadManager();
	    	// 调用UploadManager的putFile方法进行文件上传
	    	list($ret, $err) = $upload_manager->putFile($token, $key, $file_path);
	    	if($err!==null) {
	    		$res->failed($err);
	    	} else {
	    		$res->success();
	    		// array_merge($res->result,['domain'=>$domain,'key'=>$ret['key'], 'hash'=>$ret['hash']]);
	    		$res->result['domain'] = $domain;
	    		$res->result['key'] = $ret['key'];
	    		$res->result['hash'] = $ret['hash'];
	    		$res->result['bucket'] = $bucket;
	    	}
	    } else {
	    	$res->failed('未接收到文件');
	    }
	    return $res;
    }

    /**
     * 从服务器传输文件到七牛云
     * @param  [type] $bucket    目标bucket
     * @param  [type] $file_path 要传输文件的服务器路径
     * @return [type]            res
     */
    public function transfer_file($bucket, $file_path)
    {
		// 构建鉴权对象
		$auth = $this->generate_auth();
		// 生成上传 Token
		$token = $auth->uploadToken($bucket);
		// 文件后缀
		$ext = pathinfo($file_path, PATHINFO_EXTENSION);
		// 文件前缀（类似文件夹）
	    $prefix = str_replace("-","",date('Y-m-d/'));
		// 上传到七牛后保存的文件名（不带后缀）
		$file_name = uniqid();
	    // 上传后的完整文件名（含前缀后缀）
	    $key = $prefix.$file_name.'.'.$ext;
	    // 域名
	    $domain = Bucket::get(['bucket_name'=>$bucket])->bucket_domain;
	    // return '.'.$file_path;
	 //    try {
		//     $file = fopen('.'.$file_path, 'rb');
		//     return '.'.$file_path.($file==false);
		// } catch (\Exception $e) {
		// 	return $e->getMessage();
		// }
	    $res = new Res;
	    try {
	    	// 初始化 UploadManager 对象并进行文件的上传。
			$uploadMgr = new \Qiniu\Storage\UploadManager();
			// 调用 UploadManager 的 putFile 方法进行文件的上传。
			list($ret, $err) = $uploadMgr->putFile($token, $key, '.'.$file_path);
			if ($err !== null) {
			    $res->failed();
			    $res->result['obj'] = $err;
			} else {
			    $res->success();
			    $res->result['obj'] = $ret;
			    $res->result['domain'] = $domain;
	    		$res->result['key'] = $ret['key'];
	    		$res->result['hash'] = $ret['hash'];
	    		$res->result['bucket'] = $bucket;
			}
	    } catch (\Exception $e) {
	    	$res->failed($e->getMessage());
	    }

	    return $res;		
    }

    /**
     * 获取七牛云指定bucket存储空间的文件列表
     * @param  [type]  $bucket [指定存储空间名称]
     * @param  string  $marker [上次列举返回的位置标记，作为本次列举的起点信息]
     * @param  string  $prefix [要列取文件的公共前缀]
     * @param  integer $limit  [本次列举的条目数]
     * @return [type]          [description]
     */
    public function list_file($bucket, $marker='', $prefix='', $limit=100)
    {
    	$auth = $this->generate_auth();
		$bucketManager = new \Qiniu\Storage\BucketManager($auth);
		// 要列取文件的公共前缀
		// $prefix = input('?prefix') ? input('prefix') : '';
		// // 上次列举返回的位置标记，作为本次列举的起点信息。
		// $marker = input('?marker') ? input('marker') : '';
		// // 本次列举的条目数
		// $limit = input('?limit') ? input('limit') : 20;
		$delimiter = '';
		// 列举文件
		list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);
		if ($err !== null) {

		    $result = $err;
		} else {
		    if (array_key_exists('marker', $ret)) {
		        echo "Marker:" . $ret["marker"] . "\n";
		    }

		    $result = $ret;
		}
		return $result;
    }
}