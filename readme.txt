=== Payment Method Order Column ===
Contributors: alessio.rosi
Tags: woocommerce, orders, payment method, order column, hpos
Requires at least: 5.0
Tested up to: 7.1.5
Requires PHP: 7.4
Stable tag: 3.1.1
License: GPL2
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Adds a payment method column to the WooCommerce orders list, with a filter and sorting by payment method.

== Description ==
Payment Method Order Column adds a column to the WooCommerce orders list in the admin interface, showing an icon for the payment method used in each order. You can filter and sort orders by payment method.

**Features**

* Payment method column in the orders list, with a tooltip showing the method name
* Dropdown filter by payment method
* Sortable column
* Customizable icons for each payment method (WooCommerce > Payment Column)
* Compatible with High-Performance Order Storage (HPOS) and legacy order storage
* Italian and English translations

Requires WooCommerce 4.0 or higher. Tested with WooCommerce 11.3.1.

= More plugins by Eraclito =

* [Pause Sales for WooCommerce](https://wordpress.org/plugins/eraclito-woo-pause-sales/) - pause WooCommerce sales and show a custom banner message to your customers.
* [Eraclito Limit Product Quantity](https://wordpress.org/plugins/eraclito-limit-product-quantity/) - limit the maximum quantity of a product that can be purchased per order and warn customers when the limit is reached.
* [Woocommerce Poste Delivery Business PRO](https://www.eraclito.it/woocommerce-poste-delivery-business/) - integrate Poste Italiane Delivery Business shipping and tracking into WooCommerce.

== Installation ==
1. Install the plugin from the WordPress plugin directory, or upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin from the Plugins menu.
3. Open the WooCommerce orders list to see the new column and filter.
4. Optional: go to WooCommerce > Payment Column to customize the icons.

== Frequently Asked Questions ==

= Does it work with HPOS? =
Yes. The plugin declares compatibility with High-Performance Order Storage and also works with legacy order storage.

= Why don't I see a payment method in the filter? =
The filter lists every registered payment gateway, including disabled ones, so older orders can still be filtered.

== Screenshots ==
1. The payment method column in the WooCommerce orders list
2. Filter the orders list by payment method
3. The settings page: customize the icon of each payment method

== Changelog ==

= 3.1.1 =
* Version bump.

= 3.1.0 =
* New settings page layout with banner, panel and Eraclito branding.
* Interface strings are now in English, Italian translation included.
* Verified compatibility with WordPress 7.1.5 and WooCommerce 11.3.1.
* Added uninstall routine that removes the plugin settings.
* Added icons, banner and screenshots for the plugin directory.

= 3.0.4 =
* Verified compatibility with WordPress 7.1.4 and WooCommerce 11.2.1
* Fix: sorting by payment method (HPOS and legacy order storage)
* Fix: filter by payment method with HPOS
* Fix: filter and settings now list disabled payment gateways too
* Fix: translation files (invalid .mo header, duplicated string)
* New: English (en_US) translation
* Minimum PHP version is now 7.4

= 3.0.0 =
* Full HPOS support
* Settings page to customize icons
* Translation system
* Code refactoring

== Upgrade Notice ==

= 3.1.1 =
Maintenance release.

= 3.1.0 =
New settings page layout and English interface strings (Italian translation included).

= 3.0.4 =
Fixes sorting and filtering by payment method with HPOS. Requires PHP 7.4 or higher.
