<?php
header('Content-Type: text/html; charset=UTF-8');

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

if (isset($_REQUEST['code'])) {
	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = WB_CALLBACK_URL;
	try {
		$token = $o->getAccessToken( 'code', $keys ) ;
	} catch (OAuthException $e) {
	}
}

if ($token) {
	require_once('db_lib.php');
	$oDB = new db;
	$uid = $token['uid'];
	$weibo_token = $token['access_token'];

	$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $token['access_token'] );
    $user_message = $c->show_user_by_id($uid);//根据ID获取用户等基本信息
    $weibo_nick = $user_message['screen_name'];

    $oDB->update('weibo_binding', "if_use = 1", "if_use = 0");

	$result = $oDB->select("SELECT * FROM weibo_binding WHERE weibo_uid = $uid ");
	if (mysqli_num_rows($result)) 
	{
		$oDB->update('weibo_binding', "weibo_uid = $uid", "weibo_nick = '$weibo_nick', weibo_token = '$weibo_token', if_use = 1");
	}
	else
	{
		$oDB->insert('weibo_binding', 'weibo_uid, weibo_nick, weibo_token, if_use', "$uid, '$weibo_nick', '$weibo_token', 1");
	}	
?>
授权完成,<a href="http://event.idsclub.org">进入你的微博列表页面</a><br />
<?php
} else {
?>
授权失败。
<?php
}
?>
