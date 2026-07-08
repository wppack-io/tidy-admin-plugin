# Coverage roadmap

Which plugins Tidy Admin supports today, and which it should support next.

The selection criteria, in order of weight:

1. **Active installs** — the more sites a plugin runs on, the more admin
   screens a module cleans up.
2. **A commercial edition exists** — plugins with a Pro/Premium version (or
   paid add-ons/services) are the ones whose free versions carry upgrade
   menus, teaser pages, banners, and notices in wp-admin. That is exactly
   the UI this plugin relocates.
3. **Promotion surface** — how much of the admin the free version's
   promotions occupy (dedicated submenus, locked feature pages, dashboard
   widgets, recurring notices).

As with everything in this project: nothing functional is ever removed.
Upgrade guidance moves to the Upgrades panel, documentation and support to
the Help panel, setup reminders to the dashboard widget — see
[ui-guidelines.md](ui-guidelines.md).

Active-install counts are approximate, rounded figures from the plugins'
WordPress.org listings at the time of writing (July 2026); they change over
time and are listed only to explain prioritization.

## Currently supported

| Plugin | Active installs | Commercial edition |
|---|---|---|
| [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/) | 10M+ | Yoast SEO Premium, Academy, add-ons |
| [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) | 2M+ | ACF PRO (WP Engine) |
| [WPConsent](https://wordpress.org/plugins/wpconsent-cookies-banner-privacy-suite/) | 100k+ | WPConsent Pro (Awesome Motive) |
| [MonsterInsights (Google Analytics)](https://wordpress.org/plugins/google-analytics-for-wordpress/) | 3M+ | MonsterInsights Pro (Awesome Motive) |
| [OptinMonster](https://wordpress.org/plugins/optinmonster/) | 1M+ | OptinMonster (SaaS, Awesome Motive) |
| [All in One SEO](https://wordpress.org/plugins/all-in-one-seo-pack/) | 3M+ | AIOSEO Pro |
| [All-in-One WP Migration](https://wordpress.org/plugins/all-in-one-wp-migration/) | 5M+ | Paid extensions |
| [Wordfence](https://wordpress.org/plugins/wordfence/) | 4M+ | Wordfence Premium / Care / Response |
| [WP Mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/) | 3M+ | WP Mail SMTP Pro |
| [MC4WP (Mailchimp for WP)](https://wordpress.org/plugins/mailchimp-for-wp/) | 2M+ | MC4WP Premium |
| [Instagram Feed (Smash Balloon)](https://wordpress.org/plugins/instagram-feed/) | 1M+ | Instagram Feed Pro |
| [Facebook Feed (Smash Balloon)](https://wordpress.org/plugins/custom-facebook-feed/) | 200k+ | Custom Facebook Feed Pro |
| [YouTube Feeds (Smash Balloon)](https://wordpress.org/plugins/feeds-for-youtube/) | 100k+ | YouTube Feed Pro |
| [Twitter Feeds (Smash Balloon)](https://wordpress.org/plugins/custom-twitter-feeds/) | 100k+ | Custom Twitter Feeds Pro |
| [Reviews Feed (Smash Balloon)](https://wordpress.org/plugins/reviews-feed/) | 40k+ | Reviews Feed Pro |
| [TikTok Feeds (Smash Balloon)](https://wordpress.org/plugins/feeds-for-tiktok/) | 20k+ | TikTok Feeds Pro |
| [WPChat (Smash Balloon)](https://wordpress.org/plugins/smashballoon-wpchat-livechat-customer-support/) | 10k+ | WPChat paid tiers |
| [Duplicator](https://wordpress.org/plugins/duplicator/) | 1M+ | Duplicator Pro |
| [PublishPress Future](https://wordpress.org/plugins/post-expirator/) | 800k+ | PublishPress Future Pro |
| [Redirection](https://wordpress.org/plugins/redirection/) | 2M+ | — (free, donation-supported) |
| [Broken Link Checker](https://wordpress.org/plugins/broken-link-checker/) | 700k+ | Cloud Link Checker (paid service) |
| [Post Types Order](https://wordpress.org/plugins/post-types-order/) | 600k+ | Advanced Post Types Order |
| [Contact Form CFDB7](https://wordpress.org/plugins/contact-form-cfdb7/) | 500k+ | Paid extensions |
| [Custom Post Type UI](https://wordpress.org/plugins/custom-post-type-ui/) | 1M+ | CPT UI Pro (Pluginize / WebDevStudios) |
| [Taxonomy Terms Order](https://wordpress.org/plugins/taxonomy-terms-order/) | 500k+ | Advanced Taxonomy Terms Order |
| [W3 Total Cache](https://wordpress.org/plugins/w3-total-cache/) | 1M+ | W3 Total Cache Pro |
| [YARPP](https://wordpress.org/plugins/yet-another-related-posts-plugin/) | 200k+ | — (review requests only) |
| [BNFW](https://wordpress.org/plugins/bnfw/) | 200k+ | Paid add-ons, priority support |
| [EmbedPress](https://wordpress.org/plugins/embedpress/) | 100k+ | EmbedPress Pro |
| [Location Weather](https://wordpress.org/plugins/location-weather/) | 10k+ | Location Weather Pro |

Plus a **WordPress core** module (`WordPressCore`, always active) whose
features clean up core's own admin-UI nudges — every feature ships **off by
default**, opt-in on Settings › Tidy Admin, since these touch WordPress
itself rather than a vendor's promotions.

## Candidates — tier 1

The largest reach combined with a substantial promotion surface in the free
version. These are the plugins where a module helps the most people.

| Plugin | Active installs | Commercial edition | Free-version promotion surface |
|---|---|---|---|
| [Elementor](https://wordpress.org/plugins/elementor/) | 10M+ | Elementor Pro | Upgrade submenu, locked (Pro) widgets and feature pages, admin notices |
| [WPForms Lite](https://wordpress.org/plugins/wpforms-lite/) | 6M+ | WPForms Pro | Upgrade submenu, teaser pages (Entries, Addons), dashboard widget, notices |
| [LiteSpeed Cache](https://wordpress.org/plugins/litespeed-cache/) | 6M+ | QUIC.cloud paid services | Service sign-up prompts, promotional notices |
| [Really Simple Security](https://wordpress.org/plugins/really-simple-ssl/) | 5M+ | Really Simple Security Pro | Upgrade prompts in its dashboard, locked feature toggles, notices |
| [Jetpack](https://wordpress.org/plugins/jetpack/) | 4M+ | Paid plans | My Jetpack plan cards, recommendation banners, notices |
| [UpdraftPlus](https://wordpress.org/plugins/updraftplus/) | 3M+ | UpdraftPlus Premium | Premium tabs and teaser settings, add-on store, notices |
| [Rank Math SEO](https://wordpress.org/plugins/seo-by-rank-math/) | 3M+ | Rank Math Pro | Upgrade menus, Pro-badged modules, setup-wizard promotions |
| [WooCommerce](https://wordpress.org/plugins/woocommerce/) | 7M+ | Paid extensions, Woo.com marketplace | Marketing hub, extension and payment recommendations, in-app promotions and notices |
| [CookieYes](https://wordpress.org/plugins/cookie-law-info/) | 3M+ | CookieYes paid plans | Connect-account and upgrade prompts, usage-limit nudges, notices |

## Candidates — tier 2

Solid install bases with a clear commercial edition; smaller reach or a
smaller promotion surface than tier 1.

| Plugin | Active installs | Commercial edition | Free-version promotion surface |
|---|---|---|---|
| [Essential Addons for Elementor](https://wordpress.org/plugins/essential-addons-for-elementor-lite/) | 2M+ | Essential Addons Pro | Locked (Pro) elements, upgrade menus, notices |
| [Smush](https://wordpress.org/plugins/wp-smushit/) | 1M+ | Smush Pro (WPMU DEV) | Pro teasers, cross-sells for other WPMU DEV plugins |
| [WP-Optimize](https://wordpress.org/plugins/wp-optimize/) | 1M+ | WP-Optimize Premium | Premium tabs, teaser features, notices |
| [WP Fastest Cache](https://wordpress.org/plugins/wp-fastest-cache/) | 1M+ | Premium | Locked settings checkboxes, upgrade prompts |
| [Ninja Forms](https://wordpress.org/plugins/ninja-forms/) | 600k+ | Paid add-ons | Add-on store menu, membership promotions |
| [WP Statistics](https://wordpress.org/plugins/wp-statistics/) | 500k+ | WP Statistics Premium | Premium teasers in reports, notices |
| [NextGEN Gallery](https://wordpress.org/plugins/nextgen-gallery/) | 400k+ | NextGEN Pro | Upgrade menus, teaser pages |
| [SeedProd](https://wordpress.org/plugins/coming-soon/) | 1M+ | SeedProd Pro (Awesome Motive) | Upgrade menus, locked templates/blocks, notices |
| [All-In-One Security (AIOS)](https://wordpress.org/plugins/all-in-one-wp-security-and-firewall/) | 1M+ | AIOS Premium | Premium feature teasers, upgrade prompts |
| [EWWW Image Optimizer](https://wordpress.org/plugins/ewww-image-optimizer/) | 1M+ | EWWW IO paid tiers | Pro teasers in settings, notices |
| [Complianz](https://wordpress.org/plugins/complianz-gdpr/) | 1M+ | Complianz Premium | Locked features, upgrade prompts |
| [Imagify](https://wordpress.org/plugins/imagify/) | 900k+ | Imagify paid plans (WP Media) | Quota and upgrade prompts, notices |
| [Solid Security](https://wordpress.org/plugins/better-wp-security/) | 900k+ | Solid Security Pro | Pro-badged features, upgrade prompts |
| [TablePress](https://wordpress.org/plugins/tablepress/) | 800k+ | TablePress Pro | Premium feature modules and teasers |
| [Polylang](https://wordpress.org/plugins/polylang/) | 700k+ | Polylang Pro / Business | Pro feature teasers, add-on prompts |
| [Fluent Forms](https://wordpress.org/plugins/fluentform/) | 600k+ | Fluent Forms Pro | Pro-badged fields, upgrade menus |
| [Pretty Links](https://wordpress.org/plugins/pretty-link/) | 400k+ | Pretty Links Pro (Awesome Motive) | Upgrade menus, teaser pages |
| [SEOPress](https://wordpress.org/plugins/wp-seopress/) | 300k+ | SEOPress Pro | Upgrade prompts, Pro-badged settings |
| [Limit Login Attempts Reloaded](https://wordpress.org/plugins/limit-login-attempts-reloaded/) | 2M+ | Premium micro-cloud plans | App-connect nudges, upgrade prompts |
| [Loginizer](https://wordpress.org/plugins/loginizer/) | 1M+ | Loginizer Pro (Softaculous) | Upgrade prompts, Pro-feature teasers |
| [Code Snippets](https://wordpress.org/plugins/code-snippets/) | 1M+ | Code Snippets Pro | Pro-feature teasers, upgrade menu |
| [Premium Addons for Elementor](https://wordpress.org/plugins/premium-addons-for-elementor/) | 1M+ | Premium Addons Pro | Locked (Pro) widgets, upgrade prompts |
| [ElementsKit Elementor Addons](https://wordpress.org/plugins/elementskit-lite/) | 1M+ | ElementsKit Pro (Wpmet) | Locked widgets, upgrade menus, cross-sells |
| [Cookie Notice & Compliance](https://wordpress.org/plugins/cookie-notice/) | 1M+ | Cookie Compliance (paid) | Compliance upsell, upgrade prompts |
| [Smart Slider 3](https://wordpress.org/plugins/smart-slider-3/) | 900k+ | Smart Slider 3 Pro | Locked (Pro) features, upgrade prompts |
| [Sucuri Security](https://wordpress.org/plugins/sucuri-scanner/) | 800k+ | Sucuri Firewall (paid) | Firewall and plan upsell prompts |
| [Popup Maker](https://wordpress.org/plugins/popup-maker/) | 700k+ | Popup Maker paid extensions | Extensions/upgrade menus, notices |
| [MailPoet](https://wordpress.org/plugins/mailpoet/) | 600k+ | MailPoet paid plans (Automattic) | Sending-plan upsell, upgrade prompts |

## Candidates — tier 3 (long tail)

Established freemium plugins with a commercial edition whose reach or
in-admin promotion is smaller than tier 2, but which still carry the kind of
upgrade UI a module would tidy. Install counts (all roughly 100k–700k) are
omitted here; grouped by area.

**Forms**

| Plugin | Commercial edition | Free-version promotion |
|---|---|---|
| [Formidable Forms](https://wordpress.org/plugins/formidable/) | Formidable Forms Pro | Upgrade menus, teaser fields, notices |
| [Forminator](https://wordpress.org/plugins/forminator/) | Forminator Pro (WPMU DEV) | Cross-sells, upgrade prompts |
| [Everest Forms](https://wordpress.org/plugins/everest-forms/) | Everest Forms Pro | Locked fields, upgrade menus |

**Page builders, blocks & design**

| Plugin | Commercial edition | Free-version promotion |
|---|---|---|
| [Kadence Blocks](https://wordpress.org/plugins/kadence-blocks/) | Kadence Blocks Pro | Pro block teasers, upgrade prompts |
| [GenerateBlocks](https://wordpress.org/plugins/generateblocks/) | GenerateBlocks Pro | Pro block teasers, upgrade prompts |
| [Spectra](https://wordpress.org/plugins/ultimate-addons-for-gutenberg/) | Spectra Pro (Brainstorm Force) | Pro block teasers, cross-sells |
| [Otter Blocks](https://wordpress.org/plugins/otter-blocks/) | Otter Pro (Themeisle) | Pro block teasers, upgrade prompts |
| [Stackable](https://wordpress.org/plugins/stackable-ultimate-gutenberg-blocks/) | Stackable Premium | Locked blocks, upgrade prompts |
| [Beaver Builder](https://wordpress.org/plugins/beaver-builder-lite-version/) | Beaver Builder (paid) | Upgrade prompts, module teasers |
| [Happy Addons for Elementor](https://wordpress.org/plugins/happy-elementor-addons/) | Happy Addons Pro | Locked widgets, upgrade prompts |

**Images, performance & backup**

| Plugin | Commercial edition | Free-version promotion |
|---|---|---|
| [ShortPixel Image Optimizer](https://wordpress.org/plugins/shortpixel-image-optimiser/) | ShortPixel paid credits | Credit and upgrade prompts, notices |
| [Optimole](https://wordpress.org/plugins/optimole-wp/) | Optimole paid plans (Themeisle) | Quota and upgrade prompts |
| [Converter for Media](https://wordpress.org/plugins/webp-converter-for-media/) | Converter for Media Pro | Pro teasers, upgrade prompts |
| [BackWPup](https://wordpress.org/plugins/backwpup/) | BackWPup Pro | Pro teasers, upgrade prompts |
| [WPvivid Backup](https://wordpress.org/plugins/wpvivid-backuprestore/) | WPvivid Pro | Pro teasers, upgrade prompts |

**Analytics & multilingual**

| Plugin | Commercial edition | Free-version promotion |
|---|---|---|
| [ExactMetrics](https://wordpress.org/plugins/google-analytics-dashboard-for-wp/) | ExactMetrics Pro (Awesome Motive) | Upgrade menus, teaser reports |
| [Analytify](https://wordpress.org/plugins/wp-analytify/) | Analytify Pro | Pro-badged reports, upgrade prompts |
| [Burst Statistics](https://wordpress.org/plugins/burst-statistics/) | Burst Pro (Really Simple) | Upgrade prompts, locked reports |
| [TranslatePress](https://wordpress.org/plugins/translatepress-multilingual/) | TranslatePress Pro | Locked languages/features, upgrade prompts |
| [GTranslate](https://wordpress.org/plugins/gtranslate/) | GTranslate paid plans | Upgrade prompts, feature teasers |

**Membership, LMS & marketing**

| Plugin | Commercial edition | Free-version promotion |
|---|---|---|
| [Tutor LMS](https://wordpress.org/plugins/tutor/) | Tutor LMS Pro | Pro add-on teasers, upgrade menus |
| [LifterLMS](https://wordpress.org/plugins/lifterlms/) | Paid add-ons | Add-on store, upgrade prompts |
| [Paid Memberships Pro](https://wordpress.org/plugins/paid-memberships-pro/) | Paid add-ons | Add-on store, upgrade prompts |
| [Ultimate Member](https://wordpress.org/plugins/ultimate-member/) | Paid extensions | Extensions marketplace, upgrade prompts |
| [FluentCRM](https://wordpress.org/plugins/fluent-crm/) | FluentCRM Pro | Pro-feature teasers, upgrade menus |
| [ThirstyAffiliates](https://wordpress.org/plugins/thirstyaffiliates/) | ThirstyAffiliates Pro | Upgrade menus, teaser pages |

**Galleries, sliders & WooCommerce add-ons**

| Plugin | Commercial edition | Free-version promotion |
|---|---|---|
| [MetaSlider](https://wordpress.org/plugins/ml-slider/) | MetaSlider Pro | Pro teasers, upgrade prompts |
| [Envira Gallery](https://wordpress.org/plugins/envira-gallery-lite/) | Envira Gallery Pro (Awesome Motive) | Upgrade menus, add-on teasers |
| [Modula](https://wordpress.org/plugins/modula-best-grid-gallery/) | Modula Pro | Pro teasers, upgrade prompts |
| [WooCommerce PDF Invoices & Packing Slips](https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/) | Pro (WP Overnight) | Pro teasers, upgrade prompts |
| [Variation Swatches for WooCommerce](https://wordpress.org/plugins/woo-variation-swatches/) | Variation Swatches Pro | Pro teasers, upgrade prompts |
| [CartFlows](https://wordpress.org/plugins/cartflows/) | CartFlows Pro | Upgrade menus, locked steps |

**Data, tables & admin utility**

| Plugin | Commercial edition | Free-version promotion |
|---|---|---|
| [Ninja Tables](https://wordpress.org/plugins/ninja-tables/) | Ninja Tables Pro | Pro teasers, upgrade menus |
| [Meta Box](https://wordpress.org/plugins/meta-box/) | Meta Box AIO / extensions | Extensions store, upgrade prompts |
| [WP Reset](https://wordpress.org/plugins/wp-reset/) | WP Reset Pro | Pro teasers, upgrade prompts |
| [Admin Columns](https://wordpress.org/plugins/codepress-admin-columns/) | Admin Columns Pro | Pro teasers, upgrade prompts |
| [Advanced Database Cleaner](https://wordpress.org/plugins/advanced-database-cleaner/) | ADC Pro | Pro teasers, upgrade prompts |

## Considered, not planned

High-install plugins that do not fit the criteria — usually because there is
no commercial edition, or the free version shows little or no promotion in
wp-admin. There is simply nothing for a module to do.

| Plugin | Reason |
|---|---|
| [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) | No commercial edition; minimal promotion |
| [Classic Editor](https://wordpress.org/plugins/classic-editor/) | No commercial edition; no promotion |
| [Site Kit by Google](https://wordpress.org/plugins/google-site-kit/) | No commercial edition |
| [Akismet](https://wordpress.org/plugins/akismet/) | Paid plans exist, but its admin footprint is small |

## Adding a module

Every new module follows the checklist in [CLAUDE.md](../CLAUDE.md) and the
classification rules in [ui-guidelines.md](ui-guidelines.md): install the
real plugin as a dev dependency, classify every piece of vendor UI, declare
each cleanup as an individually toggleable feature with a verified major
version, and walk the per-screen QA checklist.
