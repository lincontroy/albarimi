<template>
  <aside
    :class="[
      isSidebarOpen ? 'translate-x-0' : '-translate-x-full',
      'lg:translate-x-0 w-64 bg-gradient-to-b from-slate-800/50 to-slate-900/50 backdrop-blur-xl border-r border-purple-500/20 text-white transition-transform duration-300 flex flex-col fixed lg:relative h-full z-30'
    ]"
  >
    <!-- Header -->
    <div class="p-4 flex items-center justify-between border-b border-purple-500/30">
      <div class="flex items-center space-x-2">
        <div
          class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center shadow-lg shadow-purple-500/50"
        >
          <span class="text-white font-bold text-xl">A</span>
        </div>
        <span
          class="font-bold text-lg bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent"
          >Barimax</span
        >
      </div>
      <button
        @click="$emit('toggle-sidebar')"
        class="lg:hidden hover:bg-purple-500/20 p-1 rounded-lg transition-colors"
      >
        <X :size="24" />
      </button>
    </div>

    <!-- Menu -->
    <nav class="flex-1 overflow-y-auto py-4" @click="closeSidebarOnMobile">
      <div v-for="(item, index) in menuItems" :key="index">
        <!-- Dropdown Toggle -->
        <button
          v-if="item.hasDropdown"
          @click="toggleDropdown(item.label)"
          class="w-full flex items-center justify-between px-4 py-3 hover:bg-gradient-to-r hover:from-purple-500/20 hover:to-pink-500/20 transition-all duration-200 group"
        >
          <div class="flex items-center space-x-3">
            <component
              :is="item.icon"
              :size="20"
              class="text-purple-400 group-hover:text-pink-400 transition-colors"
            />
            <span class="group-hover:text-purple-300 transition-colors">{{ item.label }}</span>
          </div>
          <ChevronDown
            :size="16"
            :class="`transition-transform text-purple-400 ${
              openDropdowns[item.label] ? 'rotate-180' : ''
            }`"
          />
        </button>

        <!-- Simple Link -->
        <Link
          v-else
          :href="item.link"
          class="w-full flex items-center px-4 py-3 hover:bg-gradient-to-r hover:from-purple-500/20 hover:to-pink-500/20 transition-all duration-200 group"
        >
          <div class="flex items-center space-x-3">
            <component
              :is="item.icon"
              :size="20"
              class="text-purple-400 group-hover:text-pink-400 transition-colors"
            />
            <span class="group-hover:text-purple-300 transition-colors">{{ item.label }}</span>
          </div>
        </Link>

        <!-- Example Dropdown -->
        <div
          v-if="item.label === 'Cash Flow' && openDropdowns[item.label]"
          class="bg-slate-800/50 py-2"
        >
          <Link
            href="/cash-flow/submit-views"
            class="flex items-center space-x-2 px-12 py-2 text-sm hover:bg-purple-500/20 text-purple-300 transition-colors"
          >
            <Eye :size="16" />
            <span>Submit Views</span>
          </Link>
          <Link
            href="/cash-flow/whatsapp-withdrawals"
            class="flex items-center space-x-2 px-12 py-2 text-sm hover:bg-purple-500/20 text-purple-300 transition-colors"
          >
            <MessageCircle :size="16" />
            <span>WhatsApp Withdrawals</span>
          </Link>
        </div>

      
        <div
          v-if="item.label === 'Rewards Center' && openDropdowns[item.label]"
          class="bg-slate-800/50 py-2"
        >
          <Link
            href="/reward-center/welcome-bonus"
            class="flex items-center space-x-2 px-12 py-2 text-sm hover:bg-purple-500/20 text-purple-300 transition-colors"
          >
            <Eye :size="16" />
            <span>Welcome bonus</span>
          </Link>
          <Link
            href="/reward-center/minor-bonus"
            class="flex items-center space-x-2 px-12 py-2 text-sm hover:bg-purple-500/20 text-purple-300 transition-colors"
          >
            <MessageCircle :size="16" />
            <span>Minor Bonus</span>
          </Link>
          <Link
            href="/reward-center/pro-bonus"
            class="flex items-center space-x-2 px-12 py-2 text-sm hover:bg-purple-500/20 text-purple-300 transition-colors"
          >
            <MessageCircle :size="16" />
            <span>Pro Bonus</span>
          </Link>
          <Link
            href="/reward-center/mega-bonus"
            class="flex items-center space-x-2 px-12 py-2 text-sm hover:bg-purple-500/20 text-purple-300 transition-colors"
          >
            <MessageCircle :size="16" />
            <span>Mega Bonus</span>
          </Link>
        </div>
      </div>
    </nav>

    <!-- Bottom Buttons -->
    <div class="p-4 border-t border-purple-500/30">
      <button
        class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 px-4 py-2 rounded-lg flex items-center justify-between transition-all duration-200 shadow-lg shadow-purple-500/50"
      >
        <span>Monetize</span>
        <span class="bg-slate-900/50 px-3 py-1 rounded text-xs">Copy</span>
      </button>
    </div>

    <!-- User Info with Logout -->
    <div class="p-4 border-t border-purple-500/30">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-3">
          <div
            class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center shadow-lg shadow-purple-500/50"
          >
            <User :size="20" />
          </div>
          <div class="flex-1">
            <p class="font-semibold">{{ $page.props.auth.user.name }}</p>
            <p class="text-xs text-purple-300">{{ $page.props.auth.user.email }}</p>
          </div>
        </div>
        <!-- Logout Button -->
        <button
          @click="logout"
          class="hover:bg-red-500/20 p-2 rounded-lg transition-colors group"
          title="Logout"
        >
          <LogOut :size="18" class="text-red-400 group-hover:text-red-300" />
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import {
  DollarSign,
  CreditCard,
  Star,
  Package,
  TrendingUp,
  Wallet,
  User,
  LogOut,
  X,
  ChevronDown,
  LayoutDashboard,
  BarChart3,
  Users,
  Gift,
  Zap,
  Eye,
  MessageCircle,
  Award,
  Clock,
  List,
  Plus,
  Minus,
  History,
} from 'lucide-vue-next'

const props = defineProps({
  isSidebarOpen: Boolean,
})

const emit = defineEmits(['toggle-sidebar'])
const openDropdowns = ref({})

const toggleDropdown = (key) => {
  openDropdowns.value = {
    ...openDropdowns.value,
    [key]: !openDropdowns.value[key],
  }
}

const closeSidebarOnMobile = () => {
  if (window.innerWidth < 1024) {
    emit('toggle-sidebar')
  }
}

// Logout function
const logout = () => {
  if (confirm('Are you sure you want to logout?')) {
    router.post(route('logout'))
  }
}

// Define menu items with static or Inertia links
const menuItems = [
  { icon: LayoutDashboard, label: 'Dashboard', hasDropdown: false, link: '/dashboard' },
  { icon: BarChart3, label: 'Cash Flow', hasDropdown: true, link: '#' },
  { icon: Users, label: 'Agent', hasDropdown: false, link: '/agent-package' },
  { icon: Zap, label: 'Monetized Ads', hasDropdown: false, link: '/ads' },
  { icon: Gift, label: 'Rewards Center', hasDropdown: true, link: '#' },
  { icon: Users, label: 'Team', hasDropdown: false, link: '/team' },
  { icon: DollarSign, label: 'Dollar Zone', hasDropdown: false, link: '/certification' },
  { icon: CreditCard, label: 'Visa Codes', hasDropdown: false, link: '/visa-codes' },
  { icon: Star, label: 'Starlink', hasDropdown: false, link: '/starlink' },
  { icon: Package, label: 'Jobs', hasDropdown: false, link: '/jobs' },
  { icon: TrendingUp, label: 'Loans', hasDropdown: false, link: '/loans' },
  { icon: Wallet, label: 'Wallet', hasDropdown: false, link: '/wallet' },
  { icon: Package, label: 'Endorsement', hasDropdown: false, link: '/endorsement' },
  // { icon: User, label: 'Profile', hasDropdown: false, link: '/profile' },
  { icon: Package, label: 'Whatsapp Linkage', hasDropdown: false, link: '/whatsapp-linkage' },
  { icon: Star, label: 'Reviews', hasDropdown: false, link: '/reviews' },
]
</script>