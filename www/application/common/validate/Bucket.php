<?php
namespace app\common\validate;

use think\Validate;

class Bucket extends Validate
{
    protected $rule =   [
        'bucket_name'   =>  'require|length:2,50|unique:bucket',
        'bucket_domain'   =>  'require|length:2,500|unique:bucket',
        'bucket_style_thumb'    =>  'require|length:2,100',
        'bucket_style_original'    =>  'require|length:2,100',
        'bucket_style_water'    =>  'require|length:2,100',
        'bucket_style_fixwidth'    =>  'require|length:2,100',
        'bucket_style_fixheight'    =>  'require|length:2,100',
    ];
    
    protected $message  =   [
        'bucket_name.require'=>'空间名称不能空',
        'bucket_name.length'=>'空间名称长度在2至50之间',
        'bucket_name.unique'=>'同名空间已经存在',
        'bucket_domain.require'=>'域名不能空',
        'bucket_domain.length'=>'域名长度在2至500之间',
        'bucket_domain.unique'=>'相同域名已经存在',
        'bucket_style_thumb:require'=>'缩略图样式名必填',
        'bucket_style_thumb:length'=>'缩略图样式名长度在2至100之间',
        'bucket_style_thumb:require'=>'原图样式名必填',
        'bucket_style_thumb:length'=>'原图样式名长度在2至100之间',
        'bucket_style_thumb:require'=>'原图水印样式名必填',
        'bucket_style_thumb:length'=>'原图水印样式名长度在2至100之间',
        'bucket_style_thumb:require'=>'适应宽度样式名必填',
        'bucket_style_thumb:length'=>'适应宽度样式名长度在2至100之间',
        'bucket_style_thumb:require'=>'适应高度样式名必填',
        'bucket_style_thumb:length'=>'适应高度样式名长度在2至100之间',
    ];
    
    protected $scene = [
        'init'          =>  [],
        'edit'      =>  [
            'bucket_name'=>'require|length:2,50',
            'bucket_domain'=>'require|length:2,500',
            'bucket_style_thumb',
            'bucket_style_original',
            'bucket_style_water',
            'bucket_style_fixwidth',
            'bucket_style_fixheight'
        ],
     
    ];

}