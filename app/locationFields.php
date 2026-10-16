<?php
// Set $locationPrefix and optional $locationSelection before including this file.
$locationSelection = $locationSelection ?? [];
$divisions = query('SELECT code,name FROM divisions ORDER BY name')->fetchAll();
$districts = query('SELECT code,division_code,name FROM districts ORDER BY name')->fetchAll();
$thanas = query('SELECT code,district_code,name FROM thanas ORDER BY name')->fetchAll();
$selectedDivision = (string)($locationSelection['division_code'] ?? '');
$selectedDistrict = (string)($locationSelection['district_code'] ?? '');
$selectedThana = (string)($locationSelection['thana_code'] ?? '');
?>
<div class="location-fields" data-location-form>
    <label>Division
        <select name="division_code" required data-division data-selected="<?=e($selectedDivision)?>">
            <option value="">Select division</option>
            <?php foreach ($divisions as $place): ?>
                <option value="<?=e($place['code'])?>" <?=$selectedDivision===$place['code']?'selected':''?>><?=e($place['name'])?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>District
        <select name="district_code" required data-district data-selected="<?=e($selectedDistrict)?>"></select>
    </label>
    <label>Thana / Upazila
        <select name="thana_code" required data-thana data-selected="<?=e($selectedThana)?>"></select>
    </label>
    <label>Specific address
        <textarea name="detailed_address" rows="2" maxlength="255" required placeholder="Holding, road, village or area"><?=e($locationSelection['detailed_address'] ?? '')?></textarea>
    </label>
    <script type="application/json" data-location-data><?=json_encode(['districts'=>$districts,'thanas'=>$thanas], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?></script>
</div>
