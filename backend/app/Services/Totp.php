<?php
namespace App\Services;
final class Totp {
 private const ALPHABET='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
 public function secret(): string { $s=''; for($i=0;$i<32;$i++) $s.=self::ALPHABET[random_int(0,31)]; return $s; }
 public function code(string $secret, int $step): string {
  $bits=''; foreach(str_split($secret) as $c) { $pos=strpos(self::ALPHABET,$c); if($pos===false) throw new \InvalidArgumentException('Secreto inválido'); $bits.=str_pad(decbin($pos),5,'0',STR_PAD_LEFT); }
  $key=''; for($i=0;$i+8<=strlen($bits);$i+=8) $key.=chr(bindec(substr($bits,$i,8)));
  $hash=hash_hmac('sha1',pack('N2',0,$step),$key,true); $offset=ord($hash[19])&15;
  $n=unpack('N',substr($hash,$offset,4))[1]&0x7fffffff;
  return str_pad((string)($n%1000000),6,'0',STR_PAD_LEFT);
 }
 public function verify(string $secret,string $code,int $lastStep,?int $time=null): ?int {
  if(!preg_match('/^\d{6}$/D',$code)) return null;
  $now=intdiv($time??time(),30);
  foreach([$now,$now-1,$now+1] as $step) if($step>$lastStep && hash_equals($this->code($secret,$step),$code)) return $step;
  return null;
 }
}
