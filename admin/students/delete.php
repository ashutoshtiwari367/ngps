<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$student_id = intval($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';

if (!$student_id || !verify_csrf_token($csrf)) {
    set_flash('error', 'Invalid security token or missing student ID.');
    header('Location: ' . base_url('admin/students/index.php'));
    exit();
}

try {
    // Fetch student photo to clean up file
    $stmt = $pdo->prepare("SELECT photo, first_name, last_name FROM students WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $student_id]);
    $student = $stmt->fetch();

    if ($student) {
        if (!empty($student['photo'])) {
            $photo_path = __DIR__ . '/../../assets/uploads/' . $student['photo'];
            if (file_exists($photo_path)) {
                @unlink($photo_path);
            }
        }

        $del_stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        $del_stmt->execute(['id' => $student_id]);

        set_flash('success', 'Student profile (' . e($student['first_name'] . ' ' . $student['last_name']) . ') deleted successfully!');
    } else {
        set_flash('error', 'Student record not found.');
    }
} catch (PDOException $e) {
    set_flash('error', 'Failed to delete student: ' . $e->getMessage());
}

header('Location: ' . base_url('admin/students/index.php'));
exit();
