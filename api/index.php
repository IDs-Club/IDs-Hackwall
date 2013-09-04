<?php
header('Content-Type:application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );
require_once('db_lib.php');
$oDB = new db;

// 获取当前选择的授权用户
$result = $oDB->select("SELECT * FROM weibo_binding WHERE if_use = 1 ");
if (mysqli_num_rows($result)) 
{
	$row_set = array();
    $i       = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $row_set[$i]['userid']    		= $row['weibo_uid'];
        $row_set[$i]['access_token']    = $row['weibo_token'];
        $i++;
    }

    $c = new SaeTClientV2( WB_AKEY , WB_SKEY , $row_set[0]['access_token'] );
    $weibo_uid = $row_set[0]['userid'];
    $user_message = $c->show_user_by_id($weibo_uid);//根据ID获取用户等基本信息
    if (isset($user_message['id'])) 
    {
    	$user = array(
			'id' => $user_message['id'], 
			'screen_name' => $user_message['screen_name'], 
			'followers_count' => $user_message['followers_count']
			);

		// 重设微博更新时间
		$oDB->update('weibo_update', "weibo_uid = $weibo_uid", "last_comment = 0, last_statuse = 0, last_comm_me = 0, last_weibo = 0 ");

		echo json_encode(array('status' => 'ok', 'user' => $user));
		exit();
    }	
}

$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

$code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );

echo json_encode(array('status' => 'unauthorized', 'code_url' => $code_url));
exit();