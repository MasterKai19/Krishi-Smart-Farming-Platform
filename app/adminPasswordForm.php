<section class="panel admin-password">
    <details>
        <summary>Change admin password</summary>
        <form method="post" class="stack">
            <?=csrf()?><input type="hidden" name="action" value="change_admin_password">
            <label>Current password<input type="password" name="current_password" required autocomplete="current-password"></label>
            <label>New password<input type="password" name="new_password" minlength="8" maxlength="72" required autocomplete="new-password"></label>
            <button>Update password</button>
        </form>
    </details>
</section>
