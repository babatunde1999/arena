<?php
/**
 * Shop / vendor helpers
 * Arena Market - E-Commerce Platform
 */

/**
 * Resolve the current user's shop, creating one (and promoting the user to
 * 'vendor') if they do not have one yet.
 * @param PDO $db
 * @return array|null  Shop row ['id', 'name', 'slug'] or null if not logged in
 */
function getOrCreateShop($db) {
    $userId = getUserId();
    if (!$userId) {
        return null;
    }

    $stmt = $db->prepare("SELECT id, name, slug FROM shops WHERE user_id = :uid LIMIT 1");
    $stmt->execute([':uid' => $userId]);
    $shop = $stmt->fetch();

    if ($shop) {
        return $shop;
    }

    $name = ucfirst(\getUsername() ?? 'user') . "'s Shop";
    $slug = slugify($name) . '-' . substr(bin2hex(random_bytes(3)), 0, 6);

    $db->prepare(
        "INSERT INTO shops (user_id, name, slug, created_at) VALUES (:uid, :name, :slug, NOW())"
    )->execute([':uid' => $userId, ':name' => $name, ':slug' => $slug]);

    $db->prepare("UPDATE users SET role = 'vendor' WHERE id = :uid")->execute([':uid' => $userId]);
    $_SESSION['role'] = 'vendor';

    $stmt = $db->prepare("SELECT id, name, slug FROM shops WHERE user_id = :uid LIMIT 1");
    $stmt->execute([':uid' => $userId]);
    return $stmt->fetch();
}

/**
 * Build a URL-friendly slug from any string.
 * @param string $text
 * @return string
 */
function slugify($text) {
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $text), '-'));
    return $slug === '' ? 'item' : $slug;
}

/**
 * Generate a unique slug for a products row based on the name.
 * @param PDO $db
 * @param string $name
 * @return string
 */
function uniqueProductSlug($db, $name) {
    $base = slugify($name);
    $slug = $base;
    $i = 1;

    $stmt = $db->prepare("SELECT id FROM products WHERE slug = :slug LIMIT 1");

    while (true) {
        $stmt->execute([':slug' => $slug]);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
    }
}

/**
 * Validate and store uploaded product images.
 * Expects a re-indexed array of single-file entries, e.g. from $_FILES['images'].
 * @param array $files  List of ['name','type','tmp_name','error','size']
 * @param int   $max    Maximum number of images allowed
 * @return array ['images' => string[], 'errors' => string[]]
 */
function saveImageUploads(array $files, $max = PRODUCT_MAX_IMAGES) {
    $images = [];
    $errors = [];

    foreach ($files as $file) {
        if (!$file || !isset($file['name']) || $file['name'] === '') {
            continue;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $errors[] = 'One of the uploads failed. Please try again.';
            continue;
        }
        if ($file['size'] > MAX_IMAGE_SIZE) {
            $errors[] = 'Image "' . $file['name'] . '" must be smaller than 5MB.';
            continue;
        }

        $finfo = class_exists('finfo') ? new finfo(FILEINFO_MIME_TYPE) : null;
        $mime  = $finfo ? $finfo->file($file['tmp_name']) : $file['type'];

        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];

        if (!isset($extMap[$mime])) {
            $errors[] = 'Image "' . $file['name'] . '" must be JPG, PNG, WEBP or GIF.';
            continue;
        }

        if (!is_dir(UPLOADS_PATH)) {
            mkdir(UPLOADS_PATH, 0777, true);
        }

        $name = 'prod_' . bin2hex(random_bytes(8)) . '.' . $extMap[$mime];

        if (!move_uploaded_file($file['tmp_name'], UPLOADS_PATH . '/' . $name)) {
            $errors[] = 'Could not save "' . $file['name'] . '". Please try again.';
            continue;
        }

        $images[] = $name;
    }

    if (count($images) > $max) {
        foreach ($images as $extra) {
            @unlink(UPLOADS_PATH . '/' . $extra);
        }
        $errors[] = 'You can upload a maximum of ' . $max . ' images per product.';
        return ['images' => [], 'errors' => $errors];
    }

    return ['images' => $images, 'errors' => $errors];
}

/**
 * Re-index a multi-file $_FILES array into a flat list of single-file entries.
 * @param array $uploads  $_FILES['images'] structure
 * @return array[]
 */
function reindexPostFiles($uploads) {
    $out = [];
    if (!isset($uploads['name']) || !is_array($uploads['name'])) {
        return $out;
    }
    $count = count($uploads['name']);
    for ($i = 0; $i < $count; $i++) {
        $out[] = [
            'name'     => $uploads['name'][$i],
            'type'     => $uploads['type'][$i] ?? '',
            'tmp_name' => $uploads['tmp_name'][$i],
            'error'    => $uploads['error'][$i],
            'size'     => $uploads['size'][$i],
        ];
    }
    return $out;
}

/**
 * Collect the filenames of all images belonging to a product (cover + gallery).
 * Used when deleting a product so its files can be cleaned from disk.
 * @param PDO   $db
 * @param int   $productId
 * @return string[]
 */
function getProductImageFiles($db, $productId) {
    $files = [];
    $stmt  = $db->prepare("SELECT image FROM products WHERE id = :id");
    $stmt->execute([':id' => $productId]);
    $cover = $stmt->fetch();
    if ($cover && !empty($cover['image'])) {
        $files[] = $cover['image'];
    }
    $stmt = $db->prepare("SELECT image FROM product_images WHERE product_id = :id");
    $stmt->execute([':id' => $productId]);
    foreach ($stmt->fetchAll() as $row) {
        $files[] = $row['image'];
    }
    return $files;
}

/**
 * Delete image files from disk by filename.
 * @param string[] $names
 */
function deleteImageFiles(array $names) {
    foreach ($names as $name) {
        if ($name !== '') {
            @unlink(UPLOADS_PATH . '/' . $name);
        }
    }
}