<?php

function performStringConcats($str)
{
	for (;;)
	{
		$str1Start = strpos($str, '"');
		if ($str1Start === false)
			break;
		$str1End = strpos($str, '"', $str1Start + 1);
		if ($str1End === false)
			break;
		$str2Start = strpos($str, '"', $str1End + 1);
		if ($str2Start === false)
			break;
		$str2End = strpos($str, '"', $str2Start + 1);
		if ($str2End === false)
			break;
		$str1 = substr($str, $str1Start + 1, $str1End - $str1Start - 1);
		$str2 = substr($str, $str2Start + 1, $str2End - $str2Start - 1);
		$operator = substr($str, $str1End + 1, $str2Start - $str1End - 1);
		if (trim($operator) !== '.')
			break;
		$originalConcat = substr($str, $str1Start, $str2End - $str1Start + 1);
		$concatResult = "\"{$str1}{$str2}\"";
		$str = str_replace($originalConcat, $concatResult, $str);
	}
	return $str;
}

$reqStatements = array(
	'include',
	'require',
);

$codeRoot = realpath(dirname(__file__).'/../..');
$tempFile = '/tmp/requires-'.md5($codeRoot).'.txt';

if (!file_exists($tempFile))
{
	$generateRequiresCmd = "grep -r -e 'require *(' -e 'require_once *(' -e 'include *(' -e 'include_once *(' {$codeRoot}/* | grep -v svn | grep \.php: > {$tempFile}";
	exec($generateRequiresCmd);
}

$allRequires = file_get_contents($tempFile);
$allRequires = explode("\n", $allRequires);
foreach ($allRequires as $curRequire)
{
	$curRequire = trim($curRequire);
	if (strpos($curRequire, ':') === false)
		continue;
		
	list($filePath, $reqStatement) = explode(':', $curRequire);
	$reqStatement = trim($reqStatement);
	if (strpos($reqStatement, '//') === 0)
		continue;		// comment

	if (strpos($filePath, $codeRoot . '/alpha/web/api_v3/') === 0 ||
		strpos($filePath, $codeRoot . '/alpha/web/ma_console/') === 0)
		continue;		// don't process folders links
		
	if (strpos($filePath, $codeRoot . '/generator/sources/') === 0)
		continue;		// don't process client sources (may require files that don't exist)
		
	if (strpos($reqStatement, '$this->appendLine') !== false || 
		strpos($reqStatement, '$this->writeTest') !== false)
		continue;		// generator code
	
	foreach ($reqStatements as $curStatement)
	{
		$curPos = strpos($reqStatement, $curStatement);
		if ($curPos === false)
			continue;
		$reqStatement = substr($reqStatement, $curPos);
		break;
	}
	
	$openParent = strpos($reqStatement, '(');
	$closeParent = strrpos($reqStatement, ')');
	if ($openParent === false || $closeParent === false)
		continue;
		
	$reqFile = substr($reqStatement, $openParent + 1, $closeParent - $openParent - 1);

	$dirNameAliases = array(	
		'realpath(dirname(__FILE__))',
		'dirname(__FILE__)',
		'dirname(__file__)',
		'dirname( __FILE__ )',
		'__DIR__',
		);
	
	foreach ($dirNameAliases as $dirNameAlias)
		$reqFile = str_replace($dirNameAlias, '"'.dirname($filePath).'"', $reqFile);
	
	$reqFile = str_replace('DIRECTORY_SEPARATOR', '"/"', $reqFile);
	$reqFile = str_replace('SF_APP', '"kaltura"', $reqFile);
	$reqFile = str_replace('SF_ROOT_DIR', '"'.$codeRoot.'/alpha/"', $reqFile);
	$reqFile = str_replace('ROOT_DIR', '"'.$codeRoot.'/"', $reqFile);
	$reqFile = str_replace('KALTURA_ROOT_PATH', '"'.$codeRoot.'"', $reqFile);
	$reqFile = str_replace('$sf_symfony_lib_dir', '"'.$codeRoot.'/symfony/"', $reqFile);
	$reqFile = str_replace('KALTURA_API_PATH', '"'.$codeRoot.'/api_v3/"', $reqFile);
	$reqFile = str_replace('MODULES', '"'.$codeRoot.'/alpha/apps/kaltura/modules/"', $reqFile);	
	$reqFile = str_replace('\'', '"', $reqFile);
	$reqFile = trim(performStringConcats($reqFile));
	
	if (!$reqFile)
		continue;
		
	if (strpos($reqFile, '$') !== false || substr($reqFile, 0, 1) != '"' || substr($reqFile, -1) != '"')
	{
		echo "Not testing {$curRequire}\n";
		continue;
	}
	
	$reqFile = substr($reqFile, 1, -1);
	
	if ($reqFile == 'bootstrap.php' && strpos($filePath, '/batch/') !== false)
		continue;		// all workers find bootstrap.php since they start from that folder
		
	if (strpos($reqFile, 'Zend/') === 0)
		$reqFile = $codeRoot . '/vendor/ZendFramework/library/' . $reqFile;
	
	if (realpath($reqFile))
		continue;		// absolute require

	if (realpath(dirname($filePath). '/' . $reqFile))
		continue;		// relative require
		
	echo "Failed {$reqFile} in {$curRequire}\n";
}