<template>
	<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
		<nav class="bg-white dark:bg-gray-800 shadow">
			<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
				<div class="flex justify-between h-16">
					<div class="flex">
						<div class="flex-shrink-0 flex items-center">
							<h1 class="text-xl font-bold text-gray-900 dark:text-white">
								Poker Manager
							</h1>
						</div>
						<div class="hidden sm:ml-6 sm:flex sm:space-x-8">
							<router-link
								to="/"
								class="border-indigo-500 text-gray-900 dark:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
								active-class="border-indigo-500"
							>
								Дашборд
							</router-link>
							<router-link
								to="/tournaments"
								class="border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 hover:text-gray-700 dark:hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
								active-class="border-indigo-500 text-gray-900 dark:text-white"
							>
								Турниры
							</router-link>
						</div>
					</div>
					<div class="flex items-center">
						<span class="text-gray-700 dark:text-gray-300 mr-4">
							{{ user?.name }}
						</span>
						<button
							@click="handleLogout"
							class="text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white px-3 py-2 rounded-md text-sm font-medium"
						>
							Выход
						</button>
					</div>
				</div>
			</div>
		</nav>

		<main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
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
