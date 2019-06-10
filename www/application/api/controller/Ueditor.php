<?php
namespace app\api\controller;

use app\api\controller\Base as ApiBase;
use app\common\model\Res;
use think\Image;
use think\Request;
use app\common\controller\PictureService;
use app\common\model\Bucket;
use app\common\model\Picture;
// use think\Session;

class Ueditor extends ApiBase
{
	private $uploadfolder='/upload/';   //上传地址

	private $scrawlfolder='/upload/_scrawl/';   //涂鸦保存地址

	private $catchfolder='/upload/_catch/';   //远程抓取地址

	private $configpath='/static/lib/ueditor/utf8-php/php/config.json';	//前后端通信相关的配置

	private $config;


	public function index(){
		$this->type=input('edit_type','');

        date_default_timezone_set("Asia/chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");

        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($_SERVER['DOCUMENT_ROOT'].$this->configpath)), true);
		$this->config=$CONFIG;

		$action = input('action');
		switch ($action) {
			case 'config':
				$result =  json_encode($CONFIG);
				break;
		
				/* 上传图片 */
			case 'uploadimage':
				$result = $this->_qiniu_upload();
				break;
				/* 上传涂鸦 */
			case 'uploadscrawl':
				$result = $this->_upload_scrawl();
				break;
				/* 上传视频，demo暂时没有实现，可以查看其他文章 */
			case 'uploadvideo':
				$result = $this->_upload(array('maxSize' => 1073741824,/*1G*/'exts'=>array('mp4', 'avi', 'wmv','rm','rmvb','mkv')));
				break;
				/* 上传文件，demo暂时没有实现，可以查看其他文章 */
			case 'uploadfile':
				$result = $this->_upload(array('exts'=>array('jpg', 'gif', 'png', 'jpeg','txt','pdf','doc','docx','xls','xlsx','zip','rar','ppt','pptx',)));
				break;
		
				/* 列出图片 */
			case 'listimage':
				$result = $this->_qiniu_list($action);
				break;
				/* 列出文件，demo暂时没有实现，可以查看其他文章 */
			case 'listfile':
				$result = $this->_list($action);
				break;		
				/* 抓取远程文件，demo暂时没有实现，可以查看其他文章 */
			case 'catchimage':
				$result = $this->_upload_catch();
				break;
		
			default:
				$result = json_encode(array('state'=> '请求地址出错'));
				break;
		}
		
		/* 输出结果 */
		if (isset($_GET["callback"]) && false ) {
			if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
				echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
			} else {
				echo json_encode(array(
						'state'=> 'callback参数不合法'
				));
			}
		} else {
			exit($result) ;
		}
	}
	private function _qiniu_upload($config=array())
	{
		$title = '';
		$url='';
		if(!empty($config)){
			$this->config=array_merge($this->config,$config);;
		}

		$file = request()->file('upfile');
		if($file){

			$picture = new Picture;
			// demo中暂时关闭关于admin的处理
			// $picture->admin_id = Session::has('admin_infor')?Session::get('admin_infor')->admin_id:'';
			$pservice = new PictureService;
			$res = $pservice->up_picture($file, $picture);
			if($res->status) {
				// bucket对应的域名				
				$url = $res->result['domain'];
				// 图片在bucket中的key
				$url .= $res->result['key'];
				// 默认插入水印板式
				$url .= '-'.Bucket::get(['bucket_name'=>$res->result['bucket']])->bucket_style_water;

				$title = $res->result['key'];
				$state = 'SUCCESS';
			}else{
				$state = $res->message();
			}
		}else{
			$state = '未接收到文件';
		}
		
		$response=array(
			"state" => $state,
			"url" => $url,
			"title" => $title,
			"original" =>$title,
		);
		return json_encode($response);
	}

	private function _upload_scrawl()
	{		
		$data = input('post.' . $this->config ['scrawlFieldName']);
        $url='';
        $title = '';
        $oriName = '';
		if (empty ($data)) {
			$state= 'Scrawl Data Empty!';
		} else {
			$pservice = new PictureService;
			// 在服务器保存图片文件
			$url = $pservice->up_scrawl('png', base64_decode($data), $this->scrawlfolder);
			if ($url) {
				$state = 'SUCCESS';
			} else {
				$state = 'Save scrawl file error!';
			}
		}
		$response=array(
		"state" => $state,
		"url" => $url,
		"title" => $title,
		"original" =>$oriName ,
		);
		return json_encode($response);
	}

	private function _qiniu_list($action)
	{
		/* 判断类型 */
		switch ($action) {
			/* 列出文件 */
			case 'listfile':
				$allowFiles = $this->config['fileManagerAllowFiles'];
				$listSize = $this->config['fileManagerListSize'];
				$prefix='/';
				break;
			/* 列出图片 */
			case 'listimage':
			default:
				$allowFiles = $this->config['imageManagerAllowFiles'];
				$listSize = $this->config['imageManagerListSize'];
				$prefix='/';
		}
		// 这里暂时没有用20190606
		$start = 0;
		// 准备文件列表
		$list = [];
		$picture = Picture::all();
		foreach($picture as $n=>$p) {
			$list[] = array(
				'url'=>$p->bucket->bucket_domain.$p->picture_key.'-'.$p->bucket->bucket_style_thumb,
				'title'=>$p->picture_name,
				'url_original'=>$p->bucket->bucket_domain.$p->picture_key.'-'.$p->bucket->bucket_style_water,
			);
		}
		/* 返回数据 */
		$result = json_encode(array(
			"state" => "SUCCESS",
			"list" => $list,
			"start" => $start,
			"total" => count($list)
		));
		return $result;
	}

	/**
	 * 遍历获取目录下的指定类型的文件
	 * @param string $path
	 * @param string $allowFiles
	 * @param array $files
	 * @return array
	 */
	function getfiles($path, $allowFiles, &$files = array())
	{
	    if (!is_dir($path)) return null;
	    if(substr($path, strlen($path) - 1) != '/') $path .= '/';
	    $handle = opendir($path);
	    while (false !== ($file = readdir($handle))) {
	        if ($file != '.' && $file != '..') {
	            $path2 = $path . $file;
	            if (is_dir($path2)) {
	                $this->getfiles($path2, $allowFiles, $files);
	            } else {
	                if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
	                    $files[] = array(
	                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
	                        // 'document_root'=> $_SERVER['DOCUMENT_ROOT'],
	                        // 'root_path'=> ROOT_PATH,
	                        // 'path2'=> $path2,
	                        // 'path'=> $path,
	                        // 'mtime'=> filemtime($path2)
	                    );
	                }
	            }
	        }
	    }
	    return $files;
	}
}