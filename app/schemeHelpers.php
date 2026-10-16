<?php
function submitted_land(string $key, bool $required = true): ?string {
    $raw = trim((string)($_POST[$key] ?? ''));
    if ($raw === '' && !$required) return null;
    if (!preg_match('/^(?:[0-9]{1,6})(?:\.[0-9]{1,2})?$/', $raw) || (float)$raw <= 0) {
        throw new RuntimeException('Enter a valid land area in katha.');
    }
    return number_format((float)$raw, 2, '.', '');
}
function scheme_fields(): array {
    $title = field('title', 160);
    $benefit = field('benefit', 255);
    $description = field('description', 3000);
    $limit = submitted_land('max_land_katha', false);
    $district = trim((string)($_POST['district_code'] ?? ''));
    if ($district !== '' && !query('SELECT code FROM districts WHERE code=?', [$district])->fetchColumn()) {
        throw new RuntimeException('Choose a valid district.');
    }
    $source = trim((string)($_POST['source_url'] ?? ''));
    if ($source !== '' && (strlen($source) > 500 || !filter_var($source, FILTER_VALIDATE_URL) || parse_url($source, PHP_URL_SCHEME) !== 'https')) {
        throw new RuntimeException('Use a valid HTTPS source link.');
    }
    $status = $_POST['status'] ?? '';
    if (!in_array($status, ['draft','active','closed'], true)) throw new RuntimeException('Choose a valid scheme status.');
    return [$title,$benefit,$description,$limit,$district ?: null,$source ?: null,$status];
}
