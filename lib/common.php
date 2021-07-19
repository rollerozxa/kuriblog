<?php
if (!file_exists('conf/config.php')) {
	die('<h2>Welcome to your soon-to-be Kuriblog!</h2><a href="install.php">Click here to be taken to the installation page.</a>');
}

$t = gettimeofday();
$starttime = $t['sec'] + ($t['usec'] / 1000000);

header('Content-type: text/html; CHARSET=utf-8');

require_once('conf/config.php');
require_once('mysql.php');

define('SITE_TITLE', htmlspecialchars(SqlQueryResult("SELECT value FROM misc WHERE field='sitename'")));
define('META_DESCR', htmlspecialchars(SqlQueryResult("SELECT value FROM misc WHERE field='metadescr'")));
define('META_KEYWORDS', htmlspecialchars(SqlQueryResult("SELECT value FROM misc WHERE field='metakeywords'")));
define('GUESTCOMMENTS', SqlQueryResult("SELECT value FROM misc WHERE field='guestcomments'")?true:false);

$ipban = SqlQueryFetchRow("SELECT * FROM ipbans WHERE INSTR('".SqlEscape($_SERVER['REMOTE_ADDR'])."', ip)=1");
if ($ipban)
	die("Your IP is banned. ".($ipban['reason'] ? 'Reason: '.$ipban['reason'] : ''));

$bots = array(
	"Microsoft URL Control",
	"Yahoo! Slurp",
	"Twiceler",
	"yandex",
	'spider', 'bot',		 // catch-all
	'google',
);

$isbot = false;
foreach ($bots as $bot) {
	if (stristr($_SERVER['HTTP_USER_AGENT'], $bot) !== FALSE) {
		$isbot = true;
		break;
	}
}

$login = false;
$myuserid = 0;
$myusername = 'Guest';
$mypower = 0;
$myuserdata = NULL;

if (isset($_COOKIE['token']) && $token = $_COOKIE['token']) {
	$myuserdata = SqlQueryFetchRow("SELECT * FROM users WHERE token='".SqlEscape($token)."'");

	if (!$myuserdata) {
		setcookie('token');
		$myuserid = 0;
	} else {
		$login = true;
		$myusername = $myuserdata['name'];
		$mypower = $myuserdata['powerlevel'];
		$mytoken = sha1('wtf is this');

		SqlQuery("UPDATE users SET ip='".SqlEscape($_SERVER['REMOTE_ADDR'])."' WHERE id={$myuserid}");
	}
}

if (!$myuserdata)
	$myuserdata = array('name' => 'Guest', 'theme' => 1, 'token' => 'lol');

function QueryString($exclude) {
	if (!is_array($exclude))
		$exclude = array($exclude);

	$ret = '';

	foreach ($_GET as $k => $v) {
		if (in_array($k, $exclude))
			continue;

		$ret .= urlencode($k).'='.urlencode($v).'&';
	}

	return $ret;
}


function BuildHeader($params = 0) {
	global $login, $myuserdata, $mypower, $isbot;

	$nviews = (int)SqlQueryResult("SELECT value FROM misc WHERE field='views'");
	$nbotviews = (int)SqlQueryResult("SELECT value FROM misc WHERE field='botviews'");
	if (!$isbot) {
		$nviews++;
		SqlQuery("UPDATE misc SET value='{$nviews}' WHERE field='views'");
	} else {
		$nbotviews++;
		SqlQuery("UPDATE misc SET value='{$nbotviews}' WHERE field='botviews'");
	}

	$title = SITE_TITLE;

	$themefile = SqlQueryResult("SELECT filename FROM themes WHERE id={$myuserdata['theme']}");
	if (file_exists("theme/{$themefile}/style.php"))
		$themefile = "{$themefile}/style.php";
	else
		$themefile = "{$themefile}/style.css";

	$bannerimg = 'img/banner.png';//MOVEME
	$bannertitle = $title;
	$banneralt = $title;

	$descr = '';

	if (is_array($params)) {
		if (isset($params['title']))
			$title = $params['title'].' | '.$title;

		if (isset($params['descr']))
			$descr = "\t<meta name=\"description\" content=\"".htmlspecialchars($params['descr'])."\">\n";
	}

	include('header.php');
}

function BuildFooter() {
	global $starttime, $nqueries, $nrowst, $nrowsf;

	$t = gettimeofday();
	$endtime = $t['sec'] + ($t['usec'] / 1000000);
	$rendertime = $endtime - $starttime;

	include('footer.php');
}

function BuildCrumbs($crumbs, $extra = NULL) {
	$ret = '';

	foreach ($crumbs as $link=>$text) {
		if ($link == 'lol')
			$ret .= $text.' &raquo; ';
		else
			$ret .= "<a href=\"{$link}\">{$text}</a> &raquo; ";
	}
	$ret = substr($ret, 0, strlen($ret)-9);

	if ($extra) {
		$ret .= ' <span style="float: right;">';

		foreach ($extra as $link=>$text)
			$ret .= "<a href=\"{$link}\">{$text}</a> | ";

		$ret = substr($ret, 0, strlen($ret)-3);
		$ret .= '</span>';
	}

	return "\t<table class=\"ptable\"><tr><td class=\"c2 left\">{$ret}</td></tr></table>\n";
}

function DateTime($time = NULL) {
	if ($time)
		return gmdate('m/d/Y H:i:s', $time);
	else
		return gmdate('m/d/Y H:i:s');
}

function Message($msg, $title = 'Notice') {
	echo
"	<table class=\"ptable\">
		<tr>
			<th>{$title}</th>
		</tr>
		<tr>
			<td class=\"c1 center padded\">
				{$msg}
			</td>
		</tr>
	</table>
";
}

function MsgError($msg) {
	Message($msg, 'Error');
}

function Kill($msg) {
	BuildHeader(array('title' => 'Error'));
	MsgError($msg);
	BuildFooter();
	die();
}

function Redirect($url, $msg) {
	return "You will now be redirected to <a href=\"{$url}\">{$msg}</a>.<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1;URL={$url}\">";
}

function Username($data, $pf = '') {
	return "<a href=\"profile.php?id={$data[$pf.'id']}\"><span class=\"uc_{$data[$pf.'powerlevel']}_{$data[$pf.'sex']}\">".htmlspecialchars($data[$pf.'name'])."</span></a>";
}

function PageNum() {
	if (isset($_GET['p']))
		return max((int)$_GET['p'], 1);
	else
		return 1;
}

function PageLinks($num, $pp = 20) {
	$num = ceil($num / $pp);
	if ($num < 2) return '';

	$cur = PageNum();
	$query = QueryString(array('p','last','cid'));

	$ret = "<table class=\"ptable\"><tr><td class=\"c1 left\">Pages:";

	for ($i = 1; $i <= $num; $i++) {
		if ($i == $cur)
			$ret .= " {$i}";
		else
			$ret .= " <a href=\"?{$query}p={$i}\">{$i}</a>";
	}

	$ret .= "</td></tr></table>\n";
	return $ret;
}

function Filter_BBCode($text) {
	$text = preg_replace("@\[b\](.*?)\[/b\]@si", '<strong>$1</strong>', $text);
	$text = preg_replace("@\[i\](.*?)\[/i\]@si", '<em>$1</em>', $text);
	$text = preg_replace("@\[u\](.*?)\[/u\]@si", '<span style="text-decoration: underline;">$1</span>', $text);
	$text = preg_replace("@\[s\](.*?)\[/s\]@si", '<del>$1</del>', $text);

	$text = preg_replace("@\[url\]([^\"]+?)\[/url\]@si", '<a href="$1">$1</a>', $text);
	$text = preg_replace("@\[url=[\s\"']?([^\"]+?)[\s\"']?\](.+?)\[/url\]@si", '<a href="$1">$2</a>', $text);
	$text = preg_replace("@\[img\]([^\"]+?)\[/img\]@si", '<img src="$1" alt="">', $text);

	return $text;
}

function Filter_BlogEntry($text) {
	$text = nl2br($text, false);

	$text = Filter_BBCode($text);

	$text = preg_replace("@<(/?(script|iframe|noscript|plaintext|textarea|object|embed|meta|base|style|link|marquee|blink|frame))@si", "&lt;$1", $text);
	$text = preg_replace("@(on)([^<>]+?)=@si", "$1$2&#x3D;", $text);
	$text = preg_replace("@=([^<>]+?)(javascript:)@si", "&#x3D;$1$2", $text);

	return $text;
}

function Filter_BlogComment($text) {
	$text = htmlspecialchars($text);
	$text = nl2br($text, false);

	$text = Filter_BBCode($text);

	return $text;
}
