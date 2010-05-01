<?php

require dirname(__FILE__).'/../../bootstrap/unit.php';
require dirname(__FILE__).'/../../bootstrap/stubs.php';

$t = new lime_test(29);


$t->info('1 - Test basics of setting filters, setting cache, etc');

$parser = new sfContentFilterParser();

$t->info('  1.1 - ->getFilter() on a non-existent filter throws an exception');
try
{
  $parser->getFilterConfig('fake_filter', 'anything');
  $t->fail('Exception not thrown');
}
catch (sfException $e)
{
  $t->pass($e->getMessage());
}

$t->info('  1.2 - Add a filter, retrieve its configuration');
$filterConfiguration = array(
  'class'   => 'sfMyContentFilter',
  'cache'   => true,
  'options' => array('do_something' => 'awesome'),
);

$parser->setFilter('my_filter', $filterConfiguration['class'], $filterConfiguration['cache'], $filterConfiguration['options']);
$t->is($parser->getFilterConfig('my_filter', 'class'), $filterConfiguration['class'], '->getFilter() returns the filter config without an exception');
$t->is($parser->getFilterConfig('my_filter', 'cache'), $filterConfiguration['cache'], '->getFilter() returns the filter config without an exception');
$t->is($parser->getFilterConfig('my_filter', 'options'), $filterConfiguration['options'], '->getFilter() returns the filter config without an exception');

$t->info('  1.3 - Add a filter an an input type in the constructor');
$parser = new sfContentFilterParser(array('my_filter' => $filterConfiguration), array(
  'test_input_type' => array('my_filter')
));

$t->is($parser->getFilterConfig('my_filter', 'class'), $filterConfiguration['class']);
$t->is($parser->getInputType('test_input_type'), array('my_filter'));


$t->info('2 - Test some simple filter');

$testContent = 'The things you own end up owning you.';
$testToUpper = 'THE THINGS YOU OWN END UP OWNING YOU.';

$parser = new sfContentFilterParser();

$t->info('2.1 - Parse with no filters, make sure nothing is done');
$parser->filter('test', array(), 'test');

$parser->setFilter('upper', 'sfContentFilterUpperStub', true, array());
$parser->setFilter('substring', 'sfContentFilterSubstringStub', true, array('length' => 7));

$parser->setInputType('just_upper', array('upper'));
$parser->setInputType('all', array('upper', 'substring'));
$parser->setInputType('error', array('upper', 'fake'));

$t->info('  2.2 - Run an input type that applies only the upper filter');
$t->is($parser->filter($testContent, 'just_upper'), $testToUpper);

$t->info('  2.3 - Run an input type that applies both filters');
$t->is($parser->filter($testContent, 'all'), 'THE THI');

$t->info('  2.4 - Run an input type with an invalid filter, throws an exception');
try
{
  $parser->filter($testContent, 'error');
  $t->fail('No exception thrown');
}
catch (sfException $e)
{
  $t->pass($e->getMessage());
}

$t->info('  2.5 - Try running the parser by passing it filters');
$t->is($parser->filter($testContent, array('upper')), $testToUpper);
$t->is($parser->filter($testContent, 'upper'), $testToUpper);

$t->is($parser->filter($testContent, array('upper', 'substring')), 'THE THI');


$t->info('3 - Test some simple caching');

sfToolkit::clearDirectory('/tmp/content_filter');
$cache = new sfFileCache(array(
  'cache_dir' => '/tmp/content_filter',
));

$t->is($parser->getCacheDriver(), null, '->getCacheDriver() returns null before setting the driver');
$parser->setCacheDriver($cache);
$t->is($parser->getCacheDriver(), $cache, '->getCacheDriver() returns the correct cache driver');

// Add a non-cacheable filter
$parser->setFilter('option', 'sfContentFilterOptionStub', false, array('return' => 'test'));

$key = 'filters_upper_'.md5($testContent);
$t->is($cache->get($key), false, 'Sanity check that the cache starts empty');

$t->info('  3.1 - Test a cacheable filter, see that the cache sets correctly');
$parser->filter($testContent, 'upper');
$t->is($cache->get($key), $testToUpper, 'The cache set correctly');

$t->info('  3.2 - Mutate the cache, see that the mutation is returned');
$cache->set($key, 'something totally different');

$t->is($parser->filter($testContent, 'upper'), 'something totally different');
$t->is($cache->get($key), 'something totally different', 'The cache stayed mutated');


$t->info('  4 - Test a more complex chain: cacheable, noncacheable, cacheable');
sfToolkit::clearDirectory('/tmp/content_filter');

// Dummy key - should not actually cache to this, but we'll check that
$key2 = 'filters_option_'.md5($testToUpper);

/*
 * The first key will be the same, but the second key (from the third
 * filter) will use the content returned from the second, noncacheable,
 * filter in its key. That filter will simply return the pevious content
 * plus "test return value"
 */
$key3 = 'filters_substring_'.md5('test'.$testToUpper);

/*
 * This chain
 *   1) Capitalizes our original sentence "THE THINGS YOU OWN..."
 *   2) Prepends the word "test"
 *   3) Substrings the first 7 letters
 */
$result = $parser->filter($testContent, array('upper', 'option', 'substring'));
$t->is($result, 'testTHE', 'The overall filter chain returns the correct value');

$t->info('  4.1 - Check that the individual pieces cached');
$t->is($cache->get($key), $testToUpper, 'The first part of the chain cached');
$t->is($cache->get($key2), false, 'The second part of the chain did NOT cache');
$t->is($cache->get($key3), 'testTHE', 'The third part of the chain cached');


$t->info('  4.2 - Test another complex caching configuration: cacheable, cacheable, noncacheable');
sfToolkit::clearDirectory('/tmp/content_filter');

$key = 'filters_substring_upper_'.md5($testContent);

$result = $parser->filter($testContent, array('substring', 'upper', 'option'));
$t->is($result, 'testTHE THI', 'The overall filter chain returns the correct value');

$t->is($cache->get($key), 'THE THI', 'The first cache group returned the correct cache');

$t->info('  4.3 - Mutate the cache and re-filter');
$cache->set($key, 'totally different');
$result = $parser->filter($testContent, array('substring', 'upper', 'option'));
$t->is($result, 'testtotally different', 'The mutated cache is used');


$t->info('5 - Test the static bootstrap method');
require dirname(__FILE__).'/../../bootstrap/functional.php';

$parser = sfContentFilterParser::createInstance();
$t->is(get_class($parser), 'sfContentFilterTestParser', 'The class is sfContentFilterTestParser');
$t->is($parser->getInputType('upper_substring'), array('upper', 'substring'), '->getInputType() returns a config input type');
$t->is($parser->getFilterConfig('upper', 'class'), 'sfContentFilterUpperStub', '->getFilterConfig returns a valid value.');
$t->is(get_class($parser->getCacheDriver()), 'sfFileCache', 'The cache is set correctly');
