=== Web Kit ===
Contributors: yourcompany
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Custom Elementor widgets for the site, starting with an HTML-rendered
(no JS table library) repeater-driven data/comparison table.

== Description ==

Web Kit registers a dedicated "Web Kit" category in the Elementor widgets
panel and currently ships one widget:

* **Web Kit HTML Table** — a comparison/data table built from two
  repeaters (Columns, Rows). Column count is automatic (based on the
  Columns repeater) or can be set manually. Rendered as a plain HTML
  `<table>`, fully styleable from the widget's Style tab, no JS table
  library dependency. Each row's cells support an optional dynamic-tag
  link, plus per-cell text color and background color overrides.

* **Web Kit FAQ** — displays the FAQs added on the current post's edit
  screen (Posts only). Editors manage question/answer pairs from a
  repeater meta box on the post ("FAQs" box); the widget automatically
  shows whichever post it's rendered on - no manual post picker needed,
  so a single-post Elementor template works correctly across every post.
  Renders as a plain, always-expanded list. Optional FAQPage JSON-LD
  schema output for SEO.

== Backend: the FAQs Meta Box ==

Lives on the Post edit screen (`includes/class-wk-faq-metabox.php`).
Stores everything in one post meta key, `_wk_faq_items`, as an array of
`['question' => ..., 'answer' => ...]`. To extend it to other post types
later, add the post type slug to the `POST_TYPES` constant in that file.

== Plugin Info ==

* Name: Web Kit
* Slug: `web-kit`
* Text Domain: `web-kit`
* PHP function/hook prefix: `wk_`
* PHP namespace: `WebKit`

== Folder Structure ==

`
web-kit.php                           Plugin bootstrap, version/compat checks
includes/class-wk-plugin.php          Category + widget registration, asset registration
widgets/class-wk-html-table-widget.php    The HTML Table widget
assets/css/wk-table.css               Table widget styles
assets/js/wk-table.js                 Placeholder for future JS
`

== Adding a New Widget ==

1. Create `widgets/class-wk-your-widget.php` following the same pattern
   (namespace `WebKit\Widgets`, extends `\Elementor\Widget_Base`).
2. Add one line to `get_widgets()` in `includes/class-wk-plugin.php`
   mapping the filename to the fully-qualified class name.
3. If it needs its own CSS/JS, register the handle (prefixed `wk-`) in
   `register_assets()` in the same file, then return it from the widget's
   `get_style_depends()` / `get_script_depends()`.
4. Any new procedural functions/hooks should use the `wk_` prefix to stay
   consistent with the rest of the plugin.

== Changelog ==

= 1.0.0 =
* Initial release under the Web Kit name: HTML Table widget with full
  table borders, dynamic-tag cell links, and per-cell text/background
  color overrides.
