<section class="py-20 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-3xl sm:text-4xl font-bold text-text-primary mb-6">Simple pricing for every facility</h1>
        <p class="text-lg text-text-secondary mb-10 max-w-2xl mx-auto">Start free, scale as you grow. No hidden fees, no surprises. Cancel anytime.</p>

        {{-- Billing Toggle --}}
        <div class="inline-flex items-center gap-3 bg-surface-low rounded-xl p-1.5">
            <button @click="annual = true"
                    :class="annual ? 'bg-primary text-white shadow-sm' : 'text-text-secondary hover:text-text-primary'"
                    class="relative px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200">
                Annual
                <span class="absolute -top-2.5 -right-2 bg-success/10 text-success text-[10px] font-semibold rounded-full px-2 py-0.5">Save ~20%</span>
            </button>
            <button @click="annual = false"
                    :class="!annual ? 'bg-primary text-white shadow-sm' : 'text-text-secondary hover:text-text-primary'"
                    class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200">
                Monthly
            </button>
        </div>
    </div>
</section>
