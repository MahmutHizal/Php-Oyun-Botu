<?php
include("vendor/autoload.php");
use Sunra\PhpSimple\HtmlDomParser;

$_GET["page"] = (empty($_GET["page"]) ? NULL : $_GET["page"]);
$Template = new Template('./tpl/');
switch($_GET["page"])
{
	default:
		$Dom = HtmlDomParser::file_get_html("http://www.oyungoo.com/");
		$Template->set('title', 'Ana Sayfa');
		$Categories = array();
		foreach ($Dom->find('div.oyun-kategorileri ul li a') as $kategoriler)
		{
			if($kategoriler->rel != "nofollow")
			{
				$Categories[] = '<a href="index.php?page=category&name=' . rtrim(str_replace('http://www.oyungoo.com/', '', $kategoriler->href), '//') . '">' . $kategoriler->plaintext . '</a>';
			}
		}
		$Template->set('body', implode('<br />' . PHP_EOL, $Categories));
		$Template->display('index.tpl.php');
		unset($Dom);
	break;
	case 'category':
		$_GET["name"] = (empty($_GET["name"]) ? "oyunlar" : strip_tags($_GET["name"]));
		$Dom = HtmlDomParser::file_get_html("http://www.oyungoo.com/" . $_GET["name"]);
		$Template->set('title', 'Kategoriler');
		$URLS = array();
		foreach ($Dom->find('div.oyun_listesi ol li a') as $games) 
		{
			$Image = $games->find('img', 0);
			//$URLS[] = '<a href="index.php?page=oyun&name=' . rtrim(str_replace('http://www.oyungoo.com/', '', $games->href), '//') . '">' . $Image . '<br>' . $games->plaintext . '</a>';
			$Template->set('image', $Image->src);
			$Template->set('name', $games->plaintext);
			$Template->set('link', 'index.php?page=oyun&name=' . rtrim(str_replace('http://www.oyungoo.com/', '', $games->href), '//'));
			$URLS[] = $Template->fetch('gamelist_row.tpl.php');
		}
		$Template->set('body', implode('', $URLS));
		$Template->display('index.tpl.php');
		unset($Dom);
	break;
	case 'oyun':
		$_GET["name"] = (empty($_GET["name"]) ? NULL : strip_tags($_GET["name"]));
		$Dom = HtmlDomParser::file_get_html("http://www.oyungoo.com/" . $_GET["name"] . '/oyna/');
		$Template->set('title', 'Oyun');
		if(!is_null($_GET["name"]))
		{
			$tar = str_replace(array('http://img.oyungoo.com/', '.jpg'), '', $Dom->find('link[rel=image_src]', 0)->href);
			if(false === @file_get_contents('http://cdn.oyungoo.com/' . $tar . '.swf', 0, null, 0, 1))
			{
				$link = $Dom->find('object', 0)->data;
			}
			else
				$link = 'http://cdn.oyungoo.com/' . $tar . '.swf';
			$Template->set('body', '<object align="center" id="flashoyun" codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0" classid="CLSID:D27CDB6E-AE6D-11cf-96B8-444553540000" width="640" height="480"><param name="movie" value="' . $link . '"> <param name="quality" value="high"><param name="scale" value="exactfit"><param name="menu" value="false"><param name="allowScriptAccess" value="always"><embed name="flashoyun" src="' . $link . '" width="640" height="480" quality="high" allowscriptaccess="always" swliveconnect="false" scale="exactfit" menu="true" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"></object>');
			$Template->display('index.tpl.php');
		}
		else
			header("Location: index.php");
	break;
}
?>