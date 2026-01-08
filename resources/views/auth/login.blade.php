<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - LES ATELIERS BOUYAHYA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .construction-bg {
            background-image: url('https://images.unsplash.com/photo-1581092160562-40aa08e78837?q=80&w=1000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(0.5px);
        }
        @media (max-width: 1023px) {
            .construction-bg {
                display: none;
            }
        }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- Left Side - Login Form -->
    <div class="w-full lg:w-1/2 bg-[#0a0a0a] flex items-center justify-center p-8 lg:p-12">
        <div class="w-full max-w-md space-y-8">
            <!-- Title -->
            <h1 class="text-white text-2xl lg:text-3xl font-bold uppercase tracking-wide mb-8">
                LES ATELIERS BOUYAHYA
            </h1>

            <!-- Login Form -->
            <form class="space-y-6" action="{{ route('login') }}" method="POST">
                @csrf

                <!-- User Input -->
                <div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                            <!-- Person Icon with blue shirt and red tie -->
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="7" r="3.5" fill="#3b82f6"/>
                                <path d="M6 20v-1.5a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4V20" stroke="#ef4444" stroke-width="1.5" fill="none"/>
                                <path d="M12 10.5v-1" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                                <path d="M10 12h4" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                            </svg>
                        </div>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            autocomplete="email" 
                            required 
                            value="{{ old('email') }}"
                            class="block w-full pl-12 pr-10 py-3.5 bg-[#2a2a2a] border border-[#3a3a3a] rounded text-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 transition-colors @error('email') border-red-500 @enderror"
                            placeholder=""
                        >
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-white mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                            <!-- Padlock Icon -->
                            <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            autocomplete="current-password" 
                            required 
                            class="block w-full pl-12 pr-12 py-3.5 bg-[#2a2a2a] border border-[#3a3a3a] rounded text-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 transition-colors @error('password') border-red-500 @enderror"
                            placeholder="Enter your password"
                        >
                        <button 
                            type="button"
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-white transition-colors cursor-pointer"
                            onclick="togglePasswordVisibility()"
                        >
                            <!-- Eye Icon -->
                            <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Forgot Password Link -->
                <div>
                    <a href="#" class="text-white text-sm hover:underline">
                        Forgot Password?
                    </a>
                </div>

                <!-- Login Buttons -->
                <div class="space-y-4 pt-2">
                    <!-- Yellow Login Button -->
                    <button 
                        type="submit" 
                        class="w-full py-3.5 px-4 bg-[#FFD700] hover:bg-[#FFC700] text-white font-semibold rounded transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-[#0a0a0a] shadow-lg"
                    >
                        Login
                    </button>

                    <!-- Blue Admin Access Button -->
                    <button 
                        type="button" 
                        onclick="window.location.href='{{ route('login') }}?admin=1'"
                        class="w-full py-3.5 px-4 bg-[#0066FF] hover:bg-[#0052CC] text-white font-semibold rounded transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-[#0a0a0a] shadow-lg"
                    >
                        Accès Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Side - Construction Materials Background -->
    <div class="hidden lg:block lg:w-1/2 construction-bg"></div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Change to eye-slash icon
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                passwordInput.type = 'password';
                // Change back to eye icon
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }
    </script>
</body>
</html>

