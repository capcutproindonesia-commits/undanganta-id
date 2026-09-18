<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Invitation extends Model {
 use SoftDeletes;
 protected $fillable=['user_id','slug','title','groom_name','bride_name','event_date','venue_name','venue_address','maps_url','theme','music_url','quote','story','cover_path','gallery','gift_accounts','sections','is_published','plan','expires_at','custom_domain','views_count'];
 protected function casts(): array { return ['event_date'=>'datetime','gallery'=>'array','gift_accounts'=>'array','sections'=>'array','is_published'=>'boolean','expires_at'=>'datetime']; }
 protected static function booted(): void { static::creating(function($m){ if(!$m->slug) $m->slug=Str::slug($m->title).'-'.Str::lower(Str::random(5)); }); }
 public function user(){ return $this->belongsTo(User::class); }
 public function guests(){ return $this->hasMany(Guest::class); }
 public function wishes(){ return $this->hasMany(Wish::class)->latest(); }
 public function photos(){ return $this->hasMany(GuestPhoto::class)->latest(); }
}
