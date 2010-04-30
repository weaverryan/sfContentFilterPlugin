<?php

require dirname(__FILE__).'/../../bootstrap/unit.php';
require dirname(__FILE__).'/../../bootstrap/stubs.php';

$t = new lime_test(4);

$filterOptions = array(
  'option1' => 'option value',
  'return'  => 'unit test return',
);
$filter = new sfContentFilterOptionStub($filterOptions);

$t->is($filter->getOption('option1', 'not used'), 'option value', 'Value for a real option is returned');
$t->is($filter->getOption('option2', 'default val'), 'default val', 'Default is returned for non-existent option');
$t->is($filter->getOptions(), $filterOptions, '->getOptions() returns the correct options');

$t->is($filter->filter('test content'), 'unit test returntest content', '->filter() returns the correct value');