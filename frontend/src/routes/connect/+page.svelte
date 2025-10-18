<script lang="ts">
	import { onMount } from 'svelte';
	import { subscriptionUrl } from '$lib/store';
	import QRCode from 'qrcode';
	import { tick } from 'svelte';
	import { lang, t } from '$lib/lang/lang';
	import { userData, fetchSession } from '$lib/session';

	$: currentLang = $lang;
	$: user = $userData;
	let copied = false;
	let showQr = false;
	let subscription_url: string | null = null;

	subscriptionUrl.subscribe(value => {
		subscription_url = value;
	});

	onMount(async () => {
		await fetchSession(true);
	});

	function copyLink() {
		const input = document.getElementById('xray_link') as HTMLInputElement;
		if (!input) return;

		input.select();
		input.setSelectionRange(0, input.value.length);

		navigator.clipboard.writeText(input.value).then(() => {
			copied = true;
			setTimeout(() => {
				copied = false;
				input.setSelectionRange(0, 0);
			}, 1500);
		});
	}

	async function showQrCode() {
		showQr = true;
		await tick();

		const input = document.getElementById('xray_link') as HTMLInputElement;
		const qrContainer = document.querySelector('.connect-qr__container') as HTMLElement;

		if (!input || !qrContainer) return;

		qrContainer.innerHTML = '';
		const canvas = document.createElement('canvas');

		try {
			await QRCode.toCanvas(canvas, input.value || 'empty', { width: 150 });
			qrContainer.appendChild(canvas);
		} catch (err) {
			console.error('QR generation failed:', err);
		}
	}

	function closeQr() {
		showQr = false;
	}

</script>

<svelte:head>
	<title>FOGVPN | Connect</title>
</svelte:head>

<div class="connect">
	<div class="connect-container">
		<h1>{t('connect', currentLang)}</h1>
		<div class="connect-info">
			<div class="connect-info__top">
				<span>
					{t('status', currentLang)} 
					<span>
						{#if user}
							{#if user.plan_name?.toLowerCase() === 'free'}
								active
							{:else if user.cycle_end_at}
								active ({new Date(user.cycle_end_at).toLocaleDateString(undefined, {
									year: 'numeric',
									month: 'short',
									day: 'numeric'
								})})
							{:else}
								---
							{/if}
						{:else}
							---
						{/if}
					</span>
				</span>
				<!-- <span>{t('limit', currentLang)} <span>{user?.traffic_limit ? (user.traffic_limit / (1024**3)).toFixed(2) + ' GB' : '---'}</span></span>
				<span>{t('used', currentLang)} <span>{user?.traffic_used ? (user.traffic_used / (1024**3)).toFixed(2) + ' GB' : '---'}</span></span> -->
			  </div>
			<div class="connect-info__link">
				<input type="text" id="xray_link" readonly value={subscription_url}/>
				<button type="button" class="click-to-copy" on:click={copyLink}>
					<svg width="10px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M22 2L4 2L4 -1.74846e-06L23 -8.74228e-08C23.5523 -3.91405e-08 24 0.447716 24 1L24 20L22 20L22 2ZM1 24C0.447716 24 3.91405e-08 23.5523 8.74228e-08 23L1.66103e-06 5C1.70931e-06 4.44771 0.447718 4 1 4L19 4C19.5523 4 20 4.44772 20 5L20 23C20 23.5523 19.5523 24 19 24L1 24ZM18 6L2 6L2 22L18 22L18 6Z" fill="CurrentColor"/>
					</svg>							
					<span class:copied={!copied}>{t('copy', currentLang)}</span>
					<span class:copied={copied}>{t('copied', currentLang)}</span>
				</button>
				<button type="button" class="click-to-qr" on:click={showQrCode}>
					<svg width="10px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M1 0C0.447715 0 0 0.447715 0 1V10C0 10.5523 0.447715 11 1 11H10C10.5523 11 11 10.5523 11 10V1C11 0.447715 10.5523 0 10 0H1ZM2 9V2H9V9H2ZM14 0C13.4477 0 13 0.447715 13 1V10C13 10.5523 13.4477 11 14 11H23C23.5523 11 24 10.5523 24 10V1C24 0.447715 23.5523 0 23 0H14ZM15 9V2H22V9H15ZM0 14C0 13.4477 0.447715 13 1 13H10C10.5523 13 11 13.4477 11 14V23C11 23.5523 10.5523 24 10 24H1C0.447715 24 0 23.5523 0 23V14ZM2 15V22H9V15H2ZM14 13C13.4477 13 13 13.4477 13 14V23C13 23.5523 13.4477 24 14 24H23C23.5523 24 24 23.5523 24 23V14C24 13.4477 23.5523 13 23 13H14ZM15 22V15H22V22H15Z" fill="CurrentColor"/>
					</svg>
												
					<span>{t('qr', currentLang)}</span>
				</button>
			</div>
			<div class="connect-info__xray">
				<span>{t('xray_intro', currentLang)}</span>
				<span>{t('xray_adv1', currentLang)}</span>
				<span>{t('xray_adv2', currentLang)}</span>
				<span>{t('xray_adv3', currentLang)}</span>
				<span>{t('xray_adv4', currentLang)}</span>
				<span>{t('xray_adv5', currentLang)}</span>
			</div>
			<div class="connect-info__apps">
				<span>{t('recommended', currentLang)}</span>
				<div class="apps-btns">
					<a href="https://github.com/InvisibleManVPN/InvisibleMan-XRayClient" target="_blank" rel="noopener noreferrer">
						<button class="btn">
							<svg width="15px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="CurrentColor">
								<g id="SVGRepo_bgCarrier" stroke-width="0"/>
								<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>
								<g id="SVGRepo_iconCarrier"><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Dribbble-Light-Preview" transform="translate(-60.000000, -7439.000000)" fill="CurrentColor"> <g id="icons" transform="translate(56.000000, 160.000000)"> <path d="M13.1458647,7289.43426 C13.1508772,7291.43316 13.1568922,7294.82929 13.1619048,7297.46884 C16.7759398,7297.95757 20.3899749,7298.4613 23.997995,7299 C23.997995,7295.84873 24.002005,7292.71146 23.997995,7289.71311 C20.3809524,7289.71311 16.7649123,7289.43426 13.1458647,7289.43426 M4,7289.43526 L4,7296.22153 C6.72581454,7296.58933 9.45162907,7296.94113 12.1724311,7297.34291 C12.1774436,7294.71736 12.1704261,7292.0908 12.1704261,7289.46524 C9.44661654,7289.47024 6.72380952,7289.42627 4,7289.43526 M4,7281.84344 L4,7288.61071 C6.72581454,7288.61771 9.45162907,7288.57673 12.1774436,7288.57973 C12.1754386,7285.96017 12.1754386,7283.34361 12.1724311,7280.72405 C9.44461153,7281.06486 6.71679198,7281.42567 4,7281.84344 M24,7288.47179 C20.3879699,7288.48578 16.7759398,7288.54075 13.1619048,7288.55175 C13.1598997,7285.88921 13.1598997,7283.22967 13.1619048,7280.56914 C16.7689223,7280.01844 20.3839599,7279.50072 23.997995,7279 C24,7282.15826 23.997995,7285.31353 24,7288.47179"> </path> </g> </g> </g> </g>
							</svg>
							Windows
						</button>
					</a>
					<a href="https://apps.apple.com/app/v2raytun/id6476628951" target="_blank" rel="noopener noreferrer">
						<button class="btn">
							<svg width="15px" viewBox="-1.5 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000" stroke="#000">
								<g id="SVGRepo_bgCarrier" stroke-width="0"/>
								<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>
								<g id="SVGRepo_iconCarrier"><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Dribbble-Light-Preview" transform="translate(-102.000000, -7439.000000)" fill="CurrentColor"> <g id="icons" transform="translate(56.000000, 160.000000)"> <path d="M57.5708873,7282.19296 C58.2999598,7281.34797 58.7914012,7280.17098 58.6569121,7279 C57.6062792,7279.04 56.3352055,7279.67099 55.5818643,7280.51498 C54.905374,7281.26397 54.3148354,7282.46095 54.4735932,7283.60894 C55.6455696,7283.69593 56.8418148,7283.03894 57.5708873,7282.19296 M60.1989864,7289.62485 C60.2283111,7292.65181 62.9696641,7293.65879 63,7293.67179 C62.9777537,7293.74279 62.562152,7295.10677 61.5560117,7296.51675 C60.6853718,7297.73474 59.7823735,7298.94772 58.3596204,7298.97372 C56.9621472,7298.99872 56.5121648,7298.17973 54.9134635,7298.17973 C53.3157735,7298.17973 52.8162425,7298.94772 51.4935978,7298.99872 C50.1203933,7299.04772 49.0738052,7297.68074 48.197098,7296.46676 C46.4032359,7293.98379 45.0330649,7289.44985 46.8734421,7286.3899 C47.7875635,7284.87092 49.4206455,7283.90793 51.1942837,7283.88393 C52.5422083,7283.85893 53.8153044,7284.75292 54.6394294,7284.75292 C55.4635543,7284.75292 57.0106846,7283.67793 58.6366882,7283.83593 C59.3172232,7283.86293 61.2283842,7284.09893 62.4549652,7285.8199 C62.355868,7285.8789 60.1747177,7287.09489 60.1989864,7289.62485"> </path> </g> </g> </g> </g>
							</svg>
							iOS & Mac
						</button>
					</a>
					<a href="https://play.google.com/store/apps/details?id=app.hiddify.com" target="_blank" rel="noopener noreferrer">
						<button class="btn">
							<svg width="15px" fill="CurrentColor" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve" stroke="CurrentColor">
								<g id="SVGRepo_bgCarrier" stroke-width="0"/>
								<g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>
								<g id="SVGRepo_iconCarrier"> <g fill="CurrentColor"> <path display="inline" d="M120.606,169h270.788v220.663c0,13.109-10.628,23.737-23.721,23.737h-27.123v67.203 c0,17.066-13.612,30.897-30.415,30.897c-16.846,0-30.438-13.831-30.438-30.897v-67.203h-47.371v67.203 c0,17.066-13.639,30.897-30.441,30.897c-16.799,0-30.437-13.831-30.437-30.897v-67.203h-27.099 c-13.096,0-23.744-10.628-23.744-23.737V169z M67.541,167.199c-16.974,0-30.723,13.963-30.723,31.2v121.937 c0,17.217,13.749,31.204,30.723,31.204c16.977,0,30.723-13.987,30.723-31.204V198.399 C98.264,181.162,84.518,167.199,67.541,167.199z M391.395,146.764H120.606c3.342-38.578,28.367-71.776,64.392-90.998 l-25.746-37.804c-3.472-5.098-2.162-12.054,2.946-15.525c5.102-3.471,12.044-2.151,15.533,2.943l28.061,41.232 c15.558-5.38,32.446-8.469,50.208-8.469c17.783,0,34.672,3.089,50.229,8.476L334.29,5.395c3.446-5.108,10.41-6.428,15.512-2.957 c5.108,3.471,6.418,10.427,2.946,15.525l-25.725,37.804C363.047,74.977,388.055,108.175,391.395,146.764z M213.865,94.345 c0-8.273-6.699-14.983-14.969-14.983c-8.291,0-14.99,6.71-14.99,14.983c0,8.269,6.721,14.976,14.99,14.976 S213.865,102.614,213.865,94.345z M329.992,94.345c0-8.273-6.722-14.983-14.99-14.983c-8.291,0-14.97,6.71-14.97,14.983 c0,8.269,6.679,14.976,14.97,14.976C323.271,109.321,329.992,102.614,329.992,94.345z M444.48,167.156 c-16.956,0-30.744,13.984-30.744,31.222v121.98c0,17.238,13.788,31.226,30.744,31.226c16.978,0,30.701-13.987,30.701-31.226 v-121.98C475.182,181.14,461.458,167.156,444.48,167.156z"> </path> </g> </g>
							</svg>
							Android
						</button>
					</a>
				</div>
			</div>
		</div>
	</div>
	{#if showQr}
	<div class="connect-qr" on:click={closeQr}>
		<div class="connect-qr__container"></div>
	</div>
	{/if}
</div>

