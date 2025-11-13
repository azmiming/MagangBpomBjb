<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Badan POM')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --navy-dark: #001F3F;
            --navy-medium: #003366;
            --blue-light: #6495ED;
            --white: #FFFFFF;
        }

        .sidebar {
            background-color: var(--navy-dark);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
            width: 250px;
            z-index: 1000;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            margin-top: 70px;
        }
        .header {
            background-color: var(--navy-medium);
            color: var(--white);
            padding: 15px 20px;
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            z-index: 999;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .nav-item {
            padding: 10px 20px;
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
        }
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
            transition: all 0.2s ease;
        }
        .card-custom {
            background-color: var(--blue-light);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }
        .tree-img {
            max-width: 150px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="text-center mb-4">
            <img src="{{ asset('images/Logo BPOM Border Putih.png') }}" alt="Logo Badan POM" class="img-fluid" style="max-width: 120px; height: auto; margin-bottom: 15px;">
        </div>
        <hr class="bg-light">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'fw-bold' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
       <a href="{{ route('present.index') }}" class="nav-item {{ request()->routeIs('present.*') ? 'fw-bold' : '' }}">
    <i class="fas fa-file-powerpoint"></i> Present
</a>
       <a href="{{ route('profil.index') }}" class="nav-item {{ request()->routeIs('profil.index') ? 'fw-bold' : '' }}">
    <i class="fas fa-user"></i> History
</a>
        <a href="{{ route('report') }}" class="nav-item {{ request()->routeIs('report') ? 'fw-bold' : '' }}">
            <i class="fas fa-chart-bar"></i> Report
        </a>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="d-flex align-items-center">
            <i class="fas fa-bell text-white me-3"></i>
            
            
            <i class="fas fa-chevron-down ms-2 text-white"></i>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>