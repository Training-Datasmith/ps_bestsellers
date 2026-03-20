# Architecture: ps_bestsellers

## Purpose

A PrestaShop front-office module that displays a list of best-selling products on the
homepage or other widget-compatible positions. Products are sorted by sales volume.

## Directory Structure

```
ps_bestsellers.php   # Main module class (Ps_BestSellers extends Module implements WidgetInterface)
views/templates/     # Smarty/Twig templates for product list display
translations/        # Module translation files
tests/               # PHPStan and unit tests
```

## Key Design Decisions

The module implements `WidgetInterface` and uses PrestaShop's Product Search API
(`ProductSearchProvider`) to query best-selling products with configurable limit and
display options (price, cover image, etc.).

## Extension Points

Place the widget in any theme widget position. Configure the number of products and
display options via the module configuration panel in the back office.
