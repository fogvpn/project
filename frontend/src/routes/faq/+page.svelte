<script lang="ts">
	import { lang, t } from '$lib/lang/lang';
	$: currentLang = $lang;
	
	let activeIndex: number | null = null;

	function toggle(index: number) {
		activeIndex = activeIndex === index ? null : index;
	}
</script>

<svelte:head>
	<title>FOGVPN | FAQ</title>
</svelte:head>

<div class="faq">
	<div class="faq-container">
		<h1>{t('faq', currentLang)}</h1>
		<div class="faq-blocks">

			<div class="faq-block" on:click={() => toggle(0)}>
				<h2>{t('faq_q1', currentLang)}</h2>
				<p class:active={activeIndex === 0}>
					{t('faq_a1', currentLang)}
				</p>
			</div>

			<div class="faq-block" on:click={() => toggle(1)}>
				<h2>{t('faq_q2', currentLang)}</h2>
				<p class:active={activeIndex === 1}>
					{t('faq_a2', currentLang)}
				</p>
			</div>

			<div class="faq-block" on:click={() => toggle(2)}>
				<h2>{t('faq_q3', currentLang)}</h2>
				<p class:active={activeIndex === 2}>
					{t('faq_a3', currentLang)}
				</p>
			</div>

			<div class="faq-block" on:click={() => toggle(3)}>
				<h2>{t('faq_q4', currentLang)}</h2>
				<p class:active={activeIndex === 3}>
					{t('faq_a4', currentLang)}
				</p>
			</div>

			<div class="faq-block" on:click={() => toggle(4)}>
				<h2>{t('faq_q5', currentLang)}</h2>
				<p class:active={activeIndex === 4}>
					{@html t('faq_a5', currentLang)}
				</p>
			</div>

		</div>
	</div>
</div>
