<?php
namespace App\Http\Controllers; use App\Models\Guest; use App\Models\Invitation; use Illuminate\Http\Request;
class GuestController extends Controller {
 public function index(Invitation $invitation){$this->authorize('update',$invitation);$guests=$invitation->guests()->latest()->paginate(50);return view('guests.index',compact('invitation','guests'));}
 public function store(Request $r,Invitation $invitation){$this->authorize('update',$invitation);$d=$r->validate(['name'=>'required|max:150','phone'=>'nullable|max:30','category'=>'nullable|max:80']);$invitation->guests()->create($d);return back()->with('ok','Tamu ditambahkan.');}
 public function destroy(Invitation $invitation,Guest $guest){$this->authorize('update',$invitation);abort_unless($guest->invitation_id===$invitation->id,404);$guest->delete();return back()->with('ok','Tamu dihapus.');}
 public function export(Invitation $invitation){$this->authorize('update',$invitation);return response()->streamDownload(function()use($invitation){$h=fopen('php://output','w');fputcsv($h,['Nama','WA','Kategori','RSVP','Jumlah','Check-in']);foreach($invitation->guests as $g)fputcsv($h,[$g->name,$g->phone,$g->category,$g->rsvp_status,$g->party_size,$g->checked_in_at]);fclose($h);},$invitation->slug.'-tamu.csv',['Content-Type'=>'text/csv']);}
}
