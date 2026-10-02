<?php
$page_title = 'Schedule Examination';
require_once __DIR__ . '/../../includes/header.php';
require_admin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_name = trim($_POST['exam_name'] ?? '');
    $session = trim($_POST['academic_session'] ?? '2026-2027');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $status = trim($_POST['status'] ?? 'Scheduled');
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf)) {
        $error = 'Invalid security token.';
    } elseif (empty($exam_name) || empty($start_date) || empty($end_date)) {
        $error = 'Please fill in Exam Name, Start Date, and End Date.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO examinations (exam_name, academic_session, start_date, end_date, status) VALUES (:name, :session, :start, :end, :status)");
            $stmt->execute([
                'name' => $exam_name,
                'session' => $session,
                'start' => $start_date,
                'end' => $end_date,
                'status' => $status
            ]);
            set_flash('success', 'Examination schedule created successfully!');
            header('Location: ' . base_url('admin/exams/index.php'));
            exit();
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Schedule New Examination</h1>
            <p class="text-xs text-slate-500 mt-1">Configure examination term dates and publication status.</p>
        </div>
        <a href="<?= base_url('admin/exams/index.php') ?>" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">
            &larr; Back to Exams
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

            <div>
                <label for="exam_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Examination Title *</label>
                <input type="text" id="exam_name" name="exam_name" required placeholder="e.g. Mid-Term Examination 2026"
                    class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="academic_session" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Academic Session *</label>
                    <input type="text" id="academic_session" name="academic_session" required value="2026-2027"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Initial Status *</label>
                    <select id="status" name="status" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <option value="Scheduled">Scheduled</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                        <option value="Published">Published (Visible to Students)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Start Date *</label>
                    <input type="date" id="start_date" name="start_date" required value="<?= date('Y-m-d') ?>"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label for="end_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">End Date *</label>
                    <input type="date" id="end_date" name="end_date" required value="<?= date('Y-m-d', strtotime('+10 days')) ?>"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="<?= base_url('admin/exams/index.php') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">Create Schedule</button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
