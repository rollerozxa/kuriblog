<?php
require('lib/common.php');

$id = (int)$_GET['id'];
$user = fetch("SELECT u.*, t.name themename FROM users u LEFT JOIN themes t ON t.id=u.theme WHERE u.id = ? ", [$id]);
if (!$user)
	Kill('Invalid user ID.');

$ranks = array(-1 => 'Banned', 'Normal user', 'Comment moderator', 'Blog poster', 'Admin');

BuildHeader(array('title' => 'Profile for '.htmlspecialchars($user['name'])));

$crumbs = BuildCrumbs(array('./'=>'Main', 'lol'=>'Profile for '.htmlspecialchars($user['name'])));
echo $crumbs;

?>
	<table class="ptable">
		<tr>
			<th colspan=2>General info</th>
		</tr>
		<tr>
			<td class="c1 center bold" style="width: 150px;">Registered on:</td>
			<td class="c2 left"><?=DateTime($user['regdate']) ?></td>
		</tr>
		<tr>
			<td class="c1 center bold" style="width: 150px;">Rank:</td>
			<td class="c2 left"><?=$ranks[$user['powerlevel']] ?></td>
		</tr>
		<tr>
			<td class="c1 center bold" style="width: 150px;">Theme:</td>
			<td class="c2 left"><?=htmlspecialchars($user['themename']) ?></td>
		</tr>
		<?php if ($mypower >= 3) { ?>
		<tr>
			<td class="c1 center bold" style="width: 150px;">IP:</td>
			<td class="c2 left"><?=$user['ip'] ?></td>
		</tr>
		<?php } ?>
	</table>
	<?php if ($mypower >= 3) { ?>
	<table class="ptable">
		<tr>
			<th>Admin options</th>
		</tr>
		<tr>
			<td class="c2">
				<a href="editprofile.php?id=<?=$id ?>">Edit user</a>
			</td>
		</tr>
	</table>
	<?php } ?>
<?php

echo $crumbs;

BuildFooter();
