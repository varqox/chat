<?php
session_start();
// $_POST['00']="xd";
// if(isset($_GET['time']))
// 	$_POST['time']=$_GET['time'];
// file_put_contents("xxx.log", print_r($_POST, true));
// header('Content-type: application/text');

$size_after_cleaning=100;
$MAX_REQ_MSG=50;
$MAX_ACTIVE_USER_TIME=60;
$USER_TIME_REFRESH=5;

require_once "debug.php";
require_once "data_base.php";

$max_message_lenght=20*1024;

if(isset($_POST['message']))
{
	$msg=json_decode($_POST['message']);
	deb('message received: '.$_POST['message'].' text: '.$msg->text);
	if ($msg->user=="SYSTEM")
	{
		exit;	//TODO - return message: show 'Fuck you'
	}
	if ($msg->text[0]=='/')//user command catch
	{
		$end_of_first_word=1;
		for (;$end_of_first_word<strlen($msg->text)&&$msg->text[$end_of_first_word]!=' ';$end_of_first_word++)
			;
		$command=substr($msg->text, 1,$end_of_first_word-1);
		$parameter=substr($msg->text,$end_of_first_word);
		deb("command received: ".$msg->text." command: ",$D_Debug);//.$command.strlen($msg->text)
		if($command=='clean')
		{
			//deb("Thinking about cleaning",$D_Info);
			$msg_to_hist->user='SYSTEM';
			$msg_to_hist->date=$msg->date;
			$data_old=json_decode(file_get_contents("history.txt"));
			if ($data_old->{'size'}>$size_after_cleaning)
			{
				deb("cleaning becouse of command",$D_Info);
				$msg_to_hist->text="<div class=\"system_msg\">user: $msg->user cleaned the chat</div>";
				for ($i=$data_old->{'size'}-$size_after_cleaning;$i<$data_old->{'size'};$i++)
				{
					$data_new->{'chat'}[]=$data_old->{'chat'}[$i];
				}
				$data_new->{'chat'}[]=$msg_to_hist;
				$data_new->{'size'}=$size_after_cleaning+1;
				$tmp=fopen("history.txt","w");
				fclose($tmp);
				file_put_contents("history.txt", json_encode($data_new));
			}
			else
			{
				deb("cleaning insued, but nothing to clean",$D_Info);
				$msg_to_hist->text="user: $msg->user tried to clean the chat, but there are not anought messages";
				$data_new=$data_old;
				$data_new->{'chat'}[]=$msg_to_hist;
				$data_new->{'size'}++;
				$tmp=fopen("history.txt","w");
				fclose($tmp);
				file_put_contents("history.txt", json_encode($data_new));
			}
		}
		exit;
	}
	$data=json_decode(file_get_contents("history.txt"));
	$msg=json_decode($_POST['message']);

	if(strlen($_POST['message'])>$max_message_lenght)
	{
		$ilod=strlen($_POST['message'])-$max_message_lenght;
		deb("zadlugie dane obcinam z:".strlen($_POST['message'])." o:".$ilod);
		$msg->text=substr($msg->text,0,strlen($msg->text)-$ilod);
		$msg->text.='...';
	}
	$data->{'chat'}[]=$msg;
	++$data->{'size'};
	if($data == '')
	{
		$tmp = fopen("history.txt", "w");
		fclose($tmp);
	}
	file_put_contents("history.txt", json_encode($data));
	// file_put_contents("history.txt", $_POST['message'], FILE_APPEND);
	exit;
}

// function get_time($str)
// {
// 	$out="";
// 	for($i=0; $i<strlen($str); ++$i)
// 	{
// 		if($str[$i]==' ')
// 			break;
// 		$out.=$str[$i];
// 	}
// return $out;
// }

if(!isset($_GET['what']))
	exit;

// echo "<pre>";
// print_r($_GET);
// echo $_GET['time'];
// $file=file("history.txt");
$what=$_GET['what'];
$user="tets_user";//$_SESSION['user'];
$sid=session_id();
$act_u_cont=load_data("data/active_users.txt",false);
$il=0;
$refresh=false;
$is_in_active_users=false;
foreach ($act_u_cont->data as &$val)
{
	if($val->{'time'}-time()>$MAX_ACTIVE_USER_TIME)
		$il++;
	if($val->{'sid'}==$sid&&$val->{'time'}>$USER_TIME_REFRESH)
		$refresh=true;
	if($val->{'sid'}==$sid)
		$is_in_active_users=true;
}
if($is_in_active_users==false)
	$refresh=true;
if($il>0)
	$refresh=true;
if($refresh)
{
	$u_r_cont=load_data("data/active_users.txt",true);
	$new_data=array();
	foreach ($u_r_cont->data as &$val)
	{
		if($val->{'sid'}==$sid)
		{
			$val->{'time'}=time();
			$new_data[]=$val;
		}
		else if($val->{'time'}-time()<$MAX_ACTIVE_USER_TIME)
			$new_data[]=$val;
	}
	$val=array();
	$val['time']=time();
	$val['sid']=$sid;
	$val['name']=$user;
	if($is_in_active_users==false)
		$new_data[]=$val;
	$u_r_cont->data=$new_data;
	unload_data($u_r_cont);
}
switch ($what)
{
	case 0:	//GET_NEW
		if(!isset($_GET['from_each']))
		{
			echo 'BAD REQUEST';
			exit;
		}
		$data=json_decode(file_get_contents("history.txt"));
		$result=array();
		$result['chat']=array();
		//deb('data size: '.$data->{'size'});
		if(!isset($data->{'chat'}))
		{
			echo '{"chat":[],"count":0,"clear":1}';
			deb('sanding empty info');
			exit;
		}
		if($data->{'size'}-$_GET['from_each']<=$MAX_REQ_MSG)
			$result['begining']=$_GET['from_each'];
		else
			{
				$result['begining']=$data->{'size'}-$MAX_REQ_MSG;
				$result['clear']=1;
			}
		for($i=$result['begining']; $i<$data->{'size'}; ++$i)
			$result['chat'][]=$data->{'chat'}[$i];
		if($_GET['from_each']>$data->{'size'})
		{
			$result['clear']=1;
			$i=0;
			if($data->{'size'}-$i>$MAX_REQ_MSG)
				$i=$data->{'size'}-$MAX_REQ_MSG;
			$result['begining']=$i;
			for(; $i<$data->{'size'}; ++$i)
			$result['chat'][]=$data->{'chat'}[$i];
		}

		$result['count']=count($result['chat']);
		echo json_encode($result);
	break;
	case 1: //GET_OLD
		if(!isset($_GET['end'])||!isset($_GET['number'])||$_GET['number']>$MAX_REQ_MSG)
		{
			echo 'BAD REQUEST';
			exit;
		}
		$data=json_decode(file_get_contents("history.txt"));
		if($_GET['end']>$data->{'size'})
		{
			echo 'BAD REQUEST';
			exit;
		}
		$result=array();
		$result['chat']=array();
		$result['count']=0;
		for ($i=$_GET['end'];($i>=$_GET['end']-$_GET['number'])&&$i>0;$i--)
		{
			$result['chat'][]=$data->{'chat'}[$i];
			$result['count']++;
		}
		echo json_encode($result);
	case 2: //GET_USERS
		$result=array();
		foreach ($act_u_cont->data as &$val)
		{
    		$result['name'][]=$val['name'];
    		$result['time'][]=$val['time']-time();
		}
		echo json_encode($result);
		unload_data($act_u_cont);
	break;
}

// foreach($file as &$line)
// {
// 	if(get_time($line)<$_GET['from_each'])
// 		continue;
// 	echo $line, "\n";
// 	// echo strtotime(get_date($line)), "\n";
// }
// echo "</pre>";
?>
