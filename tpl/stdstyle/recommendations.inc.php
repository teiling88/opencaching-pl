<?php
/***************************************************************************
		./tpl/stdstyle/recommendations.inc.php
		-------------------
		begin                : November 4 2005
		copyright            : (C) 2005 The OpenCaching Group
		forum contact at     : http://www.opencaching.com/phpBB2

   Unicode Reminder メモ

	***************************************************************************/

	$recommendation_line = '<tr>
				<td bgcolor="{bgcolor}">{treffer}%</td>
				<td bgcolor="{bgcolor}">&nbsp;</td>
				<td bgcolor="{bgcolor}"><a href="viewcache.php?cacheid={cacheid}">{cachename}</a></td>
			</tr>
			<tr><td class="spacer" colspan="3" bgcolor="{bgcolor}"></td></tr>
			';

	$norecommendations = '<tr><td colspan="3">No recommendations can be given, since no further evaluations are present.</td></tr>';

	$bgcolor1 = '#eeeeee';
	$bgcolor2 = '#e0e0e0';
?>
