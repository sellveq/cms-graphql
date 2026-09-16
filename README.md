# ScandiPWA CmsGraphQl

Fork of [scandipwa/cms-graphql](https://github.com/scandipwa/cms-graphql) 1.4.7, maintained by Selveq for Magento 2.4.9 and PHP 8.3. Module name and namespace are unchanged, and the package replaces `scandipwa/cms-graphql` at every version, so it installs as a drop-in replacement. Selveq is not affiliated with or endorsed by Scandiweb.

## What it does

- Gives a CMS page a width: a `page_width` column on `cms_page`, a Width select in the admin page form and listing, and `page_width` on the `CmsPage` type.
- Resolves a page by its URL key, so `cmsPage(url_key: "home")` answers the page core answers only for `identifier`.
- Answers `disabled: true` for a switched-off CMS block instead of a not-found error, by identifier or by numeric id alike, so the storefront can tell an absent block from a disabled one.
- Returns the whitelisted `{{widget}}` directives — Slider, New Products, Catalog Product List and Recently Viewed — as `<widget>` elements carrying their parameters, for the theme to render rather than the server.

## Install

```sh
composer require selveq/cms-graphql
bin/magento setup:upgrade
```

## License

[OSL-3.0](LICENSE), the license of the original work. Scandiweb's copyright notices are kept in every file, and each file Selveq changed carries a `Modifications © Selveq` notice.
