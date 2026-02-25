<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Internal Server Error | SEOFriendly</title>
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

        @keyframes pulse {

            0%,
            100% {
                opacity: 0.5;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.05);
            }
        }

        .animate-pulse-slow {
            animation: pulse 4s ease-in-out infinite;
        }

        .gradient-text-red {
            background: linear-gradient(135deg, #f87171 0%, #fb923c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body class="h-full flex items-center justify-center p-6 text-slate-200 antialiased overflow-hidden">
    <!-- Background Gradients -->
    <div class="fixed top-0 left-0 w-full h-full -z-10 overflow-hidden">
        <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-rose-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-orange-500/10 rounded-full blur-[120px]">
        </div>
    </div>

    <div class="max-w-2xl w-full text-center space-y-8">
        <!-- Icon -->
        <div class="relative inline-block animate-pulse-slow">
            <svg class="w-64 h-64 mx-auto text-rose-500/20" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12 9V14M12 17.01L12.01 16.9989M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-8xl font-black gradient-text-red tracking-tighter shadow-2xl">500</span>
            </div>
        </div>

        <div class="space-y-4">
            <h1 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Something Went Wrong</h1>
            <p class="text-slate-400 text-lg max-w-md mx-auto">
                Our servers are having a bit of a moment. Our developers have been notified and are on it.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <a href="/"
                class="px-8 py-3.5 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-semibold shadow-xl shadow-rose-500/20 transition-all hover:-translate-y-1">
                Try Refreshing
            </a>
            <div class="glass px-6 py-3 rounded-2xl">
                <span class="text-xs font-mono text-slate-500">Error Code:</span>
                <span class="text-xs font-mono text-rose-400 ml-2">HTTP_500_SERVER_ERROR</span>
            </div>
        </div>
    </div>
</body>

</html>
