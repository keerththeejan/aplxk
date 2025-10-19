<?php
// backend/services_list.php (public list)
require_once __DIR__ . '/init.php';
header('Content-Type: application/json');

// Ensure table exists (safe if exists)
$conn->query("CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  image_url VARCHAR(512) NOT NULL,
  title VARCHAR(120) NOT NULL,
  description VARCHAR(500) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Seed defaults if table is empty
try {
  $cntRes = $conn->query('SELECT COUNT(*) AS c FROM services');
  $count = (int)($cntRes->fetch_assoc()['c'] ?? 0);
  if ($count === 0) {
    $seed = [
      ['/APLX/frontend/images/truck-moving-shipping-container-min-1024x683.jpeg', 'Air Freight', 'Efficient and reliable air freight solutions for your business needs.', 1],
      ['/APLX/frontend/images/premium_photo-1661962420310-d3be75c8921c.jpg', 'Ocean Freight', 'Comprehensive ocean freight services worldwide.', 2],
      ['/APLX/frontend/images/cda6387f3ee1ca2a8f08f4e846dfcf59.jpg', 'Land Transport', 'Efficient land transportation solutions for all your needs.', 3],
      ['/APLX/frontend/images/iStock-1024024568-scaled.jpg', 'Warehousing', 'Secure storage and inventory management.', 4],
    ];
    $stmt = $conn->prepare('INSERT INTO services(image_url, title, description, sort_order) VALUES (?,?,?,?)');
    foreach ($seed as $row) {
      [$img, $title, $desc, $ord] = $row;
      $stmt->bind_param('sssi', $img, $title, $desc, $ord);
      $stmt->execute();
    }
  }
} catch (Throwable $e) { /* ignore seed errors */ }

$q = $conn->query('SELECT id, image_url, title, description, sort_order FROM services ORDER BY sort_order, id');
$rows = [];
while ($row = $q->fetch_assoc()) { $rows[] = $row; }
echo json_encode(['items' => $rows]);
