<?php
$dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
$fname=str_replace('Test', '', basename(__FILE__));
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);

require_once $baseDir.$relDir.'atmAutoloadDefaultRule.php';

class testAutoloadContainerRule extends atmAutoloadDefaultRule
{
    
}
