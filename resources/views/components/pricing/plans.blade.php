<section class="pb-20 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">

            {{-- Free --}}
            <div class="bg-white rounded-2xl border border-gray-100 hover:shadow-lg transition-shadow p-8 flex flex-col">
                <h3 class="text-xl font-semibold text-text-primary">Free</h3>
                <p class="text-text-secondary text-sm mt-2 mb-6">For a single site getting started with QR reporting.</p>
                <div class="mb-6">
                    <span class="text-4xl font-bold text-text-primary">$0</span>
                    <span class="text-text-secondary">/user · mo</span>
                </div>
                <a href="{{ route('register') }}" class="block text-center bg-surface-low hover:bg-surface-container text-text-primary px-6 py-2.5 rounded-xl font-medium transition-colors mb-8">Get started free</a>
                <ul class="space-y-3 flex-1">
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>1 facility</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Up to 10 locations</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>QR scan + public issue reporting</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>1 supervisor · 3 staff</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>30-day history</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Mobile web</li>
                </ul>
            </div>

            {{-- Standard --}}
            <div class="bg-white rounded-2xl border border-gray-100 hover:shadow-lg transition-shadow p-8 flex flex-col">
                <h3 class="text-xl font-semibold text-text-primary">Standard</h3>
                <p class="text-text-secondary text-sm mt-2 mb-6">Unlimited locations, tasks, and photo-proof workflows.</p>
                <div class="mb-6">
                    <span class="text-4xl font-bold text-text-primary" x-text="annual ? '$4' : '$5'"></span>
                    <span class="text-text-secondary">/user · mo</span>
                </div>
                <a href="{{ route('register') }}" class="block text-center bg-primary hover:bg-primary-dark text-white px-6 py-2.5 rounded-xl font-medium transition-colors mb-8">Start free trial</a>
                <ul class="space-y-3 flex-1">
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Everything in Free</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Unlimited locations</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Unlimited tasks</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Before/after photo proof</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Task status workflow</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Email notifications</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>1-year history</li>
                </ul>
            </div>

            {{-- Premium (Most Popular) --}}
            <div class="bg-white rounded-2xl border-2 border-primary hover:shadow-lg transition-shadow p-8 flex flex-col relative">
                <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-primary text-white text-xs font-semibold rounded-full px-3 py-1">Most popular</span>
                <h3 class="text-xl font-semibold text-text-primary">Premium</h3>
                <p class="text-text-secondary text-sm mt-2 mb-6">Analytics, reports, and admin controls for growing teams.</p>
                <div class="mb-6">
                    <span class="text-4xl font-bold text-text-primary" x-text="annual ? '$9' : '$11'"></span>
                    <span class="text-text-secondary">/user · mo</span>
                </div>
                <a href="{{ route('register') }}" class="block text-center bg-primary hover:bg-primary-dark text-white px-6 py-2.5 rounded-xl font-medium transition-colors mb-8">Start free trial</a>
                <ul class="space-y-3 flex-1">
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Everything in Standard</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Dashboard analytics & health score</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>PDF report export</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>WhatsApp notifications</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Role-based access (all roles)</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>SLA tracking</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Priority support</li>
                </ul>
            </div>

            {{-- Enterprise --}}
            <div class="bg-white rounded-2xl border border-gray-100 hover:shadow-lg transition-shadow p-8 flex flex-col">
                <h3 class="text-xl font-semibold text-text-primary">Enterprise</h3>
                <p class="text-text-secondary text-sm mt-2 mb-6">Org-wide controls, SLAs, and dedicated support.</p>
                <div class="mb-6">
                    <span class="text-4xl font-bold text-text-primary">Custom</span>
                </div>
                <a href="mailto:sales@scanproof.com" class="block text-center bg-surface-low hover:bg-surface-container text-text-primary px-6 py-2.5 rounded-xl font-medium transition-colors mb-8">Contact sales</a>
                <ul class="space-y-3 flex-1">
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Everything in Premium</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Unlimited facilities & users</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>GPS verification</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Client portal</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Automated daily reports</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>SSO & onboarding</li>
                    <li class="flex gap-3 text-sm text-text-secondary"><svg class="w-5 h-5 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Dedicated success manager</li>
                </ul>
            </div>

        </div>
    </div>
</section>
