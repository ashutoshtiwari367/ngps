<?php
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: ' . base_url('admin/dashboard.php'));
    } elseif (is_teacher()) {
        header('Location: ' . base_url('teacher/dashboard.php'));
    } else {
        header('Location: ' . base_url('student/dashboard.php'));
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // 1. Check in `users` table (Admin)
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'] ?: 'admin';

                if ($_SESSION['role'] === 'accountant') {
                    set_flash('success', 'Welcome to Accounts Department, ' . $user['full_name'] . '!');
                    header('Location: ' . base_url('admin/accounts/index.php'));
                } else {
                    set_flash('success', 'Welcome back, Administrator ' . $user['full_name'] . '!');
                    header('Location: ' . base_url('admin/dashboard.php'));
                }
                exit();
            }

            // 2. Check in `teachers` table (Teacher)
            $tch_stmt = $pdo->prepare("SELECT * FROM teachers WHERE (username = :u1 OR teacher_id = :u2) AND status = 'Active' LIMIT 1");
            $tch_stmt->execute([
                'u1' => $username,
                'u2' => $username
            ]);
            $teacher = $tch_stmt->fetch();

            if ($teacher && !empty($teacher['password']) && password_verify($password, $teacher['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = 'tch_' . $teacher['id'];
                $_SESSION['teacher_id_pk'] = $teacher['id'];
                $_SESSION['username'] = $teacher['username'] ?: $teacher['teacher_id'];
                $_SESSION['full_name'] = $teacher['name'];
                $_SESSION['email'] = $teacher['email'];
                $_SESSION['role'] = 'teacher';

                set_flash('success', 'Welcome to Teacher Portal, ' . $teacher['name'] . '!');
                header('Location: ' . base_url('teacher/dashboard.php'));
                exit();
            }

            // 3. Check in `students` table (Student / Parent)
            $stu_stmt = $pdo->prepare("SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE (s.admission_no = :u1 OR s.email = :u2) AND s.status = 'Active' LIMIT 1");
            $stu_stmt->execute([
                'u1' => $username,
                'u2' => $username
            ]);
            $student = $stu_stmt->fetch();

            if ($student && !empty($student['password']) && password_verify($password, $student['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = 'stu_' . $student['id'];
                $_SESSION['student_id_pk'] = $student['id'];
                $_SESSION['username'] = $student['admission_no'];
                $_SESSION['full_name'] = $student['first_name'] . ' ' . $student['last_name'];
                $_SESSION['email'] = $student['email'];
                $_SESSION['class_name'] = $student['class_name'] . ' (' . $student['section'] . ')';
                $_SESSION['role'] = 'student';

                set_flash('success', 'Welcome to Student & Parent Portal, ' . $_SESSION['full_name'] . '!');
                header('Location: ' . base_url('student/dashboard.php'));
                exit();
            }

            $error = 'Invalid username/ID or password.';
        } catch (PDOException $e) {
            $error = 'Database authentication error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Sign In - Next Generation Public School</title>
    
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        
        <!-- Header / Logo -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white shadow-xl shadow-blue-500/30 mb-4 hover:scale-105 transition-transform">
                <i class="fa-solid fa-graduation-cap text-3xl"></i>
            </a>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Next Generation Public School</h1>
            <p class="text-cyan-400 text-xs font-semibold uppercase tracking-widest mt-1">Smart Academic Portal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl p-6 sm:p-8 border border-slate-100/20">
            
            <div class="mb-6">
                <h2 class="text-xl font-bold text-slate-900">Sign In to Portal</h2>
                <p class="text-xs text-slate-500 mt-1">Enter your credentials to access Admin, Teacher, or Student portal.</p>
            </div>

            <!-- Error Banner -->
            <?php if (!empty($error)): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-center space-x-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Logout Notice -->
            <?php if (isset($_GET['logout'])): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium flex items-center space-x-2">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                    <span>You have been successfully logged out.</span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="login.php" method="POST" class="space-y-5">
                <?= csrf_field() ?>

                <!-- Username / ID Input -->
                <div>
                    <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Username / Admission No / Teacher ID</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-user text-sm"></i>
                        </div>
                        <input type="text" id="username" name="username" required value="admin"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all placeholder-slate-400"
                            placeholder="Enter Username, Admission No, or ID">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input type="password" id="password" name="password" required value="admin123"
                            class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all placeholder-slate-400"
                            placeholder="Enter password">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <i id="eye-icon" class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Default Credentials Quick Banner -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2 text-xs text-slate-800">
                    <div class="flex items-center justify-between">
                        <span class="font-bold flex items-center space-x-1.5 text-blue-700">
                            <i class="fa-solid fa-user-shield text-blue-600"></i>
                            <span>Admin:</span>
                        </span>
                        <button type="button" onclick="setLogin('admin', 'admin123')" class="font-mono bg-blue-100 hover:bg-blue-200 px-2 py-0.5 rounded text-blue-800 font-bold transition-colors">
                            admin / admin123
                        </button>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                        <span class="font-bold flex items-center space-x-1.5 text-amber-700">
                            <i class="fa-solid fa-calculator text-amber-600"></i>
                            <span>Accounts Dept:</span>
                        </span>
                        <button type="button" onclick="setLogin('accountant', 'account123')" class="font-mono bg-amber-100 hover:bg-amber-200 px-2 py-0.5 rounded text-amber-800 font-bold transition-colors">
                            accountant / account123
                        </button>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                        <span class="font-bold flex items-center space-x-1.5 text-indigo-700">
                            <i class="fa-solid fa-chalkboard-user text-indigo-600"></i>
                            <span>Teacher:</span>
                        </span>
                        <button type="button" onclick="setLogin('rajesh', 'teacher123')" class="font-mono bg-indigo-100 hover:bg-indigo-200 px-2 py-0.5 rounded text-indigo-800 font-bold transition-colors">
                            rajesh / teacher123
                        </button>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                        <span class="font-bold flex items-center space-x-1.5 text-emerald-700">
                            <i class="fa-solid fa-user-graduate text-emerald-600"></i>
                            <span>Student / Parent:</span>
                        </span>
                        <button type="button" onclick="setLogin('STU-2026-001', 'student123')" class="font-mono bg-emerald-100 hover:bg-emerald-200 px-2 py-0.5 rounded text-emerald-800 font-bold transition-colors">
                            STU-2026-001 / student123
                        </button>
                    </div>
                </div>


                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 transition-all duration-200 flex items-center justify-center space-x-2 text-sm">
                    <span>Sign In to Portal</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

        </div>

        <p class="text-center text-xs text-slate-500 mt-6">&copy; <?= date('Y') ?> Next Generation Public School. All rights reserved.</p>

    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        function setLogin(u, p) {
            document.getElementById('username').value = u;
            document.getElementById('password').value = p;
        }
    </script>
</body>
</html>
