<script lang="ts">
	import { lang, t } from '$lib/lang/lang';
	import { onMount } from 'svelte';
	import { paymentsData, fetchPayments } from '$lib/session';

	$: currentLang = $lang;

	onMount(async () => {
		await fetchPayments();
	});
</script>

<svelte:head>
	<title>FOGVPN | Payments</title>
</svelte:head>

<div class="payments">
	<div class="payments-container">
		<h1>{t('payments', currentLang)}</h1>

		{#if $paymentsData && $paymentsData.length > 0}
			<div class="payments-cards">
				{#each $paymentsData as p}
					<div class="payments-card">
						<p><b>{t('status', currentLang)}</b> {p.status}</p>
						<p><b>{t('sum', currentLang)}</b> {p.amount} {p.currency}</p>
						<p><b>{t('date', currentLang)}</b> {p.paid_at ?? p.created_at}</p>
					</div>
				{/each}
			</div>
		{:else}
			<h2>{t('no_data', currentLang)}</h2>
		{/if}

		<a href="/pricing" class="btn pay-btn">{t('pay', currentLang)}</a>
	</div>
</div>