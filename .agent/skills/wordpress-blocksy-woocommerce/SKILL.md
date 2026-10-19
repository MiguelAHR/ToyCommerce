---
name: wordpress-blocksy-woocommerce
description: >-
  Specialized engineering and design skill for building, debugging, and styling WordPress
  sites powered by the Blocksy theme and WooCommerce. Covers Gutenberg block composition,
  AJAX cart fragments, HPOS compatibility, mu-plugins architecture, and responsive layouts.
---

# WordPress + Blocksy + WooCommerce Engineering Skill

## Core Principles
1. **Zero Intrusion on Core/Plugins:**
   - Never edit files in `wp-content/themes/blocksy/` or `wp-content/plugins/woocommerce/`.
   - All customizations must live in `wp-content/mu-plugins/` (`minikids-estetica.php`, CSS, and JS).
2. **Preserve WooCommerce State & AJAX:**
   - Always retain `.widget_shopping_cart_content` for `wc-cart-fragments.js` auto-updates.
   - Do not hijack `add-to-cart` endpoints or break HPOS tables (`wp_wc_orders`).
3. **Blocksy Header & Container Safety:**
   - Never apply `overflow: hidden` or `overflow-x: clip` to `.ct-container` (this breaks dropdowns and drawers).
   - Sticky headers require `z-index: 9999` and an offset for logged-in admin users:
     `body.admin-bar header.ct-header { top: 32px !important; }`
4. **Native Gutenberg Architecture:**
   - Use standard Gutenberg comments (`<!-- wp:... -->`) instead of arbitrary raw HTML tags inside page posts.
   - For product collections, use `wp:woocommerce/product-collection`.
5. **Strict Local Verification:**
   - Homepage payload must remain lightweight (< 100 KB).
   - Verify with PowerShell HTTP scripts checking status 200, key element counts, and responsive rendering.
