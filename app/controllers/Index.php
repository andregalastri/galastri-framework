<?php

namespace app\controllers;

use galastri\core\Controller;
use galastri\extensions\Exception;

/**
 * This is a route controller. It is defined by the parent node and its name needs to be in pascal
 * case.
 *
 * Every route controller needs to extend the core controller.
 *
 * Methods inside the route controller that has the same name of the child node will be called when
 * the route points to them. Other methods can be created for better coding.
 *
 * The main method is required in every route controller and refers to the index of the route. All
 * other child nodes will have its own methods, following the name defined in the route
 * configuration in camel case.
 *
 * There are, also, two special methods:
 *
 *   __doBefore() : It is called in every request, regardless which child node the route is
 *   pointing. It is executed before the route method.
 *
 *   __doAfter() : It is called in every request, regardless which child node the route is pointing.
 *   It is executed after the route method.
 *
 * There are, also, the request methods. These methods are called after the route method and is
 * called based on the request method (POST, GET, PUT, etc). It needs to be configured in the route
 * configuration, with 'requestMethod' paramenter.
 */
class Index extends Controller
{
    /**
     * Optional method that is executed before the route method.
     *
     * @return array
     */
    protected function __doBefore(): array
    {
        try {
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Main method that is required in all route controllers. This method refers to the index of the
     * route.
     *
     * @return array
     */
    protected function main(): array
    {
        try {
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Optional method that is executed after the route method.
     *
     * @return void
     */
    protected function __doAfter(): array
    {
        try {
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
}
