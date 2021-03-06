<?php
require('lib/common.php');

$id = (int)$_GET['id'];
$new = ($id == 0);

$action = (isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : ''));

if ($new) {
	$action = 'post';
	$actioncap = 'New';
} elseif ($action == 'delete') {
	if ($_GET['token'] !== $mytoken) Kill('No.');
	$action = 'delete';
	$actioncap = 'Delete';
} else {
	$action = 'edit';
	$actioncap = 'Edit';
}

if (!$login)
	Kill('You must be logged in to '.$action.' blog entries.');

if ($mypower < 2)
	Kill('You aren\'t allowed to '.$action.' blog entries.');

if (!$new) {
	$entry = fetch("SELECT * FROM blog_entries WHERE id = ?", [$id]);
	if (!$entry)
		Kill('Invalid blog entry ID.');

	if (($mypower < 3) && ($entry['userid'] != $myuserid))
		Kill('You aren\'t allowed to '.$action.' this blog entry.');
} else
	$entry = array('userid' => $myuserid, 'title' => '', 'text' => '');

$error = '';

if ($action == 'delete') {
	query("DELETE FROM blog_entries WHERE id = ?", [$id]);
	query("DELETE FROM blog_comments WHERE entryid = ?", [$id]);
	die(header('Location: index.php'));
} elseif (isset($_POST['submit']) && $_POST['submit']) {
	$title = trim($_POST['title']);
	$text = trim($_POST['text']);

	if ($title == '')
		$error = 'Your blog entry has no title. Enter a title and try again.';
	elseif ($text == '')
		$error = 'Your blog entry is empty. Enter some text and try again.';
	else {
		if ($new)
			query("INSERT INTO blog_entries (userid, title, text, date) VALUES (?,?,?, UNIX_TIMESTAMP())", [$myuserid, $title, $text]);
		else
			query("UPDATE blog_entries SET title = ?, text = ? WHERE id = ?", [$title, $text, $id]);

		die(header('Location: index.php'));
	}
} else {
	$_POST['title'] = $entry['title'];
	$_POST['text'] = $entry['text'];
}

BuildHeader(array('title' => $actioncap.' blog entry'));

if ($error)
	MsgError($error);

?>
	<form action="" method="post">
		<table class="ptable">
			<tr>
				<th colspan=2><?=$actioncap ?> blog entry</th>
			</tr>
			<tr>
				<td class="c1 center bold" style="width: 150px;">Title:</td>
				<td class="c2 left"><input type="text" name="title" style="width: 100%;" maxlength=512 value="<?=htmlspecialchars($_POST['title']) ?>"></td>
			</tr>
			<tr>
				<td class="c1 center bold">Text:</td>
				<td class="c2 left"><textarea name="text" style="width: 100%; height: 200px;"><?=htmlspecialchars($_POST['text']) ?></textarea></td>
			</tr>
			<tr>
				<td class="c1">&nbsp;</td>
				<td class="c2 left"><input type="submit" name="submit" value="Submit"></td>
			</tr>
		</table>
	</form>
<?php

BuildFooter();
