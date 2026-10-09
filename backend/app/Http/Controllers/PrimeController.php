<?php
namespace App\Http\Controllers;
use App\Services\{Totp,CsvImporter,CtiClient,PrimeEngine};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Crypt,DB,Hash,Storage};
use Illuminate\Validation\ValidationException;
final class PrimeController extends Controller {
 private function audit(string $event,array $details=[]): void { DB::table('audit_events')->insert(['user_id'=>Auth::id(),'event'=>$event,'details'=>json_encode($details),'created_at'=>now()]); }
 private function allow(Request $r,array $roles=['analista','administrador','auditor']): void {
  abort_unless(Auth::check() && $r->session()->get('mfa_verified'),401,'Inicie sesión y valide MFA.');
  abort_unless(in_array(Auth::user()->role,$roles,true),403,'Operación no permitida para su rol.');
 }
 public function session(Request $r) {
  return ['csrf'=>csrf_token(),'user'=>Auth::check()&&$r->session()->get('mfa_verified')?['name'=>Auth::user()->name,'email'=>Auth::user()->email,'role'=>Auth::user()->role]:null];
 }
 public function login(Request $r) {
  $v=$r->validate(['email'=>'required|email','password'=>'required|string']);
  $user=DB::table('users')->where('email',$v['email'])->first();
  if(!$user || !Hash::check($v['password'],$user->password)) { $this->audit('login_denied'); throw ValidationException::withMessages(['email'=>'Credenciales incorrectas.']); }
  Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken();
  $r->session()->put(['pending_user'=>$user->id,'pending_until'=>time()+300]);
  $response=['requires_mfa'=>true,'enrollment'=>!$user->mfa_confirmed,'csrf'=>csrf_token()];
  if(!$user->mfa_confirmed) {
   $secret=Crypt::decryptString($user->mfa_secret); $response['secret']=$secret;
   $response['otpauth']='otpauth://totp/'.rawurlencode('PRIME:'.$user->email).'?secret='.$secret.'&issuer=PRIME&algorithm=SHA1&digits=6&period=30';
  }
  return $response;
 }
 public function verify(Request $r,Totp $totp) {
  $v=$r->validate(['code'=>'required|string|max:100']);
  abort_unless($r->session()->get('pending_user') && $r->session()->get('pending_until',0)>=time(),401,'Desafío MFA expirado.');
  $id=$r->session()->get('pending_user'); $recovery=[];
  DB::transaction(function() use($id,$v,$totp,&$recovery) {
   $user=DB::table('users')->where('id',$id)->lockForUpdate()->first(); abort_unless($user,401);
   $step=$totp->verify(Crypt::decryptString($user->mfa_secret),$v['code'],$user->mfa_last_step);
   $hashes=json_decode($user->recovery_hashes??'[]',true); $matched=null;
   if($step===null && $user->mfa_confirmed) foreach($hashes as $i=>$hash) if(hash_equals($hash,hash('sha256',$v['code']))) { $matched=$i; break; }
   if($step===null && $matched===null) { $this->audit('mfa_denied',['target_user'=>$id]); throw ValidationException::withMessages(['code'=>'Código incorrecto, expirado o ya utilizado.']); }
   if($matched!==null) unset($hashes[$matched]);
   if(!$user->mfa_confirmed) { for($i=0;$i<8;$i++) $recovery[]=bin2hex(random_bytes(12)); $hashes=array_map(fn($c)=>hash('sha256',$c),$recovery); }
   DB::table('users')->where('id',$id)->update(['mfa_confirmed'=>true,'mfa_last_step'=>$step??$user->mfa_last_step,'recovery_hashes'=>json_encode(array_values($hashes))]);
  });
  Auth::loginUsingId($id); $r->session()->forget(['pending_user','pending_until']); $r->session()->regenerate(); $r->session()->regenerateToken(); $r->session()->put('mfa_verified',true);
  $this->audit('mfa_login'); return ['csrf'=>csrf_token(),'recovery_codes'=>$recovery,'user'=>['name'=>Auth::user()->name,'email'=>Auth::user()->email,'role'=>Auth::user()->role]];
 }
 public function logout(Request $r) { $this->allow($r); $this->audit('logout'); Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken(); return ['csrf'=>csrf_token()]; }
 public function index(Request $r) {
  $this->allow($r); $result=[];
  foreach(DB::table('findings')->orderBy('id')->limit(1000)->get() as $f) {
   $latest=DB::table('triage_revisions')->where('finding_id',$f->id)->latest('id')->first();
   $calculation=json_decode($latest->calculation,true); $snapshot=json_decode($latest->snapshot,true);
   unset($snapshot['epss']['raw'],$snapshot['kev']['raw']);
   $result[]=[...get_object_vars($f),'input'=>json_decode($f->input,true),'calculation'=>$calculation,'cti'=>$snapshot,'revision_id'=>$latest->id];
  }
  usort($result,fn($a,$b)=>($b['calculation']['kev_urgent']<=>$a['calculation']['kev_urgent'])?:($b['calculation']['score']<=>$a['calculation']['score'])?:($a['id']<=>$b['id']));
  return ['findings'=>$result,'policy'=>PrimeEngine::VERSION];
 }
 private function revision(int $id,array $input,array $cti,PrimeEngine $engine): void {
  $calc=$engine->calculate($input,$cti);
  DB::table('triage_revisions')->insert(['finding_id'=>$id,'policy_version'=>PrimeEngine::VERSION,'snapshot'=>json_encode($cti),'calculation'=>json_encode($calc),'score'=>$calc['score'],'priority'=>$calc['priority'],'provisional'=>$calc['provisional'],'created_at'=>now()]);
 }
 public function upload(Request $r,CsvImporter $csv,CtiClient $cti,PrimeEngine $engine) {
  $this->allow($r,['analista','administrador']); $r->validate(['file'=>'required|file|max:2048']); $file=$r->file('file');
  $rows=$csv->parse($file->getRealPath());
  if(DB::table('findings')->whereIn('reference',array_column($rows,'id'))->exists()) throw ValidationException::withMessages(['file'=>'Existe un ID previamente importado. No se sobrescribieron hallazgos.']);
  $path=$file->store('imports','local');
  try { $batch=DB::transaction(function() use($rows,$path,$file,$cti,$engine) {
   $batch=DB::table('import_batches')->insertGetId(['uploaded_by'=>Auth::id(),'path'=>$path,'sha256'=>hash_file('sha256',$file->getRealPath()),'count'=>count($rows),'created_at'=>now()]);
   foreach($rows as $row) {
    $id=DB::table('findings')->insertGetId(['batch_id'=>$batch,'reference'=>$row['id'],'cve'=>$row['cve'],'asset'=>$row['asset'],'zone'=>$row['zone'],'product'=>$row['product'],'product_version'=>$row['version'],'input'=>json_encode($row),'status'=>'abierto','created_at'=>now(),'updated_at'=>now()]);
    $this->revision($id,$row,$cti->pending($row['cve']),$engine);
   }
   $this->audit('csv_imported',['batch'=>$batch,'count'=>count($rows)]); return $batch;
  }); } catch(\Throwable $e) { Storage::disk('local')->delete($path); throw $e; }
  return ['batch_id'=>$batch,'count'=>count($rows)];
 }
 public function enrich(Request $r,int $id,CtiClient $cti,PrimeEngine $engine) {
  $this->allow($r,['analista','administrador']); $f=DB::table('findings')->find($id); abort_unless($f,404);
  $snapshot=$cti->fetch($f->cve);
  DB::transaction(function() use($f,$snapshot,$engine) { $this->revision($f->id,json_decode($f->input,true),$snapshot,$engine); $this->audit('cti_refreshed',['finding'=>$f->id,'epss_status'=>$snapshot['epss']['status'],'kev_status'=>$snapshot['kev']['status']]); });
  return ['updated'=>true,'epss_status'=>$snapshot['epss']['status'],'kev_status'=>$snapshot['kev']['status']];
 }
 public function history(Request $r,int $id) { $this->allow($r); abort_unless(DB::table('findings')->find($id),404);
  return ['revisions'=>DB::table('triage_revisions')->where('finding_id',$id)->orderBy('id')->get()->map(function($x) {$x->snapshot=json_decode($x->snapshot,true);$x->calculation=json_decode($x->calculation,true);return $x;}),'evidence'=>DB::table('evidence_files')->where('finding_id',$id)->get(['id','name','sha256','size','created_at'])]; }
 public function propose(Request $r,int $id) {
  $this->allow($r,['analista','administrador']); $v=$r->validate(['treatment'=>'required|string|max:4000']);
  DB::transaction(function() use($r,$id,$v) {
   $f=DB::table('findings')->where('id',$id)->lockForUpdate()->first(); abort_unless($f,404); abort_unless($f->status==='abierto',409,'El hallazgo ya tiene tratamiento propuesto.');
   DB::table('findings')->where('id',$id)->update(['status'=>'propuesto','treatment'=>$v['treatment'],'proposed_by'=>Auth::id(),'updated_at'=>now()]); $this->audit('treatment_proposed',['finding'=>$id]);
  }); return ['status'=>'propuesto'];
 }
 public function evidence(Request $r,int $id) {
  $this->allow($r,['analista','administrador']); abort_unless(DB::table('findings')->find($id),404);
  $r->validate(['file'=>'required|file|max:2048|mimes:txt,png,pdf,json']); $file=$r->file('file');
  $path=$file->store('evidence','local'); $eid=DB::table('evidence_files')->insertGetId(['finding_id'=>$id,'uploaded_by'=>Auth::id(),'path'=>$path,'name'=>mb_substr(basename($file->getClientOriginalName()),0,200),'sha256'=>hash_file('sha256',$file->getRealPath()),'size'=>$file->getSize(),'created_at'=>now()]);
  $this->audit('evidence_uploaded',['finding'=>$id,'evidence'=>$eid]); return ['id'=>$eid];
 }
 public function review(Request $r,int $id) {
  $this->allow($r,['administrador']); $v=$r->validate(['note'=>'required|string|min:10|max:4000']);
  DB::transaction(function() use($id,$v) {
   $f=DB::table('findings')->where('id',$id)->lockForUpdate()->first(); abort_unless($f,404);
   abort_unless($f->status==='propuesto',409,'Primero proponga tratamiento.'); abort_if($f->proposed_by===Auth::id(),403,'La aprobación requiere otra persona.');
   abort_unless(DB::table('evidence_files')->where('finding_id',$id)->exists(),422,'Adjunte evidencia antes de cerrar.');
   DB::table('findings')->where('id',$id)->update(['status'=>'mitigado','reviewed_by'=>Auth::id(),'updated_at'=>now()]); $this->audit('treatment_reviewed',['finding'=>$id,'note'=>$v['note']]);
  }); return ['status'=>'mitigado','message'=>'Cierre administrativo registrado. La eficacia del control debe sustentarse en la evidencia revisada.'];
 }
 public function download(Request $r,int $id) { $this->allow($r); $e=DB::table('evidence_files')->find($id); abort_unless($e,404); return Storage::disk('local')->download($e->path,$e->name,['X-Content-Type-Options'=>'nosniff']); }
 public function auditLog(Request $r) { $this->allow($r,['administrador','auditor']); return ['events'=>DB::table('audit_events')->latest('id')->limit(200)->get()]; }
 public function export(Request $r) {
  $this->allow($r); $rows=$this->index($r)['findings'];
  return response()->streamDownload(function() use($rows) {
   $out=fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF"); fputcsv($out,['ID','CVE','Activo','Zona','Prioridad','IRC','CTI provisional','Estado'],',','"','');
   foreach($rows as $f) {
    $row=[$f['reference'],$f['cve']??'No aplica',$f['asset'],$f['zone'],$f['calculation']['priority'],$f['calculation']['display_score'],$f['calculation']['provisional']?'Sí':'No',$f['status']];
    $row=array_map(fn($v)=>preg_match('/^[\s\x00-\x1F]*[=+@-]/u',(string)$v)?"'".$v:$v,$row); fputcsv($out,$row,',','"','');
   } fclose($out);
  },'prime-triaje.csv',['Content-Type'=>'text/csv; charset=UTF-8','X-Content-Type-Options'=>'nosniff']);
 }
}
