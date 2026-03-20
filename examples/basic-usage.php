<?php

declare(strict_types=1);

/**
 * Example: Working with the ps_bestsellers PrestaShop module.
 *
 * ps_bestsellers displays a configurable block of best-selling products
 * on the storefront. It hooks into PrestaShop's widget and block system
 * to render a product listing sorted by order volume.
 *
 * This file documents common usage patterns.
 */

// --- Module renders via widget hooks ---
// The module registers on displayLeftColumn, displayRightColumn,
// displayHome, and similar hooks. PrestaShop dispatches these automatically.
// No direct PHP call needed for standard usage.

// --- Querying best-selling products programmatically ---
// ps_bestsellers uses PrestaShop's ProductSale helper to get data.
// You can replicate this in custom code:

// $bestSellers = ProductSale::getBestSales(
//     id_lang: (int) Context::getContext()->language->id,
//     pageNumber: 0,
//     nbProducts: 8,          // Number of products to display
//     orderBy: 'quantity',    // Sort field
//     orderWay: 'DESC',
// );
//
// foreach ($bestSellers as $product) {
//     echo $product['name'] . ' — sold: ' . $product['sales'] . "\n";
// }

// --- Back Office configuration ---
// Modules > Best Sellers:
//   - Number of products to display (default: 8)
//   - Hook placement (home, left column, right column, footer)

// --- Template override ---
// Copy and modify the block template in your theme:
//   themes/{theme}/modules/ps_bestsellers/views/templates/hook/ps_bestsellers.tpl

// --- Hook: displayHome (widget output) ---
// The module responds to renderWidget() calls from the theme.
// Widget configuration (number of products, title) is set in Back Office.
//
// To call the widget directly in a custom template:
// {widget name="ps_bestsellers" hook="displayHome"}
