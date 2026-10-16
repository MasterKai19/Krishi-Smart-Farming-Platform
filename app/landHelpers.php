<?php
const LAND_UNITS=['katha'=>720,'decimal'=>435.6,'acre'=>43560,'hectare'=>107639.104167,'sqft'=>1];
const COST_CATEGORIES=['seeds'=>'Seeds','fertilizer'=>'Fertilizer','labour'=>'Labour','irrigation'=>'Irrigation','pesticides'=>'Pesticides','machinery'=>'Machinery','rent'=>'Rent','other'=>'Other'];
function land_money($n):string{return 'BDT '.number_format((float)$n,2);}
function land_number(string $key,int $places,float $max,bool $positive=false):string{
 $v=trim((string)($_POST[$key]??''));
 if(!preg_match('/^\d{1,10}(?:\.\d{1,'.$places.'})?$/D',$v) || (float)$v>$max || ($positive && (float)$v<=0))throw new RuntimeException('Enter a valid '.str_replace('_',' ',$key).' with up to '.$places.' decimal places.');
 return number_format((float)$v,$places,'.','');
}
function land_date(string $key,bool $optional=false):?string{
 $v=trim((string)($_POST[$key]??''));if($v==='' && $optional)return null;
 $d=DateTime::createFromFormat('!Y-m-d',$v);
 if(!$d || $d->format('Y-m-d')!==$v)throw new RuntimeException('Enter a valid '.str_replace('_',' ',$key).'.');return $v;
}
function land_optional(string $key,int $max):?string{
 $v=trim((string)($_POST[$key]??''));if(strlen($v)>$max)throw new RuntimeException('Please shorten '.str_replace('_',' ',$key).'.');return $v===''?null:$v;
}
function land_scope(array $me):array{
 if($_SESSION['role']==='farmer')return ['l.farmer_id=?',[$me['id']]];
 if($_SESSION['role']==='admin')return ['1=1',[]];
 $district=query('SELECT district_code FROM thanas WHERE code=?',[$me['thana_code']??''])->fetchColumn();
 return ["t.district_code=? AND f.status='active'",[$district?:'']];
}
function land_record(int $id,array $me):array{
 [$where,$params]=land_scope($me);
 $row=query('SELECT l.*,f.name AS farmer_name,f.phone AS farmer_phone,t.name AS thana_name,t.district_code,d.name AS district_name,d.division_code FROM lands l JOIN farmers f ON f.id=l.farmer_id JOIN thanas t ON t.code=l.thana_code JOIN districts d ON d.code=t.district_code WHERE l.id=? AND '.$where,array_merge([$id],$params))->fetch();
 if(!$row)throw new RuntimeException('Land not found or access is not allowed.');return $row;
}
function land_lock(int $id,array $me):array{
 if($_SESSION['role']!=='farmer')throw new RuntimeException('Only the farmer can change land records.');
 // Same owner lock serializes changes with the scheme snapshot transaction.
 query('SELECT id FROM farmers WHERE id=? FOR UPDATE',[$me['id']]);
 $l=query('SELECT * FROM lands WHERE id=? AND farmer_id=? FOR UPDATE',[$id,$me['id']])->fetch();
 if(!$l)throw new RuntimeException('Land not found or access is not allowed.');return $l;
}
function land_snapshot(array $land,array $cycle):array{
 $cost=query('SELECT * FROM v_land_costs WHERE cycle_id=?',[$cycle['id']])->fetch();
 $categories=query('SELECT category,SUM(amount) AS total FROM land_expenses WHERE cycle_id=? GROUP BY category',[$cycle['id']])->fetchAll();
 $entries=query('SELECT category,item,quantity,unit,unit_cost,amount,expense_date,note FROM land_expenses WHERE cycle_id=? ORDER BY expense_date,id',[$cycle['id']])->fetchAll();
 return ['captured_at'=>date(DATE_ATOM),'land'=>array_intersect_key($land,array_flip(['id','name','address','area_sqft','display_unit','tenure','soil_type','irrigation','district_name','thana_name'])),'cycle'=>$cycle,'cost'=>$cost,'categories'=>$categories,'entries'=>$entries];
}
function render_land_snapshot(array $application):void{
 $s=json_decode($application['snapshot_json']??'null',true);
 if(!$s){echo '<p class="muted">Legacy application: no per-land financial snapshot was captured.</p>';return;}
 ?>
 <div class="land-snapshot"><strong>Land &amp; costs at submission</strong>
 <p><?=e($s['land']['name'])?> · <?=e(number_format((float)$s['land']['area_sqft']/720,3))?> katha<br><?=e($s['land']['address'].' · '.$s['land']['district_name'])?></p>
 <p><?=e($s['cycle']['crop'].' · '.$s['cycle']['season'])?> · <?=e($s['land']['tenure'])?><br>Recorded spending: <strong><?=e(land_money($s['cost']['total_cost']))?></strong> · Budget: <?=e(land_money($s['cycle']['budget']))?></p>
 <details><summary>Financial breakdown</summary><ul><?php foreach($s['categories'] as $row):?><li><?=e(COST_CATEGORIES[$row['category']]??$row['category'])?>: <?=e(land_money($row['total']))?></li><?php endforeach;?></ul>
 <?php foreach($s['entries'] as $entry):?><p class="muted"><?=e($entry['expense_date'].' · '.$entry['item'].' · '.$entry['quantity'].' '.$entry['unit'].' × '.land_money($entry['unit_cost']).' = '.land_money($entry['amount']))?></p><?php endforeach;?></details>
 <small>Saved <?=e($s['captured_at'])?>. Later edits do not change this review record. Farmer-declared information; verify supporting documents.</small></div>
 <?php
}
