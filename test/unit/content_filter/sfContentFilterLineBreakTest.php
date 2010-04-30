<?php

require dirname(__FILE__).'/../../bootstrap/unit.php';
require dirname(__FILE__).'/../../bootstrap/stubs.php';

$testCases = array(
  'Just add paragraphs' => "<p>Just add paragraphs</p>\n",
  "One line\nbreak"   => "<p>One line<br />\nbreak</p>\n",
  "Two line\n\nbreaks"   => "<p>Two line</p>\n<p>breaks</p>\n",
  "<p>html present\n\nNo breaking</p>" => "<p>html present\n\nNo breaking</p>",
);

$t = new lime_test(count($testCases));

$filter = new sfContentFilterLineBreak();

foreach ($testCases as $from => $to)
{
  $t->is($filter->filter($from), $to, sprintf('%s => %s', $from, $to));
}
