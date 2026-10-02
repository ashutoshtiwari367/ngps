<?php
$page_title = 'Post Notice';
require_once __DIR__ . '/../../includes/header.php';
require_admin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $target_role = trim($_POST['target_role'] ?? 'All');
    $author_name = trim($_POST['author_name'] ?? $_SESSION['full_name']);
    $is_important = isset($_POST['is_important']) ? 1 : 0;
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf)) {
        $error = 'Invalid security token.';
    } elseif (empty($title) || empty($content)) {
        $error = 'Please enter both Notice Title and Content.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO notices (title, content, target_role, author_name, is_important) VALUES (:title, :content, :role, :author, :imp)");
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'role' => $target_role,
                'author' => $author_name,
                'imp' => $is_important
            ]);
            set_flash('success', 'School notice published successfully!');
            header('Location: ' . base_url('admin/notices/index.php'));
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
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Post Digital Notice</h1>
            <p class="text-xs text-slate-500 mt-1">Broadcast announcement to all users, staff, or students.</p>
        </div>
        <a href="<?= base_url('admin/notices/index.php') ?>" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">
            &larr; Back to Notices
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
                <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Notice Title *</label>
                <input type="text" id="title" name="title" required placeholder="e.g. Parent-Teacher Conference Schedule"
                    class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="target_role" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Target Audience *</label>
                    <select id="target_role" name="target_role" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <option value="All">All Users (Admin, Teachers, Students/Parents)</option>
                        <option value="Teacher">Teachers & Staff Only</option>
                        <option value="Student">Students & Parents Only</option>
                        <option value="Admin">Admin Staff Only</option>
                    </select>
                </div>

                <div>
                    <label for="author_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Author / Publisher Name</label>
                    <input type="text" id="author_name" name="author_name" value="<?= e($_SESSION['full_name']) ?>" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>
            </div>

            <div>
                <label for="content" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Notice Content / Description *</label>
                <textarea id="content" name="content" rows="5" required placeholder="Type full notice description here..."
                    class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"></textarea>
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" id="is_important" name="is_important" value="1" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                <label for="is_important" class="text-xs font-bold text-slate-800">Mark as Important (Highlight with warning badge)</label>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="<?= base_url('admin/notices/index.php') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">Publish Notice</button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
