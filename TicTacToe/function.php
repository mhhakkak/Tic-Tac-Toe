<?php


require_once "function_draw.php";
require_once "function_game.php";
require_once "function_result.php";
require_once "function_save.php";

// start session
session_start();

$CURRENT_URL = "http://localhost/Projects/tictactoe/";
define('CURRENT_URL', $CURRENT_URL);

function game()
{
	global $CURRENT_URL;
	$el = null;

	// Start the game
	if (!isset($_SESSION['status']))
	{
		if (isset($_COOKIE['game_save']) && $_COOKIE['game_save'])
		{
			$el .= "<a href='$CURRENT_URL?action=new'>New Game</a>";
			$lastGame = json_decode($_COOKIE['game_save'], true);
			game_start($lastGame);			
		}
		else
		{
			game_start();
		}
	}
	elseif (isset($_POST['restart']))
	{
		game_save();
		game_start();
	}
	elseif (isset($_POST['resign']))
	{
		$_SESSION['status'] = 'resign';
		game_save();
		game_start();
	}
	else
	{
		game_turn();
	}

	if (isset($_GET['action']) && $_GET['action'] == 'setName')
	{
		$el .= game_setName();
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'showResult')
	{
		$el .= game_showResult();
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'play2Computer')
	{
		$_SESSION['playerNames']['O'] = 'computer';
		$_SESSION['lastwinner'] = 'X';
		$_SESSION['previous'] = 'O';
		$_SESSION['status'] = 'new';

		header("Location:" .$CURRENT_URL);
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'play2player')
	{
		$_SESSION['playerNames']['O'] = 'player2';
		$_SESSION['lastwinner'] = null;
		$_SESSION['status'] = 'new';

		header("Location:" .$CURRENT_URL);
	}
	elseif (isset($_GET['action']) && $_GET['action'] == 'new')
	{
		$_SESSION['status'] = 'new';
		game_save();
		game_start();
		header("Location:" .$CURRENT_URL);

	}
	else
	{
		$el .= game_winner();
		$el .= game_creatTable();
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		if ($_SESSION['status'] === 'inprogress' && $_SESSION['playerNames']['O'] == 'computer')
		{
			$_SESSION['game'][game_computerMove()] = $_SESSION['current'];
			$_SESSION['current'] = 'X';
			$el .= game_winner();
		}
		header("Location:" .$CURRENT_URL);
	}
	return $el;
}



function game_activeChecker($_player)
{
	if (isset($_SESSION['previous']) && $_SESSION['previous'] === $_player)
	{
		return ' unactive';
	}
	return null;
}



function game_checkNameChanged()
{
	if (strtolower($_SESSION['playerNames']['X']) !== 'player1' || strtolower($_SESSION['playerNames']['O']) !== 'player2')
	{
		return true;
	}
	return false;
}
?>
