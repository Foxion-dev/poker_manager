<template>
	<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
		<nav class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-50">
			<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
				<div class="flex justify-between h-16">
					<div class="flex">
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
								to="/admin/rooms"
								class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200"
								:class="$route.name === 'AdminRooms' || $route.name === 'AdminCurrencies' || $route.name === 'AdminUsers'
									? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md' 
									: 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
							>
								<span class="mr-2">🛠️</span>
								Админка
							</router-link>
						</div>
					</div>
					<div class="flex items-center space-x-4">
						<div class="hidden sm:flex items-center space-x-3 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
							<div class="h-8 w-8 rounded-full bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
								{{ user?.name?.charAt(0).toUpperCase() }}
							</div>
							<span class="text-sm font-medium text-gray-700 dark:text-gray-300">
								{{ user?.name }}
							</span>
						</div>
						<button
							@click="handleLogout"
							class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
						>
							<span class="mr-2">🚪</span>
							Выход
						</button>
					</div>
				</div>
			</div>
		</nav>

		<main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
			<router-view />
		</main>
	</div>
</template>

<script setup>
import { storeToRefs } from 'pinia';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const handleLogout = async () => {
	await authStore.logout();
	router.push({ name: 'Login' });
};
</script>
