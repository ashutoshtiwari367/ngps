<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$teacher_id_pk = intval($_GET['id'] ?? 0);

if (!$teacher_id_pk) {
    set_flash('error', 'Invalid teacher ID.');
    header('Location: ' . base_url('admin/teachers/index.php'));
    exit();
}

$errors = [];

// Fetch teacher
$stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $teacher_id_pk]);
$teacher = $stmt->fetch();

if (!$teacher) {
    set_flash('error', 'Teacher record not found.');
    header('Location: ' . base_url('admin/teachers/index.php'));
    exit();
}

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
        $joining_date = trim($_POST['joining_date'] ?? '');
        $status = trim($_POST['status'] ?? 'Active');
        $username = trim($_POST['username'] ?? '');
        $new_password = trim($_POST['password'] ?? '');

        if (empty($teacher_id)) $errors[] = "Teacher ID is required.";
        if (empty($name)) $errors[] = "Teacher name is required.";
        if (empty($subject)) $errors[] = "Subject is required.";

        if (empty($errors)) {
            try {
                if (!empty($new_password)) {
                    $hashed_pwd = password_hash($new_password, PASSWORD_DEFAULT);
                    $up_stmt = $pdo->prepare("UPDATE teachers SET 
                        teacher_id = :teacher_id,
                        name = :name,
                        subject = :subject,
                        qualification = :qualification,
                        mobile = :mobile,
                        email = :email,
                        address = :address,
                        joining_date = :joining_date,
                        status = :status,
                        username = :username,
                        password = :password
                        WHERE id = :id");

                    $up_stmt->execute([
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
                        'password'     => $hashed_pwd,
                        'id'           => $teacher_id_pk
                    ]);
                } else {
                    $up_stmt = $pdo->prepare("UPDATE teachers SET 
                        teacher_id = :teacher_id,
                        name = :name,
                        subject = :subject,
                        qualification = :qualification,
                        mobile = :mobile,
                        email = :email,
                        address = :address,
                        joining_date = :joining_date,
                        status = :status,
                        username = :username
                        WHERE id = :id");

                    $up_stmt->execute([
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
                        'id'           => $teacher_id_pk
                    ]);
                }

                set_flash('success', 'Teacher record & portal credentials updated!');
                header('Location: ' . base_url('admin/teachers/index.php'));
                exit();
            } catch (PDOException $e) {
                $errors[] = "Database update error: " . $e->getMessage();
            }
        }
    }
}

$page_title = 'Edit Teacher';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6 max-w-3xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Teacher Account</h1>
            <p class="text-sm text-slate-500 mt-1">Update details for <span class="font-semibold text-slate-800"><?= e($teacher['name']) ?></span>.</p>
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

        <form action="edit.php?id=<?= $teacher_id_pk ?>" method="POST" class="space-y-6">
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
                        <input type="text" name="username" value="<?= e($teacher['username'] ?: strtolower(str_replace(' ', '', $teacher['name']))) ?>" placeholder="Username"
                            class="w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Reset Password (Optional)</label>
                        <input type="text" name="password" placeholder="Leave blank to keep existing"
                            class="w-full py-2.5 px-3 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all">
                    </div>
                </div>
            </div>

            <!-- General Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Teacher ID *</label>
                    <input type="text" name="teacher_id" required value="<?= e($teacher['teacher_id']) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Full Name *</label>
                    <input type="text" name="name" required value="<?= e($teacher['name']) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Primary Subject *</label>
                    <input type="text" name="subject" required value="<?= e($teacher['subject']) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Qualification</label>
                    <input type="text" name="qualification" value="<?= e($teacher['qualification']) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mobile Number *</label>
                    <input type="text" name="mobile" required value="<?= e($teacher['mobile']) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="<?= e($teacher['email']) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Joining Date</label>
                    <input type="date" name="joining_date" value="<?= e($teacher['joining_date']) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status</label>
                    <select name="status" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <option value="Active" <?= ($teacher['status'] === 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= ($teacher['status'] === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Address</label>
                    <textarea name="address" rows="2"
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"><?= e($teacher['address']) ?></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">
                    Update Teacher Profile & Account
                </button>
            </div>
        </form>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
