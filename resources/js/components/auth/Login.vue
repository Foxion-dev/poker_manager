<template>
	<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-12 px-4 sm:px-6 lg:px-8">
		<div class="max-w-md w-full">
			<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 space-y-6">
				<div class="text-center">
					<div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 mb-4">
						<span class="text-3xl">🃏</span>
					</div>
					<h2 class="text-3xl font-bold text-gray-900 dark:text-white">
						Добро пожаловать
					</h2>
					<p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
						Войдите в свой аккаунт Poker Manager
					</p>
				</div>

				<form class="space-y-5" @submit.prevent="handleLogin">
					<div class="space-y-4">
						<div>
							<label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
								Email адрес
							</label>
							<input
								id="email"
								v-model="form.email"
								type="email"
								required
								class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								placeholder="your@email.com"
							/>
						</div>
						<div>
							<label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
								Пароль
							</label>
							<input
								id="password"
								v-model="form.password"
								type="password"
								required
								class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200"
								placeholder="••••••••"
							/>
						</div>
					</div>

					<div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm">
						{{ error }}
					</div>

					<button
						type="submit"
						:disabled="loading"
						class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-[1.02]"
					>
						<span v-if="loading" class="mr-2">
							<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
						</span>
						{{ loading ? 'Вход...' : 'Войти' }}
					</button>

					<div class="text-center">
						<p class="text-sm text-gray-600 dark:text-gray-400">
							Нет аккаунта?
							<router-link
								to="/register"
								class="ml-1 font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors"
							>
								Зарегистрироваться
							</router-link>
						</p>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
	email: '',
	password: '',
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
	loading.value = true;
	error.value = '';

	try {
		await authStore.login(form.value);
		router.push({ name: 'Dashboard' });
	} catch (err) {
		error.value = err.response?.data?.message || 'Ошибка входа';
	} finally {
		loading.value = false;
	}
};
</script>
