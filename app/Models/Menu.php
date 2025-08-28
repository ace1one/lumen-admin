<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['name', 'description'];

    public function subMenus()
    {
        return $this->hasMany(SubMenu::class);
    }
}