/* 项目自定义admin模块下通用js */
/**
 * 实现htmlencode和htmldecode的js
 * @type {Object}
 * 用法：HtmlUtil.htmlEncode(value);
 */
var HtmlUtil = {
    /*1.用浏览器内部转换器实现html转码*/
    htmlEncode:function (html){
        //1.首先动态创建一个容器标签元素，如DIV
        var temp = document.createElement ("div");
        //2.然后将要转换的字符串设置为这个元素的innerText(ie支持)或者textContent(火狐，google支持)
        (temp.textContent != undefined ) ? (temp.textContent = html) : (temp.innerText = html);
        //3.最后返回这个元素的innerHTML，即得到经过HTML编码转换的字符串了
        var output = temp.innerHTML;
        temp = null;
        return output;
    },
    /*2.用浏览器内部转换器实现html解码*/
    htmlDecode:function (text){
        //1.首先动态创建一个容器标签元素，如DIV
        var temp = document.createElement("div");
        //2.然后将要转换的字符串设置为这个元素的innerHTML(ie，火狐，google都支持)
        temp.innerHTML = text;
        //3.最后返回这个元素的innerText(ie支持)或者textContent(火狐，google支持)，即得到经过HTML解码的字符串了。
        var output = temp.innerText || temp.textContent;
        temp = null;
        return output;
    },
};
$(function() {
	/**
	 * 实现表格中表头复选框的全选功能
	 */
	$("table thead th input:checkbox").on("click",function() {
		$(this).closest("table").find("tr > td:first-child input:checkbox").prop("checked", $("table thead th input:checkbox").prop("checked"));
	});
});
/*基于layer插件的弹出层实现*/
/*
	参数解释：
	title	标题
	url		请求的url
	id		需要操作的数据id
	w		弹出层宽度（缺省调默认值）
	h		弹出层高度（缺省调默认值）
*/
function layer_show(title,url,w,h){
	if (title == null || title == '') {
		title=false;
	};
	if (url == null || url == '') {
		url="404.html";
	};
	if (w == null || w == '') {
		w=($(window).width() - 50);
	};
	if (h == null || h == '') {
		h=($(window).height() - 50);
	};
	layer.open({
		type: 2,
		area: [w+'px', h +'px'],
		fix: false, //不固定
		maxmin: true,
		shade:0.4,
		title: title,
		content: url
	});
}

/*关闭弹出框口*/
function layer_close(){
	var index = parent.layer.getFrameIndex(window.name);
	parent.layer.close(index);
}
/**
 * 执行jquery的ajax请求，返回后台传回的结果
 * 依赖jquery和layer库
 * @param  {[type]} url  请求的url
 * @param  {[type]} data 需要向后台传递的数据
 * @return {[type]}      无返回值，如果后台执行成功则前台刷新页面
 */
function ajax_post(url,data)
{
	$.ajax({
        type: "POST",	// POST方法提交
        url: url,	// 执行的方法
        data: data,	// 
        dataType: "json",	// 数据类型为json
        success: function(result) {
        	// 当ajax请求执行成功时执行
            if (result.status == true) {
            	// 返回result对象中的status元素值为1表示数据插入成功
                layer.msg(result.message, {icon: 6, time: 2000});	// 使用H-ui的浮动提示框，2秒后自动消失
				setTimeout(function() {
					parent.location.reload();
				}, 2000);	//2秒后对父页面执行刷新（相当于关闭了弹层同时更新了数据）
            } else {
            	// 返回result对象的status值不为1，表示数据插入失败
            	layer.alert(result.message+"<p>请自行刷新页面</p>", {icon: 5});
            	// 页面停留在这里，不再执行任何动作
            }
        }
	});
}

/**
 * 执行ajax_post前增加一步用户确认操作
 * 依赖jquery和layer库
 * @param  {[type]} url      要请求的url
 * @param  {[type]} data     要上传的数据
 * @param  {[type]} action   在确认框中要显示的动作名称，如：删除
 * @param  {[type]} tar_name 在确认框中要显示的对象名称，一般传递object_name
 * @return {[type]}          无
 */
function ajax_post_confirm(url, data, tar_name, action) {
    // 用layer.confirm插件进行操作确认
    layer.confirm("确认要对 <span class='c-red'>"+tar_name+"</span> 进行 <span class='c-blue'>"+action+"</span> 操作吗？", function() {
        // 当layer.confirm被确认后开始提交操作
        // $.get(url, data, success)
        ajax_post(url, data);
    });
}

/**
 * 根据表格前的checkbox执行批量删除
 * @param  {[type]} url        [description]
 * @param  {[type]} checkbox_name 要求每一个checkbox的value属性都是对应记录的id
 * @return {[type]}            [description]
 */
function item_multidelete(url, checkbox_name) {
	// 定义一个数组用于保存已被选中的checkbox
	var ids = new Array();
	// 遍历名为to_multidelete[]的checkbox
	$("input[name='"+checkbox_name+"']").each(function() {
		if($(this).is(":checked")) {
			// 把被选中的checkbox的value值保存到module_ids中
			ids.push($(this).val());
		}
	});
	// 判断一下数组的长度，如果是0，则不用麻烦服务器了
	if(ids.length == 0) {
		layer.alert("没有选中任何数据", {icon:5});
	} else {
		// 确实有被选中的记录，页面进行一次确认
		layer.confirm("确定要删除这些数据吗？", function() {
			ajax_post(url, {ids: ids});
		});
	}
}


// iframe高度自适应
function iframe_height_fix(iframe_obj, plus) {
	var plusheight = plus<28 ? 28 : plus;
	iframe_obj.on("load",function() {
		iframe_obj.height(0);
		var mainheight = iframe_obj.contents().find("body").height();
		mainheight = mainheight<300 ? 300 : mainheight;
		iframe_obj.height(mainheight+plusheight);
	});
}

// 在iframe内关闭modal弹层
function modal_close() {
	window.parent.$('.modal').modal('hide');
}

/**
 * 创建并显示bootstrap模态框，id为automodal
 * @param  {[type]} title       		设置模态框显示的标题
 * @param  {[type]} url         		设置模态框要加载的url
 * @param  {[type]} iframe_height_plus 	让iframe高度在识别基础上增加多少
 * @return {[type]}			            [description]
 */
function modal_show_iframe(title, url, iframe_height_plus) {
	$("#automodal").remove();
	// 最外层节点.modal
	var modal_obj = $("<div></div>");
	modal_obj.attr({
		"id":"automodal",
		"role":"dialog",
		"aria-labelledby":"modalLabel"
	}).addClass("modal fade");
	// .modal-dialog节点
	var modal_dialog = $("<div></div>");
	modal_dialog.attr({"aria-hidden":"true"}).addClass("modal-dialog modal-lg");
	// .modal-content节点
	var modal_content = $("<div></div>");
	modal_content.addClass("modal-content");
	// .modal-header节点
	var modal_header = $("<div></div>");
	modal_header.addClass("modal-header");
	// 右上角关闭按钮节点，appendTo(modal_header)
	var close_button = $("<button></button>");
	close_button.attr({"type":"button","data-dismiss":"modal","aria-hidden":"true"}).addClass("close").append("<i class=\"fa fa-close\"></i>").appendTo(modal_header);
	// .modal-title节点，appendTo(modal_header)
	var modal_title = $("<h4></h4>");
	modal_title.attr({"id":"modalLabel"}).addClass("modal-title").appendTo(modal_header);
	// .modal_body节点
	var modal_body = $("<div></div>");
	modal_body.addClass("modal-body");
	// iframe节点，appendTo(modal_body)
	var iframe = $("<iframe></iframe>");
	iframe.attr({"width":"100%","frameborder":"0"}).appendTo(modal_body);
	// 把modal_header和modal_body节点append到modal_content中，再把modal_content节点appendTo(modal_dialog)
	modal_content.append(modal_header).append(modal_body).appendTo(modal_dialog);
	// 把modal_dialog节点appendTo(modal_obj)
	modal_dialog.appendTo(modal_obj);
	// 把modal_obj节点append到$("body")中
	$("body").append(modal_obj);
	// 设置模态框标题
	$(modal_obj).find(".modal-title").text(title);
	// 设置iframe要加载的url
	$(modal_obj).find("iframe").attr("src",url);
	// 根据加载内容调整iframe高度，并增加iframe_height_plus以做调整
	iframe_height_fix($(modal_obj).find("iframe"), iframe_height_plus);
	// 显示模态框
	$(modal_obj).modal("show");
}

/**
 * 取得指定名称的URL参数
 * @param {[type]} name [description]
 */
function GetQueryString(name)
{
     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
     var r = window.location.search.substr(1).match(reg);//search,查询？后面的参数，并匹配正则
     if(r!=null) {
     	return unescape(r[2]);
     }
     return null;
}