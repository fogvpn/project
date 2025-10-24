import { writable, get } from 'svelte/store';
import translations from './i18n.json';

export type Lang = 'en' | 'ru';

export const lang = writable<Lang>('en');

type Dict = Record<Lang, Record<string, unknown>>;
const dict: Dict = translations as Dict;

function getByPath(obj: unknown, path: string): unknown {
  if (!obj) return undefined;
  return path.split('.').reduce<unknown>((acc, part) => {
    if (acc && typeof acc === 'object' && part in (acc as any)) {
      return (acc as any)[part];
    }
    return undefined;
  }, obj);
}

export function t(key: string, current?: Lang): string {
  const currentLang = current ?? get(lang);
  const fromCurrent = getByPath(dict[currentLang], key);
  if (typeof fromCurrent === 'string') return fromCurrent;

  const fromEn = getByPath(dict.en, key);
  if (typeof fromEn === 'string') return fromEn;

  return key;
}