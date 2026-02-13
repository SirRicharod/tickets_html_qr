<?php 
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        :root {
            /* Light mode colors */
            --bg-primary: #f5f5f7;
            --bg-secondary: #ffffff;
            --bg-tertiary: #fafafa;
            --text-primary: #1d1d1f;
            --text-secondary: #6e6e73;
            --border-color: rgba(0, 0, 0, 0.1);
            --shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 32px rgba(0, 0, 0, 0.12);
            --nav-bg: rgba(255, 255, 255, 0.8);
            --gradient-start: #667eea;
            --gradient-end: #764ba2;
            --input-bg: #ffffff;
            --input-border: rgba(0, 0, 0, 0.15);
        }

        [data-theme="dark"] {
            /* Dark mode colors - Apple style */
            --bg-primary: #000000;
            --bg-secondary: #1c1c1e;
            --bg-tertiary: #2c2c2e;
            --text-primary: #f5f5f7;
            --text-secondary: #a1a1a6;
            --border-color: rgba(255, 255, 255, 0.15);
            --shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
            --shadow-hover: 0 8px 32px rgba(0, 0, 0, 0.6);
            --nav-bg: rgba(28, 28, 30, 0.8);
            --gradient-start: #667eea;
            --gradient-end: #764ba2;
            --input-bg: #1c1c1e;
            --input-border: rgba(255, 255, 255, 0.2);
        }

        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .navbar {
            background: var(--nav-bg) !important;
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            border-bottom: 1px solid var(--border-color);
            border-radius: 0 0 20px 20px !important;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .navbar-brand {
            color: var(--text-primary) !important;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .nav-link {
            color: var(--text-secondary) !important;
            border-radius: 12px;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
            font-weight: 500;
        }

        .nav-link:hover {
            color: var(--text-primary) !important;
            background: var(--bg-tertiary);
        }

        .container {
            background: var(--bg-secondary);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 2.5rem;
            border: 1px solid var(--border-color);
        }

        .container:hover {
            box-shadow: var(--shadow-hover);
        }

        h1, h2, h3, h4, h5, h6 {
            color: var(--text-primary);
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .form-control, .form-select {
            background: var(--input-bg);
            color: var(--text-primary);
            border: 1.5px solid var(--input-border);
            border-radius: 12px;
            padding: 0.875rem 1rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background: var(--input-bg);
            color: var(--text-primary);
            border-color: var(--gradient-start);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--gradient-end) 0%, var(--gradient-start) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary, .btn-danger, .btn-success, .btn-warning {
            border-radius: 12px;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            border: none;
        }

        .alert {
            border-radius: 16px;
            border: 1px solid var(--border-color);
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .alert-danger {
            background: rgba(255, 59, 48, 0.1);
            border-color: rgba(255, 59, 48, 0.3);
            color: #ff3b30;
        }

        [data-theme="dark"] .alert-danger {
            color: #ff6961;
        }

        .alert-success {
            background: rgba(52, 199, 89, 0.1);
            border-color: rgba(52, 199, 89, 0.3);
            color: #34c759;
        }

        [data-theme="dark"] .alert-success {
            color: #32d74b;
        }

        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .table {
            color: var(--text-primary);
            background: var(--bg-secondary);
            border-radius: 16px;
            overflow: hidden;
        }

        .table thead {
            background: var(--bg-tertiary);
        }

        .table tbody tr {
            border-bottom: 1px solid var(--border-color);
        }

        img.imgoffer {
            border-radius: 20px;
            width: 100%;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }

        /* Dark mode toggle button */
        .theme-toggle {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-hover);
        }

        .theme-toggle:active {
            transform: scale(0.95);
        }

        .theme-toggle svg {
            width: 24px;
            height: 24px;
            fill: var(--text-primary);
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 12px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-primary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--text-secondary);
            border-radius: 6px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-primary);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Ticket System</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <div class="navbar-nav">
                        <a class="nav-link" href="index.php">Home</a>
                        <a class="nav-link" href="about.php">About</a>
                        <a class="nav-link" href="order.php">Order</a>
                        <a class="nav-link" href="contact.php">Contact</a>
                        <a class="nav-link" href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <!-- Dark Mode Toggle Button -->
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
        <svg id="sunIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M12 18a6 6 0 1 1 0-12 6 6 0 0 1 0 12zm0-2a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM11 1h2v3h-2V1zm0 19h2v3h-2v-3zM3.515 4.929l1.414-1.414L7.05 5.636 5.636 7.05 3.515 4.93zM16.95 18.364l1.414-1.414 2.121 2.121-1.414 1.414-2.121-2.121zm2.121-14.85l1.414 1.415-2.121 2.121-1.414-1.414 2.121-2.121zM5.636 16.95l1.414 1.414-2.121 2.121-1.414-1.414 2.121-2.121zM23 11v2h-3v-2h3zM4 11v2H1v-2h3z"/>
        </svg>
        <svg id="moonIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="display: none;">
            <path d="M10 7a7 7 0 0 0 12 4.9v.1c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2h.1A6.979 6.979 0 0 0 10 7zm-6 5a8 8 0 0 0 15.062 3.762A9 9 0 0 1 8.238 4.938 7.999 7.999 0 0 0 4 12z"/>
        </svg>
    </button>

    <script>
        // Dark mode toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');
        const html = document.documentElement;

        // Check for saved theme preference or default to light mode
        const currentTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', currentTheme);
        updateIcons(currentTheme);

        themeToggle.addEventListener('click', () => {
            const theme = html.getAttribute('data-theme');
            const newTheme = theme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcons(newTheme);
        });

        function updateIcons(theme) {
            if (theme === 'dark') {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            } else {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            }
        }
    </script>
