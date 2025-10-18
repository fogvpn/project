import { writable, derived } from 'svelte/store';

function authStore<T>(key: string, initial: T) {
  const { subscribe, set } = writable<T>(initial);

  return {
    subscribe,
    set,
    clear: () => set(initial),
    initFromLocalStorage: () => {
      if (typeof localStorage === 'undefined') return;
      const stored = localStorage.getItem(key);
      if (stored) set(JSON.parse(stored));
    },
    saveToLocalStorage: (value: T) => {
      if (typeof localStorage !== 'undefined') {
        localStorage.setItem(key, JSON.stringify(value));
      }
    }
  };
}

export const subscriptionUrl = authStore<string | null>('subscription_url', null);

export const isAuth = derived(subscriptionUrl, ($subscriptionUrl) => !!$subscriptionUrl);
