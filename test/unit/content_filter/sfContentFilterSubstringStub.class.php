<?php
// stub class for testing
class sfContentFilterSubstringStub extends sfContentFilterAbstract
{
  // simply returns the first XXX characters
  protected function _doFilter($content)
  {
    return substr($content, 0, $this->getOption('length', 10));
  }
}