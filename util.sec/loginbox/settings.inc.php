<?php
  $loginbox_loggedin = tr('logged_as').' <a href="http://www.opencaching.pl/myhome.php">{username}</a> - <a href="http://www.opencaching.pl/logout.php">'.$language[$lang]['logout'].'</a>';
  $loginbox_form = '<form action="http://www.opencaching.pl/login.php" method="post" enctype="application/x-www-form-urlencoded" name="login" dir="ltr" style="display: inline;">'.tr('user').':&nbsp;<input name="email" size="10" type="text" class="textboxes" value="" />&nbsp;&nbsp;&nbsp;&nbsp;<br>'.$language[$lang]['password'].'&nbsp;<input name="password" size="10" type="password" class="textboxes" value="" /><p>&nbsp;<input type="hidden" name="action" value="login" /><input type="submit" name="LogMeIn" value="'.$language[$lang]['login'].'" class="formbuttons" /></form>';
?>
