sfContentFilterPlugin
=====================

Simple plugin to apply an group of filters to raw content. For example,
filters might include:

 * HTML filter

   The removal of all non-whitelisted HTML tags

 * Markdown

   Conversion from Markdown to HTML

 * InlineObject

   Parsing of inline objects. See [sfInlineObjectPlugin](http://github.com/weaverryan/sfInlineObjectPlugin)

 * Line breaks

   Conversion of carriage returns into actual html breaks &lt;br/&gt;

 * Url filter

   Change all urls into actual links

 * Smileys

   Convert text smileys into image smileys


Usage
-----

This plugin comes packaged with several `filters` and more can be easily
added. These filters are grouped into `input types`, which are ordered
collections of filters. By default, this plugin comes packaged with an
`input type` called `default`, which consists of the following filters:

 * Line breaks
 * Url filter
 * HTML filter

With that in mind, using the plugin couldn't be easier. To filter a piece
of content, simply use the `filter_content()` helper. The first argument
is the text to filter and the second argument is the `input type` with
which to filter:

    $text = 'Visit:
    http://www.sympalphp.org.';

    echo filter_content($content, 'default');

    Visit:<br/><a href="http://www.sympalphp.org" title="http://www.sympalphp.org">http://www.sympalphp.org</a>.

Notice how the line break filter converted a line break into a &lt;br/&gt;
tag and the url filter converted a url into a full link.

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
              nofollow: false

All filter configuration is done beneath the `filters` key as seen above
and consists of the following options:

 * class

   This is the class that will perform the filtering. This class will
   extend `sfContentFilter`. Read below for more information on creating
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
    
    class sfContentFilterItalicize extends sfContentFilter
    {
      public function filter($content)
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

    echo filter_content('sfContentFilterPlugin is built in symfony and was inspired by sympal and drupal.');

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
            cache_dir:  <?php echo sfConfig::get('sf_app_cache_dir') ?>/inline_objects

Now, each filter with the `cache` key set to true will be cached automatically.