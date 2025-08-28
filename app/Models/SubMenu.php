<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubMenu extends Model
{
    protected $fillable = ['menu_id', 'name', 'description'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}