<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Wish extends Model { protected $fillable=['invitation_id','guest_name','message','is_approved']; protected function casts(): array{return ['is_approved'=>'boolean'];} public function invitation(){return $this->belongsTo(Invitation::class);} }
