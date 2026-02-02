<template>
	<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
		<nav class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-50">
			<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
				<div class="flex justify-between h-16">
					<div class="flex items-center">
						<div class="flex-shrink-0 flex items-center">
							<div class="flex items-center space-x-2">
								<div class="h-8 w-8 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center">
									<span class="text-white text-lg font-bold">🃏</span>
								</div>
								<h1 class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
									Poker Manager
								</h1>
							</div>
						</div>
						<div class="hidden sm:ml-8 sm:flex sm:space-x-1">
							<router-link
								to="/"
								class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200"
								:class="$route.name === 'Dashboard' 
									? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' 
									: 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
							>
								<span class="mr-2">📊</span>
								Дашборд
							</router-link>
							<router-link
								to="/tournaments"
								class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200"
								:class="$route.name === 'Tournaments' || $route.name === 'TournamentCreate' || $route.name === 'TournamentEdit'
									? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' 
									: 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
							>
								<span class="mr-2">🎯</span>
								Турниры
							</router-link>
							<router-link
								to="/locations"
								class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200"
								:class="$route.name === 'Locations' || $route.name === 'LocationDetail'
									? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' 
									: 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
							>
								<span class="mr-2">📍</span>
								Локации
							</router-link>
						</div>
					</div>
					<div class="flex items-center space-x-2 sm:space-x-4">
						<router-link
							v-if="user?.is_admin"
							to="/admin/settings"
							class="hidden sm:inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md hover:from-indigo-600 hover:to-purple-700"
							:class="$route.path.startsWith('/admin')
								? 'ring-2 ring-offset-2 ring-indigo-500' 
								: ''"
						>
							<span class="mr-2">🛠️</span>
							Админка
						</router-link>
						<div class="hidden sm:block relative" ref="userMenuRef">
							<button
								@click="userMenuOpen = !userMenuOpen"
								class="flex items-center space-x-3 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
							>
								<div class="h-8 w-8 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
									{{ user?.name?.charAt(0).toUpperCase() }}
								</div>
								<span class="text-sm font-medium text-gray-700 dark:text-gray-300">
									{{ user?.name }}
								</span>
								<svg
									class="h-4 w-4 text-gray-500 transition-transform"
									:class="{ 'rotate-180': userMenuOpen }"
									fill="none"
									stroke="currentColor"
									viewBox="0 0 24 24"
								>
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
								</svg>
							</button>
							<transition
								enter-active-class="transition ease-out duration-150"
								enter-from-class="opacity-0 scale-95"
								enter-to-class="opacity-100 scale-100"
								leave-active-class="transition ease-in duration-100"
								leave-from-class="opacity-100 scale-100"
								leave-to-class="opacity-0 scale-95"
							>
								<div
									v-show="userMenuOpen"
									class="absolute right-0 mt-2 w-56 py-1 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
								>
									<router-link
										to="/packs"
										@click="userMenuOpen = false"
										class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
										:class="$route.name === 'Packs' || $route.name === 'PackDetail'
											? 'bg-gray-100 dark:bg-gray-700 font-medium' 
											: ''"
									>
										<span class="mr-2">📦</span>
										Паки
									</router-link>
									<router-link
										to="/settings"
										@click="userMenuOpen = false"
										class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
										:class="$route.name === 'Settings' ? 'bg-gray-100 dark:bg-gray-700 font-medium' : ''"
									>
										<span class="mr-2">⚙️</span>
										Настройки
									</router-link>
									<router-link
										to="/help"
										@click="userMenuOpen = false"
										class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
										:class="$route.name === 'Help' ? 'bg-gray-100 dark:bg-gray-700 font-medium' : ''"
									>
										<span class="mr-2">📖</span>
										Помощь
									</router-link>
									<div class="border-t border-gray-200 dark:border-gray-700 my-1" />
									<button
										@click="handleLogout"
										class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
									>
										<span class="mr-2">🚪</span>
										Выход
									</button>
								</div>
							</transition>
						</div>
						<button
							@click="mobileMenuOpen = !mobileMenuOpen"
							class="sm:hidden inline-flex items-center justify-center p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
							aria-label="Меню"
						>
							<svg
								class="h-6 w-6"
								fill="none"
								stroke="currentColor"
								viewBox="0 0 24 24"
								:class="mobileMenuOpen ? 'hidden' : 'block'"
							>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
							</svg>
							<svg
								class="h-6 w-6"
								fill="none"
								stroke="currentColor"
								viewBox="0 0 24 24"
								:class="mobileMenuOpen ? 'block' : 'hidden'"
							>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
							</svg>
						</button>
					</div>
				</div>
			</div>
			<transition
				enter-active-class="transition ease-out duration-200"
				enter-from-class="opacity-0 -translate-y-1"
				enter-to-class="opacity-100 translate-y-0"
				leave-active-class="transition ease-in duration-150"
				leave-from-class="opacity-100 translate-y-0"
				leave-to-class="opacity-0 -translate-y-1"
			>
				<div
					v-show="mobileMenuOpen"
					class="sm:hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
				>
				<div class="px-2 pt-2 pb-3 space-y-1">
					<router-link
						to="/"
						@click="mobileMenuOpen = false"
						class="block px-3 py-2 text-base font-medium rounded-lg transition-all duration-200"
						:class="$route.name === 'Dashboard' 
							? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' 
							: 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
					>
						<span class="mr-2">📊</span>
						Дашборд
					</router-link>
					<router-link
						to="/tournaments"
						@click="mobileMenuOpen = false"
						class="block px-3 py-2 text-base font-medium rounded-lg transition-all duration-200"
						:class="$route.name === 'Tournaments' || $route.name === 'TournamentCreate' || $route.name === 'TournamentEdit'
							? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' 
							: 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
					>
						<span class="mr-2">🎯</span>
						Турниры
					</router-link>
					<router-link
						to="/locations"
						@click="mobileMenuOpen = false"
						class="block px-3 py-2 text-base font-medium rounded-lg transition-all duration-200"
						:class="$route.name === 'Locations' || $route.name === 'LocationDetail'
							? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' 
							: 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
					>
						<span class="mr-2">📍</span>
						Локации
					</router-link>
					<router-link
						v-if="user?.is_admin"
						to="/admin/settings"
						@click="mobileMenuOpen = false"
						class="block px-3 py-2 text-base font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md"
						:class="$route.path.startsWith('/admin')
							? 'ring-2 ring-offset-2 ring-indigo-500' 
							: ''"
					>
						<span class="mr-2">🛠️</span>
						Админка
					</router-link>
					<div class="px-3 py-2 border-t border-gray-200 dark:border-gray-700 mt-2 pt-2">
						<div class="flex items-center space-x-3 mb-3">
							<div class="h-8 w-8 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
								{{ user?.name?.charAt(0).toUpperCase() }}
							</div>
							<span class="text-sm font-medium text-gray-700 dark:text-gray-300">
								{{ user?.name }}
							</span>
						</div>
						<router-link
							to="/packs"
							@click="mobileMenuOpen = false"
							class="block px-3 py-2 text-base font-medium rounded-lg transition-all duration-200 mb-1"
							:class="$route.name === 'Packs' || $route.name === 'PackDetail'
								? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' 
								: 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
						>
							<span class="mr-2">📦</span>
							Паки
						</router-link>
						<router-link
							to="/settings"
							@click="mobileMenuOpen = false"
							class="block px-3 py-2 text-base font-medium rounded-lg transition-all duration-200 mb-1"
							:class="$route.name === 'Settings'
								? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' 
								: 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
						>
							<span class="mr-2">⚙️</span>
							Настройки
						</router-link>
						<router-link
							to="/help"
							@click="mobileMenuOpen = false"
							class="block px-3 py-2 text-base font-medium rounded-lg transition-all duration-200 mb-1"
							:class="$route.name === 'Help'
								? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' 
								: 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
						>
							<span class="mr-2">📖</span>
							Помощь
						</router-link>
						<button
							@click="handleLogout"
							class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
						>
							<span class="mr-2">🚪</span>
							Выход
						</button>
					</div>
				</div>
			</div>
			</transition>
		</nav>

		<main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
			<router-view />
		</main>
	</div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const mobileMenuOpen = ref(false);
const userMenuOpen = ref(false);
const userMenuRef = ref(null);

watch(() => route.path, () => {
	mobileMenuOpen.value = false;
	userMenuOpen.value = false;
});

const handleClickOutside = (event) => {
	if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
		userMenuOpen.value = false;
	}
};

onMounted(() => {
	document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
	document.removeEventListener('click', handleClickOutside);
});

const handleLogout = async () => {
	mobileMenuOpen.value = false;
	await authStore.logout();
	router.push({ name: 'Login' });
};
</script>
