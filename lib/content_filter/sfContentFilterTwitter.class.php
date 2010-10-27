<?php

/**
 * Filter that transforms @username and #hashtag parts of a tweet and
 * replaces them with link tags
 * 
 * @package     sfContentFilterPlugin
 * @subpackage  filter
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfContentFilterTwitter extends sfContentFilterAbstract
{
  /**
   * Logic taken from drupal (http://drupal.org/project/urlfilter)
   * 
   * @see sfContentFilterAbstract
   */
  public function _doFilter($content)
  {
    $content = $this->processAtMessages($content);
    $content = $this->processHashTagMessages($content);
    
    return $content;
  }

  /**
   * Changes the @weaverryan messages to links
   *
   * @param  string $content The content to parse
   * @return string
   */
  protected function processAtMessages($content)
  {
    $regex = '#[@]+([A-Za-z0-9-_]+)#';

    $content = preg_replace_callback($regex, array($this, 'parseMessageCallback'), $content);

    return $content;
  }

  /**
   * Responds to the preg_replace_callback in processAtMessages to replace
   * each @weaverryan type string with a link to that twitter page.
   *
   * @param  array $matches
   * @return string
   */
  public function parseMessageCallback($matches)
  {
    $twitterHandle = $matches[1];

    $attributes = array_merge(array(
      'href'  => 'http://www.twitter.com/'.$twitterHandle,
    ), $this->getOption('username_link_attributes', array()));

    return sfContentFilterUtil::contentTag('a', '@'.$twitterHandle, $attributes);
  }

  /**
   * Changes the @weaverryan messages to links
   *
   * @param  string $content The content to parse
   * @return string
   */
  protected function processHashTagMessages($content)
  {
    $regex = '#[\#]+([A-Za-z0-9-_]+)#';

    $content = preg_replace_callback($regex, array($this, 'parseHashCallback'), $content);

    return $content;
  }

  /**
   * Responds to the preg_replace_callback in processHashTagMessages to replace
   * each #hashtag type string with a link to a search page for that hash.
   *
   * @param  array $matches
   * @return string
   */
  public function parseHashCallback($matches)
  {
    $nakedHash = $matches[1];

    $attributes = array_merge(array(
      'href'  => 'http://search.twitter.com/search?q=%23'.$nakedHash,
    ), $this->getOption('hash_link_attributes', array()));

    return sfContentFilterUtil::contentTag('a', '#'.$nakedHash, $attributes);
  }
}