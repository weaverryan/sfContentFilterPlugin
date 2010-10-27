<?php

/**
 * Abstract parent class for all of the individual filters.
 * 
 * @package     sfContentFilterPlugin
 * @subpackage  content_filter
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */

abstract class sfContentFilterAbstract
{
  protected $_options;

  /**
   * Class constructor
   */
  public function __construct($options = array())
  {
    $this->_options = $options;
  }

  /**
   * Internal function defined by each filter.
   * 
   * Performs the actual filtering of the content
   * 
   * @param string $content The raw content that will be processed
   * 
   * @return string
   */
  abstract protected function _doFilter($content);

  /**
   * Performs the actual filtering on the given content
   * 
   * @param string $content The raw content that will be processed
   * 
   * @return string
   */
  public function filter($content)
  {
    return $this->_doFilter($content);
  }

  /**
   * Returns the array of options
   * 
   * @return array
   */
  public function getOptions()
  {
    return $this->_options;
  }

  /**
   * Returns a given option or default if the option doesn't exist
   * 
   * @param string $name    The name of the option
   * @param mixed $default  The default to return if the option doesn't exist
   * 
   * @return mixed
   */
  public function getOption($name, $default = null)
  {
    return isset($this->_options[$name]) ? $this->_options[$name] : $default;
  }

  /**
   * @param  string $name The name of the option to set
   * @param  mixed $value The value to set on the option
   * @return void
   */
  public function setOption($name, $value)
  {
    $this->_options[$name] = $value;
  }
}