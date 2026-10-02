<?php
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$student_id = intval($_GET['id'] ?? 0);

if (!$student_id) {
    set_flash('error', 'Invalid student ID.');
    header('Location: ' . base_url('admin/students/index.php'));
    exit();
}

$errors = [];
$classes = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC, section ASC")->fetchAll();

// Fetch existing student details
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $student_id]);
$student = $stmt->fetch();

if (!$student) {
    set_flash('error', 'Student record not found.');
    header('Location: ' . base_url('admin/students/index.php'));
    exit();
}

// Process POST Form Submission BEFORE HTML Output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = "Invalid security token.";
    } else {
        $admission_no = trim($_POST['admission_no'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $father_name = trim($_POST['father_name'] ?? '');
        $mother_name = trim($_POST['mother_name'] ?? '');
        $dob = trim($_POST['dob'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $class_id = intval($_POST['class_id'] ?? 0);
        $section = trim($_POST['section'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $admission_date = trim($_POST['admission_date'] ?? '');
        $status = trim($_POST['status'] ?? 'Active');

        if (empty($admission_no)) $errors[] = "Admission number is required.";
        if (empty($first_name)) $errors[] = "First name is required.";
        if (empty($last_name)) $errors[] = "Last name is required.";
        if (empty($class_id)) $errors[] = "Please select a class.";

        // Keep old photo by default
        $photo_name = $student['photo'];

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['photo']['tmp_name'];
            $file_orig = $_FILES['photo']['name'];
            $file_ext = strtolower(pathinfo($file_orig, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_exts)) {
                $new_photo_name = "stu_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
                $upload_dir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                if (move_uploaded_file($file_tmp, $upload_dir . $new_photo_name)) {
                    if (!empty($student['photo']) && file_exists($upload_dir . $student['photo'])) {
                        @unlink($upload_dir . $student['photo']);
                    }
                    $photo_name = $new_photo_name;
                }
            } else {
                $errors[] = "Invalid photo format. Only JPG, PNG, and WEBP images are allowed.";
            }
        }

        if (empty($errors)) {
            try {
                $update_stmt = $pdo->prepare("UPDATE students SET 
                    admission_no = :admission_no,
                    first_name = :first_name,
                    last_name = :last_name,
                    father_name = :father_name,
                    mother_name = :mother_name,
                    dob = :dob,
                    gender = :gender,
                    class_id = :class_id,
                    section = :section,
                    mobile = :mobile,
                    email = :email,
                    address = :address,
                    admission_date = :admission_date,
                    photo = :photo,
                    status = :status
                    WHERE id = :id");

                $update_stmt->execute([
                    'admission_no'   => $admission_no,
                    'first_name'     => $first_name,
                    'last_name'      => $last_name,
                    'father_name'    => $father_name,
                    'mother_name'    => $mother_name,
                    'dob'            => $dob,
                    'gender'         => $gender,
                    'class_id'       => $class_id,
                    'section'        => $section,
                    'mobile'         => $mobile,
                    'email'          => $email,
                    'address'        => $address,
                    'admission_date' => $admission_date,
                    'photo'          => $photo_name,
                    'status'         => $status,
                    'id'             => $student_id
                ]);

                set_flash('success', 'Student details updated successfully!');
                header('Location: ' . base_url('admin/students/index.php'));
                exit();
            } catch (PDOException $e) {
                $errors[] = "Database update failed: " . $e->getMessage();
            }
        }
    }
}

// Now include header for HTML view rendering
$page_title = 'Edit Student';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Student</h1>
            <p class="text-sm text-slate-500 mt-1">Update profile and academic details for <span class="font-semibold text-slate-800"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></span>.</p>
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

        <form action="edit.php?id=<?= $student_id ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Section 1: Academic Details -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-blue-600 mb-4 pb-2 border-b border-slate-100 flex items-center space-x-2">
                    <i class="fa-solid fa-id-card"></i>
                    <span>1. Admission & Academic Details</span>
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Admission No *</label>
                        <input type="text" name="admission_no" required value="<?= e($student['admission_no']) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Class *</label>
                        <select name="class_id" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($student['class_id'] == $c['id']) ? 'selected' : '' ?>>
                                    <?= e($c['class_name']) ?> (Section <?= e($c['section']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Section *</label>
                        <input type="text" name="section" required value="<?= e($student['section']) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Admission Date</label>
                        <input type="date" name="admission_date" value="<?= e($student['admission_date']) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status</label>
                        <select name="status" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                            <option value="Active" <?= ($student['status'] === 'Active') ? 'selected' : '' ?>>Active</option>
                            <option value="Inactive" <?= ($student['status'] === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                            <option value="Graduated" <?= ($student['status'] === 'Graduated') ? 'selected' : '' ?>>Graduated</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Personal Information -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-blue-600 mb-4 pb-2 border-b border-slate-100 flex items-center space-x-2">
                    <i class="fa-solid fa-user"></i>
                    <span>2. Personal Details</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">First Name *</label>
                        <input type="text" name="first_name" required value="<?= e($student['first_name']) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Last Name *</label>
                        <input type="text" name="last_name" required value="<?= e($student['last_name']) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Gender *</label>
                        <select name="gender" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                            <option value="Male" <?= ($student['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($student['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($student['gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Date of Birth *</label>
                        <input type="date" name="dob" required value="<?= e($student['dob']) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Father's Name</label>
                        <input type="text" name="father_name" value="<?= e($student['father_name']) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mother's Name</label>
                        <input type="text" name="mother_name" value="<?= e($student['mother_name']) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>
                </div>
            </div>

            <!-- Section 3: Contact & Photo -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-blue-600 mb-4 pb-2 border-b border-slate-100 flex items-center space-x-2">
                    <i class="fa-solid fa-address-book"></i>
                    <span>3. Contact & Photo</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mobile Number *</label>
                        <input type="text" name="mobile" required value="<?= e($student['mobile']) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
                        <input type="email" name="email" value="<?= e($student['email']) ?>" 
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Replace Photo</label>
                        <input type="file" name="photo" accept="image/*" onchange="previewImage(this, 'photo-preview')"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Residential Address</label>
                        <textarea name="address" rows="2"
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"><?= e($student['address']) ?></textarea>
                    </div>

                    <div class="flex items-center space-x-3">
                        <div class="w-16 h-16 rounded-xl border border-slate-200 overflow-hidden bg-slate-100">
                            <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/../../assets/uploads/' . $student['photo'])): ?>
                                <img id="photo-preview" src="<?= base_url('assets/uploads/' . e($student['photo'])) ?>" alt="Photo" class="w-full h-full object-cover">
                            <?php else: ?>
                                <img id="photo-preview" src="#" alt="Photo Preview" class="w-full h-full object-cover hidden">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-500/20 transition-all">
                    Update Student Profile
                </button>
            </div>
        </form>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
