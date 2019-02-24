<?php


function game_saveCookie($_cookieName, $_value = null)
{	
	$_value = json_encode($_value);
	setcookie($_cookieName, $_value, time() + (86400*365));
}


function game_save()
{
	if (isset($_SESSION['save']) && $_SESSION['save'] === true)
	{
		return ;
	}
	$_SESSION['game_time_end'] 	= time();
	$_SESSION['save'] = true;
	
	// delete the moves saved in last game
	game_saveCookie('game_save');

	game_saveResult();
	game_saveResult($_SESSION['playerNames']['X'], true);
	game_saveResult($_SESSION['playerNames']['O'], true);
	game_saveResult($_SESSION['playerNames']['X'].'-'. $_SESSION['playerNames']['O']);
}


function game_saveResult($_player = null, $_single = false)
{
	$_cookieprefix = 'game_detail';
	if ($_player)
	{
		$_cookieprefix .= '_' . $_player;
	}
	$new_value = [];
	$detail_list = ['count', 'win', 'lose', 'draw', 'resign', 'inprogress', 'total_time', 'total_move', 'total_move_win'];

	if (isset($_COOKIE[$_cookieprefix]))
	{
		$new_value = json_decode($_COOKIE[$_cookieprefix], true);
	}
	$new_value['player'] = $_player;

	foreach ($detail_list as $value)
	{
		if (!isset($new_value[$value]))
		{
			$new_value[$value] = 0;
		}
	}

	$new_value['count'] = $new_value['count'] + 1;

	$game_has_winner = game_check_winner();

	if ($game_has_winner)
	{
		if ($_SESSION['playerNames'][$game_has_winner] == $_player)
		{
			$new_value['win'] = $new_value['win'] + 1;
		}
		elseif($_single)
		{
			$new_value['lose'] = $new_value['lose'] + 1;
		}
		elseif ($_single === false)
		{
			if (!isset($new_value['win_'.$game_has_winner]))
			{
				$new_value['win_'.$game_has_winner] = 0;
			}
			$new_value['win_'.$game_has_winner] = $new_value['win_'.$game_has_winner] + 1;
		}
		else
		{
			$new_value['win'] = $new_value['win'] + 1;
			unset($new_value['lose']);
		}
	}
	elseif ($game_has_winner === false)
	{
		$new_value['draw'] = $new_value['draw'] + 1;
	}
	elseif ($_single && isset($_SESSION['status']) && $_SESSION['status'] === 'resign')
	{
		if ($_SESSION['playerNames'][$_SESSION['current']] == $_player)
		{
			$new_value['resign'] = $new_value['resign'] + 1;
		}
		else
		{
			$new_value['win'] = $new_value['win'] + 1;			
		}
	}
	else
		$new_value['inprogress'] = $new_value['inprogress'] + 1;

	$new_value['total_time'] = $new_value['total_time'] + ($_SESSION['game_time_end'] - $_SESSION['game_time_start']);
	$new_value['total_move'] = $new_value['total_move'] + ($_SESSION['game_move_x'] + $_SESSION['game_move_o']);

	if ($game_has_winner)
	{
		$new_value['total_move_win'] = $new_value['total_move_win'] + $_SESSION['game_move_'.strtolower($game_has_winner)];
	}

	if ($_single)
	{
		$new_value['type'] = 'single';
	}
	elseif ($_player)
	{
		$new_value['type'] = 'together';
	}
	else
	{
		$new_value['type'] = 'total';
	}

	if ($new_value['player'] === null)
	{
		unset($new_value['player']);
	}

	game_saveCookie($_cookieprefix, $new_value);
}


?>