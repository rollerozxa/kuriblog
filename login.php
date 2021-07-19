<?php

require('lib/common.php');

$error = '';

if (isset($_GET['logout'])) {
	setcookie('token');
	die(header('Location: index.php'));
} elseif (isset($_POST['login'])) {
	if (!$_POST['username'] or !$_POST['password'])
		$error = 'Please enter an user name and a password.';
	else {
		$logindata = SqlQueryResult("SELECT id,password,token FROM users WHERE name = '".SqlEscape($_POST['username']).'"');

		if (!password_verify($_POST['password'], $logindata['password']))
			$error = 'Invalid user name or password.';
		else {
			setcookie('token', $logindata['token'], time()+999999);
			die(header('Location: index.php'));
		}
	}
}

BuildHeader(array('title' => 'Log in'));

$crumbs = BuildCrumbs(array('./'=>'Main', 'lol'=>'Log in'));
echo $crumbs;

if ($error)
	MsgError($error);

?>
	<form action="login.php" method="post">
		<table class="ptable">
			<tr>
				<th colspan=2>Log in</th>
			</tr>
			<tr>
				<td class="c1 center bold" style="width: 150px;">User name:</td>
				<td class="c2 left"><input type="text" name="username" size=20 maxlength=20 value="<?=(isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '') ?>"></td>
			</tr>
			<tr>
				<td class="c1 center bold">Password:</td>
				<td class="c2 left"><input type="password" name="password" size=20 maxlength=32></td>
			</tr>
			<tr>
				<td class="c1">&nbsp;</td>
				<td class="c2 left"><input type="submit" name="login" value="Log in"></td>
			</tr>
			<tr>
				<td class="c1 left smaller" colspan=2>Don't have an account? <a href="register.php">Register one now</a>!</td>
			</tr>
		</table>
	</form>
<?php

echo $crumbs;

BuildFooter();
