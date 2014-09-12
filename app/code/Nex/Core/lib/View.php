<?php

namespace App\Nex\Core ;
use Nex\System\Nex ;

class View_Lib
{
    protected $viewPath ;
    protected $baseDir = 'view/' ;

    public function __construct($file)
    {
        $file .= Nex::config('design.view.ext');

        $this->viewPath = $this->findFile($file);
    }

    public function render()
    {
        include(DOC_ROOT.$this->viewPath);
    }

    protected function findFile($file)
    {
       return Nex::findDesignFile($this->baseDir.$file);
    }
}
