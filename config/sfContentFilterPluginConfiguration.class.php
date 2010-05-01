<?php

/**
 * Plugin Configuration for sfContentFilterPlugin
 * 
 * @package     sfContentFilterPlugin
 * @subpackage  config
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfContentFilterPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @var sfContentFilterParser The parser instance
   */
  protected $_parser;

  public function initialize()
  {
    $this->dispatcher->connect('context.load_factories', array($this, 'bootstrap'));

    // Listener so we can "extend" the actions class
    $action = new sfContentFilterAction();
    $this->dispatcher->connect('component.method_not_found', array($action, 'listenComponentMethodNotFound'));
  }

  /**
   * Listens to the context.load_factories event and:
   * 
   *  * Adds ContentFilter to the standard helpers
   */
  public function bootstrap(sfEvent $event)
  {
    $helpers = sfConfig::get('sf_standard_helpers', array());
    $helpers[] = 'ContentFilter';
    
    sfConfig::set('sf_standard_helpers', $helpers);
  }

  /**
   * Returns the parser to be used to parser content.
   * 
   * This allows us to effectively only have one parser instance without
   * implementing the singleton pattern
   * 
   * @param string $class The name of the class to use for the parser
   */
  public function getParser()
  {
    if ($this->_parser === null)
    {
      $this->_parser = sfContentFilterParser::createInstance();
    }
    
    return $this->_parser;
  }
}