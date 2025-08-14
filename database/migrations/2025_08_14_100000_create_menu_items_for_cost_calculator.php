<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemsForCostCalculator extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        // Check if the menu system table exists
        if (Schema::hasTable('menus') && Schema::hasTable('menu_items')) {
            // Get admin menu ID (using the slug to be consistent with core)
            $adminMenu = DB::table('menus')->where('slug', 'adminsettings')->first();
            $adminMenuId = $adminMenu ? $adminMenu->id : 1; // Default to 1 if not found
            
            // Clean up any existing menu items for this module to avoid duplicates
            $this->cleanupExistingMenuItems();
            
            // Find the highest order for positioning
            $maxOrder = DB::table('menu_items')
                ->where('menu_id', $adminMenuId)
                ->whereNull('parent_id')
                ->max('order') ?? 0;
            
            // Get the column list to ensure we only use existing columns
            $columns = Schema::getColumnListing('menu_items');
            
            // Create base menu item data
            $menuItemData = [
                'menu_id' => $adminMenuId,
                'title' => 'Cost Calculator',
                'url' => '#',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Add optional columns if they exist in the schema
            if (in_array('parent_id', $columns)) {
                $menuItemData['parent_id'] = null;
            }
            
            if (in_array('icon', $columns)) {
                $menuItemData['icon'] = 'bi bi-calculator';
            }
            
            if (in_array('is_parent', $columns)) {
                $menuItemData['is_parent'] = true;
            }
            
            if (in_array('order', $columns)) {
                $menuItemData['order'] = $maxOrder + 1;
            }
            
            if (in_array('module', $columns)) {
                $menuItemData['module'] = 'td-cost-calcultaror';
            }
            
            // Create parent menu item for Cost Calculator
            $parentId = DB::table('menu_items')->insertGetId($menuItemData);
            
            // Create submenu items
            $items = [
                [
                    'title' => 'Dashboard',
                    'url' => '/admin/cost-calculator',
                    'icon' => 'bi bi-speedometer',
                    'order' => 1,
                ],
                [
                    'title' => 'Cost Items',
                    'url' => '/admin/cost-calculator/cost-items',
                    'icon' => 'bi bi-cash',
                    'order' => 2,
                ],
                [
                    'title' => 'Products',
                    'url' => '/admin/cost-calculator/products',
                    'icon' => 'bi bi-box',
                    'order' => 3,
                ],
                [
                    'title' => 'Forecasting',
                    'url' => '/admin/cost-calculator/forecast',
                    'icon' => 'bi bi-graph-up',
                    'order' => 4,
                ]
            ];
            
            foreach ($items as $item) {
                // Create base submenu item data
                $submenuItemData = [
                    'menu_id' => $adminMenuId,
                    'title' => $item['title'],
                    'url' => $item['url'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Add optional columns if they exist in the schema
                if (in_array('parent_id', $columns)) {
                    $submenuItemData['parent_id'] = $parentId;
                }
                
                if (in_array('icon', $columns)) {
                    $submenuItemData['icon'] = $item['icon'];
                }
                
                if (in_array('is_parent', $columns)) {
                    $submenuItemData['is_parent'] = false;
                }
                
                if (in_array('order', $columns)) {
                    $submenuItemData['order'] = $item['order'];
                }
                
                if (in_array('module', $columns)) {
                    $submenuItemData['module'] = 'td-cost-calcultaror';
                }
                
                // Insert the submenu item
                DB::table('menu_items')->insert($submenuItemData);
            }
        }
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        $this->cleanupExistingMenuItems();
    }
    
    /**
     * Clean up existing menu items for this module
     */
    private function cleanupExistingMenuItems()
    {
        if (Schema::hasTable('menu_items')) {
            // Find the parent Cost Calculator menu item
            $parentItem = DB::table('menu_items')
                ->where('title', 'Cost Calculator')
                ->first();
                
            if ($parentItem) {
                // Check if parent_id column exists
                if (Schema::hasColumn('menu_items', 'parent_id')) {
                    // Delete all child items first
                    DB::table('menu_items')
                        ->where('parent_id', $parentItem->id)
                        ->delete();
                }
                
                // Then delete the parent item
                DB::table('menu_items')
                    ->where('id', $parentItem->id)
                    ->delete();
            }
            
            // Additional cleanup: if module column exists, use it too
            if (Schema::hasColumn('menu_items', 'module')) {
                DB::table('menu_items')
                    ->where('module', 'td-cost-calcultaror')
                    ->delete();
            }
        }
    }
}
