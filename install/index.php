<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File install/index.php
 |     Author: Russell Phillips
 |  Copyright: (C) 2006 - 2015 The Bitsand Project
 |             (http://github.com/PeteAUK/bitsand)
 |
 | Bitsand is free software; you can redistribute it and/or modify it under the
 | terms of the GNU General Public License as published by the Free Software
 | Foundation, either version 3 of the License, or (at your option) any later
 | version.
 |
 | Bitsand is distributed in the hope that it will be useful, but WITHOUT ANY
 | WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 | FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 | details.
 |
 | You should have received a copy of the GNU General Public License along with
 | Bitsand.  If not, see <http://www.gnu.org/licenses/>.
 +---------------------------------------------------------------------------*/

//Initialise $CSS_PREFIX
$CSS_PREFIX = '../';

//Do not check that user is logged in
$bLoginCheck = False;
//Load required inc files
include ($CSS_PREFIX . 'inc/inc_head_db.php');
include ($CSS_PREFIX . 'inc/inc_head_html.php');
//Report all errors except E_NOTICE
error_reporting (E_ALL ^ E_NOTICE);

if (isset ($_POST ['txtEmail'])) {
	$s = "This e-mail was sent from a Bitsand installation, installed at " .
		SYSTEM_URL . "\n\nIt was sent at " . date ("H:i") . " on " .
		date ("d F Y") . "\n";
	mail ($_POST ['txtEmail'], "Testing Bitsand", $s);
}
?>
<h1>Bitsand - Installation Tests &amp; Tools</h1>

<p class = "warn">
THIS <span style = "font-family: monospace">install</span> DIRECTORY MUST BE DELETED BEFORE THE SYSTEM GOES LIVE
</p>

<p>
<b>PHP &amp; MySQL Version Check</b><br>
<?php
echo "PHP version: ";
$aPhpVersion = explode ('.', PHP_VERSION);

if ($aPhpVersion [0] >= 7)
	echo "<span class = 'sans-green'>" . PHP_VERSION . " (OK)</span><br>\n";
elseif ($aPhpVersion [0] >= 4 && $aPhpVersion [0] < 7)
	echo "<span style = 'color: orange; font-weight: bold'>" . PHP_VERSION . " (PHP 5 and below are long end of life, consider upgrading to PHP 7 or above)</span><br>\n";
else
	echo "<span class = 'sans-warn'>" . PHP_VERSION . " (You need a newer version of PHP to run Bitsand)</span><br>\n";

echo "DataBase Type: ";
if (DB_TYPE === "mysqli" ) {
	echo "<span class = 'sans-green'>" . DB_TYPE . " (OK)</span><br>\n";
	$sMySQLVersion = mysqli_get_server_info ($link);
} elseif (DB_TYPE === "mysql" ) {
	echo "<span style = 'color: orange; font-weight: bold'>" . DB_TYPE . " (Consider moving to mysqli)</span><br>\n";
	$sMySQLVersion = mysql_get_server_info($link);
} else {
	echo "<span class = 'sans-warn'>" . DB_TYPE . " (UNKNOWN TYPE)</span><br>\n";
	exit;
}
echo "MySQL version: ";
$aMySqlVersion = explode ('.', $sMySQLVersion);

if ($aMySqlVersion [0] >= 5)
	echo "<span class = 'sans-green'>$sMySQLVersion (OK)</span><br>\n";
elseif ($aMySqlVersion [0] == 4)
	echo "<span style = 'color: orange; font-weight: bold'>$sMySQLVersion (Probably OK)</span><br>\n";
elseif ($aMySqlVersion [0] == "")
	echo "Could not connect to database. Check config file<br>\n";
else
	echo "<span class = 'sans-warn'>$sMySQLVersion (You need a newer version of MySQL to run Bitsand)</span><br>\n";

// See if we have the necessary items to load https files
$wrappers = stream_get_wrappers();
echo 'HTTPS Stream Wrapper: ';
$https = false;
if (in_array('https', $wrappers)) {
	echo '<span class="sans-green">Yes</span><br/>' , PHP_EOL;
	$https = true;
} else {
	echo '<span class="sans-warn">No</span><br/>' , PHP_EOL;
}

// See if we have curl loaded, but only if don't have the stream wrapper
if (!$https) {
	echo 'CURL: ';
	if (function_exists('curl_init')) {
		echo '<span class="sans-green">Yes</span><br/>' , PHP_EOL;
		$https = true;
	} else {
		echo '<span class="sans-warn">No</span><br/>' , PHP_EOL;
	}
}

if (!$https) {
	echo '<span class="sans-warn">Warning:</span> You have no way to remotely load HTTPS files which may prohibit linking with other Bitsand installations.' , PHP_EOL;
}
?>
</p>

<p>
<b>New Install</b><br>
The following are for use on new Bitsand installations. Do not use these if you have are upgrading an existing installation.<br>
<a href = "create_tables.php">Create tables (with prefixes)</a><br>
<a href = "initial_config.php">Initial configuration</a> - use this to perform initial set up of Bitsand after creating the database tables<br>
</p>

<p>
<b>Upgrade</b><br>
The following links are for use when upgrading from the previous version of Bitsand.<br>
<a href = "update_db.php">Update the database</a><br>
</p>

<p>
<b>All Installations</b><br>
These can be used on all Bitsand installations.<br>
<a href = "config_file_test.php">Configuration file &amp; database test</a><br>
<a href = "info.php">Display lots of PHP information</a><br>

<form action = "index.php" method = "post">
<?php
if (isset ($_POST ['txtEmail']))
	echo "<b>Test e-mail sent to {$_POST ['txtEmail']}</b><br>\n";
?>
Send a test e-mail to: <input name = "txtEmail"> <input type = "submit">
</form>
</p>

<?php
include ('../inc/inc_foot.php');