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


}
