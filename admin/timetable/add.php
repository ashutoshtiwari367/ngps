<?php
$page_title = 'Add Timetable Slot';
require_once __DIR__ . '/../../includes/header.php';
require_admin();

$class_id = (int)($_GET['class_id'] ?? 0);
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

if (!$class_id && !empty($classes)) {
    $class_id = $classes[0]['id'];
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = (int)($_POST['class_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $teacher_id = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;
    $day_of_week = trim($_POST['day_of_week'] ?? 'Monday');
    $period_name = trim($_POST['period_name'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $room_number = trim($_POST['room_number'] ?? 'Room 101');
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf)) {
        $error = 'Invalid security token.';
    } elseif (empty($class_id) || empty($subject_id) || empty($period_name) || empty($start_time) || empty($end_time)) {
        $error = 'Please fill in Class, Subject, Period Name, Start Time, and End Time.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO timetables (class_id, subject_id, teacher_id, day_of_week, period_name, start_time, end_time, room_number)
                VALUES (:cid, :subid, :tid, :dow, :pname, :stime, :etime, :room)");
            $stmt->execute([
                'cid' => $class_id,
                'subid' => $subject_id,
                'tid' => $teacher_id,
                'dow' => $day_of_week,
                'pname' => $period_name,
                'stime' => $start_time,
                'etime' => $end_time,
                'room' => $room_number
            ]);
            set_flash('success', 'Timetable slot created successfully!');
            header('Location: ' . base_url('admin/timetable/index.php?class_id=' . $class_id));
            exit();
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch subjects and teachers for the selected class
$subjects_stmt = $pdo->prepare("SELECT * FROM subjects WHERE class_id = :cid ORDER BY subject_name ASC");
$subjects_stmt->execute(['cid' => $class_id]);
$subjects = $subjects_stmt->fetchAll();

$teachers = $pdo->query("SELECT * FROM teachers WHERE status = 'Active' ORDER BY name ASC")->fetchAll();
?>

<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Add Timetable Slot</h1>
            <p class="text-xs text-slate-500 mt-1">Schedule a period for a specific class, subject, and time.</p>
        </div>
        <a href="<?= base_url('admin/timetable/index.php?class_id=' . $class_id) ?>" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">
            &larr; Back to Timetables
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="class_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Class & Section *</label>
                    <select id="class_id" name="class_id" required onchange="window.location.href='add.php?class_id='+this.value"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $class_id ? 'selected' : '' ?>><?= e($c['class_name']) ?> (Sec <?= e($c['section']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="day_of_week" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Day of Week *</label>
                    <select id="day_of_week" name="day_of_week" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="subject_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Subject *</label>
                    <select id="subject_id" name="subject_id" required
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        <?php if (empty($subjects)): ?>
                            <option value="">No subjects found for this class</option>
                        <?php else: ?>
                            <?php foreach ($subjects as $sb): ?>
                                <option value="<?= $sb['id'] ?>"><?= e($sb['subject_name']) ?> (<?= e($sb['subject_code']) ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label for="teacher_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Teacher (Optional)</label>
                    <select id="teacher_id" name="teacher_id"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        <option value="">-- Unassigned --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (<?= e($t['subject']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="period_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Period Name *</label>
                    <input type="text" id="period_name" name="period_name" required value="Period 1" placeholder="e.g. Period 1"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                </div>

                <div>
                    <label for="start_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Start Time *</label>
                    <input type="time" id="start_time" name="start_time" required value="08:30"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                </div>

                <div>
                    <label for="end_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">End Time *</label>
                    <input type="time" id="end_time" name="end_time" required value="09:15"
                        class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                </div>
            </div>

            <div>
                <label for="room_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Room / Lab Number</label>
                <input type="text" id="room_number" name="room_number" value="Room 101" placeholder="e.g. Science Lab 2"
                    class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3">
                <a href="<?= base_url('admin/timetable/index.php?class_id=' . $class_id) ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">Save Slot</button>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
