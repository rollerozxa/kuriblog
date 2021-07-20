<?php

header('Content-type: application/rss+xml');
require('lib/common.php');

$siteurl = 'http'.(isset($_SERVER['HTTPS'])?'s':'').'://'.$_SERVER['SERVER_NAME'].preg_replace('{/[^/]*$}', '', $_SERVER['SCRIPT_NAME']);

echo '<?xml version="1.0" encoding="UTF-8"?>';

?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?=SITE_TITLE ?> RSS</title>
		<link><?=$siteurl ?></link>
		<description>The latest news on <?=SITE_TITLE ?>.</description>
		<atom:link href="<?=$siteurl ?>/rss.php" rel="self" type="application/rss+xml" />

<?php
	$entries = query("SELECT be.*, u.name uname FROM blog_entries be LEFT JOIN users u ON u.id=be.userid ORDER BY date DESC LIMIT 10");
	while($entry = $entries->fetch()) {
		$title = htmlspecialchars($entry['title']);
		$timestamp = DateTime($entry['date']);
		$username = htmlspecialchars($entry['uname']);
		$text = Filter_BlogEntry($entry['text']);
		$rfcdate = gmdate(DATE_RFC1123, $entry['date']);

		echo "\t\t<item>\n";
		echo "\t\t\t<title>{$title} (posted on {$timestamp} by {$username}</title>\n";
		echo "\t\t\t<link>{$siteurl}/?eid={$entry['id']}</link>\n";
		echo "\t\t\t<pubDate>{$rfcdate}</pubDate>\n";
		echo "\t\t\t<description><![CDATA[{$text}]]></description>\n";
		echo "\t\t\t<guid isPermaLink=\"false\">e{$entry['id']}</guid>\n";
		echo "\t\t</item>\n";
	}
?>
	</channel>
</rss>
