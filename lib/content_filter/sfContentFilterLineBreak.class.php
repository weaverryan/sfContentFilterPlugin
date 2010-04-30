<?php

/**
 * Converts carriage returns into html line breaks
 * 
 * Code originally taken from
 *  * Drupal (http://api.drupal.org/api/function/_filter_autop/7)
 *  * smarklinebreakconverter (http://drupalmodules.com/module/smart-line-break-converter)
 * 
 * If applied to html markup, this will do nothing - it's smart enough to
 * recognize the html and not unnecessarily convert the line breaks
 * 
 * @package     sfContentFilterPlugin
 * @subpackage  content_filter
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */

class sfContentFilterLineBreak extends sfContentFilterAbstract
{

  /**
   * @see sfContentFilterAbstract
   * 
   * Code taken from drupal
   */
  public function _doFilter($content)
  {
    // Check if the content is handling line breaks on its own.
    if (stristr($content, '<br>')
      || stristr($content, '<br />')
      || stristr($content, '<p>')
      || stristr($content, '</p>'))
    {
      return $content;
    }
    
    // All block level tags
    $block = '(?:table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|address|p|h[1-6]|hr)';

    // Split at <pre>, <script>, <style> and </pre>, </script>, </style> tags.
    // We don't apply any processing to the contents of these tags to avoid messing
    // up code. We look for matched pairs and allow basic nesting. For example:
    // "processed <pre> ignored <script> ignored </script> ignored </pre> processed"
    $chunks = preg_split('@(</?(?:pre|script|style|object)[^>]*>)@i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
    // Note: PHP ensures the array consists of alternating delimiters and literals
    // and begins and ends with a literal (inserting NULL as required).
    $ignore = FALSE;
    $ignoretag = '';
    $output = '';
    foreach ($chunks as $i => $chunk) {
      if ($i % 2) {
        // Opening or closing tag?
        $open = ($chunk[1] != '/');
        list($tag) = preg_split('/[ >]/', substr($chunk, 2 - $open), 2);
        if (!$ignore) {
          if ($open) {
            $ignore = TRUE;
            $ignoretag = $tag;
          }
        }
        // Only allow a matching tag to close it.
        elseif (!$open && $ignoretag == $tag) {
          $ignore = FALSE;
          $ignoretag = '';
        }
      }
      elseif (!$ignore) {
        $chunk = preg_replace('|\n*$|', '', $chunk) . "\n\n"; // just to make things a little easier, pad the end
        $chunk = preg_replace('|<br />\s*<br />|', "\n\n", $chunk);
        $chunk = preg_replace('!(<' . $block . '[^>]*>)!', "\n$1", $chunk); // Space things out a little
        $chunk = preg_replace('!(</' . $block . '>)!', "$1\n\n", $chunk); // Space things out a little
        $chunk = preg_replace("/\n\n+/", "\n\n", $chunk); // take care of duplicates
        $chunk = preg_replace('/^\n|\n\s*\n$/', '', $chunk);
        $chunk = '<p>' . preg_replace('/\n\s*\n\n?(.)/', "</p>\n<p>$1", $chunk) . "</p>\n"; // make paragraphs, including one at the end
        $chunk = preg_replace("|<p>(<li.+?)</p>|", "$1", $chunk); // problem with nested lists
        $chunk = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $chunk);
        $chunk = str_replace('</blockquote></p>', '</p></blockquote>', $chunk);
        $chunk = preg_replace('|<p>\s*</p>\n?|', '', $chunk); // under certain strange conditions it could create a P of entirely whitespace
        $chunk = preg_replace('!<p>\s*(</?' . $block . '[^>]*>)!', "$1", $chunk);
        $chunk = preg_replace('!(</?' . $block . '[^>]*>)\s*</p>!', "$1", $chunk);
        $chunk = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $chunk); // make line breaks
        $chunk = preg_replace('!(</?' . $block . '[^>]*>)\s*<br />!', "$1", $chunk);
        $chunk = preg_replace('!<br />(\s*</?(?:p|li|div|th|pre|td|ul|ol)>)!', '$1', $chunk);
        $chunk = preg_replace('/&([^#])(?![A-Za-z0-9]{1,8};)/', '&amp;$1', $chunk);
      }
      $output .= $chunk;
    }
    return $output;
  }
}