<?php
// backend/admin/services_api.php
require_once __DIR__ . '/../init.php';
require_admin();
header('Content-Type: application/json');

$conn->query("CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  image_url VARCHAR(512) NOT NULL,
  title VARCHAR(120) NOT NULL,
  description VARCHAR(500) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Provide CSRF token for JS clients
if ($action === 'csrf') {
  echo json_encode(['csrf' => csrf_token()]);
  exit;
}

function json_body() {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function respond($data, $code = 200){ http_response_code($code); echo json_encode($data); exit; }

if ($method === 'GET' && $action !== 'seed') {
  // List all
  $q = $conn->query('SELECT id, image_url, title, description, sort_order FROM services ORDER BY sort_order, id');
  $rows = [];
  while ($row = $q->fetch_assoc()) { $rows[] = $row; }
  respond(['items' => $rows]);
}

// For write operations and seed, require valid CSRF
csrf_check();

// Admin seed defaults
if ($action === 'seed') {
  $defaults = [
    ['/APLX/frontend/images/truck-moving-shipping-container-min-1024x683.jpeg', 'Air Freight', 'Efficient and reliable air freight solutions for your business needs.', 1],
    ['/APLX/frontend/images/premium_photo-1661962420310-d3be75c8921c.jpg', 'Ocean Freight', 'Comprehensive ocean freight services worldwide.', 2],
    ['/APLX/frontend/images/cda6387f3ee1ca2a8f08f4e846dfcf59.jpg', 'Land Transport', 'Efficient land transportation solutions for all your needs.', 3],
    ['/APLX/frontend/images/iStock-1024024568-scaled.jpg', 'Warehousing', 'Secure storage and inventory management.', 4],
  ];
  foreach ($defaults as $row) {
    [$img, $title, $desc, $ord] = $row;
    // check if exists by title
    $sel = $conn->prepare('SELECT id FROM services WHERE title=? LIMIT 1');
    $sel->bind_param('s', $title);
    $sel->execute();
    $found = $sel->get_result()->fetch_assoc();
    if ($found) {
      $id = (int)$found['id'];
      $upd = $conn->prepare('UPDATE services SET image_url=?, description=?, sort_order=? WHERE id=?');
      $upd->bind_param('ssii', $img, $desc, $ord, $id);
      $upd->execute();
    } else {
      $ins = $conn->prepare('INSERT INTO services(image_url, title, description, sort_order) VALUES (?,?,?,?)');
      $ins->bind_param('sssi', $img, $title, $desc, $ord);
      $ins->execute();
    }
  }
  respond(['ok' => true, 'seeded' => true]);
}

// Method override via _method (for HTML forms)
$_method = $_POST['_method'] ?? ($_GET['_method'] ?? null);
if ($_method) { $method = strtoupper($_method); }

// Helpers for uploads
function ensure_dir($p){ if (!is_dir($p)) { @mkdir($p, 0775, true); } return is_dir($p); }
function save_upload($field, $subdir) {
  if (!isset($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'])) return null;
  $file = $_FILES[$field];
  if ($file['error'] !== UPLOAD_ERR_OK) return null;
  $finfo = @finfo_open(FILEINFO_MIME_TYPE);
  $mime = $finfo ? finfo_buffer($finfo, file_get_contents($file['tmp_name'])) : ($file['type'] ?? '');
  $allowed = ['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp','image/gif'=>'.gif'];
  if (!isset($allowed[$mime])) { return null; }
  $root = realpath(__DIR__ . '/../../');
  $uploadDir = $root . '/uploads/' . trim($subdir, '/');
  if (!ensure_dir($uploadDir)) return null;
  $name = bin2hex(random_bytes(8)) . $allowed[$mime];
  $target = $uploadDir . '/' . $name;
  if (!move_uploaded_file($file['tmp_name'], $target)) return null;
  // Public URL
  $public = '/APLX/uploads/' . trim($subdir,'/') . '/' . $name;
  return $public;
}

switch ($method) {
  case 'POST':
    // Prefer uploaded file over URL if provided
    $image_url = save_upload('image_file', 'services');
    if (!$image_url) { $image_url = trim($_POST['image_url'] ?? ''); }
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = intval($_POST['sort_order'] ?? 0);
    if (!$image_url) respond(['error' => 'Image is required'], 400);
    if (!$title || !$description) respond(['error' => 'Title and description are required'], 400);
    $stmt = $conn->prepare('INSERT INTO services(image_url, title, description, sort_order) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sssi', $image_url, $title, $description, $sort_order);
    $stmt->execute();
    respond(['ok' => true, 'id' => $stmt->insert_id]);
  case 'PUT':
  case 'PATCH':
    $isJson = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
    $src = $isJson ? json_body() : $_POST;
    $id = intval($_GET['id'] ?? ($src['id'] ?? 0));
    if ($id <= 0) respond(['error' => 'Invalid id'], 400);
    // Load existing image_url to keep when not replaced
    $cur = $conn->prepare('SELECT image_url FROM services WHERE id=?');
    $cur->bind_param('i', $id);
    $cur->execute();
    $old = ($cur->get_result()->fetch_assoc()['image_url'] ?? '');
    $title = trim(($src['title'] ?? ''));
    $description = trim(($src['description'] ?? ''));
    $sort_order = isset($src['sort_order']) ? intval($src['sort_order']) : 0;
    // Prefer uploaded file if present; else allow image_url override; else keep old
    $newUpload = save_upload('image_file', 'services');
    $image_url = $newUpload ?: (trim($src['image_url'] ?? '') ?: $old);
    if (!$image_url) respond(['error' => 'Image is required'], 400);
    if (!$title || !$description) respond(['error' => 'Title and description are required'], 400);
    $stmt = $conn->prepare('UPDATE services SET image_url=?, title=?, description=?, sort_order=? WHERE id=?');
    $stmt->bind_param('sssii', $image_url, $title, $description, $sort_order, $id);
    $stmt->execute();
    respond(['ok' => true]);
  case 'DELETE':
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) respond(['error' => 'Invalid id'], 400);
    $stmt = $conn->prepare('DELETE FROM services WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['ok' => true]);
  default:
    respond(['error' => 'Unsupported method'], 405);
}
