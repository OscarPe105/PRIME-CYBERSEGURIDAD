<?php
namespace App\Services;
use InvalidArgumentException;
final class PrimeEngine {
 public const VERSION = 'PRIME-experimental-0.1';
 public function calculate(array $input, array $cti): array {
  foreach(['cvss','damage','exploitability','reproducibility','affected_users','discoverability'] as $key) {
   if(!isset($input[$key]) || !is_numeric($input[$key]) || $input[$key]<0 || $input[$key]>10) throw new InvalidArgumentException("Valor inválido: $key");
  }
  $dread=.30*$input['damage']+.25*$input['exploitability']+.20*$input['reproducibility']+.15*$input['affected_users']+.10*$input['discoverability'];
  $base=.60*$input['cvss']+.40*$dread;
  $epss=$cti['epss']['value']??null;
  if($epss!==null && (!is_numeric($epss)||$epss<0||$epss>1)) throw new InvalidArgumentException('EPSS fuera de rango');
  $score=min(10,$base*(1+($epss??0)));
  $severity=$score>=9?'Crítico':($score>=7?'Alto':($score>=4?'Medio':'Bajo'));
  $kev=$cti['kev']['value']??null;
  $provisional=in_array($cti['epss']['status']??'pending',['pending','error','missing'],true)||in_array($cti['kev']['status']??'pending',['pending','error','missing'],true);
  return ['version'=>self::VERSION,'weights'=>['cvss'=>.60,'dread'=>.40,'damage'=>.30,'exploitability'=>.25,'reproducibility'=>.20,'affected_users'=>.15,'discoverability'=>.10],
   'dread'=>$dread,'base'=>$base,'score'=>$score,'display_score'=>number_format($score,2,'.',''),
   'severity'=>$severity,'priority'=>$kev===true?'Urgente KEV':$severity,'provisional'=>$provisional,
   'epss_applied'=>$epss!==null,'kev_urgent'=>$kev===true];
 }
}
