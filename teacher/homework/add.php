<?php
$page_title = 'Publish Homework';
require_once __DIR__ . '/../../includes/auth.php';
require_teacher();

$teacher_id_pk = get_logged_teacher_id();
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name ASC")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = (int)($_POST['class_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $issue_date = trim($_POST['issue_date'] ?? date('Y-m-d'));
    $due_date = trim($_POST['due_date'] ?? date('Y-m-d', strtotime('+2 days')));
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf)) {
        $error = 'Invalid security token.';
    } elseif (empty($class_id) || empty($subject_id) || empty($title) || empty($description)) {
        $error = 'Please fill in Class, Subject, Homework Title, and Description.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO homework (class_id, subject_id, teacher_id, title, description, issue_date, due_date)
                VALUES (:cid, :subid, :tid, :title, :desc, :idate, :ddate)");
            $stmt->execute([
                'cid' => $class_id,
                'subid' => $subject_id,
                'tid' => $teacher_id_pk ?: 1,
                'title' => $title,
                'desc' => $description,
                'idate' => $issue_date,
                'ddate' => $due_date
            ]);
            set_flash('success', 'Homework published successfully!');
            header('Location: ' . base_url('teacher/homework/index.php'));
            exit();
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Publish Homework Task</h1>
            <p class="text-xs text-slate-500 mt-1">Create and assign homework for students in your class.</p>
        </div>
        <a href="<?= base_url('teacher/homework/index.php') ?>" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">
            &larr; Back to Homework
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
        <form action="add.php" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="class_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Target Class *</label>
                    <select id="class_id" name="class_id" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['class_name']) ?> (Sec <?= e($c['section']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="subject_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Subject *</label>
                    <select id="subject_id" name="subject_id" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($subjects as $sb): ?>
                            <option value="<?= $sb['id'] ?>"><?= e($sb['subject_name']) ?> (<?= e($sb['subject_code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Homework Title *</label>
                <input type="text" id="title" name="title" required placeholder="e.g. Chapter 5 Practice Problems 1 to 10"
                    class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
            </div>

            <div>
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Detailed Instructions *</label>
                <textarea id="description" name="description" rows="4" required placeholder="Write submission guidelines and questions for students..."
                    class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="issue_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Issue Date *</label>
                    <input type="date" id="issue_date" name="issue_date" value="<?= date('Y-m-d') ?>" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                </div>

                <div>
                    <label for="due_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Due Date *</label>
                    <input type="date" id="due_date" name="due_date" value="<?= date('Y-m-d', strtotime('+2 days')) ?>" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="<?= base_url('teacher/homework/index.php') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">Publish Homework</button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
