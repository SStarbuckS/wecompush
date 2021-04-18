<?php
header("content-type:text/html;charset=utf-8");
date_default_timezone_set('PRC');

//如果不存在文本就禁止提交
if(!isset($_REQUEST['msg']))
{
  exit;
}

//获取发送数据数组
function getDataArray($MsgArray)
{
    $data = array(
        //要发送给的用户，@all为全部
        "touser" => "@all", 
        "toparty" => "@all", 
        "totag" => "@all", 
        "msgtype" => "mpnews", 
        //改成自己的应用id
        "agentid" => $MsgArray["agentid"], 
        "mpnews" => array(
            'articles' => array(
            array(
            //标题
            'title' => $MsgArray["title"],
            //缩略图
            "thumb_media_id" => "",
            //作者
            "author" => "",
            //链接
            'content_source_url' => $MsgArray["url"],
            //内容
            'content' => $MsgArray["msg"],
            //描述
            'digest' => $MsgArray["msg"],
            //分享
            "safe" => "2"
             )
            )
        )
    );
    return $data;
}


//curl请求函数，微信都是通过该函数请求
function https_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

/**
 * 开始推送
 */

//替换你的ACCESS_TOKEN
$ACCESS_TOKEN = json_decode(https_request("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=企业ID&corpsecret=应用的Secret"),true)["access_token"];
//模板消息请求URL
$url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$ACCESS_TOKEN;
$MsgArray=array();

//推送的应用id
$MsgArray["agentid"]="";

//标题是可选值
if(!isset($_REQUEST['title'])){
   $MsgArray["title"]="新提醒";
}
else{
   $MsgArray["title"]=$_REQUEST['title'];
}
//推送的文本内容
$MsgArray["msg"]=$_REQUEST['msg'];

//推送时间
$MsgArray["time"]=date('Y-m-d H:i:s',time());
$MsgArray["url"]="http://script.haokaikai.cn/Remind/msg.php?title=".$MsgArray["title"]."&time=".$MsgArray["time"]."&msg=".$MsgArray["msg"];
//转化成json数组让微信可以接收
$json_data = json_encode(getDataArray($MsgArray));
//echo $json_data;exit;
$res = https_request($url, urldecode($json_data));//请求开始
$res = json_decode($res, true);
if ($res['errcode'] == 0 && $res['errcode'] == "ok") {
    echo "发送成功!";
}
else{
     echo "发送失败!";
}
