<?php

/**
 * Filter that transforms urls into anchor tags
 * 
 * Originally taken from drupal (http://drupal.org/project/urlfilter)
 * 
 * @package     sfContentFilterPlugin
 * @subpackage  filter
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfContentFilterUrl extends sfContentFilterAbstract
{
  /**
   * Logic taken from drupal (http://drupal.org/project/urlfilter)
   * 
   * @see sfContentFilterAbstract
   */
  public function _doFilter($content)
  {
    // fixing a bug where the link doesn't match if it's right at the very end
    $content = $content.' ';

    $regex1 = "!(<p>|<li>|<br\s*/?>|[ \n\r\t\(])((http://|https://|ftp://|mailto:|smb://|afp://|file://|gopher://|news://|ssl://|sslv2://|sslv3://|tls://|tcp://|udp://)([a-zA-Z0-9@:%_+*~#?&=.,/;-]*[a-zA-Z0-9@:%_+*~#&=/;-]))([.,?]?)(?=(</p>|</li>|<br\s*/?>|[ \n\r\t\)]))!i";
    $regex2 = "!(<p>|<li>|<br\s*/?>|[ \n\r\t\(])([A-Za-z0-9._-]+@[A-Za-z0-9._+-]+\.[A-Za-z]{2,4})([.,?]?)(?=(</p>|</li>|<br\s*/?>|[ \n\r\t\)]))!i";
    $regex3 = "!(<p>|<li>|[ \n\r\t\(])(www\.[a-zA-Z0-9@:%_+*~#?&=.,/;-]*[a-zA-Z0-9@:%_+~#\&=/;-])([.,?]?)(?=(</p>|</li>|<br\s*/?>|[ \n\r\t\)]))!i";

    // Replace all types 
    $content = preg_replace_callback($regex1, array($this, 'replace1'), $content);
    $content = preg_replace($regex2, '\1<a href="mailto:\2">\2</a>\3', $content);
    $content = preg_replace_callback($regex3, array($this, 'replace3'), $content);

    if (substr($content, strlen($content) - 1, 1) == ' ')
    {
      $content = substr($content, 0, strlen($content) - 1);
    }
    
    return $content;
  }

  // transforms fully qualified urls into links
  public function replace1($match)
  {
    $caption = $this->_trim($match[2]);

    $attributes = array_merge(array(
      'href'  => $match[2],
      'title' => $match[2],
    ), $this->getOption('link_attributes', array()));

    return $match[1].sfContentFilterUtil::contentTag('a', $caption, $attributes).$match[5];
  }

  // transforms www-like urls into links
  public function replace3($match)
  {
    $caption = $this->_trim($match[2]);

    $attributes = array_merge(array(
      'href'  => 'http://'.$match[2],
      'title' => $match[2],
    ), $this->getOption('link_attributes', array()));

    return $match[1].sfContentFilterUtil::contentTag('a', $caption, $attributes).$match[3];
  }

  /**
   * Trims the url to the length given by the max_text_length option
   * 
   * @return string
   */
  protected function _trim($text)
  {
    $length = $this->getOption('max_text_length', 72);
    
    return (strlen($text) > $length) ? substr($text, 0, $length) .'...' : $text;
  }
}