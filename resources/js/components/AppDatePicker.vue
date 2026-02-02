<template>
	<VueDatePicker
		:model-value="modelValue"
		@update:model-value="$emit('update:modelValue', $event)"
		:dark="isDark"
		:range="range"
		:partial-range="partialRange"
		:enable-time-picker="enableTimePicker"
		:clearable="clearable"
		:format="format"
		:placeholder="placeholder"
		auto-apply
		:input-class-name="inputClassName"
		class="app-datepicker"
	/>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps({
	modelValue: {
		type: [Date, Array, String, Number],
		default: null
	},
	range: {
		type: [Boolean, Object],
		default: false
	},
	partialRange: {
		type: Boolean,
		default: false
	},
	enableTimePicker: {
		type: Boolean,
		default: false
	},
	clearable: {
		type: Boolean,
		default: true
	},
	format: {
		type: String,
		default: 'dd.MM.yyyy'
	},
	placeholder: {
		type: String,
		default: 'Выберите дату'
	},
	forceDark: {
		type: Boolean,
		default: null
	}
});

defineEmits(['update:modelValue']);

const systemPrefersDark = ref(false);
const hasDarkClass = ref(false);

const isDark = computed(() => {
	if (props.forceDark === true) return true;
	if (props.forceDark === false) return false;
	return hasDarkClass.value || systemPrefersDark.value;
});

const inputClassName = 'w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition duration-200';

const updateTheme = () => {
	systemPrefersDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
	hasDarkClass.value = document.documentElement.classList.contains('dark');
};

let mediaQuery = null;
let observer = null;

onMounted(() => {
	updateTheme();
	mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
	mediaQuery.addEventListener('change', updateTheme);
	observer = new MutationObserver(updateTheme);
	observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

onUnmounted(() => {
	if (mediaQuery) mediaQuery.removeEventListener('change', updateTheme);
	if (observer) observer.disconnect();
});
</script>

<style>
.app-datepicker.dp__theme_dark,
.dp__theme_dark {
	--dp-background-color: #1f2937;
	--dp-text-color: #f9fafb;
	--dp-hover-color: #374151;
	--dp-hover-text-color: #f9fafb;
	--dp-primary-color: #6366f1;
	--dp-primary-text-color: #fff;
	--dp-secondary-color: #9ca3af;
	--dp-border-color: #4b5563;
	--dp-menu-border-color: #4b5563;
}
</style>
