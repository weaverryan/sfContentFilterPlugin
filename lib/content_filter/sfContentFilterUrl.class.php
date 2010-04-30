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
    $regex1 = "!(<p>|<li>|<br\s*/?>|[ \n\r\t\(])((http://|https://|ftp://|mailto:|smb://|afp://|file://|gopher://|news://|ssl://|sslv2://|sslv3://|tls://|tcp://|udp://)([a-zA-Z0-9@:%_+*~#?&=.,/;-]*[a-zA-Z0-9@:%_+*~#&=/;-]))([.,?]?)(?=(</p>|</li>|<br\s*/?>|[ \n\r\t\)]))!i";
    $regex2 = "!(<p>|<li>|<br\s*/?>|[ \n\r\t\(])([A-Za-z0-9._-]+@[A-Za-z0-9._+-]+\.[A-Za-z]{2,4})([.,?]?)(?=(</p>|</li>|<br\s*/?>|[ \n\r\t\)]))!i";
    $regex3 = "!(<p>|<li>|[ \n\r\t\(])(www\.[a-zA-Z0-9@:%_+*~#?&=.,/;-]*[a-zA-Z0-9@:%_+~#\&=/;-])([.,?]?)(?=(</p>|</li>|<br\s*/?>|[ \n\r\t\)]))!i";

    // Replace all types 
    $content = preg_replace_callback($regex1, array($this, 'replace1'), $content);
    $content = preg_replace($regex2, '\1<a href="mailto:\2">\2</a>\3', $content);
    $content = preg_replace_callback($regex3, array($this, 'replace2'), $content);

    
    return $content;
  }

  // transforms fully qualified urls into links
  public function replace1($match)
  {
    $caption = $this->_trim($match[2]);

    return $match[1] . '<a href="'. $match[2] .'" title="'. $match[2] .'">'. $caption .'</a>'. $match[5];
  }

  // transforms www-like urls into links
  public function replace2($match)
  {
    $caption = $this->_trim($match[2]);

    return $match[1] . '<a href="http://'. $match[2] .'" title="'. $match[2] .'">'. $caption .'</a>'. $match[3];
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