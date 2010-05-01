<?php

// test actions class
class testActions extends sfActions
{
  public function preExecute()
  {
    $this->phrase = 'Visit www.sympalphp.org for information on a CMF toolkit built on _symfony_.';
  }
  
  public function executeFromAction()
  {
    $parser = $this->getContentFilterParser();
    
    $content = $parser->filter($this->phrase, array('url', 'markdown'));
    $this->renderText($content);
    
    return sfView::NONE;
  }

  public function executeFromView()
  {
    $this->setLayout(false);
  }
}