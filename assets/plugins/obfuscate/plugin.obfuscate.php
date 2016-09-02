<?php
/**
 * ObfuscateModxEvo
 * ObfuscateModxEvo plugin for MODX Evo
 *
 * 
 *
 * @category    plugin 
 * @version 1.0
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL) 
 * @internal    @properties 
 * @internal    @events OnWebPagePrerender 
 * @internal    @modx_category Content 
 * @internal    @legacy_names ObfuscateModxEvo
 * @internal    @installset base
 * @author ProjectSoft (projectsoft@ioweb.ru)
*/
//author ProjectSoft (projectsoft@ioweb.ru)
if(!defined('MODX_BASE_PATH')) die('What are you doing? Get out of here!');
global $modx, $offset_obfus;
$offset_obfus = 0;
$e =&$modx->event;

if(!function_exists("ordutf8")) {
	function ordutf8($string, &$offset_obfus) {
		$code = ord(substr($string, $offset_obfus,1)); 
		if ($code >= 128) {        //otherwise 0xxxxxxx
			if ($code < 224) $bytesnumber = 2;                //110xxxxx
			else if ($code < 240) $bytesnumber = 3;        //1110xxxx
			else if ($code < 248) $bytesnumber = 4;    //11110xxx
			$codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
			for ($i = 2; $i <= $bytesnumber; $i++) {
				$offset_obfus ++;
				$code2 = ord(substr($string, $offset_obfus, 1)) - 128;        //10xxxxxx
				$codetemp = $codetemp*64 + $code2;
			}
			$code = $codetemp;
		}
		$offset_obfus += 1;
		if ($offset_obfus >= strlen($string)) $offset_obfus = -1;
		return $code;
	}
}
if(!function_exists("obfuscate_replacer")) {
	function obfuscate_replacer(&$matches){
		global $offset_obfus;
		//-------------------------------
		
		$str = trim(nl2br(strip_tags($matches[2])));
		$offset_obfus = 0;
		//-------------------------------
		$str = html_entity_decode(preg_replace('|\s+|', ' ', preg_replace('|(\s+)?\n(\s+)?|', '',preg_replace('|&nbsp;|', ' ',$str))));
		$arr = explode("<br />", $str);
		$out = array();
		$offset_obfus = 0;
		foreach($arr as $key=>$value){
			$offset_obfus = 0;
			$obfus = "";
			while ($offset_obfus >= 0) {
				$obfus .= "&#".ordutf8($value, $offset_obfus).";";
			}
			$out[] = $obfus;
		}
		$html = implode("<br />", $out);
		return $html;
	}
}
switch ($e->name) {
	case "OnWebPagePrerender":{
		$outputPrepare = $modx->documentOutput;
		$regex = "#(\{obfuscate\}(.+)\{\/obfuscate})#Usi";
		$outputPrepare = preg_replace_callback($regex, 'obfuscate_replacer', $outputPrepare);
		$modx->documentOutput = $outputPrepare;
		break;
	}
}
?>