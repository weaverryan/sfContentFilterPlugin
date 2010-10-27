<?php

require dirname(__FILE__).'/../../bootstrap/unit.php';
require dirname(__FILE__).'/../../bootstrap/stubs.php';

$testCases = array(
  ' No url here ' => ' No url here ',
  ' http://www.sympalphp.com ' => ' <a href="http://www.sympalphp.com" title="http://www.sympalphp.com">http://www.sympalphp.com</a> ',
  ' www.sympalphp.com ' => ' <a href="http://www.sympalphp.com" title="www.sympalphp.com">www.sympalphp.com</a> ',
  ' Go to www.sympalphp.com site. ' => ' Go to <a href="http://www.sympalphp.com" title="www.sympalphp.com">www.sympalphp.com</a> site. ',
  ' Long: http://www.sympalphp.org/documentation/1_0/book/introduction/en ' => ' Long: <a href="http://www.sympalphp.org/documentation/1_0/book/introduction/en" title="http://www.sympalphp.org/documentation/1_0/book/introduction/en">http://www.sympalphp.org/docum...</a> ',
  'A string with the url at the end: http://symfony-reloaded.org' => 'A string with the url at the end: <a href="http://symfony-reloaded.org" title="http://symfony-reloaded.org">http://symfony-reloaded.org</a>'
);

$t = new lime_test(count($testCases) + 1);

$filter = new sfContentFilterUrl(array('max_text_length' => 30));

foreach ($testCases as $from => $to)
{
  $t->is($filter->filter($from), $to, sprintf('%s => %s', $from, $to));
}

$t->info('Test the filter with some link attributes set');
$from = 'Find Symfony2 at http://symfony-reloaded.org';
$to = 'Find Symfony2 at <a href="http://symfony-reloaded.org" title="http://symfony-reloaded.org" class="test_class">http://symfony-reloaded.org</a>';
$filter->setOption('link_attributes', array('class' => 'test_class'));

$t->is($filter->filter($from), $to);