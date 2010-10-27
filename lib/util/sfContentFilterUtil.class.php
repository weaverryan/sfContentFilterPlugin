<?php

class sfContentFilterUtil
{
  public static function contentTag($name, $content = '', $options = array())
  {
    if (!$name)
    {
      return '';
    }

    return '<'.$name.self::tagOptions($options).'>'.$content.'</'.$name.'>';
  }

  public static function tagOptions($options = array())
  {
    $options = self::parseAttributes($options);

    $html = '';
    foreach ($options as $key => $value)
    {
      $html .= ' '.$key.'="'.self::escapeOnce($value).'"';
    }

    return $html;
  }

  public static function parseAttributes($string)
  {
    return is_array($string) ? $string : sfToolkit::stringToArray($string);
  }

  /**
   * Escapes an HTML string.
   *
   * @param  string $html HTML string to escape
   * @return string escaped string
   */
  public static function escapeOnce($html)
  {
    return self::fixDoubleSpace(htmlspecialchars($html, ENT_COMPAT, sfConfig::get('sf_charset')));
  }

  /**
   * Fixes double escaped strings.
   *
   * @param  string $escaped HTML string to fix
   * @return string fixed escaped string
   */
  public static function fixDoubleSpace($escaped)
  {
    return preg_replace('/&amp;([a-z]+|(#\d+)|(#x[\da-f]+));/i', '&$1;', $escaped);
  }
}