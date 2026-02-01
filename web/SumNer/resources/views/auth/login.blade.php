<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-b from-[#e6eff8] to-[#eef2f9]">
        
        <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-white sm:rounded-3xl border border-[rgba(0,0,0,0.15)] relative">
            
            <div class="mb-8 text-center">
                <div class="flex justify-center mb-4">
                    <div class="h-12 w-12 bg-gray-50 rounded-full border border-[rgba(0,0,0,0.15)] flex items-center justify-center text-gray-600">
                        <img src="{{ asset('logo-summer-gray.png') }}" alt="Summer AI Logo" class="h-8 w-8">
                    </div>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-800 font-sans tracking-tight">Welcome Back</h2>
                <p class="text-sm text-gray-500 mt-2">Sign in to continue to Summer AI</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block font-medium text-sm text-gray-700 mb-1 pl-1">Email Address</label>
                    <input id="email" class="block mt-1 w-full rounded-2xl border-[rgba(0,0,0,0.15)] bg-gray-50 focus:bg-white focus:border-[#4a6fa5] focus:ring focus:ring-[#4a6fa5]/20 transition-all duration-200 py-3 px-4 text-gray-700 placeholder-gray-400 shadow-none" 
                           type="email" 
                           name="email" 
                           :value="old('email')" 
                           required 
                           autofocus 
                           autocomplete="username" 
                           placeholder="you@example.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 pl-1" />
                </div>

                <div>
                    <label for="password" class="block font-medium text-sm text-gray-700 mb-1 pl-1">Password</label>
                    <input id="password" class="block mt-1 w-full rounded-2xl border-[rgba(0,0,0,0.15)] bg-gray-50 focus:bg-white focus:border-[#4a6fa5] focus:ring focus:ring-[#4a6fa5]/20 transition-all duration-200 py-3 px-4 text-gray-700 placeholder-gray-400 shadow-none"
                           type="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           placeholder="••••••••" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 pl-1" />
                </div>

                <div class="flex items-center justify-between mt-8 pt-2">
                    <a href="{{ route('news.index') }}" class="text-sm font-medium text-gray-500 hover:text-[#1a2a6c] transition-colors duration-200 flex items-center gap-1 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Try as Guest
                    </a>

                    <div class="flex items-center gap-4">
                        <a href="{{ route('register') }}" class="text-sm font-semibold text-gray-600 hover:text-[#1a2a6c] transition-colors">
                            Register
                        </a>

                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-[#393939] via-[#343434] to-[#2b2b2b] border border-[rgba(0,0,0,0.15)] rounded-full font-semibold text-sm text-white tracking-wide hover:-translate-y-0.5 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-none hover:shadow-none">
                            {{ __('Log in') }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="mt-8 text-center text-xs text-gray-400">
            &copy; 2026 Summer AI. All rights reserved.
        </div>
    </div>
</x-guest-layout>