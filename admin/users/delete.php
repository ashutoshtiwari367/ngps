<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$id = intval($_GET['id'] ?? 0);

// Prevent self-deletion
if ($id === intval($_SESSION['user_id'] ?? 0)) {
    set_flash('error', 'You cannot delete your own account.');
    header('Location: ' . base_url('admin/users/index.php'));
    exit();
}

if (!$id) {
    set_flash('error', 'Invalid user ID.');
    header('Location: ' . base_url('admin/users/index.php'));
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() > 0) {
        set_flash('success', 'User account deleted successfully.');
    } else {
        set_flash('error', 'User not found or already deleted.');
    }
} catch (PDOException $e) {
    set_flash('error', 'Failed to delete user: ' . $e->getMessage());
}

header('Location: ' . base_url('admin/users/index.php'));
exit();
