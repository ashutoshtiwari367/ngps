<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $error = 'Invalid security token.';
    } else {
        $settings_map = [
            'school_name'      => trim($_POST['school_name'] ?? 'Next Generation Public School'),
            'school_tagline'   => trim($_POST['school_tagline'] ?? 'School Management System'),
            'school_email'     => trim($_POST['school_email'] ?? ''),
            'school_phone'     => trim($_POST['school_phone'] ?? ''),
            'school_address'   => trim($_POST['school_address'] ?? ''),
            'academic_session' => trim($_POST['academic_session'] ?? '2026-2027'),
            'currency_symbol'  => trim($_POST['currency_symbol'] ?? '₹'),
        ];

        try {
            $stmt = $pdo->prepare("INSERT INTO school_settings (setting_key, setting_value) 
                VALUES (:k, :v) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

            foreach ($settings_map as $key => $val) {
                $stmt->execute(['k' => $key, 'v' => $val]);
            }

            set_flash('success', 'School settings updated successfully!');
            header('Location: ' . base_url('admin/settings/index.php'));
            exit();
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Current Values (fetched before any HTML output)
$s_name     = get_school_setting('school_name', 'Next Generation Public School');
$s_tagline  = get_school_setting('school_tagline', 'School Management System');
$s_email    = get_school_setting('school_email', 'info@rdmakids.edu');
$s_phone    = get_school_setting('school_phone', '+91 98765 43210');
$s_address  = get_school_setting('school_address', '123 Education Boulevard, Knowledge City, New Delhi - 110001');
$s_session  = get_school_setting('academic_session', '2026-2027');
$s_currency = get_school_setting('currency_symbol', '₹');

// NOW include header (outputs HTML — must come AFTER all header() calls)
$page_title = 'School Settings';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">School Settings & Profile</h1>
            <p class="text-xs text-slate-500 mt-1">Configure school identity, official contacts, and current academic session.</p>
        </div>
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
        <form action="index.php" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="school_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">School Name *</label>
                    <input type="text" id="school_name" name="school_name" value="<?= e($s_name) ?>" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label for="school_tagline" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tagline / Subtitle</label>
                    <input type="text" id="school_tagline" name="school_tagline" value="<?= e($s_tagline) ?>"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="school_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Official Email *</label>
                    <input type="email" id="school_email" name="school_email" value="<?= e($s_email) ?>" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label for="school_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Contact Phone *</label>
                    <input type="text" id="school_phone" name="school_phone" value="<?= e($s_phone) ?>" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>
            </div>

            <div>
                <label for="school_address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">School Campus Address *</label>
                <textarea id="school_address" name="school_address" rows="2" required
                    class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"><?= e($s_address) ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label for="academic_session" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Active Academic Session *</label>
                    <input type="text" id="academic_session" name="academic_session" value="<?= e($s_session) ?>" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label for="currency_symbol" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Currency Symbol *</label>
                    <input type="text" id="currency_symbol" name="currency_symbol" value="<?= e($s_currency) ?>" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span>Save School Settings</span>
                </button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
