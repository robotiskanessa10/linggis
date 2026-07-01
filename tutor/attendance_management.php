<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Linggis Teacher Portal - Attendance Management</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#1978e5",
                        "background-light": "#f6f7f8",
                        "background-dark": "#111821",
                    },
                    fontFamily: {
                        "display": ["Inter"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .active-nav {
            background-color: rgba(25, 120, 229, 0.1);
            color: #1978e5;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
<div class="flex h-screen overflow-hidden">
<!-- Sidebar -->
<aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between shrink-0">
<div class="p-6">
<div class="flex items-center gap-3 mb-10">
<div class="h-10 w-10 bg-primary rounded-lg flex items-center justify-center text-white">
<span class="material-symbols-outlined text-2xl">school</span>
</div>
<div>
<h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white leading-none">Linggis</h1>
<p class="text-xs text-slate-500 font-medium">Teacher Portal</p>
</div>
</div>
<nav class="space-y-1">
<a class="flex items-center gap-3 px-3 py-2.5 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors group" href="dashboard_tutor.php">
<span class="material-symbols-outlined text-[22px]">dashboard</span>
<span class="text-sm font-semibold">Dashboard</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 active-nav rounded-lg group" href="#">
<span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">calendar_today</span>
<span class="text-sm font-semibold">Attendance</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors group" href="#">
<span class="material-symbols-outlined text-[22px]">sticky_note_2</span>
<span class="text-sm font-semibold">Learning Notes</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors group" href="#">
<span class="material-symbols-outlined text-[22px]">grade</span>
<span class="text-sm font-semibold">Grades</span>
</a>
</nav>
</div>
<div class="p-6 border-t border-slate-100 dark:border-slate-800">
<nav class="space-y-1">
<a class="flex items-center gap-3 px-3 py-2.5 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors group" href="#">
<span class="material-symbols-outlined text-[22px]">settings</span>
<span class="text-sm font-semibold">Settings</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors group" href="#">
<span class="material-symbols-outlined text-[22px]">logout</span>
<span class="text-sm font-semibold">Logout</span>
</a>
</nav>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 flex flex-col overflow-y-auto">
<!-- Header Area -->
<header class="px-8 pt-8 pb-4">
<div class="flex justify-between items-start mb-8">
<div>
<h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Attendance</h2>
<p class="text-slate-500 mt-1">Manage student presence for your classes.</p>
</div>
<div class="flex gap-3">
<button class="flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
<span class="material-symbols-outlined text-lg">download</span>
                            Export Report
                        </button>
<button class="flex items-center gap-2 px-6 py-2 bg-primary rounded-lg text-sm font-bold text-white shadow-lg shadow-primary/20 hover:bg-primary/90 transition-colors">
<span class="material-symbols-outlined text-lg">save</span>
                            Save Attendance
                        </button>
</div>
</div>
<!-- Filters -->
<div class="flex flex-wrap gap-4 items-end bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm mb-6">
<div class="w-72">
<label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Select Class</label>
<div class="relative">
<select class="w-full appearance-none bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 text-sm font-medium focus:ring-primary focus:border-primary">
<option>Mathematics - Class 10A</option>
<option>Mathematics - Class 11B</option>
<option>Advanced Calculus - Room 302</option>
</select>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
</div>
</div>
<div class="w-64">
<label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Date</label>
<div class="relative">
<input class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 text-sm font-medium focus:ring-primary focus:border-primary" type="text" value="October 26, 2023"/>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">calendar_month</span>
</div>
</div>
<div class="ml-auto flex items-center gap-4 h-[46px]">
<div class="flex items-center gap-2 text-sm">
<span class="w-3 h-3 rounded-full bg-green-500"></span>
<span class="text-slate-600 dark:text-slate-400 font-medium">24 Present</span>
</div>
<div class="flex items-center gap-2 text-sm">
<span class="w-3 h-3 rounded-full bg-red-500"></span>
<span class="text-slate-600 dark:text-slate-400 font-medium">2 Absent</span>
</div>
</div>
</div>
</header>
<!-- Table Content -->
<div class="px-8 pb-12">
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-bottom border-slate-200 dark:border-slate-800">
<th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Student Name</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ID Number</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Session Date</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Attendance Status</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100 dark:divide-slate-800">
<!-- Row 1 -->
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center font-bold text-xs text-slate-500">AJ</div>
<span class="font-semibold text-slate-900 dark:text-slate-100">Alice Johnson</span>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 font-mono">STU001</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">Oct 26, 2023</td>
<td class="px-6 py-4">
<div class="flex justify-end gap-1">
<button class="px-3 py-1.5 rounded bg-green-500 text-white text-[11px] font-bold uppercase tracking-wider">Present</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-red-500/10 hover:text-red-500 transition-colors">Absent</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-amber-500/10 hover:text-amber-500 transition-colors">Late</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-slate-500/10 hover:text-slate-400 transition-colors">Excused</button>
</div>
</td>
</tr>
<!-- Row 2 -->
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center font-bold text-xs text-slate-500">BS</div>
<span class="font-semibold text-slate-900 dark:text-slate-100">Bob Smith</span>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 font-mono">STU002</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">Oct 26, 2023</td>
<td class="px-6 py-4">
<div class="flex justify-end gap-1">
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-green-500/10 hover:text-green-500 transition-colors">Present</button>
<button class="px-3 py-1.5 rounded bg-red-500 text-white text-[11px] font-bold uppercase tracking-wider">Absent</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-amber-500/10 hover:text-amber-500 transition-colors">Late</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-slate-500/10 hover:text-slate-400 transition-colors">Excused</button>
</div>
</td>
</tr>
<!-- Row 3 -->
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center font-bold text-xs text-slate-500">CB</div>
<span class="font-semibold text-slate-900 dark:text-slate-100">Charlie Brown</span>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 font-mono">STU003</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">Oct 26, 2023</td>
<td class="px-6 py-4">
<div class="flex justify-end gap-1">
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-green-500/10 hover:text-green-500 transition-colors">Present</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-red-500/10 hover:text-red-500 transition-colors">Absent</button>
<button class="px-3 py-1.5 rounded bg-amber-500 text-white text-[11px] font-bold uppercase tracking-wider">Late</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-slate-500/10 hover:text-slate-400 transition-colors">Excused</button>
</div>
</td>
</tr>
<!-- Row 4 -->
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center font-bold text-xs text-slate-500">DP</div>
<span class="font-semibold text-slate-900 dark:text-slate-100">Diana Prince</span>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 font-mono">STU004</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">Oct 26, 2023</td>
<td class="px-6 py-4">
<div class="flex justify-end gap-1">
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-green-500/10 hover:text-green-500 transition-colors">Present</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-red-500/10 hover:text-red-500 transition-colors">Absent</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-amber-500/10 hover:text-amber-500 transition-colors">Late</button>
<button class="px-3 py-1.5 rounded bg-slate-400 text-white text-[11px] font-bold uppercase tracking-wider">Excused</button>
</div>
</td>
</tr>
<!-- Row 5 -->
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center font-bold text-xs text-slate-500">EN</div>
<span class="font-semibold text-slate-900 dark:text-slate-100">Edward Norton</span>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 font-mono">STU005</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">Oct 26, 2023</td>
<td class="px-6 py-4">
<div class="flex justify-end gap-1">
<button class="px-3 py-1.5 rounded bg-green-500 text-white text-[11px] font-bold uppercase tracking-wider">Present</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-red-500/10 hover:text-red-500 transition-colors">Absent</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-amber-500/10 hover:text-amber-500 transition-colors">Late</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-slate-500/10 hover:text-slate-400 transition-colors">Excused</button>
</div>
</td>
</tr>
<!-- Row 6 -->
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center font-bold text-xs text-slate-500">FW</div>
<span class="font-semibold text-slate-900 dark:text-slate-100">Fiona Watson</span>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 font-mono">STU006</td>
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">Oct 26, 2023</td>
<td class="px-6 py-4">
<div class="flex justify-end gap-1">
<button class="px-3 py-1.5 rounded bg-green-500 text-white text-[11px] font-bold uppercase tracking-wider">Present</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-red-500/10 hover:text-red-500 transition-colors">Absent</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-amber-500/10 hover:text-amber-500 transition-colors">Late</button>
<button class="px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 text-[11px] font-bold uppercase tracking-wider hover:bg-slate-500/10 hover:text-slate-400 transition-colors">Excused</button>
</div>
</td>
</tr>
</tbody>
</table>
<div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 flex items-center justify-between border-t border-slate-200 dark:border-slate-800">
<p class="text-sm text-slate-500 font-medium">Showing 6 of 26 students</p>
<div class="flex gap-2">
<button class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-slate-400 cursor-not-allowed">
<span class="material-symbols-outlined">chevron_left</span>
</button>
<button class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-slate-600 hover:bg-slate-50">
<span class="material-symbols-outlined">chevron_right</span>
</button>
</div>
</div>
</div>
</div>
</main>
</div>
</body></html>