<?php

/*
 * 一个最简单的微信公众账号后端示例
 * 当你订阅了此公众账号后，你可以与它聊天，你输入的文字信息，会被它完整的回显给你
 */

// 请把  'wxserver101'  换成你自己申请的微信公众账号
define("TOKEN", "wxserver101");

// 调试日志，可以从 BAE控制台上查看到此日志，用于调试
function mylog($msg) {
	file_put_contents("/home/bae/log/weiixn_server.log", $msg, FILE_APPEND);
}

function check_sign() {

	$sign = $_GET['signature'];
	$nonce = $_GET['nonce'];
	$timestamp = $_GET['timestamp'];
	
	$a = array($nonce, $timestamp, TOKEN);
	sort($a);
	$s1 = implode($a);
	$s2 = sha1($s1);

	if($s2 == $sign) {
		return true;
	}
	return false;
}

//首先按照微信的要求，进行 token 验证
if(false === check_sign()) {
	trigger_error("check failed");
	exit(0);
}

$echostr = $_GET['echostr'];
if($echostr) {
	// 如果 GET 请求中带有 'echostr'， 则该请求来自微信后台，直接返回 'echostr'
	echo $echostr;
	exit(0);
}

// 该请求来自于用户，获取 post data
$postdata = $HTTP_RAW_POST_DATA;
if(!$postdata) {
	mylog("no postdata");
	echo "no postdata!";
	exit(0);
}

// 将 post data 解析成 xml 对象
$xmlobj = simplexml_load_string($postdata, 'SimpleXMLElement', LIBXML_NOCDATA);
if(!xmlobj) {
	mylog("wrong postdata");
	echo "wrong postdata";
	exit(0);
}

// 从 xml 对象中获取具体的请求信息
$fromuser = $xmlobj->FromUserName;
$touser = $xmlobj->ToUserName;
$msgtype = $xmlobj->MsgType;

if('text' != $msgtype) {
	mylog("only support text message");
	echo "only support text message";
	exit(0);
} else {
	// 将请求的内容回显给用户
	$content = $xmlobj->Content;
	$retmsg = $content;
}

//$retxml = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]</Content></xml>";

// 按微信的要求构造响应的 xml 消息
$retxml = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]></Content><FuncFlag>0</FuncFlag></xml>";

$retstr = sprintf($retxml, $fromuser, $touser, time(), $retmsg);
mylog($retstr);
echo $retstr;

?>



