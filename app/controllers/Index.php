<?php

namespace app\controllers;

use galastri\core\Controller;
use galastri\modules\types\TypeString;


/**
 * This is a route controller. Its defined by the parent node and its name needs to be in pascal
 * case.
 *
 * Every route controller needs to extend the core controller.
 *
 * Each method inside the route controller refers to a child node and its name needs to be in camel
 * case.
 *
 * The main() method is required in every route controller. The main() controller means the index of
 * the route. All other child nodes will have its own methods, following the name defined in the
 * route configuration.
 *
 * There are, also, two special methods:
 *
 *   __doBefore() : It is called in every request to the parent node, regardless which child node
 *   the route is pointing. It is executed before the route method.
 *
 *   __doAfter() : It is called in every request to the parent node, regardless which child node
 *   the route is pointing. It is executed after the route method.
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
        return [];
    }

    /**
     * Main method that is required in all route controllers. This method refers to the index of the
     * route.
     *
     * @return array
     */
    protected function main(): array
    {
//         $myString = new TypeString();
//         $myString
//                 ->denyEmpty()
//                     ->onError('Campo obrigatorio')
//                  ->maxLength(5)
//                      ->onError('nao pode mais que 5')
//                      ->validate()
//                      ;
// // 
//             $myString->setValue('12312231213213');
//         echo '<br>';
        return [];
    }

    /**
     * Main method that is required in all route controllers. This method refers to the index of the
     * route.
     *
     * @return array
     */
    protected function notFound(): array
    {
        return [];
    }

    /**
     * Optional method that is executed after the route method.
     *
     * @return void
     */
    protected function __doAfter(): array
    {
        return [];
    }
}
