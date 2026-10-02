<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$class_id = intval($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';

if (!$class_id || !verify_csrf_token($csrf)) {
    set_flash('error', 'Invalid security token or missing class ID.');
    header('Location: ' . base_url('admin/classes/index.php'));
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT class_name, section FROM classes WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $class_id]);
    $class = $stmt->fetch();

    if ($class) {
        $del_stmt = $pdo->prepare("DELETE FROM classes WHERE id = :id");
        $del_stmt->execute(['id' => $class_id]);

        set_flash('success', 'Class ' . e($class['class_name']) . ' (Section ' . e($class['section']) . ') deleted successfully.');
    } else {
        set_flash('error', 'Class record not found.');
    }
} catch (PDOException $e) {
    set_flash('error', 'Failed to delete class: ' . $e->getMessage());
}

header('Location: ' . base_url('admin/classes/index.php'));
exit();
