<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found | SEOFriendly</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(2deg);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .gradient-text {
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body class="h-full flex items-center justify-center p-6 text-slate-200 antialiased overflow-hidden">
    <!-- Background Gradients -->
    <div class="fixed top-0 left-0 w-full h-full -z-10 overflow-hidden">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-500/10 rounded-full blur-[120px]">
        </div>
    </div>

    <div class="max-w-2xl w-full text-center space-y-8">
        <!-- SVG Animation -->
        <div class="relative inline-block animate-float">
            <svg class="w-64 h-64 mx-auto text-indigo-500/20" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="currentColor" />
                <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-8xl font-black gradient-text tracking-tighter shadow-2xl">404</span>
            </div>
        </div>

        <div class="space-y-4">
            <h1 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Oops! Page Lost in Cyberspace</h1>
            <p class="text-slate-400 text-lg max-w-md mx-auto">
                The URL you followed might be broken, or the page has been moved. Even our SEO AI couldn't find this
                one!
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <a href="/"
                class="px-8 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-xl shadow-indigo-500/20 transition-all hover:-translate-y-1">
                Back to Home
            </a>
            <div class="glass px-6 py-3 rounded-2xl">
                <span class="text-xs font-mono text-slate-500">Error Code:</span>
                <span class="text-xs font-mono text-indigo-400 ml-2">HTTP_404_NOT_FOUND</span>
            </div>
        </div>
    </div>
</body>

</html>
