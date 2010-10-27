sfContentFilterPlugin
=====================

Simple plugin to apply an group of filters to raw content. The following
filters are included in this plugin

 * Markdown (markdown)

   Conversion from Markdown to HTML

 * InlineObject (inline_object)

   Parsing of inline objects. Requires [sfInlineObjectPlugin](http://github.com/weaverryan/sfInlineObjectPlugin)

 * Line breaks (line_break)

   Conversion of carriage returns into actual html breaks &lt;br/&gt;

 * Url filter (url)

   Change all urls into actual links

 * Twitter filter (twitter)

   Replaces `@weaverryan` type string to a link to that user's twitter page
   and replaces `#hashtag` strings to a link to a search page for that hash. 

Installation
------------

With git:

    git submodule add git://github.com/weaverryan/sfContentFilterPlugin.git plugins/sfContentFilterPlugin
    git submodule init
    git submodule update

With subversion

    svn propedit svn:externals plugins
    
    // In the editor, add the following entry and then save
    sfContentFilterPlugin https://svn.github.com/weaverryan/sfContentFilterPlugin.git
    
    svn up

In your `config/ProjectConfiguration.class.php` file, make sure you have
the plugin enabled.

    $this->enablePlugins('sfContentFilterPlugin');

Usage
-----

This plugin comes packaged with several `filters` and more can be easily
added.

Using the plugin couldn't be easier. To filter a piece of content, simply
use the `filter_content()` helper. The first argument is the text to
filter and the second argument is the name of the filter, or the array
of filters to filter with:

    $text = 'Visit: www.sympalphp.org.';

    echo filter_content($content, 'url');

    Visit: <a href="http://www.sympalphp.org" title="www.sympalphp.org">www.sympalphp.org</a>.

Optionally, you may also configure filter by a configured `input type`, which
is simply an ordered list of filters. For example, suppose the input type
`default` will apply the `line_break` and `url` filters:

    $text = 'Visit:
    http://www.sympalphp.org.';

    echo filter_content($content, 'default');

    Visit:<br/><a href="http://www.sympalphp.org" title="http://www.sympalphp.org">http://www.sympalphp.org</a>.

Notice how the `line_break` filter converted a line break into a &lt;br/&gt;
tag and the `url` filter converted a url into a full link.

Configuration
-------------

### Filters

Defining and customizing a filter is easy, and is done in an `app.yml` file:

    all:
      content_filter:
        filters:
          url:
            class:      sfContentFilterUrl
            cache:      true
            options:
              max_text_length: 72

All filter configuration is done beneath the `filters` key as seen above
and consists of the following options:

 * class

   This is the class that will perform the filtering. This class will
   extend `sfContentFilterAbstract`. Read below for more information on creating
   custom filters.

 * cache

   Whether or not to cache the filtering. This should usually be set to
   `true` and could otherwise hurt performance.

 * options

   An array of filter-specific options used to customize the filter


### Input Types

As mentioned before, input types are simply ordered collections of filters:

    all:
      content_filter:
        input_types:
          default:    [line_break, url, html_filter]

The above configuration defines an input type called `default`, which will
apply the `line_break`, `url`, and `html_filter` filters.

Creating Custom Filters
-----------------------

Creating custom filters couldn't be easier. First, define your filter in
`app.yml` by following the above examples. For example, suppose we want
to create a filter that automatically italicizes a list of given words.
We'll add this filter to our `default` input type:

    all:
      content_filter:
        filters:
          italicize:
            class:      sfContentFilterItalicize
            cache:      true
            options:
              words:    [symfony, sympal, drupal, sfContentFilterPlugin]
        
        input_types:
          default:    [line_break, url, italicize, html_filter]

The next step is to create the `sfContentFilterItalicize` class. This can
be placed anywhere, but I recommend that you create a `lib/content_filter`
directory and place it in there.

    // lib/filter/sfContentFilterItalicize.class.php
    class sfContentFilterItalicize extends sfContentFilterAbstract
    {
      public function _doFilter($content)
      {
        $words = $this->getOption('words');
        
        foreach ($words as $word)
        {
          $content = str_replace($word, '<i>'.$word.'</i>', $content);
        }
        
        return $content;
      }
    }

That's it! Now when you filter with the `default` input type, the words
`symfony`, `sympal`, `drupal` and `sfContentFilterPlugin` will all be italicized:

    echo filter_content('sfContentFilterPlugin is built in symfony and was inspired by sympal and drupal.', 'default');

    <i>sfContentFilterPlugin</i> is built in <i>symfony</i> and was inspired by <i>sympal</i> and <i>drupal</i>.

Caching
-------

Obviously, all of this string parsing can take a toll on your application's
performance. Fortunately, the result of the individual filters can be easily
cached.

To enable caching, define the cache driver in `app.yml`:

    all:
      content_filter:
        cache:
          enabled:  true
          class:    sfFileCache
          options:
            cache_dir:  <?php echo sfConfig::get('sf_app_cache_dir') ?>/content_filter

Now, each filter with the `cache` key set to true will be cached automatically.

The Fine Details
----------------

This plugin was written for and inspired by
[sympal CMF](http://www.sympalphp.org) and was developed by both Ryan Weaver
and Jon Wage.

Much of this plugin uses filters that were originally taken from Drupal,
because why reinvent what works?

If you have questions, comments or anything else, email me at ryan [at] thatsquality.com

