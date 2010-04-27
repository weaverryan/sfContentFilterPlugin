<?php

/**
 * Contains useful functions for filtering in the view
 * 
 * @package     sfContentFilterPlugin
 * @subpackage  helper
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */

/**
 * Filters the given content based on the given inputType
 * 
 * The inputType represents an ordered collection of filters. The content
 * will be run through those filters and the returned
 * 
 * @example
 * filter_content('My content', 'default');
 * 
 * @param string $content   The raw content that will be filtered
 * @param string $inputType The name of the input type for this content
 */
function filter_content($content, $inputType)
{
  return sfApplicationConfiguration::getActive()
    ->getPluginConfiguration('sfContentFilterPlugin')
    ->getParser()
    ->filter($content, $inputType);
}