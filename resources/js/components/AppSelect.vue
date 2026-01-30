<template>
	<div ref="rootRef" class="relative">
		<button
			type="button"
			:class="triggerClasses"
			@click="open = !open"
			@blur="onBlur"
		>
			<span v-if="$slots.selected" class="flex items-center gap-2 min-w-0 text-gray-900 dark:text-white">
				<slot name="selected" :option="selectedOption" :placeholder="placeholder">
					{{ selectedLabel || placeholder }}
				</slot>
			</span>
			<span v-else class="truncate text-gray-900 dark:text-white">{{ selectedLabel || placeholder }}</span>
			<svg
				class="ml-2 h-5 w-5 flex-shrink-0 text-gray-500 dark:text-white/80 transition-transform duration-200"
				:class="{ 'rotate-180': open }"
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 20 20"
				fill="currentColor"
			>
				<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
			</svg>
		</button>
		<Transition
			enter-active-class="transition ease-out duration-100"
			enter-from-class="opacity-0 scale-95"
			enter-to-class="opacity-100 scale-100"
			leave-active-class="transition ease-in duration-75"
			leave-from-class="opacity-100 scale-100"
			leave-to-class="opacity-0 scale-95"
		>
			<div
				v-show="open"
				class="absolute z-50 mt-1 w-full min-w-[12rem] rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-xl py-1 max-h-60 overflow-auto text-gray-900 dark:text-white"
			>
				<button
					v-for="option in options"
					:key="getOptionValue(option)"
					type="button"
					class="w-full px-4 py-2.5 text-left flex items-center gap-3 text-gray-900 dark:text-white hover:bg-indigo-50 dark:hover:bg-gray-700/80 focus:bg-indigo-50 dark:focus:bg-gray-700/80 focus:outline-none transition-colors"
					:class="{ 'bg-indigo-50 dark:bg-gray-700': isSelected(option) }"
					@mousedown.prevent="select(option)"
				>
					<slot :option="option" :selected="isSelected(option)">
						{{ getOptionLabel(option) }}
					</slot>
				</button>
			</div>
		</Transition>
	</div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
	modelValue: {
		type: [String, Number, null],
		default: null,
	},
	options: {
		type: Array,
		required: true,
	},
	placeholder: {
		type: String,
		default: 'Выберите...',
	},
	optionValue: {
		type: String,
		default: 'id',
	},
	optionLabel: {
		type: String,
		default: 'name',
	},
});

const emit = defineEmits(['update:modelValue']);

const rootRef = ref(null);
const open = ref(false);

const selectedOption = computed(() => {
	const val = props.modelValue;
	if (val == null || val === '') return null;
	return props.options.find(opt => getOptionValue(opt) === val) ?? null;
});

const selectedLabel = computed(() => {
	return selectedOption.value ? getOptionLabel(selectedOption.value) : '';
});

const triggerClasses = computed(() => [
	'w-full flex items-center justify-between px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600',
	'bg-white dark:bg-gray-700 text-gray-900 dark:text-white',
	'focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none',
	'transition duration-200 text-left',
]);

function getOptionValue(option) {
	if (typeof props.optionValue === 'function') return props.optionValue(option);
	return option?.[props.optionValue];
}

function getOptionLabel(option) {
	if (typeof props.optionLabel === 'function') return props.optionLabel(option);
	return option?.[props.optionLabel] ?? '';
}

function isSelected(option) {
	return getOptionValue(option) === props.modelValue;
}

function select(option) {
	emit('update:modelValue', getOptionValue(option));
	open.value = false;
}

function onBlur() {
	setTimeout(() => { open.value = false; }, 150);
}

function handleClickOutside(e) {
	if (rootRef.value && !rootRef.value.contains(e.target)) {
		open.value = false;
	}
}

onMounted(() => {
	document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
	document.removeEventListener('click', handleClickOutside);
});
</script>
