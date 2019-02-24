<?php


function game_start($_lastGame = null)
{
	if ($_lastGame && is_array($_lastGame))
	{
		$_SESSION['game'] = $_lastGame['game'];
		$_SESSION['playerNames'] = $_lastGame['playerNames'];
		$_SESSION['lastwinner'] = $_lastGame['lastwinner'];
		$_SESSION['game_move_x'] = $_lastGame['game_move_x'];
		$_SESSION['game_move_o'] = $_lastGame['game_move_o'];
		$_SESSION['previous'] = $_lastGame['previous'];
	}
	else
	{
		$_SESSION['game'] =
		[
			1 => null,
			2 => null,
			3 => null,
			4 => null,
			5 => null,
			6 => null,
			7 => null,
			8 => null,
			9 => null,
		];
		$_SESSION['game_move_x'] = 0;
		$_SESSION['game_move_o'] = 0;
		
	}
	$_SESSION['status'] = 'awaiting';
	$_SESSION['save'] = false;

	if (!isset($_SESSION['playerNames']))
	{
		$_SESSION['playerNames'] = [ 'X' => 'Player1', 'O' =>'Player2'];
	}

	if (isset($_SESSION['lastwinner']))
	{
		$_SESSION['current'] = $_SESSION['lastwinner'];
	}
	else
	{
		$randPlayer = array_rand($_SESSION['playerNames'], 1);
		$_SESSION['current'] = $randPlayer;
		$_SESSION['lastwinner'] = null;
	}
	$_SESSION['game_time_start'] = time();

}


function game_turn()
{
	if ($_SESSION['status'] == 'win' || $_SESSION['status'] == 'draw')
	{
		return null;
	}

	foreach ($_SESSION['game'] as $cell => $value)
	{
		if (isset($_POST['cell'.$cell]))
		{
			$_SESSION['status'] 		= 'inprogress';
			if ($_SESSION['game'][$cell] === null) 
			{
				$_SESSION['game'][$cell] = $_SESSION['current'];
				$_SESSION['previous'] = $_SESSION['current'];
				if ($_SESSION['current'] === 'X')
				{
					$_SESSION['game_move_x'] = $_SESSION['game_move_x'] +1;
					$_SESSION['current'] = 'O';
				}
				else
				{	
					$_SESSION['game_move_o'] = $_SESSION['game_move_o'] +1;
					$_SESSION['current'] = 'X';
				}	
			}

		}
	}
	if ($_SESSION['status'] === 'inprogress')
	{
		$game_save_array = 
		[
			'game' => $_SESSION['game'],
			'playerNames' => $_SESSION['playerNames'],
			'game_move_x' => $_SESSION['game_move_x'],
			'game_move_o' => $_SESSION['game_move_o'],
			'lastwinner' => $_SESSION['lastwinner'],
			'previous' => $_SESSION['previous']
		];
		game_saveCookie('game_save', $game_save_array);
	}
}


function game_computerMove()
{
	$emptycells = null;
	foreach ($_SESSION['game'] as $cell => $value)
	{
		if (!$value)
		{
			$emptycells[] = $cell;
		}
	}

	$computerMove = array_rand($emptycells,1);
	$computerMove = $emptycells[$computerMove];
	return $computerMove;
}


function game_check_winner()
{
	// var_dump($_SESSION['previous']);
	// var_dump($_SESSION['current']); echo "<br>";
	$g = $_SESSION['game'];
	$winner = null;
	if
	(
		($g[1] && $g[1] == $g[2] && $g[2] == $g[3]) || 
		($g[4] && $g[4] == $g[5] && $g[5] == $g[6]) || 
		($g[7] && $g[7] == $g[8] && $g[8] == $g[9]) || 

		($g[1] && $g[1] == $g[4] && $g[4] == $g[7]) || 
		($g[2] && $g[2] == $g[5] && $g[5] == $g[8]) || 
		($g[3] && $g[3] == $g[6] && $g[6] == $g[9]) || 

		($g[1] && $g[1] == $g[5] && $g[5] == $g[9]) || 
		($g[3] && $g[3] == $g[5] && $g[5] == $g[7]) 
	)
		$winner = $_SESSION['previous'];

	elseif(!in_array(null,$g))
	{
		$winner = false;
	}

	return $winner;
}


function game_winner()
{
	if ($_SESSION['status'] === 'awaiting')
	{
		return null;
	}

	$result = null;
	$el_changeName = null;
	$winner = game_check_winner();

	if ($winner)
	{
		if (game_checkNameChanged())
		{
			game_save();
		}
		else
		{
			// $el_changeName = "<p><a href='?action=setName'>Do you want to save your name?</a></p>";
		}

		$_SESSION['lastwinner'] = $winner;
		$winner = $_SESSION['playerNames'][$winner];
		$_SESSION['status'] = 'win';
		$result = "		<div id='result'>$winner wins!$el_changeName</div>\n" ;
	}
	elseif($winner === false)
	{
		if (game_checkNameChanged())
		{
			game_save();
		}
		else
		{
			// $el_changeName = "<p><a href='?action=setName'>Do you want to save your name?</a></p>";
		}

		$_SESSION['status'] = 'draw';
		$_SESSION['lastwinner'] = null;
		$result = "		<div id='result'>Draw $el_changeName</div>\n" ;
	}
	else
	{
		$_SESSION['status'] = 'inprogress';
	}
	 return $result;
}


function game_playerHistory($_needle)
{
	$cookieName = 'game_detail_'. $_SESSION['playerNames']['X'].'-'.$_SESSION['playerNames']['O'];
	$history = null;
	if (isset($_COOKIE[$cookieName]))
	{
		$history = $_COOKIE[$cookieName];
		$history = json_decode($history, true);
	}
	if (isset($history[$_needle]))
	{
		$history = $history[$_needle];
	}
	else
	{
		$history = '-';
	}
	return $history;
}

?>