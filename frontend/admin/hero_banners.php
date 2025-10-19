<?php
require_once __DIR__ . '/../../backend/init.php';
require_admin();

// Helpers
function respond_redirect($url){ header('Location: ' . $url); exit; }
function ensure_dir($p){ if (!is_dir($p)) { @mkdir($p, 0775, true); } return is_dir($p); }
function save_upload_img($field, $subdir){
  if (!isset($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'])) return null;
  $file = $_FILES[$field];
  if ($file['error'] !== UPLOAD_ERR_OK) return null;
  $finfo = @finfo_open(FILEINFO_MIME_TYPE);
  $mime = $finfo ? finfo_buffer($finfo, file_get_contents($file['tmp_name'])) : ($file['type'] ?? '');
  $allowed = ['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp','image/gif'=>'.gif'];
  if (!isset($allowed[$mime])) return null;
  $root = realpath(__DIR__ . '/../../');
  $dir = $root . '/uploads/' . trim($subdir,'/');
  if (!ensure_dir($dir)) return null;
  $name = bin2hex(random_bytes(8)) . $allowed[$mime];
  $target = $dir . '/' . $name;
  if (!move_uploaded_file($file['tmp_name'], $target)) return null;
  return '/APLX/uploads/' . trim($subdir,'/') . '/' . $name;
}

// CRUD for hero_banners
function banner_create($conn, $eyebrow,$title,$subtitle,$tagline,$c1t,$c1l,$c2t,$c2l,$imgUrl,$sort,$active){
  $stmt = $conn->prepare('INSERT INTO hero_banners(eyebrow,title,subtitle,tagline,cta1_text,cta1_link,cta2_text,cta2_link,image_url,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
  $stmt->bind_param('ssssssssssi', $eyebrow,$title,$subtitle,$tagline,$c1t,$c1l,$c2t,$c2l,$imgUrl,$sort,$active);
  $stmt->execute();
  $newId = $conn->insert_id;
  if ((int)$active === 1) {
    // Ensure single active banner
    $q = $conn->prepare('UPDATE hero_banners SET is_active=0 WHERE id<>?');
    $q->bind_param('i', $newId);
    $q->execute();
  }
  return $newId;
}

function banner_update($conn, $id, $eyebrow,$title,$subtitle,$tagline,$c1t,$c1l,$c2t,$c2l,$imgUrl,$sort,$active){
  $stmt = $conn->prepare('UPDATE hero_banners SET eyebrow=?, title=?, subtitle=?, tagline=?, cta1_text=?, cta1_link=?, cta2_text=?, cta2_link=?, image_url=?, sort_order=?, is_active=? WHERE id=?');
  $stmt->bind_param('ssssssssssii', $eyebrow,$title,$subtitle,$tagline,$c1t,$c1l,$c2t,$c2l,$imgUrl,$sort,$active,$id);
  $stmt->execute();
  if ((int)$active === 1) {
    // Ensure single active banner
    $q = $conn->prepare('UPDATE hero_banners SET is_active=0 WHERE id<>?');
    $q->bind_param('i', $id);
    $q->execute();
  }
}

function banner_delete($conn, $id){
  $stmt = $conn->prepare('DELETE FROM hero_banners WHERE id=?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
}

function banner_get($conn, $id){
  $stmt = $conn->prepare('SELECT * FROM hero_banners WHERE id=?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  return $stmt->get_result()->fetch_assoc();
}

function banner_list($conn){
  $items = [];
  $res = $conn->query('SELECT * FROM hero_banners ORDER BY is_active DESC, sort_order ASC, id ASC');
  while ($row = $res->fetch_assoc()) { $items[] = $row; }
  return $items;
}

// Routing state
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);
$msg = '';
$err = '';

// Handle POST actions (create/update/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $mode = $_POST['_mode'] ?? 'create';
  if ($mode === 'delete') {
    $delId = intval($_POST['id'] ?? 0);
    if ($delId > 0) {
      banner_delete($conn, $delId);
      $msg = 'Banner deleted';
    }
    respond_redirect('/APLX/frontend/admin/hero_banners.php?msg=' . urlencode($msg));
  }

  // Create or update
  $eyebrow = trim($_POST['eyebrow'] ?? '');
  $title = trim($_POST['title'] ?? '');
  $subtitle = trim($_POST['subtitle'] ?? '');
  $tagline = trim($_POST['tagline'] ?? '');
  $c1t = trim($_POST['cta1_text'] ?? '');
  $c1l = trim($_POST['cta1_link'] ?? '');
  $c2t = trim($_POST['cta2_text'] ?? '');
  $c2l = trim($_POST['cta2_link'] ?? '');
  $sort = intval($_POST['sort_order'] ?? 0);
  $active = isset($_POST['is_active']) ? 1 : 0;
  $imgUrl = save_upload_img('image_file', 'hero') ?: trim($_POST['image_url'] ?? '');

  if (!$title && !$subtitle) { $err = 'Title or Subtitle required'; }
  if (!$imgUrl) { $err = $err ? $err . '; image required' : 'Image required'; }

  if (!$err) {
    if ($mode === 'update') {
      $id = intval($_POST['id'] ?? 0);
      if ($id <= 0) { $err = 'Invalid ID'; }
      else {
        $cur = banner_get($conn, $id);
        $imgFinal = $imgUrl ?: ($cur['image_url'] ?? '');
        banner_update($conn, $id, $eyebrow,$title,$subtitle,$tagline,$c1t,$c1l,$c2t,$c2l,$imgFinal,$sort,$active);
        respond_redirect('/APLX/frontend/admin/hero_banners.php?msg=' . urlencode('Banner updated'));
      }
    } else {
      banner_create($conn, $eyebrow,$title,$subtitle,$tagline,$c1t,$c1l,$c2t,$c2l,$imgUrl,$sort,$active);
      respond_redirect('/APLX/frontend/admin/hero_banners.php?msg=' . urlencode('Banner created'));
    }
  }
}

// Load editing item if any
$edit = null;
if ($action === 'edit' && $id > 0) {
  $edit = banner_get($conn, $id);
}

// List items
$items = banner_list($conn);

// Pick preview: editing item > active banner > first item
$preview = $edit ?: null;
if (!$preview) {
  foreach ($items as $it) { if ((int)$it['is_active'] === 1) { $preview = $it; break; } }
}
if (!$preview && !empty($items)) { $preview = $items[0]; }

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Hero Banners</title>
  <link rel="stylesheet" href="/APLX/css/style.css">
  <style>
    .layout{min-height:100vh}
    .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0b1220;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between}
    .content{padding:16px;margin-left:260px}
    .table{width:100%;border-collapse:separate;border-spacing:0 6px}
    .table th,.table td{padding:10px;text-align:left}
    .row{display:grid;grid-template-columns:140px 1fr;gap:10px;margin-bottom:10px}
    .actions{display:flex;gap:8px}
    .muted{color:var(--muted)}
    .preview{width:100%;max-width:420px;border:1px solid var(--border);border-radius:10px}
    .admin-two-col{display:grid;grid-template-columns:1.2fr .8fr;gap:16px;align-items:start}
    @media (max-width: 900px){
      .content{margin-left:0;padding:12px}
      .admin-two-col{grid-template-columns:1fr}
      .row{grid-template-columns:1fr}
    }
    .hero-prev{position:relative;height:260px;border-radius:14px;border:1px solid var(--border);overflow:hidden;background:#0b1220;display:grid;place-items:center}
    .hero-prev .bg{position:absolute;inset:0;background-size:cover;background-position:center;filter:brightness(.7);opacity:.9}
    .hero-prev .overlay{position:relative;z-index:1;text-align:center;color:#fff;padding:12px}
    .hero-prev .eyebrow{font-size:12px;opacity:.9}
    .hero-prev .title{font-size:22px;font-weight:800;line-height:1.2}
    .hero-prev .subtitle{font-size:18px;font-weight:800;margin-top:4px}
    .hero-prev .tag{font-size:13px;opacity:.85;margin-top:6px}
    .hero-prev .ctas{display:flex;gap:8px;justify-content:center;margin-top:10px;flex-wrap:wrap}
    .hero-prev .btn{padding:8px 12px;border-radius:10px;border:1px solid var(--border);background:#2563eb;color:#fff;text-decoration:none}
    .hero-prev .btn.second{background:#0b1220;color:#cbd5e1}
    details.adv{border:1px solid var(--border);border-radius:10px;padding:10px;margin-top:10px}
    details.adv summary{cursor:pointer;font-weight:600;list-style:none}
    details.adv[open]{background:rgba(255,255,255,.02)}
  </style>
</head>
<body>
<div class="layout">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <main class="content">
    <?php include __DIR__ . '/topbar.php'; ?>

    <section class="card">
      <h2>Hero Banners</h2>
      <?php if (isset($_GET['msg'])): ?><div class="notice" role="status" aria-live="polite"><?php echo h($_GET['msg']); ?></div><?php endif; ?>

      <div class="admin-two-col">
        <div>
          <h3 class="muted">All Banners</h3>
          <div role="table" aria-label="Hero banners list">
            <div class="table-head" role="rowgroup">
              <div role="row" class="row" style="grid-template-columns:60px 1fr 80px 120px;">
                <div role="columnheader">Img</div>
                <div role="columnheader">Title</div>
                <div role="columnheader">Order</div>
                <div role="columnheader">Actions</div>
              </div>
            </div>
            <div class="table-body" role="rowgroup">
              <?php foreach ($items as $it): ?>
                <div role="row" class="row" style="grid-template-columns:60px 1fr 80px 120px; align-items:center;">
                  <div role="cell"><img src="<?php echo h($it['image_url']); ?>" alt="" style="width:56px;height:38px;object-fit:cover;border-radius:6px;border:1px solid var(--border)"></div>
                  <div role="cell">
                    <div><strong><?php echo h($it['title'] ?: $it['subtitle']); ?></strong> <?php if(!$it['is_active']): ?><span class="muted">(inactive)</span><?php endif; ?></div>
                    <div class="muted" style="font-size:12px;">ID: <?php echo (int)$it['id']; ?> · Eyebrow: <?php echo h($it['eyebrow']); ?></div>
                  </div>
                  <div role="cell"><?php echo (int)$it['sort_order']; ?></div>
                  <div role="cell" class="actions">
                    <a class="btn btn-secondary" href="/APLX/frontend/admin/hero_banners.php?action=edit&id=<?php echo (int)$it['id']; ?>">Edit</a>
                    <form method="post" onsubmit="return confirm('Delete this banner?');">
                      <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
                      <input type="hidden" name="_mode" value="delete">
                      <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                      <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div>
          <h3 class="muted">Live Preview</h3>
          <?php $pv = $preview ?? null; $bg = $pv['image_url'] ?? ''; $ey = $pv['eyebrow'] ?? ''; $ti = $pv['title'] ?? ''; $su = $pv['subtitle'] ?? ''; $ta = $pv['tagline'] ?? ''; $c1t = $pv['cta1_text'] ?? 'Get Started'; $c1l = $pv['cta1_link'] ?? '#'; $c2t = $pv['cta2_text'] ?? 'Learn More'; $c2l = $pv['cta2_link'] ?? '#'; ?>
          <div class="hero-prev" aria-label="Hero banner preview">
            <div class="bg" style="background-image:url('<?php echo h($bg ?: "https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?q=80&w=1600&auto=format&fit=crop"); ?>')"></div>
            <div class="overlay">
              <div class="eyebrow"><?php echo h($ey ?: 'Safe Transportation & Logistics'); ?></div>
              <div class="title"><?php echo h($ti ?: 'Adaptable coordinated factors'); ?></div>
              <div class="subtitle"><?php echo h($su ?: 'Quick Conveyance'); ?></div>
              <div class="tag"><?php echo h($ta ?: 'Reliable logistics solutions for every shipment.'); ?></div>
              <div class="ctas">
                <a class="btn" href="#" onclick="return false;"><?php echo h($c1t ?: 'Get Started'); ?></a>
                <a class="btn second" href="#" onclick="return false;"><?php echo h($c2t ?: 'Learn More'); ?></a>
              </div>
            </div>
          </div>

          <h3 class="muted" style="margin-top:14px"><?php echo $edit ? 'Edit Banner' : 'Add Banner'; ?></h3>
          <form method="post" enctype="multipart/form-data" aria-label="<?php echo $edit ? 'Edit banner form' : 'Add banner form'; ?>">
            <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
            <?php if ($edit): ?><input type="hidden" name="_mode" value="update"><input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>"><?php else: ?><input type="hidden" name="_mode" value="create"><?php endif; ?>

            <label class="row"><span>Title</span><input type="text" name="title" value="<?php echo h($edit['title'] ?? ''); ?>" aria-label="Title"></label>
            <label class="row"><span>Subtitle</span><input type="text" name="subtitle" value="<?php echo h($edit['subtitle'] ?? ''); ?>" aria-label="Subtitle"></label>

            <label class="row"><span>Image URL</span><input type="text" name="image_url" value="<?php echo h($edit['image_url'] ?? ''); ?>" aria-label="Image URL"></label>
            <label class="row"><span>Upload Image</span><input type="file" name="image_file" accept="image/*" aria-label="Upload image"></label>
            <label class="row"><span>Active</span><input type="checkbox" name="is_active" value="1" <?php echo !isset($edit['is_active']) || (int)$edit['is_active'] ? 'checked' : ''; ?> aria-label="Is active"></label>

            <details class="adv">
              <summary>Advanced options</summary>
              <div class="row"><span>Eyebrow</span><input type="text" name="eyebrow" value="<?php echo h($edit['eyebrow'] ?? ''); ?>" aria-label="Eyebrow"></div>
              <div class="row"><span>Tagline</span><input type="text" name="tagline" value="<?php echo h($edit['tagline'] ?? ''); ?>" aria-label="Tagline"></div>
              <div class="row"><span>CTA1 Text</span><input type="text" name="cta1_text" value="<?php echo h($edit['cta1_text'] ?? ''); ?>" aria-label="Primary button text"></div>
              <div class="row"><span>CTA1 Link</span><input type="text" name="cta1_link" value="<?php echo h($edit['cta1_link'] ?? ''); ?>" aria-label="Primary button link"></div>
              <div class="row"><span>CTA2 Text</span><input type="text" name="cta2_text" value="<?php echo h($edit['cta2_text'] ?? ''); ?>" aria-label="Secondary button text"></div>
              <div class="row"><span>CTA2 Link</span><input type="text" name="cta2_link" value="<?php echo h($edit['cta2_link'] ?? ''); ?>" aria-label="Secondary button link"></div>
              <div class="row"><span>Sort Order</span><input type="number" name="sort_order" value="<?php echo isset($edit['sort_order']) ? (int)$edit['sort_order'] : 0; ?>" aria-label="Sort order"></div>
            </details>

            <div class="actions" style="margin-top:10px;justify-content:flex-end">
              <a class="btn btn-secondary" href="/APLX/frontend/admin/hero_banners.php">Cancel</a>
              <button class="btn" type="submit"><?php echo $edit ? 'Update' : 'Create'; ?></button>
            </div>
          </form>
          
        </div>
      </div>
    </section>
  </main>
</div>
</body>
</html>
