<?php
namespace app\controllers;

use galastri\core\Controller;
use galastri\extensions\Exception;

class Page1 extends Controller
{
    protected function main()
    {
        try {
            return [ ];
        } catch (Exception $e) {
            return [];
        }
    }
}