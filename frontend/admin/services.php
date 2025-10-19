<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Services</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .services-admin{ display:grid;grid-template-columns:2fr 1fr;gap:16px }
    @media (max-width: 1000px){ .services-admin{ grid-template-columns:1fr; } }
    .list table{ width:100%; border-collapse: collapse; }
    .list tr:hover{ background:#0b1220; }
    .actions button{ margin-right:6px; }
    .muted{ color:var(--muted); }
    .preview-icon{ font-size:20px; }
    .image-thumb{ width:64px; height:40px; object-fit:cover; border-radius:6px; border:1px solid var(--border); }
    .image-preview{ width:100%; max-width:220px; height:120px; object-fit:cover; border-radius:10px; border:1px solid var(--border); display:none; margin-bottom:8px }
    .page-actions{ text-align:right; margin:8px 0 12px; }
    .page-actions a{ display:inline-block; margin-left:8px; }
    /* Dark theme inputs */
    .card input[type="text"], .card input[type="number"], .card input[type="email"], .card input[type="tel"], .card input[type="file"], .card select{
      background:#0b1220; border:1px solid var(--border); color:var(--text); border-radius:8px; padding:10px; width:100%;
    }
    .card textarea{ resize:vertical; }
    .card input::placeholder, .card textarea::placeholder{ color:var(--muted); }
  </style>
</head>
<body>
<div class="layout">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <main class="content">
    <?php include __DIR__ . '/topbar.php'; ?>
    <div class="page-actions">
      <a class="btn btn-outline" href="/APLX/frontend/admin/settings.php" title="Back to Settings">← Back to Settings</a>
      <button id="seedBtn" class="btn" type="button" title="Insert 4 sample services">Insert Sample Services</button>
    </div>

    <section class="card">
      <h2 id="pageTitle">Services</h2>
      <div class="services-admin">
        <div class="list">
          <table aria-label="Services list">
            <thead>
              <tr>
                <th>#</th>
                <th>Icon</th>
                <th>Image</th>
                <th>Title</th>
                <th>Description</th>
                <th>Order</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="svcRows">
              <tr><td colspan="7" class="muted">Loading...</td></tr>
            </tbody>
          </table>
        </div>
        <div>
          <form id="svcForm" class="stack" method="post" action="/APLX/backend/router.php?route=admin/services" enctype="multipart/form-data">
            <input type="hidden" name="csrf" id="csrfField" value="">
            <input type="hidden" name="_method" id="methodField" value="POST">
            <input type="hidden" name="id" id="idField" value="">
            <img id="currentImg" class="image-preview" alt="Current image preview" />
            <label>Icon (upload)
              <input type="file" name="image_file" id="fileField" accept="image/*">
            </label>
            <label>Or Image URL (paste)
              <input type="text" name="image_url" id="imageUrlField" placeholder="https://... or /APLX/frontend/images/...">
            </label>
            <div id="pasteZone" style="border:1px dashed var(--border);border-radius:8px;padding:10px;color:var(--muted);text-align:center">Paste image here (Ctrl+V) or drop image</div>
            <label>Title
              <input name="title" id="titleField" required>
            </label>
            <label>Description
              <input name="description" id="descField" placeholder="Short description shown on the homepage cards" required>
            </label>
            <label>Sort Order
              <input name="sort_order" id="orderField" type="number" value="0">
            </label>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
              <button class="btn" type="submit">Save</button>
              <button class="btn btn-outline" id="resetBtn" type="button">Reset</button>
            </div>
            <small class="muted">Tip: Browse and upload an icon image for the card. Title and Description are short texts; lower sort order appears earlier.</small>
            <div id="formStatus" class="muted" aria-live="polite"></div>
          </form>
        </div>
      </div>
    </section>
  </main>
</div>
<script src="/APLX/js/admin.js"></script>
<script>
(async function(){
  const rows = document.getElementById('svcRows');
  const form = document.getElementById('svcForm');
  const statusEl = document.getElementById('formStatus');
  const csrfField = document.getElementById('csrfField');
  const methodField = document.getElementById('methodField');
  const idField = document.getElementById('idField');
  const titleField = document.getElementById('titleField');
  const descField = document.getElementById('descField');
  const orderField = document.getElementById('orderField');
  const resetBtn = document.getElementById('resetBtn');
  const fileField = document.getElementById('fileField');
  const seedBtn = document.getElementById('seedBtn');
  const imageUrlField = document.getElementById('imageUrlField');
  const pasteZone = document.getElementById('pasteZone');
  let pasteBlob = null; // holds pasted/dropped image

  async function getCSRF(){
    try{
      const res = await fetch('/APLX/backend/router.php?route=admin/services&action=csrf', { cache:'no-store' });
      if (res.ok) { const d = await res.json(); csrfField.value = d.csrf || ''; }
    }catch(_){}}
  await getCSRF();

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }

  async function load(){
    rows.innerHTML = '<tr><td colspan="7" class="muted">Loading...</td></tr>';
    let data = { items: [] };
    try{
      const res = await fetch('/APLX/backend/router.php?route=admin/services', { cache:'no-store' });
      if (!res.ok) throw new Error('HTTP '+res.status);
      data = await res.json();
    }catch(e){
      try{
        const res2 = await fetch('/APLX/backend/services_list.php', { cache:'no-store' });
        if (!res2.ok) throw new Error('HTTP '+res2.status);
        data = await res2.json();
      }catch(e2){
        rows.innerHTML = `<tr><td colspan="7" class="muted">Failed to load services (admin/public). ${String(e2)}</td></tr>`;
        statusEl.textContent = 'Load failed';
        return;
      }
    }
    const items = Array.isArray(data.items) ? data.items : [];
    if (!items.length){ rows.innerHTML = '<tr><td colspan="7" class="muted">No services. Use the form to add.</td></tr>'; return; }
    rows.innerHTML = items.map((it,i)=>{
      const icon = it.icon ? `<span class="preview-icon">${escapeHtml(it.icon)}</span>` : '';
      const img = it.image_url ? `<img class="image-thumb" src="${escapeHtml(it.image_url)}" alt="">` : '';
      return `<tr data-id="${it.id}" data-title="${escapeHtml(it.title)}" data-desc="${escapeHtml(it.description)}" data-order="${it.sort_order|0}" data-img="${escapeHtml(it.image_url||'')}">
        <td>${i+1}</td>
        <td>${icon}</td>
        <td>${img}</td>
        <td>${escapeHtml(it.title)}</td>
        <td>${escapeHtml(it.description)}</td>
        <td>${it.sort_order|0}</td>
        <td class="actions">
          <button class="btn btn-small" data-act="edit" data-id="${it.id}">Edit</button>
          <button class="btn btn-small btn-danger" data-act="del" data-id="${it.id}">Delete</button>
        </td>
      </tr>`;
    }).join('');

    // Auto-fill the form with the first item for quick editing
    const firstEdit = rows.querySelector('button[data-act="edit"]');
    if (firstEdit) {
      // Populate without scrolling
      const tr = firstEdit.closest('tr');
      idField.value = firstEdit.getAttribute('data-id');
      methodField.value = 'PATCH';
      titleField.value = (tr?.children[3]?.textContent || '').trim();
      descField.value = (tr?.children[4]?.textContent || '').trim();
      orderField.value = parseInt((tr?.children[5]?.textContent || '0').trim(), 10) || 0;
      const imgUrl = tr?.getAttribute('data-img') || '';
      const imgEl = document.getElementById('currentImg');
      if (imgEl && imgUrl){ imgEl.src = imgUrl; imgEl.style.display='block'; } else if (imgEl){ imgEl.style.display='none'; }
      statusEl.textContent = 'Loaded first service into form';
    }
  }

  function resetForm(){
    methodField.value = 'POST';
    idField.value=''; titleField.value=''; descField.value=''; orderField.value='0';
    if (fileField) fileField.value = '';
    statusEl.textContent='';
  }
  resetBtn.addEventListener('click', resetForm);

  // Seed defaults
  seedBtn?.addEventListener('click', async ()=>{
    try{
      statusEl.textContent = 'Seeding sample services...';
      const fd = new FormData();
      fd.append('csrf', csrfField.value);
      const res = await fetch('/APLX/backend/router.php?route=admin/services&action=seed', { method:'POST', body: fd });
      if (res.ok) { await load(); statusEl.textContent = 'Sample services inserted'; }
      else { statusEl.textContent = 'Seed failed'; }
    }catch(_){ statusEl.textContent = 'Seed failed'; }
  });

  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('button[data-act]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    if (btn.dataset.act === 'edit'){
      const tr = btn.closest('tr');
      idField.value = id;
      methodField.value = 'PATCH';
      titleField.value = tr.children[3].textContent.trim();
      descField.value = tr.children[4].textContent.trim();
      orderField.value = parseInt(tr.children[5].textContent.trim()||'0',10);
      const imgUrl = tr.getAttribute('data-img') || '';
      const imgEl = document.getElementById('currentImg');
      if (imgEl && imgUrl){ imgEl.src = imgUrl; imgEl.style.display='block'; } else if (imgEl){ imgEl.style.display='none'; }
      window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 20, behavior:'smooth' });
    } else if (btn.dataset.act === 'del'){
      if (!confirm('Delete this service?')) return;
      const fd = new FormData();
      fd.append('csrf', csrfField.value);
      fd.append('_method', 'DELETE');
      const res = await fetch(`/APLX/backend/router.php?route=admin/services&id=${encodeURIComponent(id)}`, { method:'POST', body: fd });
      if (res.ok){ await load(); statusEl.textContent = 'Deleted'; } else { statusEl.textContent = 'Delete failed'; }
    }
  });

  // Clicking on a row (outside action buttons) populates the form for that item
  rows.addEventListener('click', (e)=>{
    const tr = e.target.closest('tr');
    if (!tr || e.target.closest('td.actions')) return; // ignore clicks in actions cell
    const id = tr.getAttribute('data-id');
    if (!id) return;
    idField.value = id;
    methodField.value = 'PATCH';
    titleField.value = tr.children[3].textContent.trim();
    descField.value = tr.children[4].textContent.trim();
    orderField.value = parseInt(tr.children[5].textContent.trim()||'0',10);
    const imgUrl = tr.getAttribute('data-img') || '';
    const imgEl = document.getElementById('currentImg');
    if (imgEl && imgUrl){ imgEl.src = imgUrl; imgEl.style.display='block'; } else if (imgEl){ imgEl.style.display='none'; }
    statusEl.textContent = 'Loaded service into form';
  });

  // Preview newly selected file in the form
  fileField.addEventListener('change', ()=>{
    const imgEl = document.getElementById('currentImg');
    if (!imgEl) return;
    if (fileField.files && fileField.files[0]){
      const url = URL.createObjectURL(fileField.files[0]);
      imgEl.src = url; imgEl.style.display='block';
      pasteBlob = null;
      imageUrlField.value = '';
    }
  });

  // Preview when typing/pasting an image URL
  imageUrlField.addEventListener('input', ()=>{
    const v = imageUrlField.value.trim();
    const imgEl = document.getElementById('currentImg');
    if (!imgEl) return;
    if (v){ imgEl.src = v; imgEl.style.display='block'; pasteBlob = null; if (fileField) fileField.value=''; }
  });

  // Helpers to handle paste/drop of images
  function handleFiles(files){
    if (!files || !files.length) return;
    const f = files[0];
    if (!f.type.startsWith('image/')) return;
    pasteBlob = f;
    const imgEl = document.getElementById('currentImg');
    if (imgEl){ imgEl.src = URL.createObjectURL(f); imgEl.style.display='block'; }
    if (fileField) fileField.value='';
    if (imageUrlField) imageUrlField.value='';
  }

  // Paste support
  pasteZone.addEventListener('paste', (e)=>{
    const items = e.clipboardData?.items || [];
    for (const it of items){
      if (it.kind === 'file' && it.type.startsWith('image/')){
        const blob = it.getAsFile();
        handleFiles([blob]);
        e.preventDefault();
        break;
      }
    }
  });

  // Drag & drop support
  ;['dragenter','dragover'].forEach(ev=>pasteZone.addEventListener(ev, (e)=>{ e.preventDefault(); pasteZone.style.background='rgba(255,255,255,.03)'; }));
  ;['dragleave','drop'].forEach(ev=>pasteZone.addEventListener(ev, (e)=>{ e.preventDefault(); pasteZone.style.background='transparent'; }));
  pasteZone.addEventListener('drop', (e)=>{
    const files = e.dataTransfer?.files;
    handleFiles(files);
  });

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    statusEl.textContent = 'Saving...';
    const fd = new FormData(form);
    // If no file chosen but we have a pasted image blob, send it as image_file
    if ((!fileField.files || fileField.files.length === 0) && pasteBlob){
      const name = 'pasted-' + Date.now() + '.png';
      fd.append('image_file', pasteBlob, name);
    }
    const res = await fetch(form.action + (methodField.value==='PATCH'?`?id=${encodeURIComponent(idField.value)}`:''), { method:'POST', body: fd });
    if (res.ok){ await load(); statusEl.textContent = 'Saved'; resetForm(); await getCSRF(); } else { statusEl.textContent = 'Save failed'; }
  });

  load();
})();
</script>
</body>
</html>
