<?php


function game_showResult($_type = 'single')
{
	global $CURRENT_URL;

	$datatable = game_getResult();
	if (!$datatable)
	{
		return null;
	}
	// get the key's value
	$fields = array_keys($datatable[key($datatable)]);
	$result = null;
	$result .= '<ol id="resultTable">';

	$result .= '<div class="ttitle">';

	$ascParam = null;

	if (!isset($_GET['asc']))
	{
		$ascParam = '&asc=true';
	}	
	foreach ($fields as $key => $fieldName)
	{
		$result .= "<span  class='f_$fieldName'><a href='?action=showResult&by=$fieldName$ascParam'>".$fieldName.'</a></span>';
		
	}




	$result .= "</div>";

	foreach ($datatable as $playerName => $value)
	{
		$result .= "<li>";

		foreach ($fields as $key => $fieldName)
		{
			if ($fieldName === 'rank')
			{
				$value[$fieldName] = game_getRankName($value[$fieldName]);
			}
			$result .= "<span  class='f_$fieldName'>".$value[$fieldName].'</span>';
		}
		
		$result .= "</li>";
	}
	$result .= "</ol>";
	$result .= "<div class='row '>";
	$result .= "<a class='type' href='$CURRENT_URL?action=showResult&type=total'>Total</a> ...::|||::... ";
	$result .= "<a class='type' href='$CURRENT_URL?action=showResult&type=single'>Single</a> ...::|||::... ";
	$result .= "<a class='type' href='$CURRENT_URL?action=showResult&type=together'>Together</a><br><br><br>";
	$result .= "<a class='button' href='$CURRENT_URL'>Return</a>";
	$result .= "</div>";
	return $result;
}

function game_getResult($_type = 'single')
{
	$result = null;
	if (isset($_GET['type']))
	{
		$_type = $_GET['type'];
	}
	foreach ($_COOKIE as $key => $value)
	{
		if (strpos($key, 'game_detail') !== false)
		{
			$value = json_decode($value, true);
			if (isset($value['type']) && $value['type'] == $_type)
			{
				if (!isset($value['player']))
				{
					$value['player'] = "total";
				}

				$point			= $value['win']*3 + $value['draw']*1;
				$avrg_time 		= $value['total_time']/$value['count'];
				$avrg_time_move = $value['total_time']/$value['total_move'];
				$avrg_move_win 	= '-';
				
				if ($value['win']>0)
				{
					$avrg_move_win = $value['total_move_win']/$value['win'];
				}


				$result[$value['player']] = 
				[
					'player'			=> $value['player'],
					'count' 			=> $value['count'],
					'win' 				=> $value['win'],
					'lose' 				=> $value['lose'],
					'draw' 				=> $value['draw'],
					'resign' 			=> $value['resign'],
					'inprogress' 		=> $value['inprogress'],
					'avrg_time' 		=> round($avrg_time, 0),
					'avrg_move_win' 	=> round($avrg_move_win, 1),
					'avrg_time_move' 	=> round($avrg_time_move, 0),
					'point' 			=> $point,
					'rank' 				=> null,
				];

			}
		}
	}
	
	$result = game_getRank($result);
	$result = game_sortResult($result);
	return $result;
}

function game_sortResult($_datatable, $_by = null, $_desc = null)
{
	if ($_by === null && isset($_GET['by']))
	{
		$_by = $_GET['by'];
	}
	else
	{
		$_by = 'point';
	}
	if ($_desc === null)
	{
		if (isset($_GET['asc'])) 
		{	
			$_desc = false;
		}
		else
		{
			$_desc = true;
		}
	}
	$datatable_filterd = array_column($_datatable, $_by, 'player');

	if ($_desc)
	{
		// sort array desending
		arsort($datatable_filterd);
	}
	else
	{
		// sort array assending
		asort($datatable_filterd);
	}
	$_datatable = array_merge($datatable_filterd, $_datatable);
	return $_datatable;
}
function game_getRank($_datatable)
{
	$_datatable = game_sortResult($_datatable, 'point', true);
	$datatable_filterd = array_column($_datatable, 'point', 'player');
	$counter =0;
	$silver = null;
	$bronze = null;

	foreach ($datatable_filterd as $playerName => $point)
	{
		if ($counter <= count($datatable_filterd) * 0.05)
		{
			$_datatable[$playerName]['rank'] = 1;
		}
		elseif ($counter <= count($datatable_filterd) * 0.15 || !$silver)
		{
			$_datatable[$playerName]['rank'] = 2;
			$silver = true;
		}
		elseif ($counter <= count($datatable_filterd) * 0.30 || !$bronze)
		{
			$_datatable[$playerName]['rank'] = 2;
			$bronze = true;
		}
		else
		{
			$_datatable[$playerName]['rank'] = 4;
		}	
		$counter++;
	}

	$_datatable = game_updateRank($_datatable, 'avrg_time', false);
	$_datatable = game_updateRank($_datatable, 'avrg_move_win', false);
	$_datatable = game_updateRank($_datatable, 'avrg_time_move', false);
	return $_datatable;
}

function game_updateRank($_datatable, $_field, $_desc)
{
	$_datatable = game_sortResult($_datatable, $_field, $_desc);
	$datatable_filterd = array_column($_datatable, $_field, 'player');
	$counter = 0;

	foreach ($datatable_filterd as $playerName => $value)
	{
		if ($value && $counter <= count($datatable_filterd) * 0.05)
		{
			$_datatable[$playerName]['rank'] = $_datatable[$playerName]['rank'] -1;
		}
		$counter++;
	}
	return $_datatable;
}


function game_getRankName($_rank)
{
	if ($_rank <= 1)
	{
		$_rank = 'Gold';
	}
	elseif ($_rank == 2)
	{
		$_rank = 'Silver';
	}
	elseif ($_rank == 2)
	{
		$_rank = 'Bronze';
	}
	else
	{
		$_rank = ' - ';
	}
	
	return $_rank;
}
?>