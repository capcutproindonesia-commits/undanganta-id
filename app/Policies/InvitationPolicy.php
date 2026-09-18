<?php
namespace App\Policies;
use App\Models\Invitation; use App\Models\User;
class InvitationPolicy { public function view(User $u, Invitation $i): bool{return $u->is_admin||$i->user_id===$u->id;} public function update(User $u, Invitation $i): bool{return $this->view($u,$i);} public function delete(User $u, Invitation $i): bool{return $this->view($u,$i);} }
