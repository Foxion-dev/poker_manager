<template>
	<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
		<div class="max-w-md w-full space-y-8">
			<div>
				<h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
					Вход в систему
				</h2>
			</div>
			<form class="mt-8 space-y-6" @submit.prevent="handleLogin">
				<div class="rounded-md shadow-sm -space-y-px">
					<div>
						<label for="email" class="sr-only">Email</label>
						<input
							id="email"
							v-model="form.email"
							type="email"
							required
							class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
							placeholder="Email адрес"
						/>
					</div>
					<div>
						<label for="password" class="sr-only">Пароль</label>
						<input
							id="password"
							v-model="form.password"
							type="password"
							required
							class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
							placeholder="Пароль"
						/>
					</div>
				</div>

				<div v-if="error" class="text-red-600 text-sm text-center">
					{{ error }}
				</div>

				<div>
					<button
						type="submit"
						:disabled="loading"
						class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
					>
						{{ loading ? 'Вход...' : 'Войти' }}
					</button>
				</div>

				<div class="text-center">
					<router-link
						to="/register"
						class="text-indigo-600 hover:text-indigo-500"
					>
						Нет аккаунта? Зарегистрироваться
					</router-link>
				</div>
			</form>
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
