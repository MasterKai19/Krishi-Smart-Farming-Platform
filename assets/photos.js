const photoInput = document.getElementById('problem-photo');
if (photoInput) {
  const preview = document.getElementById('photo-preview');
  const picture = document.getElementById('photo-preview-image');
  const caption = document.getElementById('photo-preview-caption');
  const error = document.getElementById('photo-error');
  let objectUrl;
  const clearPreview = () => {
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = undefined;
    picture.removeAttribute('src');
    preview.hidden = true;
    photoInput.setCustomValidity('');
    error.textContent = '';
  };
  photoInput.addEventListener('change', () => {
    clearPreview();
    const file = photoInput.files[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > 5 * 1024 * 1024) {
      error.textContent = 'Choose a JPG or PNG photo no larger than 5 MB.';
      photoInput.setCustomValidity(error.textContent);
      photoInput.reportValidity();
      return;
    }
    objectUrl = URL.createObjectURL(file);
    picture.src = objectUrl;
    caption.textContent = file.name + ' · ' + (file.size / 1024 / 1024).toFixed(2) + ' MB';
    preview.hidden = false;
  });
  document.getElementById('remove-photo').addEventListener('click', () => { photoInput.value = ''; clearPreview(); });
}
