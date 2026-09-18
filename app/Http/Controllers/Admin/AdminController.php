<?php
namespace App\Http\Controllers\Admin; use App\Http\Controllers\Controller; use App\Models\{GuestPhoto,Invitation,Order,Plan,User}; use Illuminate\Http\Request;
class AdminController extends Controller {
 public function index(){return view('admin.index',['users'=>User::count(),'invitations'=>Invitation::count(),'orders'=>Order::latest()->take(20)->get(),'plans'=>Plan::orderBy('price')->get(),'photos'=>GuestPhoto::where('status','pending')->latest()->take(30)->get()]);}
 public function verify(Order $order){$order->update(['status'=>'paid','verified_at'=>now()]);$order->invitation?->update(['plan'=>$order->plan->code,'expires_at'=>now()->addDays($order->plan->duration_days)]);return back()->with('ok','Pembayaran diverifikasi.');}
 public function photo(GuestPhoto $photo,string $status){abort_unless(in_array($status,['approved','rejected']),422);$photo->update(['status'=>$status]);return back()->with('ok','Moderasi foto diperbarui.');}
}
