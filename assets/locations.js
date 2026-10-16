document.querySelectorAll('[data-location-form]').forEach(form => {
  const data = JSON.parse(form.querySelector('[data-location-data]').textContent);
  const division = form.querySelector('[data-division]');
  const district = form.querySelector('[data-district]');
  const thana = form.querySelector('[data-thana]');
  function populate(select, rows, parent, parentKey, prompt, selected) {
    select.replaceChildren(new Option(prompt, ''));
    rows.filter(row => row[parentKey] === parent).forEach(row => {
      select.add(new Option(row.name, row.code));
    });
    select.value = selected || '';
    if (!select.value) select.selectedIndex = 0;
    select.disabled = !parent;
  }
  function showThanas(selected = '') {
    populate(thana, data.thanas, district.value, 'district_code', 'Select thana / upazila', selected);
  }
  function showDistricts(selected = '', selectedThana = '') {
    populate(district, data.districts, division.value, 'division_code', 'Select district', selected);
    showThanas(selectedThana);
  }
  division.addEventListener('change', () => showDistricts());
  district.addEventListener('change', () => showThanas());
  showDistricts(district.dataset.selected, thana.dataset.selected);
});
