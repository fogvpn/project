<script lang="ts">
	import { fade } from 'svelte/transition';

	export let message: string = '';
	export let duration: number = 5000;

	let visible = false;
	let timeout: number | undefined;

	$: if (message) {
		visible = true;

		if (timeout) clearTimeout(timeout);
		timeout = window.setTimeout(() => {
			visible = false;
		}, duration);
	}
</script>

{#if visible}
	<div class="input-error" transition:fade={{ duration: 300 }}>
		{message}
	</div>
{/if}