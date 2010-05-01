<?php

/**
 * Listens to the component.method_not_found event and acts like a subclass
 * to the actions class
 * 
 * @package     sfContentFilterPlugin
 * @subpackage  action
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfContentFilterAction
{
  protected $_action;
  
  /**
   * Listens to the component.method_not_found event to effectively
   * extend the actions class
   */
  public function listenComponentMethodNotFound(sfEvent $event)
  {
    $this->_action = $event->getSubject();
    $method = $event['method'];
    $arguments = $event['arguments'];

    if (method_exists($this, $method))
    {
      $result = call_user_func_array(array($this, $method), $arguments);

      $event->setReturnValue($result);

      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Returns the current inline object parser
   * 
   * @return sfInlineObjectParser
   */
  public function getContentFilterParser()
  {
    return $this->_action->getContext()
      ->getConfiguration()
      ->getPluginConfiguration('sfContentFilterPlugin')
      ->getParser();
  }
}