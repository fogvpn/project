<script lang="ts">
	import { onMount, tick } from 'svelte';
	import { lang, t } from '$lib/lang/lang';
	import { fetchSession } from '$lib/session';
	import ErrorMessage from '$lib/ErrorMessage.svelte';
	import { goto } from '$app/navigation';
	import { subscriptionUrl } from '$lib/store';
	$: currentLang = $lang;

	let uuidInput = '';
	let errorMessage = '';
	let copied = false;
	let loading = false;
	let captchaId: number | null = null;
	let captchaToken = '';
	let currentTheme: 'light' | 'dark' = 'light';

	const HCAPTCHA_SITEKEY = '10000000-ffff-ffff-ffff-000000000001';

	function generateSecureId(length = 43) {
		const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		const specials = '!@#$%^&*()-_=+[]{};:,.<>?';
		let resultArray: string[] = [];

		for (let i = 0; i < 3; i++) {
			resultArray.push(specials[Math.floor(Math.random() * specials.length)]);
		}

		while (resultArray.length < length) {
			resultArray.push(chars[Math.floor(Math.random() * chars.length)]);
		}

		resultArray.sort(() => 0.5 - Math.random());
		return resultArray.join('');
	}

	function copyId() {
		const input = document.getElementById('account_id') as HTMLInputElement;
		if (!input) return;
		input.select();
		navigator.clipboard.writeText(uuidInput).then(() => {
			copied = true;
			setTimeout(() => {
				copied = false;
				input.setSelectionRange(0, 0);
			}, 2000);
		});
	}

	function loadHCaptcha(): Promise<void> {
		return new Promise((resolve) => {
			if ((window as any).hcaptcha) return resolve();

			(window as any).onHCaptchaLoad = () => resolve();

			const script = document.createElement('script');
			script.src = 'https://js.hcaptcha.com/1/api.js?render=explicit&onload=onHCaptchaLoad';
			script.async = true;
			script.defer = true;
			document.body.appendChild(script);
		});
	}

	async function renderCaptcha() {
		await tick();
		const container = document.querySelector('.h-captcha');
		if (!container || !(window as any).hcaptcha) return;

		currentTheme = document.documentElement.classList.contains('light') ? 'light' : 'dark';

		if (captchaId !== null) {
			(window as any).hcaptcha.reset(captchaId);
			return;
		}

		captchaId = (window as any).hcaptcha.render(container, {
			sitekey: HCAPTCHA_SITEKEY,
			theme: currentTheme,
			size: 'normal',
			callback: (token: string) => captchaToken = token,
			'error-callback': () => {
				captchaToken = '';
				errorMessage = 'Captcha error, please try again.';
			},
			'expired-callback': () => captchaToken = ''
		});
	}

	function observeThemeChanges() {
		const observer = new MutationObserver(() => {
			const newTheme = document.documentElement.classList.contains('light') ? 'light' : 'dark';
			if (newTheme !== currentTheme && captchaId !== null) {
				currentTheme = newTheme;
				(window as any).hcaptcha.reset(captchaId);
			}
		});
		observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
	}

	async function register(event: Event) {
		event.preventDefault();
		errorMessage = '';
		loading = true;

		if (!captchaToken) {
			errorMessage = 'Please complete captcha';
			loading = false;
			return;
		}

		try {
			const res = await fetch('/api/register', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				credentials: 'include',
				body: JSON.stringify({ uuid: uuidInput, captcha: captchaToken })
			});

			if (!res.ok) {
				const text = await res.text();
				errorMessage = text || 'Registration failed';
			} else {
				const data = await res.json();
				subscriptionUrl.set(data.subscription);
				await goto('/connect');
			}
		} catch (err) {
			console.error(err);
			errorMessage = 'Network error';
		} finally {
			loading = false;
		}
	}

	onMount(async () => {
		const s = await fetchSession();
		if (s) {
			goto('/connect');
			return;
		}

		uuidInput = generateSecureId();
		await loadHCaptcha();
		await renderCaptcha();
		observeThemeChanges();
	});
</script>

<svelte:head>
	<title>FOGVPN | Register</title>
</svelte:head>

<div class="register">
	<div class="register-container">
		<h1>{t('register', currentLang)}</h1>
		<div class="register-info">
			<p>
				{@html t('we_prioritize', currentLang)}
			</p>
			<div class="register-important">
				<h2>{t('important_title', currentLang)}</h2>
				<span>{t('important_1', currentLang)}</span>
				<span>{t('important_2', currentLang)}</span>
				<span>{t('important_3', currentLang)}</span>
				<span>{t('important_4', currentLang)}</span>
			</div>
			<div>
				<h2>{t('account_id', currentLang)}</h2>
				<form id="register_form" on:submit={register}>
					<div class="register-form">
						<div class="register-input">
							<input
							type="text"
							id="account_id"
							readonly
							bind:value={uuidInput}
							/>
							<button type="button" class="click-to-copy" on:click={copyId}>
								<svg width="10" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M22 2L4 2L4 -1.74846e-06L23 -8.74228e-08C23.5523 -3.91405e-08 24 0.447716 24 1L24 20L22 20L22 2ZM1 24C0.447716 24 3.91405e-08 23.5523 8.74228e-08 23L1.66103e-06 5C1.70931e-06 4.44771 0.447718 4 1 4L19 4C19.5523 4 20 4.44772 20 5L20 23C20 23.5523 19.5523 24 19 24L1 24ZM18 6L2 6L2 22L18 22L18 6Z" fill="CurrentColor"/>
								</svg>							
								<span class:copied={!copied}>{t('copy', currentLang)}</span>
								<span class:copied={copied}>{t('copied', currentLang)}</span>
							</button>
						</div>
						<div class="captcha"> 
							<div class="h-captcha"></div>
						</div>
						<ErrorMessage message={errorMessage} />
					</div>

					

					<button type="submit" class="btn" class:loading={loading} disabled={loading}>
						<span class="loader"></span>
						<span class="btn-text">{t('submit', currentLang)}</span>
					</button>
					<span class="already-have">
						{@html t('already_have_account', currentLang)}
					</span>
				</form>
			</div>
		</div>
	</div>
</div>