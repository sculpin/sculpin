Sculpin Taxonomy
================

Provides building blocks for handling taxonomy concerns with collections of
Sculpin sources.

Visit [sculpin.io](http://sculpin.io) for more information.


ProxySource Taxonomy
--------------------

`ProxySourceTaxonomyDataProvider` and `ProxySourceTaxonomyIndexGenerator` are
provided to enable simple creation of taxons using `ProxySource` based objects.


### ProxySourceTaxonomyDataProvider

A  data provider that scans a source's data for a key that can contain a string
or array of strings representing the taxons associated with the source.

 * **DataProviderManager $dataProviderManager**:
   A `DataProviderManager` instance.
 * **(string) $dataProviderName**:
   The name of the data provider.
 * **(string) $taxonomyKey**:
   The key in a source's data that contains the list of taxons. For example, if
   the taxonomy is "Tags," and it is desired for the metadata to have the key
   `tags`, the `$taxonomyKey` would be "tags".


License
-------

MIT, see LICENSE.


Community
---------

Want to get involved? Here are a few ways:

* Find us in the **#sculpin** IRC channel on **irc.freenode.org**.
* Join the [Sculpin Users](http://groups.google.com/group/sculpin-users)
  mailing list.
* Mention [@getsculpin](http://twitter.com/getsculpin) on Twitter.
