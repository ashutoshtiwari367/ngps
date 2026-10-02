<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    set_flash('error', 'Invalid user ID.');
    header('Location: ' . base_url('admin/users/index.php'));
    exit();
}

$user = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$user->execute(['id' => $id]);
$user = $user->fetch();

if (!$user) {
    set_flash('error', 'User not found.');
    header('Location: ' . base_url('admin/users/index.php'));
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = 'Invalid security token.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $role      = in_array($_POST['role'] ?? '', ['admin', 'accountant']) ? $_POST['role'] : $user['role'];
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        if (!$full_name) $errors[] = 'Full name is required.';
        if (!$username)  $errors[] = 'Username is required.';
        if (!$email)     $errors[] = 'Email is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
        if ($password && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password && $password !== $confirm) $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            try {
                // Check duplicate username (excluding self)
                $chk = $pdo->prepare("SELECT id FROM users WHERE username = :u AND id != :id");
                $chk->execute(['u' => $username, 'id' => $id]);
                if ($chk->fetch()) {
                    $errors[] = "Username \"$username\" is already taken.";
                } else {
                    if ($password) {
                        $hashed = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("UPDATE users SET full_name=:fn, username=:u, email=:e, role=:r, password=:p WHERE id=:id");
                        $stmt->execute(['fn' => $full_name, 'u' => $username, 'e' => $email, 'r' => $role, 'p' => $hashed, 'id' => $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET full_name=:fn, username=:u, email=:e, role=:r WHERE id=:id");
                        $stmt->execute(['fn' => $full_name, 'u' => $username, 'e' => $email, 'r' => $role, 'id' => $id]);
                    }
                    set_flash('success', "User \"$full_name\" updated successfully!");
                    header('Location: ' . base_url('admin/users/index.php'));
                    exit();
                }
            } catch (PDOException $ex) {
                $errors[] = 'Database error: ' . $ex->getMessage();
            }
        }
    }
}

$page_title = 'Edit User Account';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-2xl mx-auto space-y-6">

    <!-- Breadcrumb -->
    <div class="flex items-center space-x-2 text-xs text-slate-500">
        <a href="<?= base_url('admin/users/index.php') ?>" class="hover:text-blue-600 font-medium transition-colors">
            <i class="fa-solid fa-users-gear mr-1"></i>User Accounts
        </a>
        <i class="fa-solid fa-chevron-right text-[9px]"></i>
        <span class="text-slate-700 font-semibold">Edit User</span>
    </div>

    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-slate-600 to-slate-800 text-white flex items-center justify-center shadow-lg font-black text-xl">
            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Edit: <?= e($user['full_name']) ?></h1>
            <p class="text-xs text-slate-500 mt-0.5">Update account details. Leave password blank to keep existing.</p>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm space-y-1">
            <div class="flex items-center space-x-2 font-bold mb-1">
                <i class="fa-solid fa-circle-exclamation text-rose-500"></i><span>Errors:</span>
            </div>
            <?php foreach ($errors as $err): ?>
                <div class="flex items-start space-x-2 text-xs"><i class="fa-solid fa-arrow-right mt-0.5 text-rose-400"></i><span><?= e($err) ?></span></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="edit.php?id=<?= $id ?>" method="POST" class="space-y-5" autocomplete="off">
            <?= csrf_field() ?>

            <!-- Role -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Account Role *</label>
                <div class="grid grid-cols-2 gap-3">
                    <?php foreach (['admin' => ['fa-user-shield', 'blue', 'Administrator'], 'accountant' => ['fa-calculator', 'amber', 'Accountant']] as $r => [$icon, $color, $label]): ?>
                    <?php $selected = (($_POST['role'] ?? $user['role']) === $r); ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="<?= $r ?>" class="peer sr-only" <?= $selected ? 'checked' : '' ?>>
                        <div class="p-3.5 rounded-xl border-2 border-slate-200 transition-all peer-checked:border-<?= $color ?>-400 peer-checked:bg-<?= $color ?>-50 flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-<?= $color ?>-100 text-<?= $color ?>-600 flex items-center justify-center">
                                <i class="fa-solid <?= $icon ?> text-sm"></i>
                            </div>
                            <span class="font-bold text-sm text-slate-800"><?= $label ?></span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Full Name -->
            <div>
                <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Full Name *</label>
                <input type="text" id="full_name" name="full_name" required value="<?= e($_POST['full_name'] ?? $user['full_name']) ?>"
                    class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Username *</label>
                    <input type="text" id="username" name="username" required value="<?= e($_POST['username'] ?? $user['username']) ?>"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email *</label>
                    <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? $user['email']) ?>"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">New Password <span class="font-normal text-slate-400">(leave blank to keep)</span></label>
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="New password (optional)" minlength="6"
                            class="w-full py-2.5 px-3.5 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <button type="button" onclick="togglePwd('password','ei1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="ei1" class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="confirm_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" minlength="6"
                            class="w-full py-2.5 px-3.5 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <button type="button" onclick="togglePwd('confirm_password','ei2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="ei2" class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <a href="<?= base_url('admin/users/index.php') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center space-x-2">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePwd(fieldId, iconId) {
    const f = document.getElementById(fieldId), i = document.getElementById(iconId);
    f.type = f.type === 'password' ? 'text' : 'password';
    i.classList.toggle('fa-eye'); i.classList.toggle('fa-eye-slash');
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
