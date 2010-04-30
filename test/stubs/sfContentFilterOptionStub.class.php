<?php
// stub class for testing
class sfContentFilterOptionStub extends sfContentFilterAbstract
{
  // simply returns the value of an option
  protected function _doFilter($content)
  {
    return $this->getOption('return').$content;
  }
}