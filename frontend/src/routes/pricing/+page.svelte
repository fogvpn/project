<script lang="ts">
	import { onMount } from 'svelte';
	import { lang, t } from '$lib/lang/lang';
	import { fetchSession } from '$lib/session';
	import { goto } from '$app/navigation';

	$: currentLang = $lang;

	let loggedIn = false;

	onMount(async () => {
		const s = await fetchSession();
		loggedIn = !!s;
	});

	async function subscribe(plan: 'month' | 'year') {
		const res = await fetch('/api/payment', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ plan })
		});
		if (!res.ok) return;
		const { url } = await res.json();
		window.location.href = url;
	}
</script>

<svelte:head>
	<title>FOGVPN | Pricing</title>
</svelte:head>

<div class="pricing">
	<a
		href={loggedIn ? '/connect' : '/register'}
		class="pricing-card pricing-card__free"
	>
		<h1>{t('pricing_free_title', currentLang)}</h1>
		<p>{t('pricing_free_desc', currentLang)}</p>
	</a>

	<button
		on:click={() => loggedIn ? subscribe('month') : goto('/register')}
		class="pricing-card pricing-card__month"
	>
		<h1>{t('pricing_month_title', currentLang)}</h1>
		<p>{t('pricing_month_desc', currentLang)}</p>
	</button>

	<button
		on:click={() => loggedIn ? subscribe('year') : goto('/register')}
		class="pricing-card pricing-card__year"
	>
		<h1>{t('pricing_year_title', currentLang)}</h1>
		<p>{t('pricing_year_desc', currentLang)}</p>
	</button>
</div>
