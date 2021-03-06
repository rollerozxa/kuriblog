<?php
require('lib/admincommon.php');

if (isset($_POST['add'])) {
	$ip = $_POST['ip'];
	$reason = $_POST['reason'];

	if (!result("SELECT COUNT(*) FROM ipbans WHERE ip = ?", [$ip]))
		query("INSERT INTO ipbans (ip,reason) VALUES (?,?)", [$ip, $reason]);

	die(header('Location: ipbans.php'));
} else if (isset($_POST['remove'])) {
	$ip = $_POST['ip'];
	query("DELETE FROM ipbans WHERE ip = ?", [$ip]);

	die(header('Location: ipbans.php'));
}

BuildHeader(array('title' => 'IP bans'));
BuildAdminBar('ipbans');

?>
	<table class="ptable">
		<tr>
			<th>IP</th>
			<th>Reason</th>
			<th>&nbsp;</th>
		</tr>
<?php

$ipbans = query("SELECT * FROM ipbans");

$i = 0;
while ($ipban = $ipbans->fetch()) {
	$i++;
	echo "
	<tr>
		<td class=\"c1 left\">{$ipban['ip']}</td>
		<td class=\"c2 left\">{$ipban['reason']}</td>
		<td class=\"c1 right\"><form action=\"\" method=\"post\"><input type=\"hidden\" name=\"ip\" value=\"".htmlspecialchars($ipban['ip'])."\"><input type=\"submit\" name=\"remove\" value=\"Remove\"></form></td>
	</tr>";
}

if ($i === 0)
	echo "<tr><td class=\"c1\" colspan=\"3\">No IP bans.</td></tr>";

?>
	</table>
	<form action="" method="post">
		<table class="ptable">
			<tr>
				<th colspan=2>Add an IP ban</td>
			</tr>
			<tr>
				<td class="c1 center bold" style="width: 150px;">IP:</td>
				<td class="c2 left"><input type="text" name="ip" size=32 maxlength=50></td>
			</tr>
			<tr>
				<td class="c1 center bold">Reason:</td>
				<td class="c2 left"><input type="text" name="reason" size=32 maxlength=200></td>
			</tr>
			<tr>
				<th colspan=2>&nbsp;</td>
			</tr>
			<tr>
				<td class="c1">&nbsp;</td>
				<td class="c2 left"><input type="submit" name="add" value="Add IP ban"></td>
			</tr>
		</table>
	</form>
<?php

BuildFooter();
