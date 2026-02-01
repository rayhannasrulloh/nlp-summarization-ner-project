<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-b from-[#e6eff8] to-[#eef2f9]">
        
        <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-white sm:rounded-3xl border border-[rgba(0,0,0,0.15)] relative">
            
            <div class="mb-8 text-center">
                <div class="flex justify-center mb-4">
                    <div class="h-12 w-12 bg-gray-50 rounded-full border border-[rgba(0,0,0,0.15)] flex items-center justify-center text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-800 font-sans tracking-tight">Create Account</h2>
                <p class="text-sm text-gray-500 mt-2">Join Summer AI today</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block font-medium text-sm text-gray-700 mb-1 pl-1">Full Name</label>
                    <input id="name" class="block mt-1 w-full rounded-2xl border-[rgba(0,0,0,0.15)] bg-gray-50 focus:bg-white focus:border-[#4a6fa5] focus:ring focus:ring-[#4a6fa5]/20 transition-all duration-200 py-3 px-4 text-gray-700 placeholder-gray-400 shadow-none" 
                           type="text" 
                           name="name" 
                           :value="old('name')" 
                           required 
                           autofocus 
                           autocomplete="name" 
                           placeholder="John Doe" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2 pl-1" />
                </div>

                <div>
                    <label for="email" class="block font-medium text-sm text-gray-700 mb-1 pl-1">Email Address</label>
                    <input id="email" class="block mt-1 w-full rounded-2xl border-[rgba(0,0,0,0.15)] bg-gray-50 focus:bg-white focus:border-[#4a6fa5] focus:ring focus:ring-[#4a6fa5]/20 transition-all duration-200 py-3 px-4 text-gray-700 placeholder-gray-400 shadow-none" 
                           type="email" 
                           name="email" 
                           :value="old('email')" 
                           required 
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
                           autocomplete="new-password"
                           placeholder="••••••••" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 pl-1" />
                </div>

                <div>
                    <label for="password_confirmation" class="block font-medium text-sm text-gray-700 mb-1 pl-1">Confirm Password</label>
                    <input id="password_confirmation" class="block mt-1 w-full rounded-2xl border-[rgba(0,0,0,0.15)] bg-gray-50 focus:bg-white focus:border-[#4a6fa5] focus:ring focus:ring-[#4a6fa5]/20 transition-all duration-200 py-3 px-4 text-gray-700 placeholder-gray-400 shadow-none"
                           type="password"
                           name="password_confirmation"
                           required
                           autocomplete="new-password"
                           placeholder="••••••••" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 pl-1" />
                </div>

                <div class="flex items-center justify-between mt-8 pt-2">
                    <a href="{{ route('news.index') }}" class="text-sm font-medium text-gray-500 hover:text-[#1a2a6c] transition-colors duration-200 flex items-center gap-1 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Guest
                    </a>

                    <div class="flex items-center gap-4">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-[#1a2a6c] transition-colors">
                            Log in
                        </a>

                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-[#1a2a6c] via-[#2a4878] to-[#4a6fa5] border border-[rgba(0,0,0,0.15)] rounded-full font-semibold text-sm text-white tracking-wide hover:-translate-y-0.5 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-none hover:shadow-none">
                            {{ __('Register') }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
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