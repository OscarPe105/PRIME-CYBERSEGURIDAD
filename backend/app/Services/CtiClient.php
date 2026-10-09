<?php
namespace App\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
final class CtiClient {
 public function pending(?string $cve): array {
  $status=$cve?'pending':'not_applicable';
  return ['queried_at'=>now()->toIso8601String(),'epss'=>['status'=>$status,'value'=>null,'percentile'=>null], 'kev'=>['status'=>$status,'value'=>null]];
 }
 public function fetch(?string $cve): array {
  if(!$cve) return $this->pending(null);
  $snapshot=$this->pending($cve);
  try {
   $r=Http::timeout(8)->connectTimeout(4)->get('https://api.first.org/data/v1/epss',['cve'=>$cve])->throw(); $raw=$r->body(); $data=$r->json('data');
   if(!is_array($data)) throw new \RuntimeException('EPSS inválido');
   $item=collect($data)->firstWhere('cve',$cve);
   if($item && (!isset($item['epss'],$item['percentile'],$item['date']) || !is_numeric($item['epss']) || $item['epss']<0 || $item['epss']>1 || !is_numeric($item['percentile']) || $item['percentile']<0 || $item['percentile']>1)) throw new \RuntimeException('EPSS inválido');
   $snapshot['epss']=['status'=>$item?'ok':'missing','value'=>$item?(float)$item['epss']:null,'percentile'=>$item?(float)$item['percentile']:null,'data_date'=>$item['date']??null,'source'=>'https://api.first.org/data/v1/epss','sha256'=>hash('sha256',$raw),'raw'=>$raw];
  } catch(\Throwable $e) { $snapshot['epss']=['status'=>'error','value'=>null,'error_type'=>get_class($e)]; }
  try {
   $cached=Cache::get('prime-kev-catalog'); $fromCache=$cached!==null;
   if(!$cached) {
    $r=Http::timeout(12)->connectTimeout(4)->get('https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json')->throw();
    $json=$r->json(); if(!is_array($json['vulnerabilities']??null) || !isset($json['catalogVersion'],$json['dateReleased'])) throw new \RuntimeException('KEV inválido');
    $cached=['raw'=>$r->body(),'retrieved_at'=>now()->toIso8601String()]; Cache::put('prime-kev-catalog',$cached,now()->addHours(24));
   }
   $json=json_decode($cached['raw'],true,512,JSON_THROW_ON_ERROR); $item=collect($json['vulnerabilities'])->firstWhere('cveID',$cve);
   $snapshot['kev']=['status'=>'ok','value'=>$item!==null,'catalog_version'=>$json['catalogVersion'],'data_date'=>$json['dateReleased'],'source'=>'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json','retrieved_at'=>$cached['retrieved_at'],'cached'=>$fromCache,'sha256'=>hash('sha256',$cached['raw']),'entry'=>$item,'raw'=>$cached['raw']];
  } catch(\Throwable $e) { $snapshot['kev']=['status'=>'error','value'=>null,'error_type'=>get_class($e)]; }
  return $snapshot;
 }
}
