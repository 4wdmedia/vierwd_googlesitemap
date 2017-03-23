# vierwd_googlesitemap
Sitemap.xml implementation with support for multiple languages (on different domains) and URLs for database entries.

## Getting started

Install using `composer require 'vierwd/typo3-googlesitemap'` and enable in extension builder.

If you are using realurl, the sitemap is available at http://www.example.com/sitemap.xml and a robots.txt exists at http://www.example.com/robots.txt

If you are using Domains to switch languages, your different domains will have different sitemaps.

## Links to database entries

You can configure links to database entries in TypoScript:

```
plugin.tx_vierwdgooglesitemap.sitemap.settings.additionalLinks {
	products {
		records {
			table = tt_news
			select {
				pidInList = 17
				andWhere = sys_language_uid=0
				languageField = 0
			}
		}

		typolink {
			parameter = 20
			additionalParams = &tx_ttnews[tt_news]={field:uid}
			additionalParams.insertData = 1
			useCacheHash = 1
			mergeWithLinkhandlerConfiguration = 1
		}
	}
}
```