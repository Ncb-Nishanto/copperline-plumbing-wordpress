# Copperline Plumbing

Copperline is a fictional residential plumbing business in Portland, Oregon. The WordPress demo has four Elementor pages, a responsive navigation menu, and a local quote workflow from the home page through confirmation.

## Portfolio walkthrough

1. Open Home and browse Services and About. At mobile width, open the menu and use the persistent header quote button.
2. Click **Request a quote**. Try leaving a required field blank, then submit with fictional details such as `Taylor Example`, `taylor@example.test`, and ZIP code `97201`. Choose Leak or pipe repair and describe a slow drip below a kitchen sink.
3. Confirm the success message, then check **Quote Requests** in WordPress admin. The plugin validates fields and a WordPress nonce at the server, stores a private record, and redirects after a successful POST. It sends no email.
4. Open any page with **Edit with Elementor**. The headlines, copy, layout containers, and buttons use free widgets. The photo and form use the free Shortcode widget.

## Run locally

Use a fresh local WordPress installation with PHP 8.1+ and MySQL or MariaDB. Enable the usual WordPress PHP extensions including mbstring. Create a local admin account and run these commands from the WordPress root. Set `PROJECT` to the absolute path of this checkout. On Windows, run the commands in Git Bash or copy the two directories with File Explorer before using WP-CLI.

```sh
PROJECT="/absolute/path/to/home-services-wordpress-demo"
cp -R "$PROJECT/theme/copperline" wp-content/themes/
cp -R "$PROJECT/plugins/copperline-quotes" wp-content/plugins/
wp plugin install elementor --activate
wp plugin activate copperline-quotes
wp theme activate copperline
wp eval-file "$PROJECT/scripts/seed.php"
```

Use the URL printed by the seed script. For pretty permalinks, run WordPress on a local stack with URL rewrites enabled, such as Local, Apache with mod_rewrite, or a WordPress Docker environment. The seed script leaves existing Elementor content on its seeded pages in place when rerun. It will stop if a same-slug page belongs to another site setup.

## Verification

Tested locally in Chromium at 1440×1000 and 390×844 with WordPress 7.1.5, Elementor 4.4.1, and PHP 8.3.37 using the WordPress SQLite integration. The desktop and mobile runs covered all four pages, mobile navigation and the quote CTA, browser required-field checks, invalid nonce and field rejection, and a successful quote submission with a private WordPress record. All four JSON templates passed Elementor’s native library import.

[Desktop home](screenshots/desktop-home.png) · [Mobile home](screenshots/mobile-home.png) · [Mobile quote form](screenshots/mobile-contact.png)

## Elementor imports and assets

`elementor/` contains four editable page templates. In a separate Copperline install, import a JSON file through **Templates → Saved Templates → Import Templates** and insert it from the Elementor library. The Copperline theme and quote plugin provide the template layout and shortcode behavior. The seed script maps button URLs to the local site automatically. For a manual import in a different URL structure, update the button links in Elementor.

The plumbing photo was generated for this project with OpenAI ImageGen. Its brief depicts a technician tightening a copper fitting beneath a residential bathroom vanity in dark navy workwear, with warm oak and natural daylight. The photo and original theme graphics are bundled locally. Use fictional data throughout.

See [WP-CLI installation](https://make.wordpress.org/cli/handbook/guides/installing/) and [Elementor Free](https://wordpress.org/plugins/elementor/).
