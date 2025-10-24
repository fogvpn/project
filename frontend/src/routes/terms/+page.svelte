<script lang="ts">
	import { lang, t } from '$lib/lang/lang';
	$: currentLang = $lang;
</script>

<svelte:head>
	<title>FOGVPN | Terms and Conditions</title>
</svelte:head>

<div class="legal">
    <div class="legal-container">
        <div>
            <h1>{t('terms.title', currentLang)}</h1>
        <span><b>{t('common.last_updated_label', currentLang)}</b> {t('terms.last_updated', currentLang)}</span>
        </div>

        <div>
            <h2>{t('terms.s1.title', currentLang)}</h2>
        <p>{t('terms.s1.p1', currentLang)}</p>
        <p>{t('terms.s1.p2', currentLang)}</p>
        </div>

        <div>
            <h2>{t('terms.s2.title', currentLang)}</h2>
        <p>{t('terms.s2.p1', currentLang)}</p>
        <p>{t('terms.s2.p2', currentLang)}</p>
        </div>

        <div>
            <h2>{t('terms.s3.title', currentLang)}</h2>
        <p>{t('terms.s3.p1', currentLang)}</p>
        <p>{t('terms.s3.p2', currentLang)}</p>
        </div>

        <div>
            <h2>{t('terms.s4.title', currentLang)}</h2>
        <p>{t('terms.s4.p1', currentLang)}</p>
        <p>{t('terms.s4.p2', currentLang)}</p>
        <p>{t('terms.s4.p3', currentLang)}</p>
        </div>

        <div>
            <h2>{t('terms.s5.title', currentLang)}</h2>
        <p>{t('terms.s5.p1', currentLang)}</p>
        <p>{t('terms.s5.p2', currentLang)}</p>
        <p>{t('terms.s5.p3', currentLang)}</p>
        <p>{t('terms.s5.p4', currentLang)}</p>
        </div>

        <div>
            <h2>{t('terms.s6.title', currentLang)}</h2>
        <p>{t('terms.s6.p1', currentLang)}</p>
        <p>{t('terms.s6.p2', currentLang)}</p>
        </div>

        <div>
            <h2>{t('terms.s7.title', currentLang)}</h2>
        <p>{t('terms.s7.lead', currentLang)}</p>
        <ul>
            <li>{t('common.contact.email_label', currentLang)}:
            <a href={"mailto:" + t('common.contact.email_value', currentLang)}>{t('common.contact.email_value', currentLang)}</a>
            </li>
            <li>{t('common.contact.telegram_label', currentLang)}:
            <a href={t('common.contact.telegram_url', currentLang)} target="_blank">{t('common.contact.telegram_handle', currentLang)}</a>
            </li>
        </ul>
        </div>

        <span>{t('terms.closing', currentLang)}</span>
    </div>
</div>