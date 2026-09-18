<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Plan extends Model { protected $fillable=['name','code','price','duration_days','features','is_active']; protected function casts(): array{return ['features'=>'array','is_active'=>'boolean'];} }
