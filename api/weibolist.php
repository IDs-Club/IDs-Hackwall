<?php

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );
require_once('db_lib.php');
$oDB = new db;

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

    $clientHA = new SaeTClientV2( WB_AKEY , WB_SKEY , $row_set[0]['access_token'] );
    $uid = $row_set[0]['userid'];

    $last_comment = 0;
    $last_statuse = 0;
    $last_comm_me = 0;
    $last_weibo	  = 0;
 
    $rs = $oDB->select("SELECT * FROM weibo_update WHERE weibo_uid = $uid ");
    if (mysqli_num_rows($rs)) 
    {
    	$row_set = array();
	    $i       = 0;
	    while ($row = mysqli_fetch_assoc($rs)) {
	        $row_set[$i]['last-comment']    = $row['last_comment'];
	        $row_set[$i]['last-statuse']    = $row['last_statuse'];
	        $row_set[$i]['last_comm_me']    = $row['last_comm_me'];
	        $row_set[$i]['last_weibo']    = $row['last_weibo'];
	        $i++;
	    }
	    
	    $last_comment = isset($row_set[0]['last-comment']) ? $row_set[0]['last-comment'] : 0;
		$last_statuse = isset($row_set[0]['last-statuse']) ? $row_set[0]['last-statuse'] : 0;
		$last_comm_me = isset($row_set[0]['last_comm_me']) ? $row_set[0]['last_comm_me'] : 0;
		$last_weibo = isset($row_set[0]['last_weibo']) ? $row_set[0]['last_weibo'] : 0;
    }

    header('Content-Type: application/json; charset=UTF-8');
	header('Access-Control-Allow-Origin: *');

	$time_line = $clientHA->user_timeline_by_id($uid, 1, 50, $last_weibo);
	$comments  = $clientHA->comments_to_me(1, 50, $last_comm_me);
	$commentsHA = $clientHA->comments_mentions(1, 50, $last_comment);
	$statusesHA = $clientHA->mentions(1, 50, $last_statuse);

	$tl = (isset($time_line["statuses"]) AND $time_line["statuses"]) ? $time_line["statuses"][0]['id'] : $last_weibo;
	$comm = (isset($commentsHA["comments"]) AND $commentsHA["comments"]) ? $commentsHA["comments"][0]['id'] : $last_comment;
	$lcm = (isset($comments["comments"]) AND $comments["comments"]) ? $comments["comments"][0]['id'] : $last_comm_me;
	$stat = (isset($statusesHA["statuses"]) AND $statusesHA["statuses"]) ? $statusesHA["statuses"][0]['id'] : $last_statuse;

	$rs = $oDB->select("SELECT * FROM weibo_update WHERE weibo_uid = $uid ");
	if (mysqli_num_rows($rs)) 
	{
		$oDB->update('weibo_update', "weibo_uid = $uid", "last_comment = $comm, last_weibo = $tl, last_comm_me = $lcm, last_statuse = $stat");
	}
	else
	{
		$oDB->insert('weibo_update', 'weibo_uid, last_comment, last_statuse, last_comm_me, last_weibo', "$uid, $comm, $stat, $lcm, $tl");
	}

	$result = array_map(function ($s) 
	{
		if (isset($s['retweeted_status'])) 
		{
			$origin['text'] = $s['retweeted_status']['text'];
			$origin['user'] = isset($s['retweeted_status']['user']) ? $s['retweeted_status']['user']['screen_name'] : '';
			$origin['thumb'] = isset($s['retweeted_status']['bmiddle_pic']) ? $s['retweeted_status']['bmiddle_pic'] : '';
			$origin['type'] = 1;
		}
		if (isset($s['status'])) 
		{
			$origin['text'] = $s['status']['text'];
			$origin['user'] = $s['status']['user']['screen_name'];
			$origin['type'] = 2;
		}
	    return array(
	    	'id'		=>  $s['id'],
	    	'image_url' =>  $s['user']['profile_image_url'],
	        'text'      =>  $s['text'],
	        'user'      =>  $s['user']['screen_name'],
	        'thumb'		=>  isset($s['bmiddle_pic']) ? $s['bmiddle_pic'] : '',
	        'origin'	=>  isset($origin) ? $origin : ''
	    );
	}, array_merge($commentsHA['comments'],$comments['comments'], $statusesHA['statuses'], $time_line['statuses']));

	$exists = array();
	$result = array_filter($result, function ($s) use (&$exists) {
	    if (in_array($s['text'], $exists)) {
	        return false;
	    }

	    list ($repost) = array_map('trim', explode('//', $s['text']));
	    if (empty($repost)) {
	        return false;
	    }

	    $exists[] = $s['text'];
	    return true;
	});

	$res = array_sort($result, 'id', 'desc');

	$r = array_slice($res, 0, 100);

	$r = array_reverse($r);
 
	echo json_encode(array_values($r));
}

function array_sort($arr,$keys,$type='asc')
{ 
	$keysvalue = $new_array = array();
	foreach ($arr as $k=>$v){
		$keysvalue[$k] = $v[$keys];
	}
	if($type == 'asc'){
		asort($keysvalue);
	}else{
		arsort($keysvalue);
	}
	reset($keysvalue);
	foreach ($keysvalue as $k=>$v){
		$new_array[$k] = $arr[$k];
	}
	return $new_array; 
}

