<?php
$landSummary=query('SELECT COUNT(*) total,COALESCE(SUM(archived=0),0) active,COALESCE(SUM(area_sqft),0) area FROM lands WHERE farmer_id=?',[$me['id']])->fetch();
$schemeCounts=array_fill_keys(['pending','approved','rejected'],0);
foreach(query('SELECT status,COUNT(*) total FROM scheme_applications WHERE farmer_id=? GROUP BY status',[$me['id']])->fetchAll() as $row)$schemeCounts[$row['status']]=(int)$row['total'];
$totalSchemes=array_sum($schemeCounts);
$cycleCount=(int)query('SELECT COUNT(*) FROM crop_cycles c JOIN lands l ON l.id=c.land_id WHERE l.farmer_id=?',[$me['id']])->fetchColumn();
$expenseSummary=query('SELECT COUNT(*) entries,COALESCE(SUM(e.amount),0) spent FROM land_expenses e JOIN crop_cycles c ON c.id=e.cycle_id JOIN lands l ON l.id=c.land_id WHERE l.farmer_id=?',[$me['id']])->fetch();
$milestones=['Contact address completed'=>!empty($me['thana_code'])&&!empty($me['detailed_address']),'At least one land registered'=>(int)$landSummary['total']>0,'A crop season recorded'=>$cycleCount>0,'Cultivation expenses recorded'=>(int)$expenseSummary['entries']>0];
$completed=count(array_filter($milestones));$progress=$completed*25;$tier=['Getting started','Getting started','Good','Better','Excellent'][$completed];
?>
<section class="stats overview-stats" aria-label="Your activity totals">
 <div><span>My lands</span><strong><?=(int)$landSummary['total']?></strong><small><?=(int)$landSummary['active']?> active · <?=(int)$landSummary['total']-(int)$landSummary['active']?> archived</small></div>
 <div><span>Total applications</span><strong><?=$totalAppointments+$totalSchemes?></strong><small>Appointments + scheme applications</small></div>
 <div><span>Appointment requests</span><strong><?=$totalAppointments?></strong><small><?=$counts['pending']?> awaiting a response</small></div>
 <div><span>Scheme applications</span><strong><?=$totalSchemes?></strong><small><?=$schemeCounts['pending']?> awaiting a decision</small></div>
</section>
<div class="overview-grid">
 <section class="panel progress-panel"><span class="eyebrow">YOUR RECORD-KEEPING PROGRESS</span><div class="section-heading"><h2><?=$tier?></h2><span class="progress-percentage"><?=$progress?>%</span></div><progress value="<?=$progress?>" max="100" aria-label="Record completion progress"><?=$progress?>%</progress><p><?=$completed?> of 4 record-keeping milestones completed.</p><ul class="milestones"><?php foreach($milestones as $label=>$done):?><li><span class="milestone-mark <?=$done?'done':''?>" aria-hidden="true"><?=$done?'✓':'○'?></span><span><?=e($label)?></span><small><?=$done?'Complete':'Not yet'?></small></li><?php endforeach;?></ul><p class="muted">This tier reflects record completion only. It is not an official rating or a measure of scheme eligibility.</p></section>
 <section class="panel"><span class="eyebrow">FARM SUMMARY</span><h2>Your farming records</h2><dl class="overview-facts"><div><dt>Registered land area</dt><dd><?=number_format((float)$landSummary['area']/720,2)?> katha</dd></div><div><dt>Crop seasons</dt><dd><?=$cycleCount?></dd></div><div><dt>Recorded expenses</dt><dd><?=e(land_money($expenseSummary['spent']))?></dd></div><div><dt>Expense entries</dt><dd><?=(int)$expenseSummary['entries']?></dd></div><div><dt>Unread appointment updates</dt><dd><?=$unread?></dd></div></dl><p class="muted">Includes archived plots and all crop seasons. One katha = 720 sq ft.</p></section>
 <section class="panel"><span class="eyebrow">APPOINTMENT STATUS</span><h2>Your requests</h2><dl class="overview-facts"><?php foreach($counts as $status=>$count):?><div><dt><?=e(ucfirst($status))?></dt><dd><?=$count?></dd></div><?php endforeach;?></dl></section>
 <section class="panel"><span class="eyebrow">SCHEME STATUS</span><h2>Your applications</h2><dl class="overview-facts"><?php foreach($schemeCounts as $status=>$count):?><div><dt><?=e(ucfirst($status))?></dt><dd><?=$count?></dd></div><?php endforeach;?></dl><p class="muted">View officer responses and contact details on the Government schemes page.</p></section>
</div>
