<script>
	export let error;
	export let status;
</script>

<div class="error">
	<h1>{status}</h1>
	<h1>{error?.message ?? 'Page Not Found'}</h1>
</div>

<style>
@media (max-width: 767px) {
	.error {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		white-space: nowrap;
	}
}
</style>