<?php
verify_csrf();
if($_SESSION['role']!=='farmer')throw new RuntimeException('Only farmers can change their land records.');
$action=$_POST['action']??'';
$landId=(int)($_POST['land_id']??0);
db()->beginTransaction();
query('SELECT id FROM farmers WHERE id=? FOR UPDATE',[$me['id']]);
if($action==='save_land'){
 if($landId)$old=land_lock($landId,$me);
 $name=field('name',100);$address=field('address',255);$thana=field('thana_code',8);
 if(!query('SELECT code FROM thanas WHERE code=?',[$thana])->fetch())throw new RuntimeException('Choose a valid land location.');
 $unit=$_POST['display_unit']??'';$tenure=$_POST['tenure']??'';
 if(!isset(LAND_UNITS[$unit]) || !in_array($tenure,['owned','leased','shared'],true))throw new RuntimeException('Choose valid area and tenure values.');
 $area=land_number('area',4,100000000,true);$sqft=number_format((float)$area*LAND_UNITS[$unit],4,'.','');
 if((float)$sqft<0.01 || (float)$sqft>99999999999)throw new RuntimeException('The land area is outside the supported range.');
 $args=[$name,$thana,$address,$sqft,$unit,$tenure,land_optional('soil_type',100),land_optional('irrigation',100),land_optional('notes',1000)];
 if($landId)query('UPDATE lands SET name=?,thana_code=?,address=?,area_sqft=?,display_unit=?,tenure=?,soil_type=?,irrigation=?,notes=? WHERE id=?',array_merge($args,[$landId]));
 else{query('INSERT INTO lands(name,thana_code,address,area_sqft,display_unit,tenure,soil_type,irrigation,notes,farmer_id) VALUES (?,?,?,?,?,?,?,?,?,?)',array_merge($args,[$me['id']]));$landId=(int)db()->lastInsertId();}
}elseif($action==='archive_land'){
 $land=land_lock($landId,$me);query('UPDATE lands SET archived=? WHERE id=?',[!$land['archived']?1:0,$landId]);
}elseif($action==='save_cycle'){
 $land=land_lock($landId,$me);if($land['archived'])throw new RuntimeException('Restore this land before adding or changing a crop season.');
 $cycleId=(int)($_POST['cycle_id']??0);
 if($cycleId && !query('SELECT id FROM crop_cycles WHERE id=? AND land_id=?',[$cycleId,$landId])->fetch())throw new RuntimeException('Crop season not found.');
 $crop=field('crop',100);$season=field('season',100);$start=land_date('sowing_date');$end=land_date('expected_harvest',true);$budget=land_number('budget',2,9999999999.99);$status=$_POST['status']??'';
 if($end && $end<$start)throw new RuntimeException('Harvest date cannot be before sowing date.');
 if(!in_array($status,['planned','growing','harvested'],true))throw new RuntimeException('Choose a valid crop status.');
 $args=[$crop,land_optional('variety',100),$season,$start,$end,$budget,$status];
 if($cycleId)query('UPDATE crop_cycles SET crop=?,variety=?,season=?,sowing_date=?,expected_harvest=?,budget=?,status=? WHERE id=?',array_merge($args,[$cycleId]));
 else{query('INSERT INTO crop_cycles(crop,variety,season,sowing_date,expected_harvest,budget,status,land_id) VALUES (?,?,?,?,?,?,?,?)',array_merge($args,[$landId]));$cycleId=(int)db()->lastInsertId();}
}elseif(in_array($action,['save_expense','delete_expense'],true)){
 $land=land_lock($landId,$me);if($land['archived'])throw new RuntimeException('Restore this land before changing expenses.');
 $cycleId=(int)($_POST['cycle_id']??0);
 if(!query('SELECT id FROM crop_cycles WHERE id=? AND land_id=?',[$cycleId,$landId])->fetch())throw new RuntimeException('Choose a crop season belonging to this land.');
 $expenseId=(int)($_POST['expense_id']??0);
 if($expenseId && !query('SELECT id FROM land_expenses WHERE id=? AND cycle_id=?',[$expenseId,$cycleId])->fetch())throw new RuntimeException('Expense not found.');
 if($action==='delete_expense'){
  if(!$expenseId)throw new RuntimeException('Choose an expense.');query('DELETE FROM land_expenses WHERE id=?',[$expenseId]);
 }else{
  $category=$_POST['category']??'';if(!isset(COST_CATEGORIES[$category]))throw new RuntimeException('Choose an expense category.');
  $quantity=land_number('quantity',3,1000000,true);$rate=land_number('unit_cost',2,100000000);
  if((float)$quantity*(float)$rate>999999999999.99)throw new RuntimeException('Expense amount is too large.');
  $date=land_date('expense_date');if($date>date('Y-m-d'))throw new RuntimeException('Recorded spending cannot have a future date. Use the estimate calculator instead.');
  $args=[$category,field('item',150),$quantity,field('unit',30),$rate,$date,land_optional('note',500)];
  if($expenseId)query('UPDATE land_expenses SET category=?,item=?,quantity=?,unit=?,unit_cost=?,expense_date=?,note=? WHERE id=?',array_merge($args,[$expenseId]));
  else query('INSERT INTO land_expenses(category,item,quantity,unit,unit_cost,expense_date,note,cycle_id) VALUES (?,?,?,?,?,?,?,?)',array_merge($args,[$cycleId]));
 }
}else throw new RuntimeException('Unknown land action.');
db()->commit();flash('Land records saved.');go('land.php?land='.$landId.(isset($cycleId)?'&cycle='.$cycleId:''));
