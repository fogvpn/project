<script lang="ts">
	import { onMount } from 'svelte';
	import { fade } from 'svelte/transition';
	import { page } from '$app/stores';
	import { lang, t } from '$lib/lang/lang';
	import { sessionLoaded, fetchSession, logout, userData } from '$lib/session';
	import { isAuth } from '$lib/store';
	import '../app.css';

	let mainEl: HTMLElement;
	let vantaEffect: any;
	let copied = false;

	$: currentLang = $lang;

	function loadScript(src: string): Promise<void> {
		return new Promise((resolve, reject) => {
			const script = document.createElement('script');
			script.src = src;
			script.onload = () => resolve();
			script.onerror = () => reject(new Error(`Failed to load ${src}`));
			document.head.appendChild(script);
		});
	}

	function initVanta(isLight = false) {
		if (!mainEl || !(window as any).VANTA) return;
		if (vantaEffect) vantaEffect.destroy();

		vantaEffect = (window as any).VANTA.FOG({
			el: mainEl,
			mouseControls: true,
			touchControls: true,
			gyroControls: false,
			minHeight: 200.0,
			minWidth: 200.0,
			highlightColor: isLight ? 0xc1c1ff : 0x0,
			midtoneColor: isLight ? 0xa1c4fd : 0x7d9bff,
			lowlightColor: isLight ? 0x6b6b6b : 0x0d0d0d,
			baseColor: isLight ? 0xf1efff : 0x1a1a1a,
			blurFactor: 0.4,
			speed: 0.5,
			zoom: 1.5,
		});
	}

	function setFavicon(isLight: boolean) {
		const link: HTMLLinkElement =
			document.querySelector("link[rel='icon']") || document.createElement("link");

		link.rel = "icon";
		link.href = isLight ? "/favicon/favicon-white.ico" : "/favicon/favicon-black.ico";

		if (!link.parentNode) document.head.appendChild(link);
	}

	function switchLang(l: 'en' | 'ru') {
		lang.set(l);
		localStorage.setItem('lang', l);
	}

	function toggleTheme() {
		const isLight = !document.documentElement.classList.contains('light');
		document.documentElement.classList.toggle('light', isLight);
		localStorage.setItem('theme', isLight ? 'light' : 'dark');
		initVanta(isLight);
		setFavicon(isLight);
	}

	function copyID(event: Event) {
		const btn = event.currentTarget as HTMLButtonElement;
		const id = btn.dataset.id;
		if (!id) return;

		navigator.clipboard.writeText(id).then(() => {
			copied = true;
			setTimeout(() => (copied = false), 1500);
		});
	}

	function setRealVh() {
		const vh = window.innerHeight;
		document.documentElement.style.setProperty('--real-vh', `${vh}px`);
		if (mainEl) {
			mainEl.style.height = `${vh}px`;
		}
	}

	function resizeMain() {
		if (!mainEl) return;

		const padding = window.innerWidth <= 1279 ? 20 : 40;
		const maxW = 1840;
		const w = Math.min(window.innerWidth - padding * 2, maxW);

		const vh = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--real-vh'));
		const h = vh - padding * 2;

		mainEl.style.width = `${w}px`;
		mainEl.style.height = `${h}px`;
		mainEl.style.margin = `${padding}px auto`;
		mainEl.style.minHeight = `${h}px`;
		mainEl.style.maxHeight = `${h}px`;
		mainEl.style.overflow = 'hidden';
	}

	onMount(async () => {
		setRealVh();
		window.addEventListener('resize', setRealVh);

		await fetchSession();

		const savedLang = localStorage.getItem('lang') as 'en' | 'ru' | null;
		if (savedLang) lang.set(savedLang);
		else lang.set(navigator.language.slice(0, 2).toLowerCase() === 'ru' ? 'ru' : 'en');

		const savedTheme = localStorage.getItem('theme');
		let isLight: boolean;
		if (savedTheme) isLight = savedTheme === 'light';
		else isLight = !window.matchMedia('(prefers-color-scheme: dark)').matches;
		document.documentElement.classList.toggle('light', isLight);
		setFavicon(isLight);

		resizeMain();
		window.addEventListener('resize', resizeMain);

		await loadScript('https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js');
		await loadScript('https://cdn.jsdelivr.net/npm/vanta/dist/vanta.fog.min.js');

		initVanta(isLight);

		const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
		const themeListener = (e: MediaQueryListEvent) => {
			if (!savedTheme) {
				const nowLight = !e.matches;
				document.documentElement.classList.toggle('light', nowLight);
				initVanta(nowLight);
				setFavicon(nowLight);
			}
		};
		mediaQuery.addEventListener('change', themeListener);
	});
</script>

<svelte:head>
	<meta name="author" content="FOGVPN">
    <meta name="description" content="Stay connected, even under strict restrictions. With open source code and the XRay protocol, you can access any resource quickly and securely">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="FOGVPN">
    <meta property="og:description" content="Stay connected, even under strict restrictions. With open source code and the XRay protocol, you can access any resource quickly and securely">
    <meta property="og:image" content="favicon/favicon-white.ico">
    <meta property="og:image:width" content="300">
    <meta property="og:image:height" content="300">
    <meta property="og:url" content="https://fogproject.top/">
    <meta property="og:type" content="website">
	<link rel="icon" href="favicon/favicon-white.ico" type="image/x-icon">
	<link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
	<link rel="manifest" href="favicon/site.webmanifest">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/vanta/dist/vanta.fog.min.js"></script>
</svelte:head>

<section class="top-buttons">
	{#if $sessionLoaded}
	  {#if $isAuth}
		<!-- <a class:active={$page.url.pathname === '/payments'} href="/payments">{t('payments', currentLang)}</a>
		<span> | </span> -->
		<button on:click={logout}>{t('logout', currentLang)}</button>
	  {:else}
		<a class:active={$page.url.pathname === '/login'} href="/login">{t('login', currentLang)}</a>
		<span> / </span>
		<a class:active={$page.url.pathname === '/register'} href="/register">{t('register', currentLang)}</a>
	  {/if}
	{/if}
</section>

<section class="under-buttons">
	<button class:active={currentLang === 'en'} on:click={() => switchLang('en')}>Eng</button>
	<span> | </span>
	<button class:active={currentLang === 'ru'} on:click={() => switchLang('ru')}>Rus</button>
</section>

<section class="support">
	<a href="mailto:support@fogproject.top">{t('support', currentLang)}</a>
	<a href="http://" target="_blank" rel="noopener noreferrer">
		<svg width="20" height="20" viewBox="0 0 98 98" xmlns="http://www.w3.org/2000/svg"><path fill="CurrentColor" d="M48.854 0C21.839 0 0 22 0 49.217c0 21.756 13.993 40.172 33.405 46.69 2.427.49 3.316-1.059 3.316-2.362 0-1.141-.08-5.052-.08-9.127-13.59 2.934-16.42-5.867-16.42-5.867-2.184-5.704-5.42-7.17-5.42-7.17-4.448-3.015.324-3.015.324-3.015 4.934.326 7.523 5.052 7.523 5.052 4.367 7.496 11.404 5.378 14.235 4.074.404-3.178 1.699-5.378 3.074-6.6-10.839-1.141-22.243-5.378-22.243-24.283 0-5.378 1.94-9.778 5.014-13.2-.485-1.222-2.184-6.275.486-13.038 0 0 4.125-1.304 13.426 5.052a46.97 46.97 0 0 1 12.214-1.63c4.125 0 8.33.571 12.213 1.63 9.302-6.356 13.427-5.052 13.427-5.052 2.67 6.763.97 11.816.485 13.038 3.155 3.422 5.015 7.822 5.015 13.2 0 18.905-11.404 23.06-22.324 24.283 1.78 1.548 3.316 4.481 3.316 9.126 0 6.6-.08 11.897-.08 13.526 0 1.304.89 2.853 3.316 2.364 19.412-6.52 33.405-24.935 33.405-46.691C97.707 22 75.788 0 48.854 0z"/></svg>
	</a>
	<a href="https://t.me/fogvpnupdates" target="_blank" rel="noopener noreferrer">
		<svg class="tg-svg" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="CurrentColor" d="M11.99432,2a10,10,0,1,0,10,10A9.99917,9.99917,0,0,0,11.99432,2Zm3.17951,15.15247a.70547.70547,0,0,1-1.002.3515l-2.71467-2.10938L9.71484,17.002a.29969.29969,0,0,1-.285.03894l.334-2.98846.01069.00848.00683-.059s4.885-4.44751,5.084-4.637c.20147-.189.135-.23.135-.23.01147-.23053-.36152,0-.36152,0L8.16632,13.299l-2.69549-.918s-.414-.1485-.453-.475c-.041-.324.46649-.5.46649-.5l10.717-4.25751s.881-.39252.881.25751Z"></path></svg>
	</a>
</section>

<main bind:this={mainEl}>
	<div class="container">
		
		<button class="theme" on:click={toggleTheme}></button>

		<div class="main-left">

			<header>
				<a href="/"><h1 class="header-h"><span>FOG</span>VPN</h1></a>
				{#if $page.url.pathname === '/'}
					<p transition:fade={{ duration: 400 }}>
						{t('slogan', currentLang)}
					</p>
				{/if}
			</header>
		
			<nav>
				<a class:active={$page.url.pathname === '/'} href="/">{t('home', currentLang)}</a>
				<a class:active={$page.url.pathname === ($sessionLoaded && $isAuth ? '/connect' : '/register')}
					href={$sessionLoaded && $isAuth ? '/connect' : '/register'}>
					XRay
				</a>
				<a class:active={$page.url.pathname === '/about'} href="/about">{t('about', currentLang)}</a>
				<!-- <a class:active={$page.url.pathname === '/pricing'} href="/pricing">{t('pricing', currentLang)}</a> -->
				<a class:active={$page.url.pathname === '/faq'} href="/faq">FAQ</a>
			</nav>

		</div>

		<div class="main-right">
			<slot />
		</div>
		
	</div>
</main>