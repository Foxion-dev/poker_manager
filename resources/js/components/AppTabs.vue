<template>
	<nav class="mb-6 border-b border-gray-200 dark:border-gray-700" :aria-label="ariaLabel">
		<ul class="flex gap-1 -mb-px">
			<li v-for="tab in tabs" :key="tab.id">
				<button
					type="button"
					:class="[
						'px-4 py-3 text-sm font-medium border-b-2 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 rounded-t-lg',
						modelValue === tab.id
							? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
							: 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600'
					]"
					@click="$emit('update:modelValue', tab.id)"
				>
					{{ tab.label }}
				</button>
			</li>
		</ul>
	</nav>
</template>

<script setup>
defineProps({
	tabs: {
		type: Array,
		required: true,
	},
	modelValue: {
		type: [String, Number],
		default: null,
	},
	ariaLabel: {
		type: String,
		default: '',
	},
});

defineEmits(['update:modelValue']);
</script>
