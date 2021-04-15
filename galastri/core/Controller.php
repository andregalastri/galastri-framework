<?php

namespace galastri\core;

use galastri\core\Route;
use galastri\core\Debug;
// use galastri\extensions\Exception;
use galastri\modules\Toolbox;
use galastri\modules\PerformanceAnalysis;

/**
 * This is the core controller. This class stores the parameters resolved by the
 * \galastri\core\Route class and make it ready to use by the route controller (the child of this
 * class here).
 *
 * The class calls for methods of the child class and return a array with the returned data.
 */
abstract class Controller
{
    /**
     * Sets if the constructor of the class finished its instanciantion.
     *
     * @var bool
     */
    private bool $isConstructed = false;

    /**
     * Stores the returned data of the method __doBefore(), if it exists in the route controller.
     *
     * @var array
     */
    private array $doBeforeData = [];

    /**
     * Stores the returned data of the method __doAfter(), if it exists in the route controller.
     *
     * @var array
     */
    private array $doAfterData = [];

    /**
     * Stores the returned data of the route method.
     *
     * @var array
     */
    private array $controllerData = [];

    /**
     * Stores the returned data of the request method, if it exists in the route configuration
     * parameter 'requestMethod'.
     *
     * @var array
     */
    private array $requestMethodData = [];

    /**
     * Stores a merged array of $doBeforeData, $doAfterData, $controllerData and $requestMethodData
     * attributes.
     *
     * @var array
     */
    private array $resultData = [];

    /**
     * Sets if the core controller will proceed to call other methods (false) or it will stop in the
     * current method and conclude the execution (true).
     *
     * @var bool
     */
    private bool $stopControllerFlag = false;

    /**
     * Stores the projectTitle route parameter.
     *
     * @var null|string
     */
    private ?string $projectTitle;

    /**
     * Stores the pageTitle route parameter.
     *
     * @var null|string
     */
    private ?string $pageTitle;

    /**
     * Stores the authTag route parameter.
     *
     * @var null|string
     */
    private ?string $authTag;

    /**
     * Stores the authFailRedirect route parameter.
     *
     * @var null|string
     */
    private ?string $authFailRedirect;

    /**
     * Stores the output route parameter.
     *
     * @var string
     */
    private string $output;

    /**
     * Stores the viewTemplateFile route parameter.
     *
     * @var null|string
     */
    private ?string $viewTemplateFile;

    /**
     * Stores the viewBaseFolder route parameter.
     *
     * @var null|string
     */
    private ?string $viewBaseFolder;

    /**
     * Stores the fileDownloadable route parameter.
     *
     * @var bool
     */
    private bool $fileDownloadable;

    /**
     * Stores the fileBaseFolder route parameter.
     *
     * @var null|string
     */
    private ?string $fileBaseFolder;

    /**
     * Stores the viewFilePath route parameter.
     *
     * @var null|string
     */
    private ?string $viewFilePath;

    /**
     * Stores the requestMethod route parameter.
     *
     * @var array
     */
    private array $requestMethod;

    /**
     * Any route controller needs to have a main() method. This abstract method here makes it a
     * requirement that all child class need to declare.
     *
     * @return array
     */
    abstract protected function main();

    /**
     * This is just a declaration of the method __doBefore(). This method will be override by the
     * route controller, when it is needed to run a code before the route method.
     *
     * @return array
     */
    protected function __doBefore()
    {
    }

    /**
     * This is just a declaration of the method __doAfter(). This method will be override by the
     * route controller, when it is needed to run a code after the route method.
     *
     * @return array
     */
    protected function __doAfter()
    {
    }

    /**
     * When the route class is instanciated, the __construct() method will do some jobs.
     *
     * It will set the parameters defined in the \galastri\core\Route class and will store it in
     * internal attributes owned by this class. It makes things easier to get the route parameter
     * values and also set new values, if it is needed.
     *
     * Then it calls for the __doBefore() method, if it exists in the route controller, the route
     * method defined in the \galastri\core\Galastri, checks if there is additional request methods
     * available and calls it and finally calls the __doAfter() method, if it exists.
     *
     * All the results of these calls are merged into one array, stored in the $resultData
     * attribute.
     * 
     * NOTE: The __construct() method cannot be called by the route controller nor reexecuted by it.
     * That is why the attribute $isContructed is tested. When false, the construction will do what
     * is needed and then sets it to true. This way, it won't reexecute its code if it is called by
     * the route controller via parent::__construct();
     *
     * @return void
     */
    final public function __construct()
    {
        if (!$this->isConstructed) {
            // try {
                $this->setInitialParameterValues();
                $this->callDoBefore();
                $this->callControllerMethod();
                $this->callDoAfter();
                $this->mergeResults();

                $this->isConstructed = true;
            // } catch (Exception $e) {
            //     Debug::setBacklog($e->getTrace());
            //     Debug::setError($e->getMessage(), $e->getCode(), $e->getData())::print();
            // } catch (\Error | \Throwable | \Exception | \TypeError $e) {
            //     Debug::setBacklog($e->getTrace());
            //     Debug::setError($e->getMessage(), $e->getCode())::print();
            // }
        }
    }

    /**
     * Gets all pertinent route parameters and stores it in attributes owned by this class.
     * The parameters stored are:
     * 
     * Child node parameters
     * - fileDownloadable
     * - fileBaseFolder
     * - viewFilePath
     * 
     * Route paramaters
     * - projectTitle
     * - pageTitle
     * - authTag
     * - authFailRedirect
     * - output
     * - viewTemplateFile
     * - viewBaseFolder
     *
     * @return void
     */
    private function setInitialParameterValues(): void
    {
        $childNodeParam = Route::getChildNodeParam();
        $routeParam = Route::getRouteParam();

        $this->setFileDownloadable($childNodeParam['fileDownloadable']);
        $this->setFileBaseFolder($childNodeParam['fileBaseFolder']);
        $this->setViewFilePath($childNodeParam['viewFilePath']);

        $this->setProjectTitle($routeParam['projectTitle']);
        $this->setPageTitle($routeParam['pageTitle']);
        $this->setAuthTag($routeParam['authTag']);
        $this->setAuthFailRedirect($routeParam['authFailRedirect']);
        $this->setOutput($routeParam['output']);
        $this->setViewTemplateFile($routeParam['viewTemplateFile']);
        $this->setViewBaseFolder($routeParam['viewBaseFolder']);
    }

    /**
     * This method checks if the method __doBefore() exists in the route controller. If it is true,
     * then the method is called.
     * 
     * The purpouse of this is to have a method that is always executed before the route method, to
     * set some data or object that will be used before any request.
     * 
     * The __doBefore() must return an array. If not, it will throw an exception.
     * 
     * @return void
     */
    private function callDoBefore(): void
    {
        Debug::setBacklog();

        if (method_exists($this, '__doBefore')) {
            $this->doBeforeData = $this->__doBefore();

            PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
        }
    }

    /**
     * This method calls the route method define as child note in the \galastri\core\Route.
     * 
     * It also tests if there is a request method defined in the route parameter requestMethod. If
     * so, then it is called too right after the route method.
     * 
     * The route method and the request method must return an array.
     *
     * @return void
     */
    private function callControllerMethod(): void
    {
        Debug::setBacklog();

        if (!$this->stopControllerFlag) {
            $controllerMethod = Route::getChildNodeName();
            $serverRequestMethod = Toolbox::lowerCase($_SERVER['REQUEST_METHOD']);

            $this->controllerData = $this->$controllerMethod();

            if (Route::getChildNodeParam('requestMethod') and !$this->stopControllerFlag) {
                $requestMethod = Route::getChildNodeParam('requestMethod')[$serverRequestMethod];
                $this->requestMethodData = $this->$requestMethod();
            }

            PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
        }
    }

    /**
     * This method checks if the method __doAfter() exists in the route controller. If it is true,
     * then the method is called.
     * 
     * The purpouse of this is to have a method that is always executed after the route method, to
     * set some data or object that will be used after any request.
     * 
     * The __doAfter() must return an array. If not, it will throw an exception.
     * @return void
     */
    private function callDoAfter(): void
    {
        Debug::setBacklog();

        if (!$this->stopControllerFlag) {
            if (method_exists($this, '__doBefore')) {
                $this->doAfterData = $this->__doAfter();

                PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
            }
        }
    }

    /**
     * This method merges all the returning arrays of the controller into one array and stores it in
     * the $resultData attribute.
     * 
     * @return void
     */
    private function mergeResults(): void
    {
        $this->resultData = array_merge(
            $this->doBeforeData,
            $this->controllerData,
            $this->requestMethodData,
            $this->doAfterData
        );

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * prepareOutputData
     *
     * @return void
     */
    // private function prepareOutputData(): void
    // {
    //     $output = self::getOutput();

    //     if ($output === 'view') {
    //     }
    //     PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    // }

    /**
     * This sets the $stopControllerFlag atribute as true, to define that the controller needs to
     * stop proceeding on the execution of the controller chain calls.
     *
     * @return array
     */
    final protected function stopControllerFlag(): void
    {
        $this->stopControllerFlag = true;
    }

    /**
     * Returns the data processed by the __doBefore() method.
     *
     * @return array
     */
    final protected function getDoBeforeData(): array
    {
        return $this->doBeforeData;
    }

    /**
     * Returns the data processed by the route controller method.
     *
     * @return array
     */
    final protected function getControllerData(): array
    {
        return $this->controllerData;
    }

    /**
     * Returns the data processed by the route controller method.
     *
     * @return array
     */
    final public function getResultData(): array
    {
        return $this->resultData;
    }

    /**
     * Overwrites the actual value of fileDownloadable parameter set in the route configuration.
     *
     * @param  bool $fileDownloadable               The new value that will overwrite the previous
     *                                              value.
     *
     * @return void
     */
    final protected function setFileDownloadable(bool $fileDownloadable): void
    {
        $this->fileDownloadable = $fileDownloadable;
    }

    /**
     * Returns the fileDownloadable parameter value.
     *
     * @return bool
     */
    final public function getFileDownloadable(): bool
    {
        return $this->fileDownloadable;
    }

    /**
     * Overwrites the actual value of fileBaseFolder parameter set in the route configuration.
     *
     * @param  null|string $fileBaseFolder          The new value that will overwrite the previous
     *                                              value.
     * 
     * @return void
     */
    final protected function setFileBaseFolder(?string $fileBaseFolder): void
    {
        $this->fileBaseFolder = $fileBaseFolder;
    }

    /**
     * Returns the fileBaseFolder parameter value.
     *
     * @return null|string
     */
    final public function getFileBaseFolder(): ?string
    {
        return $this->fileBaseFolder;
    }

    /**
     * Overwrites the actual value of viewFilePath parameter set in the route configuration.
     *
     * @param  null|string $viewFilePath            The new value that will overwrite the previous
     *                                              value.
     * 
     * @return void
     */
    final protected function setViewFilePath(?string $viewFilePath): void
    {
        $this->viewFilePath = $viewFilePath;
    }

    /**
     * Returns the viewFilePath parameter value.
     *
     * @return null|string
     */
    final public function getViewFilePath(): ?string
    {
        return $this->viewFilePath;
    }

    /**
     * Overwrites the actual value of projectTitle parameter set in the project configuration or in
     * the route configuration.
     *
     * @param  null|string $projectTitle            The new value that will overwrite the previous
     *                                              value.
     * 
     * @return void
     */
    final protected function setProjectTitle(?string $projectTitle): void
    {
        $this->projectTitle = $projectTitle;
    }

    /**
     * Returns the projectTitle parameter value.
     *
     * @return null|string
     */
    final public function getProjectTitle(): ?string
    {
        return $this->projectTitle;
    }

    /**
     * Overwrites the actual value of pageTitle parameter set in the route configuration.
     *
     * @param  null|string $pageTitle               The new value that will overwrite the previous
     *                                              value.
     * 
     * @return void
     */
    final protected function setPageTitle(?string $pageTitle): void
    {
        $this->pageTitle = $pageTitle;
    }

    /**
     * Returns the pageTitle parameter value.
     *
     * @return null|string
     */
    final public function getPageTitle(): ?string
    {
        return $this->pageTitle;
    }

    /**
     * Overwrites the actual value of authTag parameter set in the route configuration.
     *
     * @param  null|string $authTag                 The new value that will overwrite the previous
     *                                              value.
     * 
     * @return void
     */
    final protected function setAuthTag(?string $authTag): void
    {
        $this->authTag = $authTag;
    }

    /**
     * Returns the authTag parameter value.
     *
     * @return null|string
     */
    final public function getAuthTag(): ?string
    {
        return $this->authTag;
    }

    /**
     * Overwrites the actual value of authFailRedirect parameter set in the route configuration.
     *
     * @param  null|string $authFailRedirect        The new value that will overwrite the previous
     *                                              value.
     * 
     * @return void
     */
    final protected function setAuthFailRedirect(?string $authFailRedirect): void
    {
        $this->authFailRedirect = $authFailRedirect;
    }

    /**
     * Returns the authFailRedirect parameter value.
     *
     * @return null|string
     */
    final public function getAuthFailRedirect(): ?string
    {
        return $this->authFailRedirect;
    }

    /**
     * Overwrites the actual value of output parameter set in the route configuration.
     *
     * @param  string $output                       The new value that will overwrite the previous
     *                                              value.
     * 
     * @return void
     */
    final protected function setOutput(string $output): void
    {
        $this->output = $output;
    }

    /**
     * Returns the output parameter value.
     *
     * @return string
     */
    final public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * Overwrites the actual value of viewFilePath parameter set in the project configuration or in
     * the route configuration.
     *
     * @param  null|string $viewTemplateFile        The new value that will overwrite the previous
     *                                              value.
     * 
     * @return void
     */
    final protected function setViewTemplateFile(?string $viewTemplateFile): void
    {
        $this->viewTemplateFile = $viewTemplateFile;
    }

    /**
     * Returns the viewFilePath parameter value.
     *
     * @return null|string
     */
    final public function getViewTemplateFile(): ?string
    {
        return $this->viewTemplateFile;
    }

    /**
     * Overwrites the actual value of viewBaseFolder parameter set in the route configuration.
     *
     * @param  null|string $viewBaseFolder          The new value that will overwrite the previous
     *                                              value.
     *
     * @return void
     */
    final protected function setViewBaseFolder(?string $viewBaseFolder): void
    {
        $this->viewBaseFolder = $viewBaseFolder;
    }

    /**
     * Returns the viewBaseFolder parameter value.
     *
     * @return null|string
     */
    final public function getViewBaseFolder(): ?string
    {
        return $this->viewBaseFolder;
    }

    /**
     * Returns the dynamic node value stored in the tag.
     *
     * @param  string $tag                          Tag name defined in the route configuration.
     * 
     * @return string
     */
    final public function getDynamicNodeValue(string $tag): string
    {
        return Route::getDynamicNodeValue($tag);
    }
}
