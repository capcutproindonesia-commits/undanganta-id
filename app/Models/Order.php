<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model { protected $fillable=['user_id','invitation_id','plan_id','amount','status','payment_method','payment_proof','verified_at']; protected function casts(): array{return ['verified_at'=>'datetime'];} public function user(){return $this->belongsTo(User::class);} public function plan(){return $this->belongsTo(Plan::class);} public function invitation(){return $this->belongsTo(Invitation::class);} }
