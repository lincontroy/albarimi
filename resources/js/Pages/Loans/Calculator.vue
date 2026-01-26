<template>
    <DashboardLayout title="Loan Calculator">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <CreditCard :size="16" class="text-purple-400" />
            <span>Loans</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Loan Calculator</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Loan Calculator</h1>
                    <p class="text-purple-300">Calculate your monthly payments and plan your loan</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Calculator -->
                    <div class="lg:col-span-2">
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <h3 class="text-xl font-bold text-white mb-6">Calculate Your Loan</h3>
                            
                            <!-- Loan Amount Slider -->
                            <div class="mb-8">
                                <div class="flex justify-between items-center mb-3">
                                    <label class="text-sm font-medium text-purple-300">Loan Amount</label>
                                    <span class="text-2xl font-bold text-white">KES {{ formatCurrency(amount) }}</span>
                                </div>
                                <input
                                    v-model="amount"
                                    type="range"
                                    :min="1000"
                                    :max="50000"
                                    step="1000"
                                    class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer slider"
                                />
                                <div class="flex justify-between text-xs text-purple-300 mt-2">
                                    <span>KES 1,000</span>
                                    <span>KES 50,000</span>
                                </div>
                            </div>

                            <!-- Loan Term Slider -->
                            <div class="mb-8">
                                <div class="flex justify-between items-center mb-3">
                                    <label class="text-sm font-medium text-purple-300">Loan Term (Months)</label>
                                    <span class="text-2xl font-bold text-white">{{ term }} months</span>
                                </div>
                                <input
                                    v-model="term"
                                    type="range"
                                    :min="3"
                                    :max="36"
                                    step="1"
                                    class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer slider"
                                />
                                <div class="flex justify-between text-xs text-purple-300 mt-2">
                                    <span>3 months</span>
                                    <span>36 months</span>
                                </div>
                            </div>

                            <!-- Interest Rate -->
                            <div class="mb-8">
                                <label class="block text-sm font-medium text-purple-300 mb-3">Interest Rate</label>
                                <div class="flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="text-4xl font-bold text-green-400 mb-1">{{ interestRate }}%</div>
                                        <div class="text-sm text-purple-300">Annual Interest Rate</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Apply Button -->
                            <div class="pt-6 border-t border-purple-500/20">
                                <Link
                                    :href="`/loans/apply?amount=${amount}&term=${term}`"
                                    class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                                >
                                    <FilePlus :size="20" />
                                    <span>Apply for This Loan</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Results -->
                    <div class="lg:col-span-1">
                        <!-- Results Card -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-6">Calculation Results</h3>
                            
                            <div class="space-y-4">
                                <!-- Monthly Payment -->
                                <div class="text-center">
                                    <div class="text-sm text-blue-300 mb-1">Monthly Payment</div>
                                    <div class="text-3xl font-bold text-green-400">KES {{ formatCurrency(calculation.monthly_payment) }}</div>
                                </div>

                                <!-- Total Repayable -->
                                <div class="text-center">
                                    <div class="text-sm text-blue-300 mb-1">Total Repayable</div>
                                    <div class="text-2xl font-bold text-white">KES {{ formatCurrency(calculation.total_amount) }}</div>
                                </div>

                                <!-- Total Interest -->
                                <div class="text-center">
                                    <div class="text-sm text-blue-300 mb-1">Total Interest</div>
                                    <div class="text-xl font-bold text-yellow-400">KES {{ formatCurrency(calculation.total_interest) }}</div>
                                </div>

                                <!-- Breakdown -->
                                <div class="pt-4 border-t border-blue-500/20">
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div class="text-center">
                                            <div class="text-blue-300">Loan Amount</div>
                                            <div class="font-bold text-white">KES {{ formatCurrency(amount) }}</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-blue-300">Loan Term</div>
                                            <div class="font-bold text-white">{{ term }} months</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Repayment Schedule -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                            <h3 class="text-lg font-bold text-white mb-4">Repayment Schedule (First 6 Months)</h3>
                            
                            <div class="space-y-2 max-h-80 overflow-y-auto">
                                <div 
                                    v-for="payment in calculation.repayment_schedule" 
                                    :key="payment.month"
                                    class="flex items-center justify-between py-2 px-3 bg-black/20 rounded-lg"
                                >
                                    <div>
                                        <div class="text-sm font-bold text-white">Month {{ payment.month }}</div>
                                        <div class="text-xs text-blue-300">{{ payment.date }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-green-400">KES {{ formatCurrency(payment.payment) }}</div>
                                        <div class="text-xs text-blue-300">
                                            P: {{ formatCurrency(payment.principal) }} | I: {{ formatCurrency(payment.interest) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="term > 6" class="pt-3 border-t border-green-500/20 text-center">
                                <span class="text-sm text-green-300">+ {{ term - 6 }} more payments</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Section -->
                <div class="mt-8 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                    <h3 class="text-lg font-bold text-white mb-4">How Our Loans Work</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-600/20 rounded-full mb-3">
                                <FilePlus :size="24" class="text-purple-400" />
                            </div>
                            <h4 class="font-bold text-white mb-2">1. Apply Online</h4>
                            <p class="text-sm text-purple-300">Fill out the simple application form with KES 200 fee</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600/20 rounded-full mb-3">
                                <Clock :size="24" class="text-blue-400" />
                            </div>
                            <h4 class="font-bold text-white mb-2">2. Quick Approval</h4>
                            <p class="text-sm text-blue-300">Get approved within 24 hours of application</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-green-600/20 rounded-full mb-3">
                                <DollarSign :size="24" class="text-green-400" />
                            </div>
                            <h4 class="font-bold text-white mb-2">3. Receive Funds</h4>
                            <p class="text-sm text-green-300">Funds disbursed directly to your account</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    CreditCard, FilePlus, DollarSign, Clock
} from 'lucide-vue-next';

const props = defineProps({
    calculation: {
        type: Object,
        default: () => ({
            amount: 10000,
            term: 12,
            interest_rate: 5.0,
            monthly_payment: 0,
            total_amount: 0,
            total_interest: 0,
            repayment_schedule: []
        })
    },
    userBalance: {
        type: [Number, String],
        default: 0
    }
});

const amount = ref(props.calculation.amount);
const term = ref(props.calculation.term);
const interestRate = ref(props.calculation.interest_rate);

// Recalculate when inputs change
const calculation = computed(() => {
    const principal = parseFloat(amount.value) || 0;
    const termMonths = parseInt(term.value) || 12;
    const monthlyInterestRate = (interestRate.value / 100) / 12;
    
    let monthlyPayment = 0;
    let totalAmount = 0;
    let totalInterest = 0;
    const schedule = [];
    
    if (monthlyInterestRate > 0 && termMonths > 0 && principal > 0) {
        // Calculate monthly payment
        monthlyPayment = principal * (monthlyInterestRate * Math.pow(1 + monthlyInterestRate, termMonths)) / 
                        (Math.pow(1 + monthlyInterestRate, termMonths) - 1);
        
        totalAmount = monthlyPayment * termMonths;
        totalInterest = totalAmount - principal;
        
        // Generate schedule for first 6 months
        let balance = principal;
        const today = new Date();
        
        for (let month = 1; month <= Math.min(6, termMonths); month++) {
            const interestPayment = balance * monthlyInterestRate;
            const principalPayment = monthlyPayment - interestPayment;
            balance -= principalPayment;
            
            if (balance < 0) balance = 0;
            
            const paymentDate = new Date(today);
            paymentDate.setMonth(paymentDate.getMonth() + month);
            
            schedule.push({
                month: month,
                date: paymentDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
                payment: Math.round(monthlyPayment),
                principal: Math.round(principalPayment),
                interest: Math.round(interestPayment),
                balance: Math.round(balance)
            });
        }
    }
    
    return {
        monthly_payment: Math.round(monthlyPayment),
        total_amount: Math.round(totalAmount),
        total_interest: Math.round(totalInterest),
        repayment_schedule: schedule
    };
});

// Utility functions
const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

// Watch for changes and update URL
watch([amount, term], () => {
    // You could update the URL query parameters here if needed
    // router.get(`/loans/calculator?amount=${amount.value}&term=${term.value}`);
});

// Style for range slider
const sliderStyle = `
    .slider::-webkit-slider-thumb {
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #8b5cf6;
        cursor: pointer;
    }
    
    .slider::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #8b5cf6;
        cursor: pointer;
        border: none;
    }
`;

// Add styles to document
const styleSheet = document.createElement("style");
styleSheet.innerText = sliderStyle;
document.head.appendChild(styleSheet);
</script>