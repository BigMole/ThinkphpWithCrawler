<?php
namespace Test;

//use Test;



function autoload($classId)
{
    $classIdParts       = explode("\\", $classId);
    $classIdLength      = count($classIdParts);
    $className          = strtolower($classIdParts[$classIdLength - 1]);
    $namespace          = strtolower($classIdParts[0]);






    for ($i = 1; $i < $classIdLength - 1; $i++) {
        $namespace .= '/' . $classIdParts[$i];
    }

    if (file_exists(dirname(__FILE__))
        . '/' . $namespace
        . '/' . $className
        . '.class.php') {
        include $namespace . '/' . $className . '.php';
    }



}

spl_autoload_register('Test\autoload');



$x=new n1();

$x->n1();
?>