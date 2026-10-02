<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

header('Location: ' . base_url('admin/dashboard.php'));
exit();
