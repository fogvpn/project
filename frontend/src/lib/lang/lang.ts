import { writable } from 'svelte/store';
import translations from './i18n.json';

export type Lang = 'en' | 'ru';

const current = writable<Lang>('en');

const dict: Record<Lang, Record<string, string>> = translations as any;

function t(key: string, lang: Lang = 'en'): string {
	return dict[lang]?.[key] ?? key;
}

export { current as lang, t };
