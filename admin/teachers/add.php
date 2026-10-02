<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$errors = [];

// Auto generate next teacher ID e.g. TCH-106
$max_id_stmt = $pdo->query("SELECT MAX(id) FROM teachers");
$next_id = ($max_id_stmt->fetchColumn() ?: 0) + 1;
$auto_tch_id = "TCH-" . (100 + $next_id);
$auto_username = "teacher" . (100 + $next_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = "Invalid security token.";
    } else {
        $teacher_id = trim($_POST['teacher_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $qualification = trim($_POST['qualification'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $joining_date = trim($_POST['joining_date'] ?? date('Y-m-d'));
        $status = trim($_POST['status'] ?? 'Active');
        $username = trim($_POST['username'] ?? '');
        $raw_password = trim($_POST['password'] ?? 'teacher123');

        if (empty($teacher_id)) $errors[] = "Teacher ID is required.";
        if (empty($name)) $errors[] = "Teacher name is required.";
        if (empty($subject)) $errors[] = "Subject is required.";
        if (empty($mobile)) $errors[] = "Mobile number is required.";

        // Hash password if username is provided
        $hashed_password = !empty($raw_password) ? password_hash($raw_password, PASSWORD_DEFAULT) : null;

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO teachers (teacher_id, name, subject, qualification, mobile, email, address, joining_date, status, username, password) 
                    VALUES (:teacher_id, :name, :subject, :qualification, :mobile, :email, :address, :joining_date, :status, :username, :password)");
                
                $stmt->execute([
                    'teacher_id'   => $teacher_id,
                    'name'         => $name,
                    'subject'      => $subject,
                    'qualification'=> $qualification,
                    'mobile'       => $mobile,
                    'email'        => $email,
                    'address'      => $address,
                    'joining_date' => $joining_date,
                    'status'       => $status,
                    'username'     => !empty($username) ? $username : null,
                    'password'     => $hashed_password
                ]);

                set_flash('success', 'Teacher profile & portal account created successfully!');
                header('Location: ' . base_url('admin/teachers/index.php'));
                exit();
            } catch (PDOException $e) {
                $errors[] = "Database insert failed: " . $e->getMessage();
            }
        }
    }
}

$page_title = 'Add Teacher';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6 max-w-3xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Add Teacher Account</h1>
            <p class="text-sm text-slate-500 mt-1">Register a new faculty member and create their portal login credentials.</p>
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

        <form action="add.php" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Portal Credentials Section -->
            <div class="p-4 bg-indigo-50/60 rounded-2xl border border-indigo-100 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-900 flex items-center space-x-2">
                    <i class="fa-solid fa-key text-indigo-600"></i>
                    <span>Teacher Portal Login Credentials</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Portal Username</label>
                        <input type="text" name="username" value="<?= e($_POST['username'] ?? $auto_username) ?>" placeholder="e.g. rajesh"
                            class="w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Portal Password</label>
                        <input type="text" name="password" value="<?= e($_POST['password'] ?? 'teacher123') ?>" placeholder="Default: teacher123"
                            class="w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>
                </div>
            </div>

            <!-- General Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Teacher ID *</label>
                    <input type="text" name="teacher_id" required value="<?= e($_POST['teacher_id'] ?? $auto_tch_id) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Full Name *</label>
                    <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="e.g. Dr. Rajesh Sharma"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Primary Subject *</label>
                    <input type="text" name="subject" required value="<?= e($_POST['subject'] ?? '') ?>" placeholder="e.g. Mathematics"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Qualification</label>
                    <input type="text" name="qualification" value="<?= e($_POST['qualification'] ?? '') ?>" placeholder="e.g. M.Sc., Ph.D."
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mobile Number *</label>
                    <input type="text" name="mobile" required value="<?= e($_POST['mobile'] ?? '') ?>" placeholder="10 digit phone number"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="teacher@school.com"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Joining Date</label>
                    <input type="date" name="joining_date" value="<?= e($_POST['joining_date'] ?? date('Y-m-d')) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status</label>
                    <select name="status" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Address</label>
                    <textarea name="address" rows="2" placeholder="Teacher residential address..."
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"><?= e($_POST['address'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">
                    Save Teacher Profile & Account
                </button>
            </div>
        </form>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
