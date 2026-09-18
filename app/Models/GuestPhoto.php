<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GuestPhoto extends Model { protected $fillable=['invitation_id','guest_name','path','caption','status']; public function invitation(){return $this->belongsTo(Invitation::class);} }
