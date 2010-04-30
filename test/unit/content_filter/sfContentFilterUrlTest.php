<?php

require dirname(__FILE__).'/../../bootstrap/unit.php';
require dirname(__FILE__).'/../../bootstrap/stubs.php';

$testCases = array(
  ' No url here ' => ' No url here ',
  ' http://www.sympalphp.com ' => ' <a href="http://www.sympalphp.com" title="http://www.sympalphp.com">http://www.sympalphp.com</a> ',
  ' www.sympalphp.com ' => ' <a href="http://www.sympalphp.com" title="www.sympalphp.com">www.sympalphp.com</a> ',
  ' Go to www.sympalphp.com site. ' => ' Go to <a href="http://www.sympalphp.com" title="www.sympalphp.com">www.sympalphp.com</a> site. ',
  ' Long: http://www.sympalphp.org/documentation/1_0/book/introduction/en ' => ' Long: <a href="http://www.sympalphp.org/documentation/1_0/book/introduction/en" title="http://www.sympalphp.org/documentation/1_0/book/introduction/en">http://www.sympalphp.org/docum...</a> ',
);

$t = new lime_test(count($testCases));

$filter = new sfContentFilterUrl(array('max_text_length' => 30));

foreach ($testCases as $from => $to)
{
  $t->is($filter->filter($from), $to, sprintf('%s => %s', $from, $to));
}
