<?php
$page_title = 'My Profile';
require_once __DIR__ . '/../includes/auth.php';
require_student();

$student_id_pk = get_logged_student_id();

// Fetch Student details
$stmt = $pdo->prepare("SELECT s.*, c.class_name, c.section, c.room_number 
    FROM students s 
    LEFT JOIN classes c ON s.class_id = c.id 
    WHERE s.id = :sid LIMIT 1");
$stmt->execute(['sid' => $student_id_pk]);
$student = $stmt->fetch();

if (!$student) {
    die("Student record not found.");
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header / Breadcrumb -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Student Profile</h1>
            <p class="text-xs text-slate-500 mt-1">Official school registration & parent details</p>
        </div>
        <a href="<?= base_url('student/dashboard.php') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
            &larr; Back to Dashboard
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <!-- Header Profile Banner -->
        <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-600 p-6 sm:p-8 text-white relative">
            <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6">
                <div class="w-24 h-24 rounded-2xl bg-white/20 backdrop-blur-md border-2 border-white/40 overflow-hidden flex items-center justify-center font-extrabold text-3xl shadow-xl">
                    <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/../assets/uploads/' . $student['photo'])): ?>
                        <img src="<?= base_url('assets/uploads/' . e($student['photo'])) ?>" alt="Photo" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="text-center sm:text-left">
                    <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md text-white font-bold text-xs rounded-full uppercase tracking-wider mb-1">
                        Class <?= e($student['class_name']) ?> (Sec <?= e($student['section']) ?>)
                    </span>
                    <h2 class="text-2xl font-extrabold"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h2>
                    <p class="text-xs text-white/80 mt-1">Admission No: <span class="font-mono font-bold"><?= e($student['admission_no']) ?></span> &bull; Roll No: <span class="font-bold"><?= e($student['roll_number'] ?? 'N/A') ?></span></p>
                </div>
            </div>
        </div>

        <!-- Detail Sections -->
        <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Personal Information -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 flex items-center">
                    <i class="fa-solid fa-user text-emerald-600 mr-2"></i> Personal Details
                </h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Full Name</span>
                        <span class="font-bold text-slate-800 text-xs"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Gender</span>
                        <span class="font-bold text-slate-800 text-xs"><?= e($student['gender'] ?? 'Not specified') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Date of Birth</span>
                        <span class="font-bold text-slate-800 text-xs"><?= !empty($student['date_of_birth']) ? date('d M Y', strtotime($student['date_of_birth'])) : 'N/A' ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Admission Date</span>
                        <span class="font-bold text-slate-800 text-xs"><?= !empty($student['admission_date']) ? date('d M Y', strtotime($student['admission_date'])) : 'N/A' ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Class & Section</span>
                        <span class="font-bold text-emerald-700 text-xs">Class <?= e($student['class_name']) ?> (<?= e($student['section']) ?>)</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Room Location</span>
                        <span class="font-bold text-slate-800 text-xs"><?= e($student['room_number'] ?? 'Room 101') ?></span>
                    </div>
                </div>
            </div>

            <!-- Parent & Guardian Information -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 flex items-center">
                    <i class="fa-solid fa-users text-indigo-600 mr-2"></i> Parent & Emergency Contacts
                </h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Father / Guardian Name</span>
                        <span class="font-bold text-slate-800 text-xs"><?= e($student['father_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Mother Name</span>
                        <span class="font-bold text-slate-800 text-xs"><?= e($student['mother_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Contact Phone</span>
                        <span class="font-bold text-slate-800 text-xs"><?= e($student['phone'] ?? 'N/A') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Email Address</span>
                        <span class="font-bold text-slate-800 text-xs"><?= e($student['email'] ?? 'N/A') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Residential Address</span>
                        <span class="font-bold text-slate-800 text-xs max-w-[200px] text-right truncate"><?= e($student['address'] ?? 'Registered Address') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 text-xs font-medium">Student Portal Login ID</span>
                        <span class="font-bold text-emerald-700 font-mono text-xs"><?= e($student['admission_no']) ?></span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
