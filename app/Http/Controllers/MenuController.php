<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helpers\ApiResponse;
use App\Models\Menu;
use App\Models\SubMenu;

class MenuController extends Controller
{
  
    // Create a new menu
    public function createMenu(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:menus,name',
            'description' => 'nullable|string',
            'subMenus' => 'nullable|array',
            'subMenus.*.name' => 'required|string',
            'subMenus.*.description' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return ApiResponse::error('Validation error', 422, $validator->errors());
        }
    
        // Create the main menu
        $menu = Menu::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
    
        // If submenus exist, create them
        if ($request->has('subMenus')) {
            $subMenusData = collect($request->subMenus)->map(function ($sub) {
                return [
                    'name' => $sub['name'],
                    'description' => $sub['description'] ?? null,
                ];
            })->toArray();
    
            // Use saveMany to create all submenus at once
            $menu->subMenus()->createMany($subMenusData);
        }
    
        // Load submenus for response
        $menu->load('subMenus');
    
        return ApiResponse::success($menu, 'Menu with submenus created successfully');
    }
    

    // Get all menus with their sub-menus
    public function getMenus()
    {
        $menus = Menu::with('subMenus')->get();
        return ApiResponse::success($menus, 'Menus retrieved successfully');
    }

    // Create a new sub-menu under a specific menu
    public function createSubMenu(Request $request, $menuId)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error', 422, $validator->errors());
        }

        $menu = Menu::find($menuId);
        if (!$menu) {
            return ApiResponse::error('Menu not found', 404);
        }

        $subMenu = SubMenu::create([
            'menu_id'     => $menuId,
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return ApiResponse::success($subMenu, 'Sub-menu created successfully');
    }

    public function getSubMenus($menuId)
    {
        $menu = Menu::with('subMenus')->find($menuId);
        if (!$menu) {
            return ApiResponse::error('Menu not found', 404);
        }

        return ApiResponse::success($menu->subMenus, 'Sub-menus retrieved successfully');
    }

    public function updateMenu(Request $request, $id)
{
    // Find the menu
    $menu = Menu::with('subMenus')->findOrFail($id);

    // Validate input
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|unique:menus,name,' . $id,
        'description' => 'nullable|string',
        'subMenus' => 'nullable|array',
        'subMenus.*.id' => 'nullable|integer|exists:sub_menus,id',
        'subMenus.*.name' => 'required|string',
        'subMenus.*.description' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return ApiResponse::error('Validation error', 422, $validator->errors());
    }

    // Update main menu
    $menu->update([
        'name' => $request->name,
        'description' => $request->description,
    ]);

    if ($request->has('subMenus')) {
        $existingIds = $menu->subMenus->pluck('id')->toArray();
        $submittedIds = collect($request->subMenus)->pluck('id')->filter()->toArray();

        // Delete submenus that are removed
        $toDelete = array_diff($existingIds, $submittedIds);
        if (!empty($toDelete)) {
            SubMenu::destroy($toDelete);
        }

        // Create or update submenus
        foreach ($request->subMenus as $sub) {
            if (isset($sub['id'])) {
                // Update existing submenu
                $menu->subMenus()->where('id', $sub['id'])->update([
                    'name' => $sub['name'],
                    'description' => $sub['description'] ?? null,
                ]);
            } else {
                // Create new submenu
                $menu->subMenus()->create([
                    'name' => $sub['name'],
                    'description' => $sub['description'] ?? null,
                ]);
            }
        }
    } else {
        // If no subMenus in request, delete all existing submenus
        $menu->subMenus()->delete();
    }

    // Load submenus for response
    $menu->load('subMenus');

    return ApiResponse::success($menu, 'Menu and submenus updated successfully');
}

 public function deleteMenu($id)
{
    $menu = Menu::find($id);
    if (!$menu) {
        return ApiResponse::error('Menu not found', 404);
    }

    // This will also delete associated sub-menus due to foreign key constraints with cascade on delete
    $menu->delete();

    return ApiResponse::success(null, 'Menu and its sub-menus deleted successfully');

}
}
