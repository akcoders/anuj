# RSS Post Manager

A CodeIgniter 3 application for importing items from public RSS 2.0 or Atom feeds, editing and prioritizing posts, assigning social platforms, and reviewing assignments on a dashboard. It organizes posts; it does not publish them to social networks.

## Get started

1. Run PHP 7.4 or newer with the `curl`, `mbstring`, `mysqli`, `dom`, and `SimpleXML` extensions, plus Apache and MySQL or MariaDB.
2. Import [database.sql](database.sql) into MySQL. The script creates the `rss_manager` database, tables, and platform records.
3. Set the database connection in [application/config/database.php](application/config/database.php) and the local `base_url` in [application/config/config.php](application/config/config.php).
4. Enable Apache `mod_rewrite` and `AllowOverride All`, then open the configured base URL. The default page is **Import feed**.

## Use the application

- **Import now:** Enter a public HTTP(S) feed URL, choose newest or oldest first, and click **Import now** to save all new items without opening a preview. Duplicate items are skipped, and the page shows imported and skipped counts.
- **Preview first:** Click **Preview posts** to inspect items and duplicate counts. Select individual posts or all new posts, then click **Import selected** or **Import all new**. Preview import uses a single-use token that expires after 30 minutes.
- **Manage posts:** Edit text and platform assignments, delete posts, or drag cards to change priority within the current page. Card and List views show compact, expandable platform-limit warnings and numbered pagination.
- **Review the dashboard:** Filter assigned posts by platform, sort them, and see warnings when a post exceeds a selected platform's character limit.

Imported posts have no platform assignments until you add them in the post editor. Character limits are advisory; the application warns but does not block saving.

For the complete functional flow, request routes, data model, duplicate rules, and code-level structure, see [details.md](details.md).
