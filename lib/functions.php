<?php
require_once __DIR__ . '/db.php';

function esc(string $str): string { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

function getServices(int $limit = 8): array {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, title, summary, icon_class FROM services ORDER BY sort_order, id LIMIT :lim');
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getPortfolios(int $limit = 8): array {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, title, image_url, category FROM portfolios ORDER BY created_at DESC LIMIT :lim');
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getPricingPlans(): array {
    $pdo = get_pdo();
    $stmt = $pdo->query('SELECT id, name, price, period, features, is_featured FROM pricing_plans ORDER BY price ASC');
    return $stmt->fetchAll();
}

function getBrands(int $limit = 10): array {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, name, logo_url FROM brands ORDER BY sort_order, id LIMIT :lim');
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getTestimonials(int $limit = 5): array {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, author_name, author_role, content, avatar_url FROM testimonials ORDER BY id DESC LIMIT :lim');
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getPosts(int $limit = 3): array {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, title, excerpt, image_url, published_at FROM posts ORDER BY published_at DESC LIMIT :lim');
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function saveCallRequest(string $name, string $phone, string $message = null): bool {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('INSERT INTO call_requests(name, phone, message) VALUES(:n, :p, :m)');
    return $stmt->execute([':n' => $name, ':p' => $phone, ':m' => $message]);
}

function saveSubscription(string $email): bool {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('INSERT INTO subscriptions(email) VALUES(:e)');
    return $stmt->execute([':e' => $email]);
}

/* ------------------------------
 | Generic CRUD helpers
 |------------------------------ */

/**
 * Whitelisted columns for each table to protect against unwanted writes.
 */
function db_allowed_columns(string $table): array {
    $map = [
        'services' => ['title','summary','icon_class','sort_order'],
        'portfolios' => ['title','image_url','category'],
        'pricing_plans' => ['name','price','period','features','is_featured'],
        'brands' => ['name','logo_url','sort_order'],
        'testimonials' => ['author_name','author_role','content','avatar_url'],
        'posts' => ['title','excerpt','image_url','published_at'],
        'call_requests' => ['name','phone','message'],
        'subscriptions' => ['email'],
    ];
    return $map[$table] ?? [];
}

/**
 * Filter an input array to allowed columns for a table.
 */
function db_filter_data(string $table, array $data): array {
    $allowed = db_allowed_columns($table);
    if (!$allowed) return [];
    $out = [];
    foreach ($allowed as $c) {
        if (array_key_exists($c, $data)) {
            $out[$c] = $data[$c];
        }
    }
    return $out;
}

/** Create a new row and return inserted id or 0 on failure */
function db_insert(string $table, array $data): int {
    $row = db_filter_data($table, $data);
    if (!$row) return 0;
    $pdo = get_pdo();
    $cols = array_keys($row);
    $place = array_map(fn($c)=>":$c", $cols);
    $sql = 'INSERT INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', $place) . ')';
    $ok = $pdo->prepare($sql)->execute(array_combine($place, array_values($row)));
    return $ok ? (int)$pdo->lastInsertId() : 0;
}

/** Update a row by id, returns bool */
function db_update(string $table, int $id, array $data): bool {
    $row = db_filter_data($table, $data);
    if (!$row) return false;
    $pdo = get_pdo();
    $sets = [];$params=[];
    foreach ($row as $k=>$v){ $sets[] = "$k = :$k"; $params[":$k"] = $v; }
    $params[':id'] = $id;
    $sql = 'UPDATE ' . $table . ' SET ' . implode(',', $sets) . ' WHERE id = :id';
    return $pdo->prepare($sql)->execute($params);
}

/** Delete a row by id */
function db_delete(string $table, int $id): bool {
    $pdo = get_pdo();
    return $pdo->prepare('DELETE FROM '.$table.' WHERE id = :id')->execute([':id'=>$id]);
}

/** Fetch single row */
function db_find(string $table, int $id): ?array {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT * FROM '.$table.' WHERE id = :id');
    $stmt->execute([':id'=>$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Fetch all rows with optional limit and order */
function db_all(string $table, int $limit = 100, string $orderBy = 'id DESC'): array {
    $pdo = get_pdo();
    $sql = 'SELECT * FROM ' . $table . ' ORDER BY ' . $orderBy . ' LIMIT :lim';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/* Convenience wrappers per entity (optional) */
function createService(array $data): int { return db_insert('services', $data); }
function updateService(int $id, array $data): bool { return db_update('services', $id, $data); }
function deleteService(int $id): bool { return db_delete('services', $id); }

function createPortfolio(array $data): int { return db_insert('portfolios', $data); }
function updatePortfolio(int $id, array $data): bool { return db_update('portfolios', $id, $data); }
function deletePortfolio(int $id): bool { return db_delete('portfolios', $id); }

function createPricingPlan(array $data): int { return db_insert('pricing_plans', $data); }
function updatePricingPlan(int $id, array $data): bool { return db_update('pricing_plans', $id, $data); }
function deletePricingPlan(int $id): bool { return db_delete('pricing_plans', $id); }

function createBrand(array $data): int { return db_insert('brands', $data); }
function updateBrand(int $id, array $data): bool { return db_update('brands', $id, $data); }
function deleteBrand(int $id): bool { return db_delete('brands', $id); }

function createTestimonial(array $data): int { return db_insert('testimonials', $data); }
function updateTestimonial(int $id, array $data): bool { return db_update('testimonials', $id, $data); }
function deleteTestimonial(int $id): bool { return db_delete('testimonials', $id); }

function createPost(array $data): int { return db_insert('posts', $data); }
function updatePost(int $id, array $data): bool { return db_update('posts', $id, $data); }
function deletePost(int $id): bool { return db_delete('posts', $id); }

function deleteCallRequest(int $id): bool { return db_delete('call_requests', $id); }
function deleteSubscription(int $id): bool { return db_delete('subscriptions', $id); }

/* ------------------------------
 | Settings helpers (key/value)
 |------------------------------ */
function ensure_settings_table(): void {
    static $checked = false; if ($checked) return; $checked = true;
    try {
        $pdo = get_pdo();
        $pdo->exec('CREATE TABLE IF NOT EXISTS settings ( `key` VARCHAR(191) PRIMARY KEY, `value` TEXT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    } catch (Throwable $e) { /* ignore */ }
}

function getSetting(string $key, $default = null): ?string {
    ensure_settings_table();
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('SELECT `value` FROM settings WHERE `key` = :k');
        $stmt->execute([':k'=>$key]);
        $row = $stmt->fetch();
        if ($row && array_key_exists('value',$row)) return (string)$row['value'];
    } catch (Throwable $e) {}
    return $default;
}

function setSetting(string $key, ?string $value): bool {
    ensure_settings_table();
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('INSERT INTO settings(`key`,`value`) VALUES(:k,:v) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
        return $stmt->execute([':k'=>$key, ':v'=>$value]);
    } catch (Throwable $e) { return false; }
}
