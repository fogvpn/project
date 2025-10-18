<script lang="ts">
	import { onMount } from 'svelte';
	import { lang, t } from '$lib/lang/lang';
	import { fetchSession } from '$lib/session';
	import ErrorMessage from '$lib/ErrorMessage.svelte';
	import { goto } from '$app/navigation';
	import { subscriptionUrl } from '$lib/store';
	$: currentLang = $lang;

	let uuidInput = '';
	let errorMessage = '';
	let loading = false;
	let accountInput: HTMLInputElement;

	onMount(async () => {
		const s = await fetchSession();
		if (s) {
			goto('/connect');
			return;
		}
		accountInput?.focus();
	});

	async function login(event: Event) {
		event.preventDefault();
		errorMessage = '';
		loading = true;

		try {
			const res = await fetch('/api/login', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				credentials: 'include',
				body: JSON.stringify({ uuid: uuidInput.trim() })
			});

			if (!res.ok) {
				const text = await res.text();
				errorMessage = text || 'Login failed';
				return;
			}

			const data = await res.json();
			if (!data?.subscription) {
				errorMessage = 'Invalid response from server';
				return;
			}

			subscriptionUrl.set(data.subscription ?? null);

			await goto('/connect');
		} catch (err) {
			console.error(err);
			errorMessage = 'Network error';
		} finally {
			loading = false;
		}
	}
</script>

<svelte:head>
	<title>FOGVPN | Login</title>
</svelte:head>

<div class="login">
	<div class="login-container">
		<h1>{t('login', currentLang)}</h1>
		<h2>{t('account_id', currentLang)}</h2>
		<form id="login_form" on:submit={login}>
			<div class="login-input">
				<input
					id="account_id"
					type="text"
					autocomplete="on"
					spellcheck="false"
					bind:this={accountInput}
					bind:value={uuidInput}
				/>
				<ErrorMessage message={errorMessage} />
			</div>

			<div class="login-btns">
				<button type="submit" class="btn" class:loading={loading} disabled={loading}>
					<span class="loader"></span>
					<span class="btn-text">{t('submit', currentLang)}</span>
				</button>
				<span class="login-or">{t('or', currentLang)}</span>
				<a href="/register">{t('register', currentLang)}</a>
			</div>
		</form>
	</div>
</div>
