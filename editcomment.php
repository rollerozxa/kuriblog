<?php
require('lib/common.php');

$id = (int)$_GET['id'];

if ($_GET['action'] == 'delete') {
	if ($_GET['token'] !== $mytoken) Kill('No.');
	$action = 'delete';
	$actioncap = 'Delete';
} else {
	$action = 'edit';
	$actioncap = 'Edit';
}

if (!$login)
	Kill('You must be logged in to '.$action.' comments.');

if ($mypower < 0)
	Kill('You aren\'t allowed to '.$action.' comments.');

$comment = fetch("SELECT * FROM blog_comments WHERE id = ?");
if (!$comment)
	Kill('Invalid blog entry ID.');

if (($mypower < 3) && ($entry['userid'] != $myuserid))
	Kill('You aren\'t allowed to '.$action.' this comment.');

$error = '';

if ($_GET['action'] == 'delete') {
	query("DELETE FROM blog_comments WHERE id = ?", [$id]);

	query("UPDATE blog_entries SET ncomments = ncomments - 1, lastcmtid = (SELECT MAX(id) FROM blog_comments WHERE entryid = ?) WHERE id = ?", [$comment['entryid'], $comment['entryid']]);
	query("UPDATE blog_entries SET lastcmtuser = (SELECT userid FROM blog_comments WHERE id = lastcmtid) WHERE id = ?", [$comment['entryid']]);

	die(header('Location: comments.php?id='.$comment['entryid']));
} elseif ($_POST['submit']) {
	$text = trim($_POST['text']);

	if ($text == '')
		$error = 'Your comment is empty. Enter some text and try again.';
	else {
		query("UPDATE blog_comments SET text = ? WHERE id = ?", [$text, $id]);

		die(header('Location: comments.php?id='.$comment['entryid'].'&cid='.$id));
	}
} else {
	$_POST['text'] = $comment['text'];
}

BuildHeader(array('title' => $actioncap.' comment'));

if ($error)
	MsgError($error);

?>
	<form action="" method="post">
		<table class="ptable">
			<tr>
				<th colspan=2><?=$actioncap ?> comment</th>
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
