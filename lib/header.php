<!DOCTYPE html>
<html>
<head>
	<title><?=$title ?></title>
	<meta name="keywords" content="<?=META_KEYWORDS ?>">
<?=$descr ?>
	<link rel="stylesheet" href="theme/common.css" type="text/css">
	<link rel="stylesheet" href="theme/<?=$themefile ?>" type="text/css">
	<link rel="alternate" type="application/rss+xml" title="RSS feed" href="rss.php">
	<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<?=isset($params['headextra']) ? $params['headextra'] : '' ?>
</head>
<body>
<div class="maincontainer">
	<table class="ptable" id="pageheader">
		<tr>
			<td class="c1 center" colspan=3>
				<a href="./"><img src="<?=$bannerimg ?>" title="<?=$bannertitle ?>" alt="<?=$banneralt ?>"></a>
			</td>
		</tr>
		<tr>
			<td class="c2 center" style="width: 15%;">
				<?=$views['views'] ?> views <!-- and <?=$views['botviews'] ?> by bots -->
			</td>
			<td class="c2 center">
				<span style="float:right;"><a href="rss.php"><img src="img/rss.png" alt="RSS feed"></a></span>
				<?php if ($login) { ?>
				<?=Username($myuserdata) ?>:
				<a href="login.php?logout">Log out</a>
				<?php if ($mypower >= 0) { ?>| <a href="editprofile.php">Edit profile</a><?php } ?>
				<?php if ($mypower >= 2) { ?>| <a href="newblogentry.php">New blog entry</a><?php } ?>
				<?php if ($mypower >= 3) { ?>| <a href="admin.php">Admin</a><?php } ?>
<?php } else { ?>
				Guest:
				<a href="register.php">Register</a>
				| <a href="login.php">Log in</a>
<?php } ?>
			</td>
			<td class="c2 center" style="width: 15%;">
				<?=DateTime() ?>
			</td>
		</tr>
	</table>
