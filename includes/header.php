<?php
require_once __DIR__ . '/auth.php';
require_login();
$page_title = $page_title ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - Next Generation Public School</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Custom scrollbars and transition tweaks */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; color: black !important; }
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex flex-col min-h-screen">
    <div class="flex h-screen overflow-hidden bg-slate-50">
        <!-- Sidebar Navigation -->
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <!-- Main Content Area Wrapper -->
        <div class="flex flex-col flex-1 w-full overflow-y-auto min-h-screen">
            <!-- Top Navbar -->
            <?php include __DIR__ . '/navbar.php'; ?>
            
            <!-- Page Content Body -->
            <main class="flex-1 p-4 md:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <!-- Flash Notification Banner -->
                <?php $flash = get_flash(); if ($flash): ?>
                    <div id="flash-alert" class="mb-6 p-4 rounded-xl shadow-sm flex items-center justify-between border transition-all duration-300 <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($flash['type'] === 'error' ? 'bg-rose-50 text-rose-800 border-rose-200' : 'bg-amber-50 text-amber-800 border-amber-200') ?>">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check text-emerald-600' : ($flash['type'] === 'error' ? 'fa-circle-xmark text-rose-600' : 'fa-triangle-exclamation text-amber-600') ?> text-lg"></i>
                            <span class="font-medium text-sm md:text-base"><?= e($flash['text']) ?></span>
                        </div>
                        <button onclick="document.getElementById('flash-alert').remove()" class="text-slate-400 hover:text-slate-600 transition-colors p-1">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                <?php endif; ?>
