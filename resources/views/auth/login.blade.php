<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - {{ $appName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap');
        
        .font-bebas { font-family: 'Bebas Neue', sans-serif; }
        .font-inter { font-family: 'Inter', sans-serif; }
        
        /* Blueprint grid pattern */
        .blueprint-grid {
            background-image: 
                linear-gradient(rgba(59, 130, 246, 0.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.08) 1px, transparent 1px),
                linear-gradient(rgba(59, 130, 246, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.04) 1px, transparent 1px);
            background-size: 100px 100px, 100px 100px, 20px 20px, 20px 20px;
            background-position: -1px -1px, -1px -1px, -1px -1px, -1px -1px;
        }
        
        /* Diagonal stripes for safety theme */
        .safety-stripes {
            background: repeating-linear-gradient(
                -45deg,
                #f59e0b,
                #f59e0b 10px,
                #1f2937 10px,
                #1f2937 20px
            );
        }
        
        /* Crane animation */
        @keyframes crane-swing {
            0%, 100% { transform: rotate(-3deg); }
            50% { transform: rotate(3deg); }
        }
        
        .crane-arm {
            animation: crane-swing 4s ease-in-out infinite;
            transform-origin: top center;
        }
        
        /* Particle float animation */
        @keyframes float-particle {
            0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.3; }
            50% { transform: translateY(-20px) rotate(180deg); opacity: 0.6; }
        }
        
        .particle {
            animation: float-particle 6s ease-in-out infinite;
        }
        
        .particle:nth-child(2) { animation-delay: 1s; }
        .particle:nth-child(3) { animation-delay: 2s; }
        .particle:nth-child(4) { animation-delay: 3s; }
        .particle:nth-child(5) { animation-delay: 4s; }
        
        /* Glowing effect for input focus */
        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2), 0 0 20px rgba(245, 158, 11, 0.1);
        }
        
        /* Construction beam pattern */
        .beam-pattern {
            background: repeating-linear-gradient(
                90deg,
                transparent,
                transparent 2px,
                rgba(245, 158, 11, 0.05) 2px,
                rgba(245, 158, 11, 0.05) 4px
            );
        }
    </style>
</head>
<body class="min-h-screen bg-gray-950 overflow-hidden font-inter">
    <!-- Background Layer -->
    <div class="fixed inset-0 -z-10">
        <!-- Dark gradient base -->
        <div class="absolute inset-0 bg-gradient-to-br from-gray-950 via-gray-900 to-gray-950"></div>
        
        <!-- Blueprint grid overlay -->
        <div class="absolute inset-0 blueprint-grid opacity-60"></div>
        
        <!-- Construction accent glows -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-[150px]"></div>
        <div class="absolute bottom-0 left-1/4 w-[400px] h-[400px] bg-blue-600/10 rounded-full blur-[120px]"></div>
        
        <!-- Floating construction particles -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="particle absolute top-1/4 left-1/4 w-2 h-2 bg-amber-400/30 rounded-sm"></div>
            <div class="particle absolute top-1/3 right-1/3 w-3 h-3 bg-blue-500/30 rounded-sm"></div>
            <div class="particle absolute bottom-1/4 left-1/3 w-2 h-2 bg-amber-500/30 rounded-sm"></div>
            <div class="particle absolute top-2/3 right-1/4 w-1.5 h-1.5 bg-blue-400/30 rounded-sm"></div>
            <div class="particle absolute bottom-1/3 right-1/2 w-2.5 h-2.5 bg-amber-300/30 rounded-sm"></div>
        </div>
    </div>

    <div class="min-h-screen flex">
        <!-- Left Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12 relative">
            <!-- Vertical safety stripe accent -->
            <div class="absolute left-0 top-0 bottom-0 w-1.5 safety-stripes opacity-60"></div>
            
            <div class="w-full max-w-md space-y-8">
                <!-- Logo & Branding -->
                <div class="text-left space-y-4">
                    <!-- Construction Icon Logo -->
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/30">
                                <!-- Hard hat / Building icon -->
                                <svg class="w-9 h-9 text-gray-900" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                    <path d="M4 10v7a2 2 0 002 2h12a2 2 0 002-2v-7"/>
                                    <path d="M9 21v-6a3 3 0 016 0v6"/>
                                </svg>
                            </div>
                            <!-- Small accent badge -->
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-blue-600 rounded-md flex items-center justify-center border-2 border-gray-950">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1 class="font-bebas text-4xl text-white tracking-wider">
                                LES ATELIERS
                            </h1>
                            <h2 class="font-bebas text-3xl text-amber-400 tracking-widest -mt-1">
                                {{ strtoupper($appName) }}
                            </h2>
                        </div>
                    </div>
                    
                    <p class="text-gray-400 text-sm pl-1 flex items-center gap-2">
                        <span class="w-8 h-px bg-gradient-to-r from-amber-500 to-transparent"></span>
                        Bâtiment & Travaux Publics
                    </p>
                </div>

                <!-- Login Card -->
                <div class="relative">
                    <!-- Corner brackets decoration -->
                    <div class="absolute -top-2 -left-2 w-6 h-6 border-l-2 border-t-2 border-amber-500/50"></div>
                    <div class="absolute -top-2 -right-2 w-6 h-6 border-r-2 border-t-2 border-amber-500/50"></div>
                    <div class="absolute -bottom-2 -left-2 w-6 h-6 border-l-2 border-b-2 border-amber-500/50"></div>
                    <div class="absolute -bottom-2 -right-2 w-6 h-6 border-r-2 border-b-2 border-amber-500/50"></div>
                    
                    <div class="bg-gray-900/80 backdrop-blur-xl rounded-lg border border-gray-700/50 p-8 shadow-2xl">
                        <form class="space-y-6" action="{{ route('login') }}" method="POST">
                            @csrf

                            <!-- Email Input -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-300 uppercase tracking-wider">
                                    Identifiant
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-500 group-focus-within:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input 
                                        id="email" 
                                        name="email" 
                                        type="email" 
                                        autocomplete="email" 
                                        required 
                                        value="{{ old('email') }}"
                                        class="input-glow block w-full pl-12 pr-4 py-3.5 bg-gray-800/80 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-amber-500 transition-all @error('email') border-red-500 @enderror"
                                        placeholder="nom@entreprise.com"
                                    >
                                </div>
                                @error('email')
                                    <p class="text-sm text-red-400 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Password Input -->
                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-gray-300 uppercase tracking-wider">
                                    Mot de passe
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-500 group-focus-within:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input 
                                        id="password" 
                                        name="password" 
                                        type="password" 
                                        autocomplete="current-password" 
                                        required 
                                        class="input-glow block w-full pl-12 pr-12 py-3.5 bg-gray-800/80 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-amber-500 transition-all @error('password') border-red-500 @enderror"
                                        placeholder="••••••••"
                                    >
                                    <button 
                                        type="button"
                                        id="togglePassword"
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-amber-400 transition-colors cursor-pointer"
                                        onclick="togglePasswordVisibility()"
                                    >
                                        <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="text-sm text-red-400 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Remember & Forgot -->
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-600 bg-gray-800 text-amber-500 focus:ring-amber-500/50 focus:ring-offset-0">
                                    <span class="text-sm text-gray-400 group-hover:text-gray-300 transition-colors">Se souvenir de moi</span>
                                </label>
                                <a href="#" class="text-sm text-blue-400 hover:text-blue-300 transition-colors">
                                    Mot de passe oublié?
                                </a>
                            </div>

                            <!-- Login Button -->
                            <button 
                                type="submit" 
                                class="w-full py-4 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-gray-900 font-bold rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 group uppercase tracking-wider"
                            >
                                <span>Connexion</span>
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>

                            <!-- Admin Access Button -->
                            <button 
                                type="button" 
                                class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Accès Admin</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Footer -->
                <p class="text-center text-sm text-gray-600">
                    © {{ date('Y') }} {{ $appName }}. Tous droits réservés.
                </p>
            </div>
        </div>

        <!-- Right Side - BTP Image -->
        <div class="hidden lg:block lg:w-1/2 relative overflow-hidden">
            <!-- Full-height BTP Image -->
            <img 
                src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" 
                alt="Chantier de construction BTP" 
                class="absolute inset-0 w-full h-full object-cover"
            >
            
            <!-- Gradient overlay for better integration -->
            <div class="absolute inset-0 bg-gradient-to-r from-gray-950 via-gray-950/70 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-gray-950/80 via-transparent to-gray-950/40"></div>
            
            <!-- Decorative corner accent -->
            <div class="absolute top-0 right-0 w-32 h-32">
                <div class="absolute top-4 right-4 w-16 h-16 border-t-2 border-r-2 border-amber-500/50"></div>
            </div>
            <div class="absolute bottom-0 right-0 w-32 h-32">
                <div class="absolute bottom-4 right-4 w-16 h-16 border-b-2 border-r-2 border-amber-500/50"></div>
            </div>
            
            <!-- Bottom overlay with company info -->
            <div class="absolute bottom-0 left-0 right-0 p-8">
                <div class="bg-gray-900/80 backdrop-blur-md rounded-xl border border-gray-700/50 p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-amber-500/20 rounded-lg flex items-center justify-center border border-amber-500/30">
                            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-white">Gestion BTP Professionnelle</h2>
                            <p class="text-gray-400 text-sm">Votre solution métier intégrée</p>
                        </div>
                    </div>
                    
                    <!-- Feature pills -->
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1.5 rounded-full bg-amber-500/20 text-amber-300 text-xs font-medium border border-amber-500/30">Chantiers</span>
                        <span class="px-3 py-1.5 rounded-full bg-blue-500/20 text-blue-300 text-xs font-medium border border-blue-500/30">Devis</span>
                        <span class="px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-medium border border-emerald-500/30">Stock</span>
                        <span class="px-3 py-1.5 rounded-full bg-violet-500/20 text-violet-300 text-xs font-medium border border-violet-500/30">Facturation</span>
                    </div>
                </div>
            </div>
            
            <!-- Safety stripes accent on the edge -->
            <div class="absolute right-0 top-0 bottom-0 w-1.5 safety-stripes opacity-40"></div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }
    </script>
</body>
</html>
