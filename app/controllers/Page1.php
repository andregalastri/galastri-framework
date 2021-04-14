<?php
namespace app\controllers;

use galastri\core\Controller;

class Page1 extends Controller
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
        return ['myPutMethod' => '4'];
    }

    protected function __doAfter()
    {
        return ['__doAfter' => '3'];
    }
}