<?php
namespace galastri\core;

use \galastri\core\Route;
use \galastri\modules\Functions as F;
use \galastri\modules\PerformanceAnalysis;

/**
 * Controller
 */
abstract class Controller
{    
    /**
     * doBeforeData
     *
     * @var array
     */
    private $doBeforeData = [];
        
    /**
     * doAfterData
     *
     * @var array
     */
    private $doAfterData = [];
        
    /**
     * controllerData
     *
     * @var array
     */
    private $controllerData = [];  
      
    /**
     * requestMethodData
     *
     * @var array
     */
    private $requestMethodData = [];   

    /**
     * resultData
     *
     * @var array
     */
    private $resultData = [];
        
    /**
     * stopControllerFlag
     *
     * @var bool
     */
    private $stopControllerFlag = false;
    
    /**
     * projectTitle
     *
     * @var mixed
     */
    private $projectTitle;
        
    /**
     * pageTitle
     *
     * @var mixed
     */
    private $pageTitle;
        
    /**
     * authTag
     *
     * @var mixed
     */
    private $authTag;
        
    /**
     * authFailRedirect
     *
     * @var mixed
     */
    private $authFailRedirect;
        
    /**
     * solver
     *
     * @var mixed
     */
    private $solver;
        
    /**
     * viewTemplateFile
     *
     * @var mixed
     */
    private $viewTemplateFile;
        
    /**
     * viewBaseFolder
     *
     * @var mixed
     */
    private $viewBaseFolder;
        
    /**
     * fileDownloadable
     *
     * @var mixed
     */
    private $fileDownloadable;
        
    /**
     * fileBaseFolder
     *
     * @var mixed
     */
    private $fileBaseFolder;
        
    /**
     * viewFilePath
     *
     * @var mixed
     */
    private $viewFilePath;
        
    /**
     * requestMethod
     *
     * @var mixed
     */
    private $requestMethod;
    
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
    private function setInitialParameterValues()
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
    private function callDoBefore()
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
    private function callControllerMethod()
    {
        if (!$this->stopControllerFlag) {
            $controllerMethod = Route::getChildNodeName();
            $serverRequestMethod = F::lowerCase($_SERVER['REQUEST_METHOD']);

            $this->controllerData = $this->$controllerMethod() ?? [];
            
            if (Route::getChildNodeParam('requestMethod') !== false) {
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
    private function callDoAfter()
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
    private function mergeResults()
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
    final protected function stopControllerFlag()
    {
        $this->stopControllerFlag = true;
    }
    
    /**
     * getDoBeforeData
     *
     * @return void
     */
    final protected function getDoBeforeData()
    {
        return $this->doBeforeData;
    }
    
    /**
     * getDoAfterData
     *
     * @return void
     */
    final protected function getDoAfterData()
    {
        return $this->doAfterData;
    }

    /**
     * setFileDownloadable
     *
     * @param  mixed $fileDownloadable
     * @return void
     */
    final protected function setFileDownloadable(bool $fileDownloadable)
    {
        $this->fileDownloadable = $fileDownloadable;
    }
        
    /**
     * getFileDownloadable
     *
     * @return void
     */
    final protected function getFileDownloadable()
    {
        return $this->fileDownloadable;
    }
            
    /**
     * setFileBaseFolder
     *
     * @param  mixed $fileBaseFolder
     * @return void
     */
    final protected function setFileBaseFolder(mixed $fileBaseFolder)
    {
        $this->fileBaseFolder = $fileBaseFolder;
    }
        
    /**
     * getFileBaseFolder
     *
     * @return void
     */
    final protected function getFileBaseFolder()
    {
        return $this->fileBaseFolder;
    }
            
    /**
     * setViewFilePath
     *
     * @param  mixed $viewFilePath
     * @return void
     */
    final protected function setViewFilePath(mixed $viewFilePath)
    {
        $this->viewFilePath = $viewFilePath;
    }
        
    /**
     * getViewFilePath
     *
     * @return void
     */
    final protected function getViewFilePath()
    {
        return $this->viewFilePath;
    }
    
    /**
     * setProjectTitle
     *
     * @param  mixed $projectTitle
     * @return void
     */
    final protected function setProjectTitle(mixed $projectTitle)
    {
        $this->projectTitle = $projectTitle;
    }
        
    /**
     * getProjectTitle
     *
     * @return void
     */
    final protected function getProjectTitle()
    {
        return $this->projectTitle;
    }
    
    /**
     * setPageTitle
     *
     * @param  mixed $pageTitle
     * @return void
     */
    final protected function setPageTitle(mixed $pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }
        
    /**
     * getPageTitle
     *
     * @return void
     */
    final protected function getPageTitle()
    {
        return $this->pageTitle;
    }
        
    /**
     * setAuthTag
     *
     * @param  mixed $authTag
     * @return void
     */
    final protected function setAuthTag(mixed $authTag)
    {
        $this->authTag = $authTag;
    }
        
    /**
     * getAuthTag
     *
     * @return void
     */
    final protected function getAuthTag()
    {
        return $this->authTag;
    }
        
    /**
     * setAuthFailRedirect
     *
     * @param  mixed $authFailRedirect
     * @return void
     */
    final protected function setAuthFailRedirect(mixed $authFailRedirect)
    {
        $this->authFailRedirect = $authFailRedirect;
    }
        
    /**
     * getAuthFailRedirect
     *
     * @return void
     */
    final protected function getAuthFailRedirect()
    {
        return $this->authFailRedirect;
    }
        
    /**
     * setSolver
     *
     * @param  mixed $solver
     * @return void
     */
    final protected function setSolver(mixed $solver)
    {
        $this->solver = $solver;
    }
        
    /**
     * getSolver
     *
     * @return void
     */
    final protected function getSolver()
    {
        return $this->solver;
    }
        
    /**
     * setViewTemplateFile
     *
     * @param  mixed $viewTemplateFile
     * @return void
     */
    final protected function setViewTemplateFile(mixed $viewTemplateFile)
    {
        $this->viewTemplateFile = $viewTemplateFile;
    }
        
    /**
     * getViewTemplateFile
     *
     * @return void
     */
    final protected function getViewTemplateFile()
    {
        return $this->viewTemplateFile;
    }
            
    /**
     * setViewBaseFolder
     *
     * @param  mixed $viewBaseFolder
     * @return void
     */
    final protected function setViewBaseFolder(mixed $viewBaseFolder)
    {
        $this->viewBaseFolder = $viewBaseFolder;
    }
        
    /**
     * getViewBaseFolder
     *
     * @return void
     */
    final protected function getViewBaseFolder()
    {
        return $this->viewBaseFolder;
    }
        
    /**
     * getDynamicNodeValue
     *
     * @param  mixed $key
     * @return void
     */
    final protected function getDynamicNodeValue(mixed $key = false)
    {
        return Route::getDynamicNodeValue($key);
    }
}
