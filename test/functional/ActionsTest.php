<?php
require dirname(__FILE__).'/../bootstrap/functional.php';

$rendered = '<div class="markdown"><p>Visit <a href="http://www.sympalphp.org" title="www.sympalphp.org">www.sympalphp.org</a> for information on a CMF toolkit built on <em>symfony</em>.</p>'."\n</div>";

$browser = new sfTestFunctional(new sfBrowser());

$browser->info('1 - Perform some sanity checks on rendering')
  
  ->info('  1.1 - Use the parser entirely in an action')
  ->get('/test/action')
  
  ->with('response')->begin()
    ->isStatusCode(200)
  ->end()
;
$browser->test()->is($browser->getResponse()->getContent(), $rendered, 'The output is what we expect');

$browser
  ->info('  1.2 - Use the parser entirely in a template')
  ->get('/test/view')
  
  ->with('response')->begin()
    ->isStatusCode(200)
  ->end()
;
$browser->test()->is($browser->getResponse()->getContent(), $rendered, 'The output is what we expect');