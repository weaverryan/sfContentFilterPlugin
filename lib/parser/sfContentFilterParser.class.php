<?php

/**
 * Parses a given string through a collection of filters.
 * 
 * This uses the singleton pattern (perhaps regrettably).
 * 
 * @package     sfContentFilterPlugin
 * @subpackage  parser
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */

class sfContentFilterParser
{
  protected $_cacheDriver;
  
  protected
    $_filters = array();

  /**
   * Parses the given content through the given inputType filter collection
   * 
   * @param string $content   The raw content that will be filtered
   * @param string $inputType The name of the input type for this content
   * 
   * @return $content
   */
  public function filter($content, $inputType)
  {
    $filters = sfConfig::get('app_content_filter_input_types', array());
    
    if (!isset($filters[$inputType]))
    {
      throw new sfException(sprintf('Unrecognized input type "%s" given', $inputType));
    }

    /*
     * @TODO Implement caching right here
     */
    $filters = $filters[$inputType];
    foreach ($filters as $filter)
    {
      $filterObject = $this->_getFilter($filter);
      $content = $filterObject->filter($content);
    }
    
    return $content;
  }

  /**
   * Returns the cache driver
   * 
   * @return sfCache or null
   */
  public function getCacheDriver()
  {
    return $this->_cache;
  }

  /**
   * Sets the cache driver to be used by the parser
   * 
   * @param sfCache $cacheDriver
   */
  public function setCacheDriver(sfCache $cacheDriver)
  {
    $this->_cacheDriver = null;
  }

  /**
   * Returns the filter object given by the filter name
   * 
   * @param string $filter The name of the filter object to return
   */
  protected function _getFilter($filter)
  {
    if (!isset($this->_filters[$filter]))
    {
      $filtersConfig = sfConfig::get('app_content_filter_filters', array());
      if (!isset($filtersConfig[$filter]))
      {
        throw new sfException(sprintf('Unrecognized filter "%s" given', $filter));
      }
      
      $filterConfig = $filtersConfig[$filter];
      $class = $filterConfig['class'];
      $options = isset($filterConfig['options']) ? $filterConfig['options'] : array();
      
      $this->_filters[$filter] = new $class($options);
    }
    
    return $this->_filters[$filter];
  }
}