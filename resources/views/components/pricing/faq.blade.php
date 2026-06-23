<section class="py-20 px-4 sm:px-6 lg:px-8 bg-surface-low">
    <div class="max-w-3xl mx-auto">
        <h2 class="text-3xl sm:text-4xl font-bold text-text-primary text-center mb-12">Frequently asked questions</h2>

        <div x-data="{ open: null }" class="space-y-4">

            {{-- Q1 --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <button @click="open === 1 ? open = null : open = 1" class="w-full flex items-center justify-between px-6 py-5 text-left">
                    <span class="font-semibold text-text-primary">Is there a free plan?</span>
                    <svg class="w-5 h-5 text-text-muted shrink-0 transition-transform duration-200" :class="open === 1 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 1" x-collapse x-cloak class="px-6 pb-5 text-text-secondary">
                    Yes. The Free plan includes 1 facility, up to 10 locations, QR scanning, public issue reporting, and 30-day history — no credit card required.
                </div>
            </div>

            {{-- Q2 --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <button @click="open === 2 ? open = null : open = 2" class="w-full flex items-center justify-between px-6 py-5 text-left">
                    <span class="font-semibold text-text-primary">Can I change plans later?</span>
                    <svg class="w-5 h-5 text-text-muted shrink-0 transition-transform duration-200" :class="open === 2 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 2" x-collapse x-cloak class="px-6 pb-5 text-text-secondary">
                    Absolutely. You can upgrade or downgrade at any time. When you upgrade, you'll be prorated for the remainder of the billing cycle. Downgrades take effect at the start of the next cycle.
                </div>
            </div>

            {{-- Q3 --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <button @click="open === 3 ? open = null : open = 3" class="w-full flex items-center justify-between px-6 py-5 text-left">
                    <span class="font-semibold text-text-primary">What counts as a user?</span>
                    <svg class="w-5 h-5 text-text-muted shrink-0 transition-transform duration-200" :class="open === 3 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 3" x-collapse x-cloak class="px-6 pb-5 text-text-secondary">
                    A user is any team member with a login — admins, supervisors, and staff. Anonymous public reporters who submit issues via QR scan do not count as users.
                </div>
            </div>

            {{-- Q4 --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <button @click="open === 4 ? open = null : open = 4" class="w-full flex items-center justify-between px-6 py-5 text-left">
                    <span class="font-semibold text-text-primary">Do you offer non-profit or education discounts?</span>
                    <svg class="w-5 h-5 text-text-muted shrink-0 transition-transform duration-200" :class="open === 4 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 4" x-collapse x-cloak class="px-6 pb-5 text-text-secondary">
                    Yes! We offer special pricing for non-profit organizations and educational institutions. Contact our sales team at sales@scanproof.com with your organization details.
                </div>
            </div>

            {{-- Q5 --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <button @click="open === 5 ? open = null : open = 5" class="w-full flex items-center justify-between px-6 py-5 text-left">
                    <span class="font-semibold text-text-primary">How is my data secured?</span>
                    <svg class="w-5 h-5 text-text-muted shrink-0 transition-transform duration-200" :class="open === 5 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 5" x-collapse x-cloak class="px-6 pb-5 text-text-secondary">
                    All data is encrypted at rest and in transit. We use industry-standard security practices, regular audits, and role-based access controls to keep your facility data safe.
                </div>
            </div>

            {{-- Q6 --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <button @click="open === 6 ? open = null : open = 6" class="w-full flex items-center justify-between px-6 py-5 text-left">
                    <span class="font-semibold text-text-primary">What happens to my data if I downgrade?</span>
                    <svg class="w-5 h-5 text-text-muted shrink-0 transition-transform duration-200" :class="open === 6 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 6" x-collapse x-cloak class="px-6 pb-5 text-text-secondary">
                    Your data is always yours. If you downgrade, you retain access to all historical data. Features exclusive to higher tiers become read-only until you upgrade again.
                </div>
            </div>

        </div>
    </div>
</section>
