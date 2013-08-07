<?php


function d()
{
	foreach (func_get_args() as $var) {
		dump($var);
	}
}


function dd()
{
	call_user_func_array('d', func_get_args());
	die;
}


function td($values)
{
	echo '<table border=1 style="border-color:#DDD;border-collapse:collapse; font-family:Courier New; color:#222; font-size:13px" cellspacing=0 cellpadding=5>';

	$th = FALSE;
	foreach ($values as $key => $value) {
		if ( ! $th) {
			echo '<tr>';
			foreach ($value as $key2 => $value2) {
				echo '<th>' . $key2 . '</th>';
			}
			echo '</tr>';
		}
		$th = TRUE;

		echo '<tr>';
		foreach ($value as $key2 => $value2) {
			echo '<td>' . (is_array($value2) ? serialize($value2) : $value2) . '</td>';
		}
		echo '</tr>';
	}

	echo '</table>';
	die;
}
