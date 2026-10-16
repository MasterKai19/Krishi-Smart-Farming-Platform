<section class="officer-summary panel">
    <span class="eyebrow">YOUR OFFICER PROFILE</span>
    <span class="profile-avatar"><?=e(strtoupper(substr($me['name'], 0, 1)))?></span>
    <h2><?=e($me['name'])?></h2>
    <p><?=e($me['specialization'])?></p>
    <div class="profile-details">
        <span>Office<strong><?=e($me['office'])?></strong></span>
        <span>Phone<strong><?=e($me['phone'])?></strong></span>
        <span>Email<strong><?=e($me['email'])?></strong></span>
    </div>
    <small>These contact details are shared with farmers when you accept their appointments.</small>
</section>
