<?php

namespace App\Nex\Core ;
use Nex\System\Nex ;

class View
{
    protected $vars = array();
    protected $viewPath ;
    protected $file ;
    protected $baseDir = 'view/' ;

    public function __construct($file)
    {
        $this->file = $file .= Nex::config('design.view.ext');

        $this->viewPath = $this->_findFile($file);
    }

    public function set($key, $val)
    {
        $this->vars[$key] = $val ;
    }

    public function assign(array $vars)
    {
        $this->vars = array_merge($this->vars, $vars) ;

        return $this ;
    }

    public function inlineController($name)
    {
        return Nex::newObj($name.Nex::CTRLR_SUFFIX);
    }

    public function inlineView($file, $vars = array())
    {
        $view = new View($file);
        $view->assign(array_merge($this->vars, $vars));

        return $view->render(false);
    }

    public function render($_now = true)
    {
        if ( !$this->viewPath ) {
            trigger_error('Could not find "'.$this->file.'" in "'.$this->baseDir.'"', E_USER_NOTICE);
            return false ;
        }

        extract($this->vars, EXTR_SKIP);

        if ( $_now ) {
            include(DOC_ROOT.$this->viewPath);
        }
        else {
            ob_start();
            include(DOC_ROOT.$this->viewPath);
            return ob_get_clean();
        }
    }

    protected function _findFile($file)
    {
       return Nex::findDesignFile($this->baseDir.$file);
    }
}
