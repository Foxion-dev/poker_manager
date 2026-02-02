<template>
	<section class="mb-8">
		<h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Смена пароля</h3>
		<form
			class="max-w-md space-y-4"
			@submit.prevent="submitPasswordChange"
		>
			<div v-if="passwordMessage" :class="passwordMessage.type === 'error' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'" class="text-sm">
				{{ passwordMessage.text }}
			</div>
			<div>
				<label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текущий пароль</label>
				<input
					id="current_password"
					v-model="passwordForm.current_password"
					type="password"
					autocomplete="current-password"
					required
					class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
				/>
			</div>
			<div>
				<label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Новый пароль</label>
				<input
					id="new_password"
					v-model="passwordForm.password"
					type="password"
					autocomplete="new-password"
					required
					minlength="8"
					class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
				/>
				<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Минимум 8 символов</p>
			</div>
			<div>
				<label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Подтверждение нового пароля</label>
				<input
					id="password_confirmation"
					v-model="passwordForm.password_confirmation"
					type="password"
					autocomplete="new-password"
					required
					class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
				/>
			</div>
			<button
				type="submit"
				:disabled="passwordLoading"
				class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
			>
				{{ passwordLoading ? 'Сохранение…' : 'Изменить пароль' }}
			</button>
		</form>
	</section>
</template>

<script setup>
import { ref } from 'vue';
import { authService } from '../../services/authService';

const passwordForm = ref({
	current_password: '',
	password: '',
	password_confirmation: '',
});
const passwordLoading = ref(false);
const passwordMessage = ref(null);

const submitPasswordChange = async () => {
	passwordMessage.value = null;
	if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
		passwordMessage.value = { type: 'error', text: 'Пароли не совпадают.' };
		return;
	}
	if (passwordForm.value.password.length < 8) {
		passwordMessage.value = { type: 'error', text: 'Новый пароль должен быть не менее 8 символов.' };
		return;
	}
	passwordLoading.value = true;
	try {
		await authService.changePassword({
			current_password: passwordForm.value.current_password,
			password: passwordForm.value.password,
			password_confirmation: passwordForm.value.password_confirmation,
		});
		passwordMessage.value = { type: 'success', text: 'Пароль успешно изменён.' };
		passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
	} catch (err) {
		const msg = err.response?.data?.errors?.current_password?.[0] ?? err.response?.data?.message ?? 'Не удалось изменить пароль.';
		passwordMessage.value = { type: 'error', text: msg };
	} finally {
		passwordLoading.value = false;
	}
};
</script>
