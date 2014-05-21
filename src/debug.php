<?php
$debuglvl=4;
$D_Debug_Verbose=5;
$D_Debug=4;
$D_Info=3;
$D_Warning=2;
$D_Error=1;
$D_None=0;

function deb($co,$lvl=5)
{
	global $debuglvl;
	if($debuglvl>=$lvl)
	{
		$handle=fopen("debug.txt","a");
		fwrite($handle, $lvl);
		fwrite($handle, " - ");
		fwrite($handle, $co);
		fwrite($handle, "\n");
		fclose($handle);
	}
}

?>