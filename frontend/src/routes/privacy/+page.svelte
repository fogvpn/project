<script lang="ts">
	import { lang, t } from '$lib/lang/lang';
	$: currentLang = $lang;
</script>

<svelte:head>
	<title>FOGVPN | Privacy Policy</title>
</svelte:head>

<div class="legal">
    <div class="legal-container">
        <div>
            <h1>{t('privacy.title', currentLang)}</h1>
            <span><b>{t('common.last_updated_label', currentLang)}</b> {t('privacy.last_updated', currentLang)}</span>
        </div>

        <div>
            <h2>{t('privacy.s1.title', currentLang)}</h2>
            <p>{t('privacy.s1.p1', currentLang)}</p>
        </div>

        <div>
            <h2>{t('privacy.s2.title', currentLang)}</h2>
            <p>{t('privacy.s2.p1', currentLang)}</p>
            <p>{t('privacy.s2.p2', currentLang)}</p>
        </div>

        <div>
            <h2>{t('privacy.s3.title', currentLang)}</h2>
            <p>{t('privacy.s3.p1', currentLang)}</p>
            <p>{t('privacy.s3.p2', currentLang)}</p>
        </div>

        <div>
            <h2>{t('privacy.s4.title', currentLang)}</h2>
            <p>{t('privacy.s4.p1', currentLang)}</p>
            <p>{t('privacy.s4.p2', currentLang)}</p>
        </div>

        <div>
            <h2>{t('privacy.s5.title', currentLang)}</h2>
            <p>{t('privacy.s5.p1', currentLang)}</p>
            <p>{t('privacy.s5.p2', currentLang)}</p>
        </div>

        <div>
            <h2>{t('privacy.s6.title', currentLang)}</h2>
            <p>{t('privacy.s6.lead', currentLang)}</p>
            <ul>
                <li>{t('common.contact.email_label', currentLang)}:
                <a href={"mailto:" + t('common.contact.email_value', currentLang)}>{t('common.contact.email_value', currentLang)}</a>
                </li>
                <li>{t('common.contact.telegram_label', currentLang)}:
                <a href={t('common.contact.telegram_url', currentLang)} target="_blank">{t('common.contact.telegram_handle', currentLang)}</a>
                </li>
            </ul>
        </div>

        <div>
            <h2>{t('privacy.s7.title', currentLang)}</h2>
            <p>{t('privacy.s7.p1', currentLang)}</p>
            <p>{t('privacy.s7.p2', currentLang)}</p>    
        </div>

    </div>
</div>