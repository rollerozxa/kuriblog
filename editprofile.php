<?php
require('lib/common.php');

if (!$login)
	Kill('You must be logged in to edit your profile.');

if ($mypower < 0)
	Kill('Banned users may not edit their profile.');

if ($mypower >= 3 && isset($_GET['id'])) {
	$adminmode = true;
	$userid = (int)$_GET['id'];
	$user = fetch("SELECT * FROM users WHERE id = ?", [$userid]);
	if (!$user) Kill('Invalid user ID.');
} else {
	$adminmode = false;
	$userid = $myuserid;
	$user = $myuserdata;
}

if ($adminmode) $action = 'Edit user '.htmlspecialchars($user['name']);
else $action = 'Edit profile';

$key = hash('sha256', "{$myuserdata['id']},{$myuserdata['password']},blahblah");
$error = '';
if (isset($_POST['savechanges'])) {
	if ($_POST['key'] != $key)
		die('No.');

	$newpass = '';
	if ($_POST['changepass'] == 'on') {
		if ($_POST['pass1'] != $_POST['pass2'])
			$error = 'The passwords you entered don\'t match.';
		else if ($userid == $myuserid)
			$newpass = 'password=\''.password_hash($_POST['pass1'], PASSWORD_DEFAULT).'\', ';
	}

	if (!$error) {
		$sex = (int)$_POST['sex'];
		if ($sex<0 || $sex>2) $sex = 2;

		$theme = (int)$_POST['theme'];

		$values = [$sex, $theme];

		$adminopts = '';
		if ($adminmode) {
			$username = trim($_POST['name']);
			$powerlevel = (int)$_POST['powerlevel'];

			$unmatches = result("SELECT COUNT(*) FROM users WHERE name = ? AND id != ?", [$username, $userid]);
			if ($unmatches) $error = 'This username is already taken.';
			else {
				if ($powerlevel < -1 || $powerlevel > 3) $powerlevel = $user['powerlevel'];
				$adminopts = ", name = ?, powerlevel = ?";
				$values[] = $username;
				$values[] = $powerlevel;
			}
		}
	}

	if (!$error) {
		$values[] = $userid;

		query("UPDATE users SET {$newpass}sex = ?, theme = ? {$adminopts} WHERE id = ?", $values);

		die(header('Location: profile.php?id='.$userid));
	}
} else {
	$_POST['sex'] = $user['sex'];

	if ($adminmode) {
		$_POST['name'] = $user['name'];
		$_POST['powerlevel'] = $user['powerlevel'];
	}
}

BuildHeader(array('title' => $action));

$crumbs = BuildCrumbs(array('./'=>'Main', 'lol'=>$action));
echo $crumbs;

if ($error)
	MsgError($error);

$themelist = '<select name="theme">';
$themes = query("SELECT t.*, (SELECT COUNT(*) FROM users WHERE theme=t.id) lovers FROM themes t ORDER BY id");
while ($theme = $themes->fetch()) {
	$check = ($myuserdata['theme'] == $theme['id']) ? ' selected="selected"' : '';
	$themelist .= "<option value=\"{$theme['id']}\"{$check}>{$theme['name']} ({$theme['lovers']})</option>";
}
$themelist .= '</select>';

?>
	<form action="" method="post" onsubmit="if (this.changepass.checked && (this.pass1.value!=this.pass2.value)) { alert('The passwords you entered don\'t match.'); return false; }">
		<table class="ptable">
			<tr>
				<th colspan=2>Credentials</th>
			</tr>
			<tr>
				<td class="c1" style="width: 155px;">&nbsp;</td>
				<td class="c2 left"><label><input type="checkbox" name="changepass"> Change password</label></td>
			<tr>
				<td class="c1 center bold">New password:</td>
				<td class="c2 left"><input type="password" name="pass1" size=20 maxlength=32 value=""></td>
			</tr>
			<tr>
				<td class="c1 center bold">Confirm new password:</td>
				<td class="c2 left"><input type="password" name="pass2" size=20 maxlength=32 value=""></td>
			</tr>

			<tr>
				<th colspan=2>Personal settings</th>
			</tr>
			<tr>
				<td class="c1 center bold">Sex:</td>
				<td class="c2 left">
					<label><input type="radio" name="sex" value=1 <?php if ($_POST['sex']==1) echo 'selected ' ?>/> Male</label>
					<label><input type="radio" name="sex" value=2 <?php if ($_POST['sex']==2) echo 'selected ' ?>/> Female</label>
					<label><input type="radio" name="sex" value=0 <?php if ($_POST['sex']==0) echo 'selected ' ?>/> N/A</label>
				</td>
			</tr>

			<tr>
				<th colspan=2>Site appearance</th>
			</tr>
			<tr>
				<td class="c1 center bold">Theme:</td>
				<td class="c2 left">
					<?=$themelist ?>
				</td>
			</tr>

			<?php if ($adminmode) { ?>
			<tr>
				<th colspan=2>Administrative options</th>
			</tr>
			<tr>
				<td class="c1 center bold">Username:</td>
				<td class="c2 left">
					<input type="text" name="name" size="20" maxlength="20" value="<?=htmlspecialchars($_POST['name']) ?>">
				</td>
			</tr>
			<tr>
				<td class="c1 center bold">Rank:</td>
				<td class="c2 left">
					<select name="rank">
						<option value="-1"<?=($_POST['powerlevel'] == -1 ? ' selected':'') ?>>Banned</option>
						<option value="0" <?=($_POST['powerlevel'] == 0 ? ' selected':'') ?>>Normal user</option>
						<option value="1" <?=($_POST['powerlevel'] == 1 ? ' selected':'') ?>>Comments moderator</option>
						<option value="2" <?=($_POST['powerlevel'] == 2 ? ' selected':'') ?>>Blog poster</option>
						<option value="3" <?=($_POST['powerlevel'] == 3 ? ' selected':'') ?>>Admin</option>
					</select>
				</td>
			</tr>
			<?php } ?>

			<tr>
				<th colspan=2>&nbsp;</th>
			</tr>
			<tr>
				<td class="c1">&nbsp;</td>
				<td class="c2 left"><input type="submit" name="savechanges" value="Save changes"></td>
			</tr>
		</table>
		<input type="hidden" name="key" value="<?=$key ?>">
	</form>
<?php

echo $crumbs;

BuildFooter();

?>