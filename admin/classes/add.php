<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$errors = [];
$teachers = $pdo->query("SELECT id, name, subject FROM teachers WHERE status = 'Active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = "Invalid security token.";
    } else {
        $class_name = trim($_POST['class_name'] ?? '');
        $section = trim($_POST['section'] ?? '');
        $teacher_id = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;
        $room_number = trim($_POST['room_number'] ?? '');
        $academic_session = trim($_POST['academic_session'] ?? '2026-2027');

        if (empty($class_name)) $errors[] = "Class name is required.";
        if (empty($section)) $errors[] = "Section is required.";
        if (empty($room_number)) $errors[] = "Room number is required.";

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO classes (class_name, section, teacher_id, room_number, academic_session) 
                    VALUES (:class_name, :section, :teacher_id, :room_number, :academic_session)");
                
                $stmt->execute([
                    'class_name'       => $class_name,
                    'section'          => $section,
                    'teacher_id'       => $teacher_id,
                    'room_number'      => $room_number,
                    'academic_session' => $academic_session
                ]);

                set_flash('success', 'Class created successfully!');
                header('Location: ' . base_url('admin/classes/index.php'));
                exit();
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}

$page_title = 'Add Class';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6 max-w-2xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Add Class</h1>
            <p class="text-sm text-slate-500 mt-1">Create a new academic class grade and section.</p>
        </div>
        <a href="index.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to List</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 md:p-8">
        
        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
                <?php foreach ($errors as $err): ?>
                    <p class="flex items-center space-x-2"><i class="fa-solid fa-circle-exclamation text-rose-500"></i><span><?= e($err) ?></span></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="add.php" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Class Name *</label>
                    <input type="text" name="class_name" required value="<?= e($_POST['class_name'] ?? '') ?>" placeholder="e.g. Class 10"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Section *</label>
                    <input type="text" name="section" required value="<?= e($_POST['section'] ?? '') ?>" placeholder="e.g. A"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Class Teacher</label>
                    <select name="teacher_id" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <option value="">-- Unassigned --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= (($_POST['teacher_id'] ?? '') == $t['id']) ? 'selected' : '' ?>>
                                <?= e($t['name']) ?> (<?= e($t['subject']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Room Number *</label>
                    <input type="text" name="room_number" required value="<?= e($_POST['room_number'] ?? '') ?>" placeholder="e.g. Room 101"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Academic Session</label>
                    <input type="text" name="academic_session" value="<?= e($_POST['academic_session'] ?? '2026-2027') ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">
                    Create Class
                </button>
            </div>
        </form>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
