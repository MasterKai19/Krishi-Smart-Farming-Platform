<?php
$district = query('SELECT t.district_code FROM thanas t WHERE t.code=?', [$me['thana_code'] ?? ''])->fetchColumn();
$applications = $district ? query("SELECT a.*,sl.snapshot_json,c.land_id,s.title,s.benefit,f.name AS farmer_name,f.phone,f.detailed_address,t.name AS thana_name,d.name AS district_name
 FROM scheme_applications a JOIN schemes s ON s.id=a.scheme_id JOIN farmers f ON f.id=a.farmer_id
 LEFT JOIN thanas t ON t.code=f.thana_code LEFT JOIN scheme_application_land sl ON sl.application_id=a.id LEFT JOIN crop_cycles c ON c.id=sl.cycle_id JOIN districts d ON d.code=COALESCE(sl.district_code,t.district_code)
 WHERE d.code=? AND f.status='active' ORDER BY FIELD(a.status,'pending','approved','rejected'),a.created_at DESC,a.id DESC", [$district])->fetchAll() : [];
?>
<section class="panel scheme-section" id="schemes">
 <span class="eyebrow">LOCAL APPLICATIONS</span><h2>Scheme requests in your district</h2>
 <p class="muted">Review applications for lands in your district. Confirm eligibility and actual scheme availability before approving.</p>
 <?php if (!$district): ?><p class="error">Complete your address to review local applications.</p><?php endif; ?>
 <?php if ($district && !$applications): ?><p class="muted">No scheme applications from your district yet.</p><?php endif; ?>
 <div class="scheme-grid">
 <?php foreach ($applications as $application): ?>
  <article class="scheme-card">
   <div class="card-top"><h3><?=e($application['title'])?></h3><span class="badge <?=e($application['status'])?>"><?=e(ucfirst($application['status']))?></span></div>
   <p><strong>Farmer:</strong> <?=e($application['farmer_name'])?> · <?=e($application['phone'])?></p>
   <p><strong>Farmer contact address:</strong> <?=e($application['detailed_address'].' · '.$application['thana_name'].' · '.$application['district_name'])?></p>
   <p><strong>Total registered land at application:</strong> <?=e($application['land_katha_snapshot'])?> katha</p>
   <p><strong>Support:</strong> <?=e($application['benefit'])?></p>
   <?php render_land_snapshot($application); ?>
   <?php if ($application['land_id']): ?><p><a href="land.php?land=<?=e($application['land_id'])?>">View current land & costs →</a></p><?php endif; ?>
   <?php if ($application['farmer_note']): ?><p><strong>Farmer note:</strong> <?=e($application['farmer_note'])?></p><?php endif; ?>
   <?php if ($application['status'] === 'pending'): ?>
    <form method="post" class="stack"><?=csrf()?><input type="hidden" name="action" value="review_scheme"><input type="hidden" name="application_id" value="<?=e($application['id'])?>"><label>Response / reason for rejection<textarea name="officer_note" maxlength="500" rows="2"></textarea></label><div class="admin-actions"><button name="decision" value="approved">Approve</button><button name="decision" value="rejected" class="secondary">Reject</button></div></form>
   <?php else: ?><p><strong>Response:</strong> <?=e($application['officer_note'] ?: 'No note')?></p><?php endif; ?>
  </article>
 <?php endforeach; ?>
 </div>
</section>
