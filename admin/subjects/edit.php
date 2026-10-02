<?php
$page_title = 'Edit Subject';
require_once __DIR__ . '/../../includes/header.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$subject = $stmt->fetch();

if (!$subject) {
    set_flash('error', 'Subject not found.');
    header('Location: ' . base_url('admin/subjects/index.php'));
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name'] ?? '');
    $subject_code = trim($_POST['subject_code'] ?? '');
    $class_id = (int)($_POST['class_id'] ?? 0);
    $teacher_id = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf)) {
        $error = 'Invalid security token.';
    } elseif (empty($subject_name) || empty($subject_code) || empty($class_id)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $update_stmt = $pdo->prepare("UPDATE subjects SET subject_name = :name, subject_code = :code, class_id = :cid, teacher_id = :tid WHERE id = :id");
            $update_stmt->execute([
                'name' => $subject_name,
                'code' => strtoupper($subject_code),
                'cid' => $class_id,
                'tid' => $teacher_id,
                'id' => $id
            ]);
            set_flash('success', 'Subject updated successfully!');
            header('Location: ' . base_url('admin/subjects/index.php'));
            exit();
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();
$teachers = $pdo->query("SELECT * FROM teachers WHERE status = 'Active' ORDER BY name ASC")->fetchAll();
?>

<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Subject</h1>
            <p class="text-xs text-slate-500 mt-1">Modify subject details and teacher assignment.</p>
        </div>
        <a href="<?= base_url('admin/subjects/index.php') ?>" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">
            &larr; Back to Subjects
        </a>
    </div>

    <!-- Error Banner -->
    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-center space-x-2">
            <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="edit.php?id=<?= $id ?>" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div>
                <label for="subject_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Subject Name *</label>
                <input type="text" id="subject_name" name="subject_name" required value="<?= e($subject['subject_name']) ?>"
                    class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>

            <div>
                <label for="subject_code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Subject Code *</label>
                <input type="text" id="subject_code" name="subject_code" required value="<?= e($subject['subject_code']) ?>"
                    class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="class_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Assign Class *</label>
                    <select id="class_id" name="class_id" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $subject['class_id'] ? 'selected' : '' ?>><?= e($c['class_name']) ?> (Section <?= e($c['section']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="teacher_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Subject Teacher</label>
                    <select id="teacher_id" name="teacher_id"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <option value="">-- Unassigned --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $t['id'] == $subject['teacher_id'] ? 'selected' : '' ?>><?= e($t['name']) ?> (<?= e($t['subject']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="<?= base_url('admin/subjects/index.php') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">Update Subject</button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
