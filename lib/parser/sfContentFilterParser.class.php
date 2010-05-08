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

  /**
   * @var array An array of filters configuration.
   */
  protected  
    $_filtersConfig = array(),
    $_inputTypes = array();

  /**
   * @var sfCache The driver to use for caching
   */
  protected $_cacheDriver;

  /**
   * An internal cache of filter objects
   */
  protected $_filterObjects = array();

  /**
   * Class constructor
   * 
   * @param array $filters  An array of filters to use. Each filter should consist
   *                        of a class, cache and options key
   * @param array $inputTypes An array of the input types, each with an array
   *                          that holds their their filters
   */
  public function __construct($filters = array(), $inputTypes = array())
  {
    $this->_filtersConfig = $filters;
    $this->_inputTypes = $inputTypes;
  }

  /**
   * Parses the given content through the given inputType filter collection
   * 
   * @param string $content   The raw content that will be filtered
   * @param mixed $filters Either a string of the input type OR an array of filters
   * 
   * @return $content
   */
  public function filter($content, $filters)
  {
    $filterList = $this->_getFilterGroups($filters);

    /*
     * Iterate through each filter group, see if the cache exists and
     * perform the filter if it is not cached (or cacheable)
     */
    foreach ($filterList as $filterGroup)
    {
      // Calculate the cache key, based on if the group is actually cacheable
      $cacheKey = $filterGroup['cache'] ? $filterGroup['cache'].'_'.md5($content) : false;
      /*
       * If the group is cacheable and a cache exists, read in that cache
       * and continue to the next group
       */
      if ($cacheKey && $cached = $this->_getCache($cacheKey))
      {
        $content = $cached;
      }
      else
      {
        // No cache found, so we'll create it

        foreach ($filterGroup['filters'] as $filter)
        {
          $filterObject = $this->_getFilterObject($filter);
          $content = $filterObject->filter($content);
        }

        // If a cache key exists (meaning group is cacheable), set the cache
        if ($cacheKey)
        {
          $this->_setCache($cacheKey, $content);
        }
      }
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
    return $this->_cacheDriver;
  }

  /**
   * Sets the cache driver to be used by the parser
   * 
   * @param sfCache $cacheDriver
   */
  public function setCacheDriver(sfCache $cacheDriver)
  {
    $this->_cacheDriver = $cacheDriver;
  }

  /**
   * Returns the array of filter names needed to filter the given input type
   * 
   * This organizes the individual filters into groups in the array based
   * on whether or not each is cachable. If all are cacheable, you'll have
   * just one group.
   * 
   * Suppose we have the following filter chain:
   *  * A (not cacheable)
   *  * B (cacheable)
   *  * C (cacheable)
   *  * D (not cacheable)
   * 
   * The array will return these in three groups:
   * 
   *  Group 0:
   *    filters:   A
   *    cache:     false
   *  Group 1:
   *    filters:   B,C
   *    cache:     generated cache key
   *  Group 2:
   *    filters:   D
   *    cache:     false
   * 
   * This allows the parser to cache as much as possible.
   * 
   * @param mixed $filters Either a string of the input type OR an array of filters
   * @return array
   */
  protected function _getFilterGroups($filtersList)
  {
    if (!is_array($filtersList))
    {
      if (isset($this->_inputTypes[$filtersList]))
      {
        $filtersList = $this->_inputTypes[$filtersList];
      }
      else
      {
        // Just assume they've passed us one filter
        $filtersList = array($filtersList);
      }
    }

    // if we have no filters, simply return an empty string
    if (count($filtersList) == 0)
    {
      return array();
    }

    // create the filters array, ready to start loading the first group
    $filters = array(0 => array('filters' => array(), 'cache' => false));
    $i = 0;           // keeps track of what group we're on
    $cacheGroup = null; // Stores the cache key. If false, we're in a non-cache group. If null, we're in a new group
    foreach ($filtersList as $filter)
    {
      $cacheable = $this->getFilterConfig($filter, 'cache', false);

      // See if this filter object can fit into the current group
      if ($cacheGroup !== null && ($cacheable !== $cacheGroup))
      {
        // The filter object needs to be in the next group
        
        // Finish up saving the cache key
        if ($cacheGroup !== false)
        {
          $filters[$i]['cache'] = 'filters_'.implode('_', $filters[$i]['filters']);
        }
        
        // increment the group number
        $i++;
        
        // create the next group
        $filters[$i] = array(
          'filters' => array(),
          'cache'   => false
        );
      }

      /*
       * If we needed to have a new group setup, that just happened. In
       * either case, we're ready to be added to the group
       */
      $filters[$i]['filters'][] = $filter;
      $cacheGroup = $cacheable;
    }
    
    // The final group (if any) hasn't been finished up yet
    if (count($filters[$i]['filters']) === 0)
    {
      // just an empty group
      unset($filters[$i]);
    }
    else
    {
      if ($cacheGroup !== false)
      {
        $filters[$i]['cache'] = 'filters_'.implode('_', $filters[$i]['filters']);
      }
    }
    
    return $filters;
  }

  /**
   * Adds a possible filter to the parser
   * 
   * @param string $name    The name of the filter
   * @param string $class   The class that renders the filter
   * @param boolean $cache  Whether the filter is cacheable or not
   * @param array  $options An array of filter-specific options
   */
  public function setFilter($name, $class, $cache, $options)
  {
    $this->_filtersConfig[$name] = array(
      'class'   => $class,
      'cache'   => $cache,
      'options' => $options,
    );
  }

  /**
   * Returns a configuration value for a given filter.
   * 
   * @param string $filter The name of the filter
   * @param string $name   The name of the config vluae
   * @param mixed $default The value to return if the config value isn't found
   */
  public function getFilterConfig($filter, $name, $default = null)
  {
    if (!isset($this->_filtersConfig[$filter]))
    {
      throw new sfException(sprintf('Unrecognized filter "%s" given', $filter));
    }

    return isset($this->_filtersConfig[$filter][$name]) ? $this->_filtersConfig[$filter][$name] : $default;
  }

  /**
   * Sets an input type
   * 
   * @param string $name
   * @param array $filters
   */
  public function setInputType($name, $filters)
  {
    $this->_inputTypes[$name] = $filters;
  }

  /**
   * Returns the array of filters associated with the given input type
   * 
   * @param string $name
   * @return array
   */
  public function getInputType($name)
  {
    if (!isset($this->_inputTypes[$name]))
    {
      throw new sfException(sprintf('Invalid input type "%s"', $name));
    }
    
    return $this->_inputTypes[$name];
  }

  /**
   * Returns the filter object given by the filter name
   * 
   * @param string $filter The name of the filter object to return
   */
  protected function _getFilterObject($filter)
  {
    if (!isset($this->_filters[$filter]))
    {
      $class = $this->getFilterConfig($filter, 'class');
      $options = $this->getFilterConfig($filter, 'options', array());
      
      $this->_filters[$filter] = new $class($options);
    }
    
    return $this->_filters[$filter];
  }

  /**
   * Returns the parsed cache of the given cache key
   */
  protected function _getCache($cacheKey)
  {
    if ($this->getCacheDriver())
    {
      return $this->getCacheDriver()->get($cacheKey);
    }
  }

  /**
   * Sets the cache to the given key
   * 
   * @param
   */
  protected function _setCache($cacheKey, $content)
  {
    if ($this->getCacheDriver())
    {
      $this->getCacheDriver()->set($cacheKey, $content);
    }
  }

  /**
   * This is not a singleton accessor, but rather a place to house the logic
   * for this class to bootstrap itself based on the application configuration
   * 
   * A better way to retrieve this class would be the sfContentFilterPluginConfiguration
   * 
   * @return sfContentFilterParser
   */
  public static function createInstance()
  {
    $class = sfConfig::get('app_content_filter_parser_class', 'sfContentFilterParser');

    $filters = sfConfig::get('app_content_filter_filters', array());
    $inputTypes = sfConfig::get('app_content_filter_input_types', array());
    $parser = new $class($filters, $inputTypes);

    // Set the cache driver if caching is enabled
    $cacheConfig = sfConfig::get('app_content_filter_cache');
    if ($cacheConfig['enabled'])
    {
      $class = $cacheConfig['class'];
      $options = isset($cacheConfig['options']) ? $cacheConfig['options'] : array();
      
      $cacheDriver = new $class($options);
      $parser->setCacheDriver($cacheDriver);
    }
    
    return $parser;
  }
}