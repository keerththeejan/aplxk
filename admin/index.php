<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/functions.php';

// --- Simple Auth ---
if (!defined('ADMIN_USER')) { define('ADMIN_USER', 'admin'); }
if (!defined('ADMIN_PASS')) { define('ADMIN_PASS', 'admin123'); }

$action = $_GET['action'] ?? 'list';
$entity = $_GET['entity'] ?? null;

if (isset($_POST['login_submit'])) {
  if (($_POST['username'] ?? '') === ADMIN_USER && ($_POST['password'] ?? '') === ADMIN_PASS) {
    $_SESSION['is_admin'] = true;
    header('Location: index.php');
    exit;
  } else {
    $login_error = 'Invalid credentials';
  }
}

if (!($_SESSION['is_admin'] ?? false)) {
  // Login screen
  echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
  echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">';
  echo '<title>Admin Login</title></head><body class="bg-light">';
  echo '<div class="container py-5" style="max-width:520px">';
  echo '<div class="card shadow-sm"><div class="card-body">';
  echo '<h1 class="h4 mb-3">Admin Login</h1>';
  if (!empty($login_error)) echo '<div class="alert alert-danger">'.htmlspecialchars($login_error).'</div>';
  echo '<form method="post">';
  echo '<div class="mb-3"><label class="form-label">Username</label><input class="form-control" type="text" name="username" required></div>';
  echo '<div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div>';
  echo '<button class="btn btn-primary" name="login_submit" type="submit">Login</button>';
  echo '</form></div></div></div></body></html>';
  exit;
}

if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: index.php');
  exit;
}

// --- Supported Entities and their fields ---
$entities = [
  'services' => [
    'title' => 'text',
    'summary' => 'textarea',
    'icon_class' => 'text',
    'sort_order' => 'number',
  ],
  'portfolios' => [
    'title' => 'text',
    'image_url' => 'text',
    'category' => 'text',
  ],
  'pricing_plans' => [
    'name' => 'text',
    'price' => 'number',
    'period' => 'text',
    'features' => 'textarea',
    'is_featured' => 'checkbox',
  ],
  'brands' => [
    'name' => 'text',
    'logo_url' => 'text',
    'sort_order' => 'number',
  ],
  'testimonials' => [
    'author_name' => 'text',
    'author_role' => 'text',
    'content' => 'textarea',
    'avatar_url' => 'text',
  ],
  'posts' => [
    'title' => 'text',
    'excerpt' => 'textarea',
    'image_url' => 'text',
    'published_at' => 'datetime',
  ],
  'call_requests' => [
    'name' => 'text',
    'phone' => 'text',
    'message' => 'textarea',
  ],
  'subscriptions' => [
    'email' => 'text',
  ],
];

$extraPages = ['settings'];

$pdo = get_pdo();

function admin_header($title = 'Admin') {
  echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
  echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">';
  echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">';
  echo '<title>'.htmlspecialchars($title).'</title></head><body>';
  echo '<nav class="navbar navbar-expand-lg navbar-dark bg-dark"><div class="container">';
  echo '<a class="navbar-brand" href="index.php">Admin Panel</a>';
  echo '<div class="collapse navbar-collapse"><ul class="navbar-nav me-auto">';
  foreach (['services','portfolios','pricing_plans','brands','testimonials','posts','call_requests','subscriptions'] as $e) {
    echo '<li class="nav-item"><a class="nav-link" href="?entity='.$e.'">'.ucwords(str_replace('_',' ',$e)).'</a></li>';
  }
  echo '<li class="nav-item"><a class="nav-link" href="?entity=settings">Settings</a></li>';
  echo '</ul><a class="btn btn-sm btn-outline-light" href="?logout=1">Logout</a></div></div></nav>';
}
function admin_footer(){ echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>'; }

function sanitize_entity_row(array $fields, array $src): array {
  $row = [];
  foreach ($fields as $name => $type) {
    if ($type === 'checkbox') {
      $row[$name] = isset($src[$name]) ? 1 : 0;
    } else {
      $row[$name] = trim((string)($src[$name] ?? ''));
    }
  }
  return $row;
}

if (!$entity) {
  admin_header('Admin Dashboard');
  echo '<div class="container py-4">';
  echo '<div class="row g-3">';
  foreach ($entities as $key => $_) {
    echo '<div class="col-12 col-md-6 col-lg-3"><a class="text-decoration-none" href="?entity='.$key.'">';
    echo '<div class="card h-100 shadow-sm"><div class="card-body"><div class="h5 mb-1">'.ucwords(str_replace('_',' ',$key)).'</div><div class="text-secondary">Manage</div></div></div>';
    echo '</a></div>';
  }
  echo '<div class="col-12 col-md-6 col-lg-3"><a class="text-decoration-none" href="?entity=settings">';
  echo '<div class="card h-100 shadow-sm"><div class="card-body"><div class="h5 mb-1">Settings</div><div class="text-secondary">Logo & Site</div></div></div>';
  echo '</a></div>';
  echo '</div></div>';
  admin_footer();
  exit;
}

// SETTINGS PAGE (not a table entity)
if ($entity === 'settings') {
  admin_header('Settings');
  $logoUrl = getSetting('site_logo_url', '');
  $logoDarkUrl = getSetting('site_logo_dark_url', '');
  $faviconUrl = getSetting('site_favicon_url', '');
  // handle post
  if (!empty($_POST['save_settings'])) {
    $messages = [];
    // helper closure for uploads
    $handleUpload = function(string $field, string $prefix) use (&$messages) {
      if (empty($_FILES[$field]['name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) return null;
      $allowed = ['png','jpg','jpeg','webp','gif','svg'];
      $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
      if (in_array($ext, $allowed, true)) {
        $targetDir = realpath(__DIR__.'/../assets/images');
        if ($targetDir === false) { $targetDir = __DIR__.'/../assets/images'; }
        @mkdir($targetDir.'/uploads', 0777, true);
        $fname = $prefix.'_'.date('Ymd_His').'.'.$ext;
        $dest = $targetDir.'/uploads/'.$fname;
        if (move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
          return 'assets/images/uploads/'.$fname;
        } else {
          $messages[] = ['type'=>'danger','text'=>'Failed to move uploaded file for '.htmlspecialchars($field).'.'];
        }
      } else {
        $messages[] = ['type'=>'warning','text'=>'Unsupported file type for '.htmlspecialchars($field).'.'];
      }
      return null;
    };

    // Uploads
    if ($rel = $handleUpload('site_logo', 'logo')) { setSetting('site_logo_url', $rel); $logoUrl = $rel; $messages[] = ['type'=>'success','text'=>'Logo uploaded successfully.']; }
    if ($rel = $handleUpload('site_logo_dark', 'logo_dark')) { setSetting('site_logo_dark_url', $rel); $logoDarkUrl = $rel; $messages[] = ['type'=>'success','text'=>'Dark logo uploaded.']; }
    if ($rel = $handleUpload('site_favicon', 'favicon')) { setSetting('site_favicon_url', $rel); $faviconUrl = $rel; $messages[] = ['type'=>'success','text'=>'Favicon uploaded.']; }
    if (isset($_POST['remove_logo'])) {
      setSetting('site_logo_url', null);
      $logoUrl = '';
      $messages[] = ['type'=>'warning','text'=>'Logo removed.'];
    }
    if (isset($_POST['remove_logo_dark'])) {
      setSetting('site_logo_dark_url', null);
      $logoDarkUrl = '';
      $messages[] = ['type'=>'warning','text'=>'Dark logo removed.'];
    }
    if (isset($_POST['remove_favicon'])) {
      setSetting('site_favicon_url', null);
      $faviconUrl = '';
      $messages[] = ['type'=>'warning','text'=>'Favicon removed.'];
    }
    echo '<div class="container pt-3">';
    foreach ($messages as $m) echo '<div class="alert alert-'.$m['type'].'">'.$m['text'].'</div>';
    echo '</div>';
  }

  echo '<div class="container py-4">';
  echo '<h1 class="h4 mb-3">Site Settings</h1>';
  echo '<div class="card"><div class="card-body">';
  echo '<form method="post" enctype="multipart/form-data">';
  // light logo
  echo '<div class="mb-3">';
  echo '<label class="form-label">Site Logo (light background)</label>';
  if ($logoUrl) { echo '<div class="mb-2"><img src="../'.htmlspecialchars($logoUrl).'" alt="logo" style="height:48px"></div>'; }
  echo '<input class="form-control" type="file" name="site_logo" accept="image/*">';
  if ($logoUrl) echo '<div class="form-text">Current: '.htmlspecialchars($logoUrl).'</div>';
  echo '</div>';
  // dark logo
  echo '<div class="mb-3">';
  echo '<label class="form-label">Site Logo (dark background)</label>';
  if ($logoDarkUrl) { echo '<div class="mb-2" style="background:#0b2a3a;padding:8px;border-radius:6px"><img src="../'.htmlspecialchars($logoDarkUrl).'" alt="logo dark" style="height:48px"></div>'; }
  echo '<input class="form-control" type="file" name="site_logo_dark" accept="image/*">';
  if ($logoDarkUrl) echo '<div class="form-text">Current: '.htmlspecialchars($logoDarkUrl).'</div>';
  echo '</div>';
  // favicon
  echo '<div class="mb-3">';
  echo '<label class="form-label">Favicon</label>';
  if ($faviconUrl) { echo '<div class="mb-2"><img src="../'.htmlspecialchars($faviconUrl).'" alt="favicon" style="height:24px"></div>'; }
  echo '<input class="form-control" type="file" name="site_favicon" accept="image/*,.ico">';
  if ($faviconUrl) echo '<div class="form-text">Current: '.htmlspecialchars($faviconUrl).'</div>';
  echo '</div>';
  echo '<button class="btn btn-primary" type="submit" name="save_settings" value="1">Save Settings</button> ';
  if ($logoUrl) echo '<button class="btn btn-outline-danger" name="remove_logo" value="1">Remove Logo</button> ';
  if ($logoDarkUrl) echo '<button class="btn btn-outline-danger" name="remove_logo_dark" value="1">Remove Dark Logo</button> ';
  if ($faviconUrl) echo '<button class="btn btn-outline-danger" name="remove_favicon" value="1">Remove Favicon</button>';
  echo '</form>';
  echo '</div></div></div>';
  admin_footer();
  exit;
}

if (!array_key_exists($entity, $entities)) {
  admin_header('Not Found');
  echo '<div class="container py-5"><div class="alert alert-danger">Unknown entity.</div></div>';
  admin_footer();
  exit;
}

$fields = $entities[$entity];

// Handle create/update/delete
if (isset($_POST['save_item'])) {
  $row = sanitize_entity_row($fields, $_POST);
  $id = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int)$_POST['id'] : null;
  if ($id) {
    // update
    $sets = [];$params=[];
    foreach ($row as $k=>$v){ $sets[] = "$k = :$k"; $params[":$k"] = $v; }
    $params[':id'] = $id;
    $sql = 'UPDATE '.$entity.' SET '.implode(',', $sets).' WHERE id = :id';
    $ok = $pdo->prepare($sql)->execute($params);
    header('Location: index.php?entity='.urlencode($entity).'&saved=' . ($ok?1:0));
    exit;
  } else {
    // insert
    $cols = array_keys($row);
    $place = array_map(fn($c)=>":$c", $cols);
    $sql = 'INSERT INTO '.$entity.'('.implode(',', $cols).') VALUES('.implode(',', $place).')';
    $ok = $pdo->prepare($sql)->execute(array_combine($place, array_values($row)));
    header('Location: index.php?entity='.urlencode($entity).'&created=' . ($ok?1:0));
    exit;
  }
}

if (isset($_POST['delete_item']) && isset($_POST['id']) && ctype_digit($_POST['id'])) {
  $id = (int)$_POST['id'];
  $ok = $pdo->prepare('DELETE FROM '.$entity.' WHERE id = :id')->execute([':id'=>$id]);
  header('Location: index.php?entity='.urlencode($entity).'&deleted=' . ($ok?1:0));
  exit;
}

admin_header('Manage '.ucwords(str_replace('_',' ',$entity)));

echo '<div class="container py-4">';
// Alerts
foreach (['created'=>'success','saved'=>'success','deleted'=>'warning'] as $k=>$cls){ if (isset($_GET[$k])) echo '<div class="alert alert-'.$cls.'">Action successful.</div>'; }

// List
echo '<div class="d-flex justify-content-between align-items-center mb-3">';
echo '<h1 class="h4 mb-0">'.ucwords(str_replace('_',' ',$entity)).'</h1>';
echo '<button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#formWrap">Add New</button>';
echo '</div>';

// Form (create/update)
$editData = null;
if ($action === 'edit' && isset($_GET['id']) && ctype_digit($_GET['id'])) {
  $stmt = $pdo->prepare('SELECT * FROM '.$entity.' WHERE id = :id');
  $stmt->execute([':id'=>(int)$_GET['id']]);
  $editData = $stmt->fetch() ?: null;
}

echo '<div class="collapse'.($editData?' show':''). '" id="formWrap">';
$btnLabel = $editData ? 'Save Changes' : 'Create';
$hiddenId = $editData ? '<input type="hidden" name="id" value="'.(int)$editData['id'].'">' : '';

echo '<div class="card mb-4"><div class="card-body">';
if ($entity === 'posts' && !$editData) { $_POST['published_at'] = date('Y-m-d H:i:s'); }

echo '<form method="post">'.$hiddenId;
foreach ($fields as $name=>$type) {
  $label = ucwords(str_replace('_',' ',$name));
  $value = $editData[$name] ?? ($_POST[$name] ?? ($type==='checkbox'?0:''));
  echo '<div class="mb-3">';
  echo '<label class="form-label">'.$label.'</label>';
  if ($type === 'textarea') {
    echo '<textarea class="form-control" name="'.$name.'" rows="3">'.htmlspecialchars((string)$value).'</textarea>';
  } elseif ($type === 'checkbox') {
    $checked = ((int)$value) ? 'checked' : '';
    echo '<div class="form-check">';
    echo '<input class="form-check-input" type="checkbox" name="'.$name.'" value="1" '.$checked.' id="fld_'.$name.'">';
    echo '<label class="form-check-label" for="fld_'.$name.'"> Yes</label>';
    echo '</div>';
  } elseif ($type === 'number') {
    echo '<input class="form-control" type="number" name="'.$name.'" value="'.htmlspecialchars((string)$value).'">';
  } elseif ($type === 'datetime') {
    echo '<input class="form-control" type="datetime-local" name="'.$name.'" value="'.htmlspecialchars(str_replace(' ','T',(string)$value)).'">';
  } else {
    echo '<input class="form-control" type="text" name="'.$name.'" value="'.htmlspecialchars((string)$value).'">';
  }
  echo '</div>';
}

echo '<button class="btn btn-success" name="save_item" type="submit">'.$btnLabel.'</button>';
if ($editData) echo ' <a class="btn btn-secondary" href="?entity='.$entity.'">Cancel</a>';

echo '</form></div></div></div>';

// Table list
$stmt = $pdo->query('SELECT * FROM '.$entity.' ORDER BY id DESC');
$rows = $stmt->fetchAll();

if (!$rows) {
  echo '<div class="alert alert-info">No records found.</div>';
} else {
  echo '<div class="table-responsive"><table class="table align-middle table-striped">';
  echo '<thead><tr>';
  echo '<th>ID</th>';
  foreach (array_keys($fields) as $h) echo '<th>'.ucwords(str_replace('_',' ',$h)).'</th>';
  if ($entity === 'posts') echo '<th>Published At</th>';
  echo '<th style="width:160px">Actions</th>';
  echo '</tr></thead><tbody>';
  foreach ($rows as $r) {
    echo '<tr>'; 
    echo '<td>'.(int)$r['id'].'</td>';
    foreach (array_keys($fields) as $h) { $val = $r[$h] ?? ''; echo '<td>'.htmlspecialchars(is_string($val)?(strlen($val)>80?substr($val,0,77).'...':$val):(string)$val).'</td>'; }
    if ($entity === 'posts') { echo '<td>'.htmlspecialchars($r['published_at'] ?? '').'</td>'; }
    echo '<td>';
    echo '<a class="btn btn-sm btn-primary" href="?entity='.$entity.'&action=edit&id='.(int)$r['id'].'"><i class="fa fa-pen"></i></a> ';
    echo '<form method="post" class="d-inline" onsubmit="return confirm(\'Delete this item?\')">';
    echo '<input type="hidden" name="id" value="'.(int)$r['id'].'">';
    echo '<button class="btn btn-sm btn-danger" name="delete_item" type="submit"><i class="fa fa-trash"></i></button>';
    echo '</form>';
    echo '</td>';
    echo '</tr>';
  }
  echo '</tbody></table></div>';
}

echo '</div>';
admin_footer();
