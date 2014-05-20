<?php
// $_POST['00']="xd";
// if(isset($_GET['time']))
// 	$_POST['time']=$_GET['time'];
// file_put_contents("xxx.log", print_r($_POST, true));
// header('Content-type: application/text');

require_once "debug.php";

if(isset($_POST['message']))
{
	$msg=json_decode($_POST['message']);
	deb('message received: '.$_POST['message'].' text: '.$msg->text);
	if ($msg->text[0]=='/')//user command catch
	{
		$end_of_first_word=1;
		for (;$end_of_first_word<strlen($msg->text)&&$msg->text[$end_of_first_word]!=' ';$end_of_first_word++)
			;
		$command=substr($msg->text, 1,$end_of_first_word-1);
		$parameter=substr($msg->text,$end_of_first_word);
		deb("command received: ".$msg->text." command: ".$command.strlen($msg->text),$D_Debug);
		if($command=='clear')
		{
			deb("cleaning besouse of command",$D_Info);
			$tmp=fopen("history.txt","w");
			fclose($tmp);
		}
		exit;
	}
	$data=json_decode(file_get_contents("history.txt"));
	$data->{'chat'}[]=json_decode($_POST['message']);
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

if(!isset($_GET['from_each']))
	exit;

// echo "<pre>";
// print_r($_GET);
// echo $_GET['time'];
// $file=file("history.txt");

$data=json_decode(file_get_contents("history.txt"));

// echo "<pre>";
// print_r($data);

$result=array();
$result['chat']=array();
if(!isset($data->{'chat'}))
{
	echo '{"chat":[],"count":0}';
	exit;
}
if ($data->{'size'}==0)
{
	$result['clear']=1;
}
for($i=$_GET['from_each']; $i<$data->{'size'}; ++$i)
	$result['chat'][]=$data->{'chat'}[$i];

// echo "<pre>";
// print_r($result);
// echo "</pre>";

$result['count']=count($result['chat']);
echo json_encode($result);

// foreach($file as &$line)
// {
// 	if(get_time($line)<$_GET['from_each'])
// 		continue;
// 	echo $line, "\n";
// 	// echo strtotime(get_date($line)), "\n";
// }
// echo "</pre>";
?>