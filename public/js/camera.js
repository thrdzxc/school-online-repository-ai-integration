(() => {
  const open = document.getElementById('open-camera');
  if (!open) return;
  const dialog = document.getElementById('photo-camera');
  const video = document.getElementById('camera-video');
  const preview = document.getElementById('camera-preview');
  const capture = document.getElementById('capture-photo');
  const retake = document.getElementById('retake-photo');
  const use = document.getElementById('use-photo');
  const error = document.getElementById('camera-error');
  const status = document.getElementById('camera-status');
  const liveStatus = document.getElementById('camera-live-status');
  const setLiveStatus = message => { if (liveStatus) liveStatus.textContent = message; };
  const input = document.getElementById('profile_photo');
  let stream, blob, previewUrl;
  let generation = 0;
  function stop() {
    generation++;
    stream?.getTracks().forEach(track => track.stop());
    stream = null;
    video.srcObject = null;
  }
  function clearPhoto() {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null;
    blob = null;
    preview.removeAttribute('src');
  }
  async function start() {
    stop();
    clearPhoto();
    const attempt = generation;
    error.textContent = '';
    setLiveStatus('Starting camera. Allow access if your browser asks.');
    retake.textContent = 'Retake Photo';
    preview.hidden = retake.hidden = use.hidden = true;
    video.hidden = capture.hidden = false;
    capture.disabled = true;
    try {
      if (!navigator.mediaDevices?.getUserMedia) {
        throw new Error('Camera access requires HTTPS or localhost and a supported browser. You can still choose an image file.');
      }
      const media = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 960 } }, audio: false });
      if (!dialog.open || attempt !== generation) {
        media.getTracks().forEach(track => track.stop());
        return;
      }
      stream = media;
      video.srcObject = media;
      await video.play();
      if (attempt === generation && dialog.open) {
        capture.disabled = false;
        setLiveStatus('Camera ready. Select Capture Photo when you are ready.');
      }
    } catch (failure) {
      if (attempt !== generation || !dialog.open) return;
      stop();
      error.textContent = failure.name === 'NotAllowedError'
        ? 'Camera permission was denied. Allow camera access in your browser, or cancel and choose an image file.'
        : failure.name === 'NotFoundError' ? 'No camera was found. Cancel and choose an image file.'
        : failure.message || 'Unable to open the camera. Cancel and choose an image file.';
      setLiveStatus('Camera unavailable');
      retake.textContent = 'Try Again';
      retake.hidden = false;
    }
  }
  open.addEventListener('click', () => { dialog.showModal(); start(); });
  retake.addEventListener('click', start);
  capture.addEventListener('click', () => {
    if (!video.videoWidth || !video.videoHeight) return;
    error.textContent = '';
    setLiveStatus('Capturing photo...');
    capture.disabled = true;
    const canvas = document.createElement('canvas');
    // Capture the same centered crop displayed by the edge-to-edge preview.
    const frame = video.getBoundingClientRect();
    const frameRatio = frame.width && frame.height ? frame.width / frame.height : video.videoWidth / video.videoHeight;
    const sourceRatio = video.videoWidth / video.videoHeight;
    const sourceWidth = sourceRatio > frameRatio ? video.videoHeight * frameRatio : video.videoWidth;
    const sourceHeight = sourceRatio > frameRatio ? video.videoHeight : video.videoWidth / frameRatio;
    const scale = Math.min(1, 1280 / Math.max(sourceWidth, sourceHeight));
    canvas.width = Math.round(sourceWidth * scale);
    canvas.height = Math.round(sourceHeight * scale);
    canvas.getContext('2d').drawImage(video,
      (video.videoWidth - sourceWidth) / 2, (video.videoHeight - sourceHeight) / 2,
      sourceWidth, sourceHeight, 0, 0, canvas.width, canvas.height);
    const attempt = generation;
    canvas.toBlob(photo => {
      if (!dialog.open || attempt !== generation) return;
      if (!photo) {
        error.textContent = 'Could not capture this photo. Please try again.';
        capture.disabled = false;
        setLiveStatus('Camera ready. Try capturing your photo again.');
        return;
      }
      stop();
      blob = photo;
      previewUrl = URL.createObjectURL(photo);
      preview.src = previewUrl;
      video.hidden = capture.hidden = true;
      preview.hidden = retake.hidden = use.hidden = false;
      setLiveStatus('Review your photo. Choose Use This Photo or Retake Photo.');
      use.focus();
    }, 'image/jpeg', 0.9);
  });
  use.addEventListener('click', () => {
    if (!blob) return;
    try {
      const transfer = new DataTransfer();
      transfer.items.add(new File([blob], 'camera-photo.jpg', { type: 'image/jpeg', lastModified: Date.now() }));
      input.files = transfer.files;
      input.dispatchEvent(new Event('change', { bubbles: true }));
      const remove = input.form.querySelector('[name="remove_photo"]');
      if (remove) remove.checked = false;
      status.textContent = 'Photo confirmed. Choose Save Profile Photo to upload it.';
      dialog.close();
    } catch (_) {
      error.textContent = 'This browser cannot attach the camera photo. Cancel and choose an image file instead.';
    }
  });
  document.getElementById('cancel-camera').addEventListener('click', () => dialog.close());
  dialog.addEventListener('close', () => { stop(); clearPhoto(); open.focus(); });
  window.addEventListener('pagehide', stop);
})();
