# Changelog

## 2.0.0

Forked from `scandipwa/cms-graphql` 1.4.7. Module name and namespace are unchanged, and the package replaces `scandipwa/cms-graphql` at every version, so it installs as a drop-in replacement.

- Magento 2.4.9 and PHP 8.3 support.
- `cmsPage(url_key: "...")` resolves the page; the argument was declared on the schema and read by nothing, so it always answered "Page id/identifier should be specified".
- The CMS block override is wired again under the method name core calls, so `disabled` is answered on every `cmsBlocks` query and block content renders through this module's template filter.
- A block requested by numeric id answers the same shape as one requested by identifier, `disabled: true` for a switched-off block instead of a not-found error.
- Block lookups are scoped to the requested store and the default store, so a block assigned to one store view is no longer served to every store view.
- `block_id` is back in the block data, so `cmsBlocks` responses carry their `cms_b` cache tags again and a block edit invalidates them.
- `{{widget id="N"}}` on a whitelisted type is matched on the resolved widget type, so a preconfigured instance is emitted as a `<widget>` element like the inline form.
- A widget parameter whose name is not a plain key is dropped and logged, and a value that skips escaping is checked for the quote that would end its attribute.
- Removed: the plugin registration on the GraphQL front controller, which 2.4.9 makes redundant and which collided by name with catalog-graphql's; translations now initialise on the persisted-query path only.
