<?php
    setlocale(LC_ALL, 'de_DE');

	require_once('libs/Smarty.class.php');
	require_once('libs/helper/connect2db.inc.php');
	require_once('libs/classes/buildReport.php');
	require_once('libs/helper/usermanagment.inc.php');
	require_once('libs/classes/scorecard.php');
	require_once('libs/classes/perspective.php');
	require_once('libs/classes/indicator.php');
	require_once('libs/classes/diagramm.php');



	// Hilfsvariablen
	$class_path		= './libs/classes/';
	$helper_path	= './libs/helper/';
	$server_path = 'http://';

	// Saeuberung der temporaeren Diagramme
	function Putzteufel()
	{
		$handle = openDir('temp');

		while ($file = readDir($handle))
		{
			if ($file != '..' && $file != '.')
			{
				if ( (time() - filemtime("temp/$file")) > 120 )
				{
					unlink("temp/$file");
				}
			}
		}

		closeDir($handle);
	}
?>
