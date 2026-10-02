<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$class_id = intval($_GET['id'] ?? 0);

if (!$class_id) {
    set_flash('error', 'Invalid class ID.');
    header('Location: ' . base_url('admin/classes/index.php'));
    exit();
}

$errors = [];
$teachers = $pdo->query("SELECT id, name, subject FROM teachers WHERE status = 'Active' ORDER BY name ASC")->fetchAll();

$stmt = $pdo->prepare("SELECT c.*, t.name as current_teacher_name, t.username as current_teacher_username 
    FROM classes c 
    LEFT JOIN teachers t ON c.teacher_id = t.id
    WHERE c.id = :id LIMIT 1");
$stmt->execute(['id' => $class_id]);
$class = $stmt->fetch();

if (!$class) {
    set_flash('error', 'Class record not found.');
    header('Location: ' . base_url('admin/classes/index.php'));
    exit();
}

// Count attendance records for this class
$att_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE class_id = :class_id");
$att_count_stmt->execute(['class_id' => $class_id]);
$attendance_records_count = $att_count_stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = "Invalid security token.";
    } else {
        $class_name     = trim($_POST['class_name'] ?? '');
        $section        = trim($_POST['section'] ?? '');
        $new_teacher_id = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;
        $room_number    = trim($_POST['room_number'] ?? '');
        $academic_session = trim($_POST['academic_session'] ?? '');

        if (empty($class_name)) $errors[] = "Class name is required.";
        if (empty($section))    $errors[] = "Section is required.";

        if (empty($errors)) {
            try {
                $up_stmt = $pdo->prepare("UPDATE classes SET 
                    class_name = :class_name,
                    section = :section,
                    teacher_id = :teacher_id,
                    room_number = :room_number,
                    academic_session = :academic_session
                    WHERE id = :id");

                $up_stmt->execute([
                    'class_name'       => $class_name,
                    'section'          => $section,
                    'teacher_id'       => $new_teacher_id,
                    'room_number'      => $room_number,
                    'academic_session' => $academic_session,
                    'id'               => $class_id
                ]);

                // Fetch new teacher name for flash message
                $new_teacher_name = 'Unassigned';
                if ($new_teacher_id) {
                    $nt = $pdo->prepare("SELECT name FROM teachers WHERE id = :id LIMIT 1");
                    $nt->execute(['id' => $new_teacher_id]);
                    $nt_row = $nt->fetch();
                    $new_teacher_name = $nt_row['name'] ?? 'Unknown';
                }

                $old_name = $class['current_teacher_name'] ?? 'Unassigned';
                $teacher_changed = ($new_teacher_id != $class['teacher_id']);

                if ($teacher_changed && $new_teacher_id) {
                    set_flash('success', "Class updated! Class Teacher changed from \"{$old_name}\" to \"{$new_teacher_name}\". All {$attendance_records_count} attendance records have been automatically transferred to {$new_teacher_name}'s portal.");
                } else {
                    set_flash('success', 'Class updated successfully!');
                }

                header('Location: ' . base_url('admin/classes/index.php'));
                exit();
            } catch (PDOException $e) {
                $errors[] = "Database update error: " . $e->getMessage();
            }
        }
    }
}

$page_title = 'Edit Class';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6 max-w-2xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Class</h1>
            <p class="text-sm text-slate-500 mt-1">Modify class settings for <span class="font-semibold text-slate-800"><?= e($class['class_name']) ?> (Section <?= e($class['section']) ?>)</span>.</p>
        </div>
        <a href="index.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to List</span>
        </a>
    </div>

    <!-- Current Teacher Transfer Info Banner -->
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-5 space-y-3">
        <div class="flex items-start space-x-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-info text-lg"></i>
            </div>
            <div class="text-sm space-y-1">
                <p class="font-bold text-amber-900">Class Teacher Data Transfer Notice</p>
                <p class="text-amber-800 text-xs leading-relaxed">
                    Agar aap is class ka <strong>Class Teacher badal dete hain</strong>, toh <strong><?= $attendance_records_count ?> attendance records</strong> automatically naye class teacher ke portal me dikhne lagenge — kyunki data class ke saath linked hai, teacher ke saath nahi.
                </p>
            </div>
        </div>

        <!-- Current Teacher Info -->
        <div class="flex items-center justify-between bg-white/80 rounded-xl border border-amber-100 px-4 py-2.5 text-xs">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-chalkboard-user text-amber-600"></i>
                <span class="text-slate-500 font-medium">Current Class Teacher:</span>
                <span class="font-extrabold text-slate-900">
                    <?= $class['current_teacher_name'] ? e($class['current_teacher_name']) : 'Not Assigned' ?>
                </span>
                <?php if ($class['current_teacher_username']): ?>
                    <span class="font-mono text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                        <?= e($class['current_teacher_username']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <div>
                <span class="text-emerald-600 font-semibold flex items-center space-x-1">
                    <i class="fa-solid fa-database text-[11px]"></i>
                    <span><?= $attendance_records_count ?> Attendance Records</span>
                </span>
            </div>
        </div>
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

        <form action="edit.php?id=<?= $class_id ?>" method="POST" class="space-y-5" id="class-edit-form">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Class Name *</label>
                    <input type="text" name="class_name" required value="<?= e($class['class_name']) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Section *</label>
                    <input type="text" name="section" required value="<?= e($class['section']) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <!-- Class Teacher Dropdown with Change Warning -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Class Teacher
                        <span class="text-amber-600 ml-1 font-normal text-[11px] normal-case">(Changing this will transfer all class data to new teacher's portal)</span>
                    </label>
                    <select name="teacher_id" id="teacher_select" data-original="<?= $class['teacher_id'] ?? '' ?>" 
                        onchange="checkTeacherChange(this)"
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        <option value="">-- Unassigned --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($class['teacher_id'] == $t['id']) ? 'selected' : '' ?>>
                                <?= e($t['name']) ?> (<?= e($t['subject']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Transfer warning (initially hidden) -->
                    <div id="teacher-change-alert" class="hidden mt-3 p-3.5 bg-orange-50 border border-orange-200 rounded-xl text-xs font-medium text-orange-800 flex items-start space-x-2">
                        <i class="fa-solid fa-triangle-exclamation text-orange-500 mt-0.5 shrink-0"></i>
                        <div>
                            <strong>Teacher change detected!</strong> Purana class teacher ka access remove ho jayega. Naye teacher ke portal me is class ke saare <strong><?= $attendance_records_count ?> attendance records aur students automatically</strong> transfer ho jayenge.
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Room Number *</label>
                    <input type="text" name="room_number" required value="<?= e($class['room_number']) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Academic Session</label>
                    <input type="text" name="academic_session" value="<?= e($class['academic_session']) ?>" 
                        class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" id="submit-btn" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">
                    Update Class
                </button>
            </div>
        </form>

    </div>

</div>

<script>
function checkTeacherChange(select) {
    const originalVal = select.getAttribute('data-original');
    const currentVal = select.value;
    const alertBox = document.getElementById('teacher-change-alert');
    const submitBtn = document.getElementById('submit-btn');

    if (currentVal !== originalVal && currentVal !== '') {
        alertBox.classList.remove('hidden');
        submitBtn.textContent = 'Confirm & Transfer Class Teacher';
        submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        submitBtn.classList.add('bg-orange-600', 'hover:bg-orange-700');
    } else {
        alertBox.classList.add('hidden');
        submitBtn.textContent = 'Update Class';
        submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        submitBtn.classList.remove('bg-orange-600', 'hover:bg-orange-700');
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
