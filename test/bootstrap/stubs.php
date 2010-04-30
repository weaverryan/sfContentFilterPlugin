<?php

$stubs = array(
  'sfContentFilterUpperStub',
  'sfContentFilterSubstringStub',
  'sfContentFilterOptionStub',
);

foreach ($stubs as $stub)
{
  require_once dirname(__FILE__).'/../stubs/'.$stub.'.class.php';
}