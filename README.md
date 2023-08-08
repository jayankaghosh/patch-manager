# Patch Manager

## Installation
`composer require jayanka/patch-manager`

## This magento extension provides a set of `bin/magento` CLI commands to maintain data patches

### Commands
1. `bin/magento j:patch:apply` - Apply patches by module name(s) or class name(s)
   Examples
    - `bin/magento j:patch:apply --module Magento_Catalog --module Magento_Sales`
    - `bin/magento j:patch:apply --className Magento\\Catalog\\Setup\\Patch\\Data\\InstallDefaultCategories`
    - `bin/magento j:patch:apply # Apply patches for all modules`

2. `bin/magento j:patch:delete` - Delete patches by its class name(s)
   Examples
    - `bin/magento j:patch:delete --className Magento\\Catalog\\Setup\\Patch\\Data\\InstallDefaultCategories`
