<?php

/**
 * Contains useful functions for filtering in the view
 * 
 * @package     sfContentFilterPlugin
 * @subpackage  helper
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */

/**
 * Filters the given content based on the given inputType or array of filters
 * 
 * The inputType represents an ordered collection of filters. The content
 * will be run through those filters and the returned
 * 
 * @example
 * filter_content('My content', 'default');
 * 
 * @param string $content   The raw content that will be filtered
 * @param mixed $filters Either an array of filters or the name of an "input type"
 */
function filter_content($content, $filters)
{
  return sfApplicationConfiguration::getActive()
    ->getPluginConfiguration('sfContentFilterPlugin')
    ->getParser()
    ->filter($content, $filters);
}