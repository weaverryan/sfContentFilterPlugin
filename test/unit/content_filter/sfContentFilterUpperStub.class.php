<?php
// stub class for testing
class sfContentFilterUpperStub extends sfContentFilterAbstract
{
  // simply capitalizes everything
  protected function _doFilter($content)
  {
    return strtoupper($content);
  }
}