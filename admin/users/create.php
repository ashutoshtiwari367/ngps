<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$errors  = [];
$success = '';
$default_role = in_array($_GET['role'] ?? '', ['admin', 'accountant']) ? $_GET['role'] : 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $role      = in_array($_POST['role'] ?? '', ['admin', 'accountant']) ? $_POST['role'] : 'admin';
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        // Validation
        if (!$full_name)   $errors[] = 'Full name is required.';
        if (!$username)    $errors[] = 'Username is required.';
        if (!$email)       $errors[] = 'Email is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            try {
                // Check duplicate username
                $chk = $pdo->prepare("SELECT id FROM users WHERE username = :u");
                $chk->execute(['u' => $username]);
                if ($chk->fetch()) {
                    $errors[] = "Username \"$username\" is already taken. Choose another.";
                } else {
                    // Check duplicate email
                    $chkE = $pdo->prepare("SELECT id FROM users WHERE email = :e");
                    $chkE->execute(['e' => $email]);
                    if ($chkE->fetch()) {
                        $errors[] = "Email \"$email\" is already registered.";
                    } else {
                        $hashed = password_hash($password, PASSWORD_BCRYPT);
                        $ins = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role) VALUES (:fn, :u, :e, :p, :r)");
                        $ins->execute(['fn' => $full_name, 'u' => $username, 'e' => $email, 'p' => $hashed, 'r' => $role]);

                        set_flash('success', "User \"$full_name\" ($role) created successfully!");
                        header('Location: ' . base_url('admin/users/index.php'));
                        exit();
                    }
                }
            } catch (PDOException $ex) {
                $errors[] = 'Database error: ' . $ex->getMessage();
            }
        }
    }
}

$page_title = 'Create User Account';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-2xl mx-auto space-y-6">

    <!-- Breadcrumb -->
    <div class="flex items-center space-x-2 text-xs text-slate-500">
        <a href="<?= base_url('admin/users/index.php') ?>" class="hover:text-blue-600 font-medium transition-colors">
            <i class="fa-solid fa-users-gear mr-1"></i>User Accounts
        </a>
        <i class="fa-solid fa-chevron-right text-[9px]"></i>
        <span class="text-slate-700 font-semibold">Create New User</span>
    </div>

    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
            <i class="fa-solid fa-user-plus text-lg"></i>
        </div>
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Create User Account</h1>
            <p class="text-xs text-slate-500 mt-0.5">Create a portal login for an Administrator or Accountant.</p>
        </div>
    </div>

    <!-- Error Banner -->
    <?php if (!empty($errors)): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm space-y-1">
            <div class="flex items-center space-x-2 font-bold mb-1">
                <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                <span>Please fix the following errors:</span>
            </div>
            <?php foreach ($errors as $err): ?>
                <div class="flex items-start space-x-2 text-xs"><i class="fa-solid fa-arrow-right mt-0.5 text-rose-400"></i><span><?= e($err) ?></span></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="create.php" method="POST" class="space-y-5" autocomplete="off">
            <?= csrf_field() ?>

            <!-- Role Selector -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Account Role *</label>
                <div class="grid grid-cols-2 gap-3">
                    <?php foreach (['admin' => ['fa-user-shield', 'blue', 'Administrator', 'Full system access — manage all modules.'], 'accountant' => ['fa-calculator', 'amber', 'Accountant', 'Fee collection, admission & accounts only.']] as $r => [$icon, $color, $label, $desc]): ?>
                    <?php
                        $selected = (($_POST['role'] ?? $default_role) === $r);
                        $colors = ['blue' => ['ring-blue-500 bg-blue-50 border-blue-400', 'text-blue-700', 'bg-blue-100 text-blue-600'],
                                   'amber' => ['ring-amber-500 bg-amber-50 border-amber-400', 'text-amber-700', 'bg-amber-100 text-amber-600']];
                    ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="<?= $r ?>" class="peer sr-only" <?= $selected ? 'checked' : '' ?>>
                        <div class="p-4 rounded-xl border-2 border-slate-200 transition-all peer-checked:<?= $colors[$color][0] ?> peer-checked:border-<?= $color ?>-400 hover:border-slate-300">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="w-9 h-9 rounded-xl <?= $colors[$color][2] ?> flex items-center justify-center">
                                    <i class="fa-solid <?= $icon ?> text-sm"></i>
                                </div>
                                <span class="font-bold text-sm text-slate-800"><?= $label ?></span>
                            </div>
                            <p class="text-[11px] text-slate-500 leading-snug"><?= $desc ?></p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Full Name -->
            <div>
                <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Full Name *</label>
                <input type="text" id="full_name" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>" required
                    placeholder="e.g. Rahul Kumar Sharma"
                    class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>

            <!-- Username & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Username *</label>
                    <input type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" required
                        placeholder="e.g. rahul_admin"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    <p class="text-[10px] text-slate-400 mt-1">Used to login. Must be unique.</p>
                </div>
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required
                        placeholder="user@school.com"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Password *</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required minlength="6"
                            placeholder="Min 6 characters"
                            class="w-full py-2.5 px-3.5 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <button type="button" onclick="togglePwd('password','eyeIcon1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="eyeIcon1" class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="confirm_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Confirm Password *</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                            placeholder="Re-enter password"
                            class="w-full py-2.5 px-3.5 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <button type="button" onclick="togglePwd('confirm_password','eyeIcon2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="eyeIcon2" class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Info Note -->
            <div class="flex items-start space-x-2.5 bg-blue-50 border border-blue-100 rounded-xl p-3.5 text-xs text-blue-700">
                <i class="fa-solid fa-circle-info mt-0.5 shrink-0"></i>
                <p>The user will log in at <strong><?= e(base_url('login.php')) ?></strong> using the username and password set above. They must change their password after first login.</p>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <a href="<?= base_url('admin/users/index.php') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">
                    Cancel
                </a>
                <button type="submit" class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Create Account</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function togglePwd(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon  = document.getElementById(iconId);
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
