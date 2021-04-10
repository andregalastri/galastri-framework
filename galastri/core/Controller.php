<?php
namespace galastri\core;

use \galastri\core\Route;
use \galastri\modules\Toolbox;
use \galastri\modules\PerformanceAnalysis;

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
     * Stores the solver route parameter.
     *
     * @var string
     */
    private string $solver;
        
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
     * main
     *
     * @return void
     */
    abstract protected function main();
    
    /**
     * __construct
     *
     * @return void
     */
    final public function __construct()
    {
        $this->setInitialParameterValues();
        $this->callDoBefore();
        $this->callControllerMethod();
        $this->callDoAfter();
        $this->mergeResults();
    }
    
    /**
     * setInitialParameterValues
     *
     * @return void
     */
    private function setInitialParameterValues() : void
    {
        $childNodeParam = Route::getChildNodeParam();
        $globalParam = Route::getGlobalParam();

        $this->setFileDownloadable($childNodeParam['fileDownloadable']);
        $this->setFileBaseFolder($childNodeParam['fileBaseFolder']);
        $this->setViewFilePath($childNodeParam['viewFilePath']);

        $this->setProjectTitle($globalParam['projectTitle']);
        $this->setPageTitle($globalParam['pageTitle']);
        $this->setAuthTag($globalParam['authTag']);
        $this->setAuthFailRedirect($globalParam['authFailRedirect']);
        $this->setSolver($globalParam['solver']);
        $this->setViewTemplateFile($globalParam['viewTemplateFile']);
        $this->setViewBaseFolder($globalParam['viewBaseFolder']);
    }
    
    /**
     * callDoBefore
     *
     * @return void
     */
    private function callDoBefore() : void
    {
        if (method_exists($this, '__doBefore')) {
            $this->doBeforeData = $this->__doBefore() ?? [];

            PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
        }
    }
    
    /**
     * callControllerMethod
     *
     * @return void
     */
    private function callControllerMethod() : void
    {
        if (!$this->stopControllerFlag) {
            $controllerMethod = Route::getChildNodeName();
            $serverRequestMethod = Toolbox::lowerCase($_SERVER['REQUEST_METHOD']);

            $this->controllerData = $this->$controllerMethod() ?? [];
            
            if (Route::getChildNodeParam('requestMethod') !== null) {
                $requestMethod = Route::getChildNodeParam('requestMethod')[$serverRequestMethod];
                $this->requestMethodData = $this->$requestMethod() ?? [];
            }

            PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
        }
    }
    
    /**
     * callDoAfter
     *
     * @return void
     */
    private function callDoAfter() : void
    {
        if (!$this->stopControllerFlag) {
            if (method_exists($this, '__doBefore')) {
                $this->doAfterData = $this->__doAfter() ?? [];
    
                PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
            }
        }
    }
    
    /**
     * mergeResults
     *
     * @return void
     */
    private function mergeResults() : void
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
     * stopControllerFlag
     *
     * @return void
     */
    final protected function stopControllerFlag() : void
    {
        $this->stopControllerFlag = true;
    }
    
    /**
     * getDoBeforeData
     *
     * @return void
     */
    final protected function getDoBeforeData() : array
    {
        return $this->doBeforeData;
    }
    
    /**
     * getDoAfterData
     *
     * @return void
     */
    final protected function getDoAfterData() : array
    {
        return $this->doAfterData;
    }

    /**
     * setFileDownloadable
     *
     * @param  bool $fileDownloadable
     * @return void
     */
    final protected function setFileDownloadable(bool $fileDownloadable) : void
    {
        $this->fileDownloadable = $fileDownloadable;
    }
        
    /**
     * getFileDownloadable
     *
     * @return bool
     */
    final protected function getFileDownloadable() : bool
    {
        return $this->fileDownloadable;
    }
            
    /**
     * setFileBaseFolder
     *
     * @param  null|string $fileBaseFolder
     * @return void
     */
    final protected function setFileBaseFolder(?string $fileBaseFolder) : void
    {
        $this->fileBaseFolder = $fileBaseFolder;
    }
        
    /**
     * getFileBaseFolder
     *
     * @return null|string
     */
    final protected function getFileBaseFolder() : ?string
    {
        return $this->fileBaseFolder;
    }
            
    /**
     * setViewFilePath
     *
     * @param  null|string $viewFilePath
     * @return void
     */
    final protected function setViewFilePath(?string $viewFilePath) : void
    {
        $this->viewFilePath = $viewFilePath;
    }
        
    /**
     * getViewFilePath
     *
     * @return null|string
     */
    final protected function getViewFilePath() : ?string
    {
        return $this->viewFilePath;
    }
    
    /**
     * setProjectTitle
     *
     * @param  null|string $projectTitle
     * @return void
     */
    final protected function setProjectTitle(?string $projectTitle) : void
    {
        $this->projectTitle = $projectTitle;
    }
        
    /**
     * getProjectTitle
     *
     * @return null|string
     */
    final protected function getProjectTitle() : ?string
    {
        return $this->projectTitle;
    }
    
    /**
     * setPageTitle
     *
     * @param  null|string $pageTitle
     * @return void
     */
    final protected function setPageTitle(?string $pageTitle) : void
    {
        $this->pageTitle = $pageTitle;
    }
        
    /**
     * getPageTitle
     *
     * @return null|string
     */
    final protected function getPageTitle() : ?string
    {
        return $this->pageTitle;
    }
        
    /**
     * setAuthTag
     *
     * @param  null|string $authTag
     * @return void
     */
    final protected function setAuthTag(?string $authTag) : void
    {
        $this->authTag = $authTag;
    }
        
    /**
     * getAuthTag
     *
     * @return null|string
     */
    final protected function getAuthTag() : ?string
    {
        return $this->authTag;
    }
        
    /**
     * setAuthFailRedirect
     *
     * @param  null|string $authFailRedirect
     * @return void
     */
    final protected function setAuthFailRedirect(?string $authFailRedirect) : void
    {
        $this->authFailRedirect = $authFailRedirect;
    }
        
    /**
     * getAuthFailRedirect
     *
     * @return null|string
     */
    final protected function getAuthFailRedirect() : ?string
    {
        return $this->authFailRedirect;
    }
        
    /**
     * setSolver
     *
     * @param  string $solver
     * @return void
     */
    final protected function setSolver(string $solver) : void
    {
        $this->solver = $solver;
    }
        
    /**
     * getSolver
     *
     * @return string
     */
    final protected function getSolver() : string
    {
        return $this->solver;
    }
        
    /**
     * setViewTemplateFile
     *
     * @param  null|string $viewTemplateFile
     * @return void
     */
    final protected function setViewTemplateFile(?string $viewTemplateFile) : void
    {
        $this->viewTemplateFile = $viewTemplateFile;
    }
        
    /**
     * getViewTemplateFile
     *
     * @return null|string
     */
    final protected function getViewTemplateFile() : ?string
    {
        return $this->viewTemplateFile;
    }
            
    /**
     * setViewBaseFolder
     *
     * @param  null|string $viewBaseFolder
     * @return void
     */
    final protected function setViewBaseFolder(?string $viewBaseFolder) : void
    {
        $this->viewBaseFolder = $viewBaseFolder;
    }
        
    /**
     * getViewBaseFolder
     *
     * @return null|string
     */
    final protected function getViewBaseFolder() : ?string
    {
        return $this->viewBaseFolder;
    }
        
    /**
     * getDynamicNodeValue
     *
     * @param  string $key
     * @return string
     */
    final protected function getDynamicNodeValue(string $key) : string
    {
        return Route::getDynamicNodeValue($key);
    }
}
