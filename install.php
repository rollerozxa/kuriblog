<?php
function shake() {
	$cset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
	$salt = "";
	$chct = strlen($cset) - 1;
	while (strlen($salt) < 32)
		$salt .= $cset[mt_rand(0, $chct)];
	return $salt;
}

if ($_POST['install']) {
	$salt = shake();

	$settings = '<?php
define(\'PASS_SALT\', '.var_export($salt, true).');

$host = \''.var_export($_POST['sqlserver'], true).'\';
$user = \''.var_export($_POST['sqlname'], true).'\';
$pass = \''.var_export($_POST['sqlpass'], true).'\';
$db = \''.var_export($_POST['sqldb'], true).'\';
';

	file_put_contents('conf/config.php', $settings);

	require('lib/mysql.php');

	$queries = file_get_contents('install.sql');
	$queries = explode(';', $queries);
	foreach ($queries as $query)
		query($query);

	die('Kuriblog installed successfully. You should delete install.php and install.sql, and register to your new blog.');
}

?>
<form action="" method="post">
SQL server: <input type="text" name="sqlserver" value="localhost"><br>
SQL username: <input type="text" name="sqlname" value=""><br>
SQL password: <input type="text" name="sqlpass" value=""><br>
SQL database: <input type="text" name="sqldb" value=""><br>
<input type="submit" name="install" value="Install">
</form>