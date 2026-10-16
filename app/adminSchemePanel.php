<?php
$districts = query('SELECT d.code,d.name,v.name AS division_name FROM districts d JOIN divisions v ON v.code=d.division_code ORDER BY v.name,d.name')->fetchAll();
$schemes = query("SELECT s.*,d.name AS district_name,(SELECT COUNT(*) FROM scheme_applications a WHERE a.scheme_id=s.id) AS application_count FROM schemes s LEFT JOIN districts d ON d.code=s.district_code ORDER BY s.id DESC")->fetchAll();
$schemeTotals = query('SELECT status,COUNT(*) AS total FROM scheme_applications GROUP BY status')->fetchAll();
$totals = array_fill_keys(['pending','approved','rejected'],0);
foreach ($schemeTotals as $row) $totals[$row['status']] = (int)$row['total'];
?>
<section class="panel scheme-section" id="schemes">
 <span class="eyebrow">ADMIN SCHEME CONTROL</span><h2>Government scheme applications</h2>
 <p class="muted">Create and publish offers for farmers. The six starter entries are illustrative examples; confirm a real program’s availability and terms before presenting one as official. Closing a scheme stops new applications and keeps existing decisions.</p>
 <p><strong><?=count($schemes)?></strong> schemes · <strong><?=$totals['pending']?></strong> awaiting officers · <strong><?=$totals['approved']?></strong> approved · <strong><?=$totals['rejected']?></strong> rejected</p>
 <details><summary>Create a scheme</summary>
  <form method="post" class="stack scheme-form"><?=csrf()?><input type="hidden" name="action" value="create_scheme">
   <label>Scheme name<input name="title" maxlength="160" required></label>
   <label>Benefit / support<input name="benefit" maxlength="255" required></label>
   <label>Description and eligibility<textarea name="description" maxlength="3000" required rows="3"></textarea></label>
   <label>Land limit in katha (optional, strictly under)<input name="max_land_katha" type="number" min="0.01" step="0.01"></label>
   <label>District<select name="district_code"><option value="">All districts</option><?php foreach ($districts as $district): ?><option value="<?=e($district['code'])?>"><?=e($district['division_name'].' · '.$district['name'])?></option><?php endforeach; ?></select></label>
   <label>Government source link (optional)<input name="source_url" type="url" maxlength="500" placeholder="https://..."></label>
   <label>Status<select name="status"><option value="draft">Draft</option><option value="active">Active — farmers can apply</option><option value="closed">Closed</option></select></label>
   <button>Create scheme</button>
  </form>
 </details>
 <div class="scheme-grid">
 <?php foreach ($schemes as $scheme): ?>
  <article class="scheme-card">
   <div class="card-top"><h3><?=e($scheme['title'])?></h3><span class="badge <?=e($scheme['status'])?>"><?=e(ucfirst($scheme['status']))?></span></div>
   <?php if ($scheme['is_example']): ?><small class="scheme-example">Illustrative example</small><?php endif; ?>
   <p><?=e($scheme['benefit'])?> · <?=e($scheme['application_count'])?> applications</p>
   <p class="muted"><?= $scheme['district_name'] ? e($scheme['district_name']).' district' : 'All districts' ?><?= $scheme['max_land_katha'] !== null ? ' · Under '.e($scheme['max_land_katha']).' katha' : '' ?></p>
   <details><summary>Edit / publish / close</summary>
    <form method="post" class="stack scheme-form"><?=csrf()?><input type="hidden" name="action" value="update_scheme"><input type="hidden" name="id" value="<?=e($scheme['id'])?>">
     <label>Scheme name<input name="title" maxlength="160" required value="<?=e($scheme['title'])?>"></label>
     <label>Benefit / support<input name="benefit" maxlength="255" required value="<?=e($scheme['benefit'])?>"></label>
     <label>Description and eligibility<textarea name="description" maxlength="3000" required rows="3"><?=e($scheme['description'])?></textarea></label>
     <label>Land limit in katha (optional, strictly under)<input name="max_land_katha" type="number" min="0.01" step="0.01" value="<?=e($scheme['max_land_katha'] ?? '')?>"></label>
     <label>District<select name="district_code"><option value="">All districts</option><?php foreach ($districts as $district): ?><option value="<?=e($district['code'])?>" <?=$scheme['district_code']===$district['code']?'selected':''?>><?=e($district['division_name'].' · '.$district['name'])?></option><?php endforeach; ?></select></label>
     <label>Government source link<input name="source_url" type="url" maxlength="500" value="<?=e($scheme['source_url'] ?? '')?>"></label>
     <label>Status<select name="status"><?php foreach (['draft'=>'Draft','active'=>'Active — farmers can apply','closed'=>'Closed'] as $value=>$label): ?><option value="<?=e($value)?>" <?=$scheme['status']===$value?'selected':''?>><?=e($label)?></option><?php endforeach; ?></select></label>
     <button>Save scheme</button>
    </form>
   </details>
  </article>
 <?php endforeach; ?>
 </div>
</section>
