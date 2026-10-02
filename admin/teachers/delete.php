<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$teacher_id = intval($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';

if (!$teacher_id || !verify_csrf_token($csrf)) {
    set_flash('error', 'Invalid security token or missing teacher ID.');
    header('Location: ' . base_url('admin/teachers/index.php'));
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT name FROM teachers WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $teacher_id]);
    $teacher = $stmt->fetch();

    if ($teacher) {
        $del_stmt = $pdo->prepare("DELETE FROM teachers WHERE id = :id");
        $del_stmt->execute(['id' => $teacher_id]);

        set_flash('success', 'Teacher profile (' . e($teacher['name']) . ') deleted successfully.');
    } else {
        set_flash('error', 'Teacher record not found.');
    }
} catch (PDOException $e) {
    set_flash('error', 'Failed to delete teacher: ' . $e->getMessage());
}

header('Location: ' . base_url('admin/teachers/index.php'));
exit();
