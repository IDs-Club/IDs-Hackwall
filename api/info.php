<?php
header('Content-Type:application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );
require_once('db_lib.php');
$oDB = new db;

$method = isset($_GET['type']) ? $_GET['type'] : '';

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

    if ($method == 'emotion') 
	{
		// 获取表情包
		$es = $c->emotions();
		foreach ($es as $val) 
		{
			$emotions[$val['phrase']] = $val['url'];
		}
		echo json_encode(array('status' => 'ok', 'emotions' => $emotions));
		exit();
	}

	if ($method == 'userinfo') 
	{
		$weibo_uid = $row_set[0]['userid'];
	    $user_message = $c->show_user_by_id($weibo_uid);//根据ID获取用户等基本信息
		$user = array(
			'id' => $user_message['id'], 
			'screen_name' => $user_message['screen_name'], 
			'followers_count' => $user_message['followers_count']
			);
		echo json_encode(array('status' => 'ok', 'user' => $user));
		exit();
	}
}

echo json_encode(array('status' => 'error'));
exit();