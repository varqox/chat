<?php
var $debuglvl=5;
var $D_Debug_Verbose=5;
var $D_Debug=4;
var $D_Info=3;
var $D_Warning=2;
var $D_Error=1;
var $D_None=0;
function deb($co,$lvl=5)
{
	if($dubuglvl>=$lvl)
	{
		$handle=fopen("debug.txt","a");
		fwrite($handle, lvl);
		fwrite($handle, " - ");
		fwrite($handle, $co);
		fwrite($handle, "\n");
		fclose($handle);
	}
}

?>