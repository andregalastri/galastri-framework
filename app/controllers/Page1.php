<?php
namespace app\controllers;

class Page1 extends \galastri\core\Controller
{
    protected function __doBefore()
    {
        return ['__doBefore' => '1'];
    }

    protected function main()
    {
        return ['main' => '2'];
    }

    protected function myPutMethod()
    {
        echo 'eeeee';
        return ['myPutMethod' => '4'];
    }

    protected function __doAfter()
    {
        return ['__doAfter' => '3'];
    }
}