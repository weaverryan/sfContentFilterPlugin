<?php

require dirname(__FILE__).'/../../bootstrap/unit.php';
require dirname(__FILE__).'/../../bootstrap/stubs.php';

$testCases = array(
  ' No twitter stuff here ' => ' No twitter stuff here ',
  'No twitter stuff here' => 'No twitter stuff here',
  'Contains @weaverryan in the middle' => 'Contains <a href="http://www.twitter.com/weaverryan">@weaverryan</a> in the middle',
  'Contains @weaverryan' => 'Contains <a href="http://www.twitter.com/weaverryan">@weaverryan</a>',
  'Try a #hashtag in the middle' => 'Try a <a href="http://search.twitter.com/search?q=%23hashtag">#hashtag</a> in the middle',
  'Try a #hashtag' => 'Try a <a href="http://search.twitter.com/search?q=%23hashtag">#hashtag</a>',
  '@weaverryan programs in #symfony' => '<a href="http://www.twitter.com/weaverryan">@weaverryan</a> programs in <a href="http://search.twitter.com/search?q=%23symfony">#symfony</a>',
);

$t = new lime_test(count($testCases) + 1);

$filter = new sfContentFilterTwitter(array(
  'username_link_attributes'  => array(),
  'hash_link_attributes'      => array(),
));

foreach ($testCases as $from => $to)
{
  $t->is($filter->filter($from), $to, sprintf('%s => %s', $from, $to));
}

$t->info('Test the filter with hash attributes');
$from = '@weaverryan programs in #symfony';
$to = '<a href="http://www.twitter.com/weaverryan" class="username_class">@weaverryan</a> programs in <a href="http://search.twitter.com/search?q=%23symfony" class="hash_class">#symfony</a>';
$filter->setOption('username_link_attributes', array('class' => 'username_class'));
$filter->setOption('hash_link_attributes', array('class' => 'hash_class'));

$t->is($filter->filter($from), $to);