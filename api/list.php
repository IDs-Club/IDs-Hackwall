<?php
	header('Content-Type:application/json; charset=UTF-8');
	header('Access-Control-Allow-Origin: *');
	require_once('db_lib.php');
	$oDB = new db;

	$method = isset($_GET['method']) ? $_GET['method'] : '';
	$item_id = isset($_GET['item_id']) ? $_GET['item_id'] : '';

	$login = FALSE;
	$result = $oDB->select("SELECT * FROM login_logs order by expiry_at desc");
	if (mysqli_num_rows($result)) 
	{
		$row_set = array();
	    $i       = 0;
	    while ($row = mysqli_fetch_assoc($result)) {
	        $row_set[$i]['member']    		= $row['member'];
	        $row_set[$i]['expiry_at']    	= $row['expiry_at'];
	        $i++;
	    }

	    if ($row_set[0]['expiry_at'] > time()) 
	    {
	    	$login = TRUE;
	    }		
	}

	if ( ! $method) 
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') 
		{
			$member = $_POST['email'];
			$pwd = $_POST['password'];
			$expiry_at = time() + 3600;
			if ($member == 'developer@idsclub.org' AND $pwd == '123123') 
			{
				$oDB->insert('login_logs', 'member, pwd, expiry_at', "'$member', '$pwd', $expiry_at ");
				echo json_encode(array('status' => 'ok'));
				exit();
			}
			else
			{
				echo json_encode(array('status' => 'error', 'msg' => 'Invalid login credentials.'));
				exit();
			}
		}
		if ($login) 
		{
			$result = $oDB->select("SELECT * FROM weibo_binding");
			if (mysqli_num_rows($result)) 
			{
				$row_set = array();
			    $i       = 0;
			    while ($row = mysqli_fetch_assoc($result)) {
			    	$row_set[$i]['id']				= $row['id'];
			    	$row_set[$i]['weibo_uid']		= $row['weibo_uid'];
			        $row_set[$i]['weibo_nick']    	= $row['weibo_nick'];
			        $row_set[$i]['if_use']			= $row['if_use'];
			        $i++;
			    }
			}
			echo json_encode(array('status' => 'ok', 'lists' => $row_set));
			exit();
		}
		echo json_encode(array('status' => 'error'));
		exit();
	}

	if ($method == 'cancel') 
	{
		if ($login AND $item_id) 
		{
			$result = $oDB->select("SELECT * FROM weibo_binding where id = $item_id");

			if (mysqli_num_rows($result)) 
			{
			    while ($row = mysqli_fetch_assoc($result)) {
			    	$id				= $row['id'];
			    	$weibo_uid		= $row['weibo_uid'];
			    }

			    $oDB->update('weibo_update', "weibo_uid = $weibo_uid", "last_comment = 0, last_statuse = 0");
			    $oDB->update('weibo_binding', "id = $id", "if_use = 0");
				echo json_encode(array('status' => 'ok'));
				exit();
			}
		}
		echo json_encode(array('status' => 'error', 'msg' => 'There may be something wrong, refresh the page and try again!'));
		exit();
	}

	if ($method == 'select') 
	{
		if ($login AND $item_id) 
		{
			$oDB->update('weibo_binding', "if_use = 1", "if_use = 0");
			$oDB->update('weibo_binding', "id = $item_id", "if_use = 1");
			echo json_encode(array('status' => 'ok'));
			exit();
		}
		echo json_encode(array('status' => 'error', 'msg' => 'There may be something wrong, refresh the page and try again!'));
		exit();
	}

	if ($method == 'update') 
	{
		if ($login AND $item_id) 
		{
			$oDB->update('weibo_update', "weibo_uid = $item_id", "last_comment = 0, last_statuse = 0, last_comm_me = 0, last_weibo = 0");
			echo json_encode(array('status' => 'ok'));
			exit();
		}
		echo json_encode(array('status' => 'error', 'msg' => 'There may be something wrong, refresh the page and try again!'));
		exit();
	}
