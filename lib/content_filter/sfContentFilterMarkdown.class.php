<?php

/**
 * Converts Markdown into HTML
 * 
 * To get the full effect of the transformation, you'll want to include
 * the markdown.css file in your response
 * 
 * @package     sfContentFilterPlugin
 * @subpackage  content_filter
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */

class sfContentFilterMarkdown extends sfContentFilterAbstract
{
  /**
   * @see sfContentFilterAbstract
   */
  protected function _doFilter($content)
  {
    // skip procesing if there's nothing to process
    if (!$content)
    {
      return $content;
    }
    
    require_once dirname(__FILE__).'/../vendor/markdown/markdown.php';

    return '<div class="markdown">'.$this->_enhanceHtml(Markdown($content)).'</div>';
  }

  /**
   * Enhances the HTML by adding icons to blockquotes, highlighting code
   * syntax, etc
   */
  protected function _enhanceHtml($html)
  {
    $html = $this->_convertStyledBoxes($html);

    // Sidebar
    $html = $this->_convertSidebar($html);

    // Fix spacer
    $html = $this->_fixSpacer($html);

    // SQL
    $html = preg_replace_callback('#<pre><code>(alter|create|drop|select|update|delete|from|group by|having|where)(.+?)</code></pre>#si', array($this, 'highlightSql'), $html);

    // Yaml
    $html = preg_replace_callback('#<pre><code>(.+?)</code></pre>#s', array($this, 'highlightYaml'), $html);

    // Syntax highlighting
    $html = preg_replace_callback('#<pre><code>(.+?)</code></pre>#s', array($this, 'highlightPhp'), $html);

    return $html;
  }

  /**
   * Converts blockquotes of different styles into blockqoutes with a specific
   * class so that they can be styled
   * 
   * @param string $html
   */
  protected function _convertStyledBoxes($html)
  {
    $boxes = $this->getOption('styled_boxes', array('quote', 'tip', 'caution', 'note'));
    $boxes = implode('|', $boxes);

    return preg_replace('#<blockquote>\s*<p><strong>('.$boxes.')</strong>\:?#sie', '\'<blockquote class="\'.strtolower("$1").\'"><p>\'', $html);
  }

  /**
   * Converts the sidebar into markup that can be styled
   * 
   * @param string $html
   */
  protected function _convertSidebar($html)
  {
    return preg_replace('#<blockquote>\s*<p><strong>sidebar</strong>\s*(.+?)\s*</p>#si', '<blockquote class="sidebar"><p class="title">$1</p>', $html);
  }

  /**
   * To help with proper markdown rendering, often a spacer is added
   * (a dash on its own line). This removes that from the output
   */
  protected function _fixSpacer($html)
  {
    return str_replace('<p>-</p>', '', $html);
  }

  /**
   * Highlights YAML using an external class
   * 
   * The argument is an already-processed regular expression
   */
  public function highlightYaml($matches)
  {
    require_once dirname(__FILE__).'/../vendor/yaml/sfYamlSyntaxHighlighter.class.php';
    $yaml = is_string($matches) ? $matches:$matches[1];

    if ($formatted = sfYamlSyntaxHighlighter::highlight($yaml))
    {
      return $formatted;
    }
    else
    {
      return $matches[0];
    }
  }

  /**
   * Applies sql highlighting
   */
  public function highlightSql($matches)
  {
    $sql = $matches[0];
    $color = "#ffcc66";
    $newSql = $sql;
    $newSql = str_replace("CREATE ", "<span style=\"color: $color;\"><strong>CREATE </strong></span>", $newSql);
    $newSql = str_replace("ALTER ", "<span style=\"color: $color;\"><strong>ALTER </strong></span>", $newSql);
    $newSql = str_replace("TABLE ", "<span style=\"color: $color;\"><strong>TABLE </strong></span>", $newSql);
    $newSql = str_replace("CONSTRAINT ", "<span style=\"color: $color;\"><strong>CONSTRAINT </strong></span>", $newSql);
    $newSql = str_replace("VIEW ", "<span style=\"color: $color;\"><strong> VIEW </strong></span>", $newSql);
    $newSql = str_replace("DROP", "<span style=\"color: $color;\"><strong>DROP </strong></span>", $newSql);
    $newSql = str_replace("SELECT ", "<span style=\"color: $color;\"><strong>SELECT </strong></span><br/>", $newSql);
    //$newSql = preg_replace("/([a-zA-Z0-9._]+) AS ([a-zA-Z0-9._]+), /", "$1 AS $2, <br/>", $newSql);
    $newSql = str_replace(', ', ', <br/>', $newSql);
    $newSql = str_replace("UPDATE ", "<span style=\"color: $color;\"><strong>UPDATE </strong></span>", $newSql);
    $newSql = str_replace("DELETE ", "<span style=\"color: $color;\"><strong>DELETE </strong></span>", $newSql);
    $newSql = str_replace("FROM ", "<br/><span style=\"color: $color;\"><strong>FROM </strong></span>", $newSql);
    $newSql = str_replace("LEFT JOIN ", "<br/><span style=\"color: $color;\"><strong>LEFT JOIN </strong></span>", $newSql);
    $newSql = str_replace("INNER JOIN ", "<br/><span style=\"color: $color;\"><strong>INNER JOIN </strong></span>", $newSql);
    $newSql = str_replace("WHERE ", "<br/><span style=\"color: $color;\"><strong>WHERE </strong></span>", $newSql);
    $newSql = str_replace("GROUP BY ", "<br/><span style=\"color: $color;\"><strong>GROUP BY </strong></span>", $newSql);
    $newSql = str_replace("HAVING ", "<br/><span style=\"color: $color;\"><strong>HAVING </strong></span>", $newSql);
    $newSql = str_replace("AS ", "<span style=\"color: $color;\"><strong>AS </strong></span>", $newSql);
    $newSql = str_replace("ON ", "<span style=\"color: $color;\"><strong>ON </strong></span>", $newSql);
    $newSql = str_replace("ORDER BY ", "<br/><span style=\"color: $color;\"><strong>ORDER BY </strong></span>", $newSql);
    $newSql = str_replace("LIMIT ", "<br/><span style=\"color: $color;\"><strong>LIMIT </strong></span>", $newSql);
    $newSql = str_replace("OFFSET ", "<br/><span style=\"color: $color;\"><strong>OFFSET </strong></span>", $newSql);
    $newSql = str_replace("LIKE ", "<span style=\"color: $color;\"><strong>LIKE </strong></span>", $newSql);
    $newSql = str_replace("PRIMARY KEY", "<span style=\"color: $color;\"><strong>PRIMARY KEY</strong></span>", $newSql);
    $newSql = str_replace("REFERENCES ", "<span style=\"color: $color;\"><strong>REFERENCES </strong></span>", $newSql);
    $newSql = str_replace("INDEX ", "<span style=\"color: $color;\"><strong>INDEX </strong></span>", $newSql);
    $newSql = str_replace("CHECK ", "<span style=\"color: $color;\"><strong>CHECK </strong></span>", $newSql);
    $newSql = str_replace("SET ", "<br/><span style=\"color: $color;\"><strong>SET </strong></span>", $newSql);
    $newSql = str_replace(" AND ", "<br/><span style=\"color: $color;\"><strong>AND </strong></span>", $newSql);
    $newSql = str_replace(" OR ", "<br/><span style=\"color: $color;\"><strong>OR </strong></span>", $newSql);

    $newSql = str_replace(" NOT IN (", " <span style=\"color: $color;\"><strong>NOT IN </strong></span>(", $newSql);
    $newSql = str_replace(" NOT IN(", " <span style=\"color: $color;\"><strong>NOT IN</strong></span>(", $newSql);

    $newSql = str_replace(" IN (", " <span style=\"color: $color;\"><strong>IN </strong></span>(", $newSql);
    $newSql = str_replace(" IN(", " <span style=\"color: $color;\"><strong>IN</strong></span>(", $newSql);

    $newSql = str_replace("EXISTS (", "<span style=\"color: $color;\"><strong>EXISTS </strong></span>(", $newSql);
    $newSql = str_replace("EXISTS(", "<span style=\"color: $color;\"><strong>EXISTS</strong></span>(", $newSql);

    $newSql = str_replace("ALL (", "<span style=\"color: $color;\"><strong>ALL </strong></span>(", $newSql);
    $newSql = str_replace("ALL(", "<span style=\"color: $color;\"><strong>ALL</strong></span>(", $newSql);

    $newSql = str_replace("ANY (", "<span style=\"color: $color;\"><strong>ANY </strong></span>(", $newSql);
    $newSql = str_replace("ANY(", "<span style=\"color: $color;\"><strong>ANY</strong></span>(", $newSql);

    return $newSql;
  }

  /**
   * Applies php highlighting
   */
  public function highlightPhp($matches)
  {
    return $this->_geshiCall($matches);
  }

  /**
   * Makes a call out to geshi to highlight code
   * 
   * @param array $matches The raw code, in preg_match format
   */
  protected function _geshiCall($matches, $default = '')
  {
    if (preg_match('/^\[(.+?)\]\s*(.+)$/s', $matches[1], $match))
    {
      if ($match[1] == 'sql')
      {
        return "<pre><code class=\"sql\">".$this->highlightSql(array(html_entity_decode($match[2]))).'</code></pre>';
      }
      else if
      ($match[1] == 'yaml' || $match[1] == 'yml')
      {
        return $this->highlightYaml($match[2]);
      }
      else if ($match[1] == 'php')
      {
        $code = html_entity_decode($match[2]);
        $code = !strpos($code, '?php') ? "<?php\n\n" . $code . "\n?>":$code;
        return $this->_getGeshi($code, 'php');
      }
      else
      {
        return $this->_getGeshi(html_entity_decode($match[2]), $match[1]);
      }
    }
    else
    {
      if ($default)
      {
        return $this->_getGeshi(html_entity_decode($matches[1]), $default);
      }
      else
      {
        return "<pre><code>".$matches[1].'</code></pre>';
      }
    }
  }

  protected function _getGeshi($text, $language)
  {
    require_once dirname(__FILE__).'/../vendor/geshi/geshi.php';
    
    if ('html' == $language)
    {
      $language = 'html4strict';
    }

    $geshi = new GeSHi($text, $language);
    $geshi->enable_classes();

    // disable links on PHP functions, HTML tags, ...
    $geshi->enable_keyword_links(false);

    return @$geshi->parse_code();
  }
}