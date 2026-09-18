<?php
namespace App\Http\Controllers; use Illuminate\Http\Request;
class DashboardController extends Controller { public function __invoke(Request $r){$invitations=$r->user()->invitations()->withCount('guests')->latest()->get();return view('dashboard.index',compact('invitations'));} }
