<?php


function game_setName()
{
	global $CURRENT_URL;
	if (isset($_POST['setName']))
	{
		$p1 = $_POST['player1'];
		$p2 = $_POST['player2'];

		$_SESSION['playerNames'] = ['X' => $p1, 'O' => $p2];
		$_SESSION['lastwinner'] = null;
		header("Location:" .$CURRENT_URL);
	}
	$el = null;
	$el .= "<form method='post' id='game' class='form'>";
	$el .= "<input type='text' name='player1' value='". $_SESSION['playerNames']['X'] ."' placeholder='player1' class='field'/><br>";
	$el .= "<input type='text' name='player2' value='". $_SESSION['playerNames']['O'] ."' placeholder='player2' class='field'/><br>";
	$el .= "<input class='button' type='submit' name='setName' value='Save Name' /> || ";
	$el .= "<a class='button' href='$CURRENT_URL'>Return</a>";
	$el .= "</form>";
	return $el;
}


function game_resetBtn()
{
	$result = null;
	$resultValue = 'Start';
	$resetName = 'restart';
	if ($_SESSION['status'] == 'win' || $_SESSION['status'] == 'draw')
	{
		$resultValue = 'PlayAgain!';
	}
	elseif ($_SESSION['status'] == 'inprogress')
	{
		$resultValue = 'Resign';
		$resetName = 'resign';
	}
	if ($_SESSION['status'] !== 'awaiting')
	{
		$result = "<input type='submit' name=$resetName value=$resultValue id='resetBtn'>\n" ;
	}

	return $result;
}


function game_creatTable()
{
	global $CURRENT_URL;

	$element = null;
	$element .= "<div class='row title'>";
	$element .= "<div class='row'><b>";
		
	$lastwinner = ['X' => null, 'O' => null];
	$cup = '<i class="fa fa-trophy" aria-hidden="true"></i>';

	if ($_SESSION['lastwinner'] === 'O')
	{
		$lastwinner['O'] = $cup;
	}
	elseif ($_SESSION['lastwinner'] === 'X')
	{
		$lastwinner['X'] = $cup;
	}

	$activeClassX =null;
	$activeClassO =null;
	if ($_SESSION['playerNames']['O'] == 'computer')
	{
		$activeClassO = ' unactive';
	}
	else
	{
		$activeClassX = game_activeChecker('X');
		$activeClassO = game_activeChecker('O');
	}

	$element .= "<div class='span5". $activeClassX."'>". $lastwinner['X'] . $_SESSION['playerNames']['X'] ."(<span class='cX'>X</span>)</div>";
	$element .= "<div class='span2'>Ties</div>";
	$element .= "<div class='span5". $activeClassO."'>". $lastwinner['O'] . $_SESSION['playerNames']['O'] ."(<span class='cO'>O</span>)</div>";

	$element .= "</div>";
	$element .= "<div class='row'>";
	$element .= "<div class='span5'>".game_playerHistory('win_X')."</div>";
	$element .= "<div class='span2'>".game_playerHistory('draw')."</div>";
	$element .= "<div class='span5'>".game_playerHistory('win_O')."</div>";

	$element .= "</div>";
	
	$element .= "</b></div>";
	$element .= "<form method='post' id='game' class='form'>";

	foreach ($_SESSION['game'] as $cell => $value)
	{
		$classname = null;
		if ($value)
		{
			$classname = 'c'.$value;
		} 
		$element .= "		<input type='submit' class='cell $classname' name='cell$cell'  value='$value'";
		if ($value)
			$element .= " disabled";
		elseif (game_check_winner())
		{
			$element .= " disabled";
		}
		$element .=  ">\n";		
	}

	if ($_SESSION['status'] !== 'inprogress')
	{
		$element .= "<div class='otherbtn'><a href='$CURRENT_URL?action=showResult'>Show Result</a>";
		if ($_SESSION['playerNames']['O'] === 'computer')
		{
			$element .= "<br><a href='$CURRENT_URL?action=play2player'>2 Players</a>  ||  ";
			$element .= "<a href='?action=setName'>Player Names</a></div>";
		}
		elseif ($_SESSION['status'] === 'awaiting')
		{
			$element .= "<br><a href='$CURRENT_URL?action=play2Computer'>Vs. Computer</a>  ||  ";
			$element .= "<a href='?action=setName'>Player Names</a></div>";
		}
	}
	$element .= game_resetBtn();
	$element .= "</form>";

	return  $element;
}


?>