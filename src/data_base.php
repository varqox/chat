<?php
require_once "debug.php";

class Container
{
	public $data;
	public $name;
	public $save;
}

class File_data
{
	public $correct;
	public $data;
}

function load_data($name,$save_on_unload)
{
	if($save_on_unload==true)
	{
		$lock=json_decode(file_get_contents($name.".lock"));
		$il=10;
		while($lock->{'locked'}==1&&$il>0)
		{
			$lock=json_decode(file_get_contents($name.".lock"));
			sleep(0.01);
			$il--;
		}
		if($lock->{'locked'}==1)
			deb("ignoring".$name.".lock",$D_Warning);
		$tmp=fopen($name.".lock","w");
		fclose($tmp);
		file_put_contents($name.".lock", json_encode($lock));
	}
	// $data['correct']=1;
	// $tmp=fopen($name,"w");
	// fclose($tmp);
	// file_put_contents($name, json_encode($data));
	$data=json_decode(file_get_contents($name));
	$cont=new Container;
	$cont->name=$name;
	$cont->save=$save_on_unload;
	if ($data->{'correct'}!=1)
	{
		deb("blad przy odczycie pliku bazy danych!",1);
		$data=json_decode(file_get_contents($name.".bak"));
		if($data->{'correct'}!=1)
		{
			deb("blad przy odczycie kopi zapasowej pliku bazy danych!",1);
			return 1;
		}
		else
		{
			deb("nadpisywanie oryginalnego pliku bazy danych",2);
			$tmp=fopen($name,"w");
			fclose($tmp);
			file_put_contents($name, json_encode($data));
			$cont->data=$data->{'data'};
		}
	}
	else
	{
		$cont->data=$data->{'data'};
	}
	return $cont;
}
function unload_data(&$cont)
{
	if($cont->save)
	{
		$new_data=new File_data;
		$new_data->{'correct'}=1;
		$new_data->{'data'}=$cont->data;
		$data=json_decode(file_get_contents($cont->name));
		$tmp=fopen($cont->name.".bak","w");
		fclose($tmp);
		file_put_contents($cont->name.".bak", json_encode($data));
		$to_write=json_encode($new_data);
		$tmpek=fopen($cont->name,"w");
		fwrite($tmpek, $to_write);
		fclose($tmpek);
		echo($to_write);
		file_put_contents($cont->name, $to_write);
	}
	$lock['locked']=0;
	$tmp=fopen($cont->name,"w");
	fclose($tmp);
	file_put_contents($cont->name.".lock", json_encode($lock));
}
?>
