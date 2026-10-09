<?php
namespace App\Console\Commands;
use App\Services\Totp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{Crypt,DB,Hash};
final class PrimeUser extends Command {
 protected $signature='prime:user {email} {role=administrador}';
 protected $description='Crea una cuenta local para el prototipo con contraseña aleatoria y enrolamiento TOTP obligatorio';
 public function handle(Totp $totp): int {
  $email=$this->argument('email');$role=$this->argument('role');
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)||!in_array($role,['analista','administrador','auditor'],true)) { $this->error('Correo o rol inválido.');return 1; }
  if(DB::table('users')->where('email',$email)->exists()) { $this->error('La cuenta ya existe.');return 1; }
  $password=bin2hex(random_bytes(12));
  DB::table('users')->insert(['name'=>ucfirst($role),'email'=>$email,'role'=>$role,'password'=>Hash::make($password),'mfa_secret'=>Crypt::encryptString($totp->secret()),'created_at'=>now(),'updated_at'=>now()]);
  $this->line('Cuenta: '.$email);$this->line('Contraseña inicial: '.$password);$this->line('Ingrese en PRIME y confirme TOTP con su aplicación autenticadora.');return 0;
 }
}
