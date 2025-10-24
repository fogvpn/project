import { writable, get } from 'svelte/store';
import { subscriptionUrl } from './store';

export interface SessionData {
  subscription?: string;
  uuid?: string;
  status?: string;
  traffic_used?: number;
  traffic_limit?: number;
  plan_id?: number;
  plan_name?: string;
  cycle_start_at?: string;
  cycle_end_at?: string | null;
  duration?: string;
}

export const sessionLoaded = writable(false);
export const userData = writable<SessionData | null>(null);

let fetched = false;

export async function fetchSession(force = false): Promise<SessionData | null> {
  if (fetched && !force) {
    sessionLoaded.set(true);
    const subscription = get(subscriptionUrl);
    if (subscription) {
      return { subscription };
    }
    return null;
  }

  fetched = true;
  sessionLoaded.set(false);

  try {
    const res = await fetch('/api/me', { credentials: 'include' });
    if (!res.ok) {
      await logoutInternal();
      return null;
    }

    const data = (await res.json()) as SessionData;

    subscriptionUrl.set(data.subscription ?? null);
    userData.set(data);

    return data;
  } catch {
    await logoutInternal();
    return null;
  } finally {
    sessionLoaded.set(true);
  }
}

async function logoutInternal() {
  try {
    await fetch('/api/logout', { method: 'POST', credentials: 'include' });
  } catch {}

  subscriptionUrl.clear();
  fetched = false;
}

export async function logout() {
  await logoutInternal();
  window.location.href = '/';
}

export const paymentsData = writable<PaymentData[] | null>(null);

export async function fetchPayments(): Promise<PaymentData[]> {
  try {
    const res = await fetch('/api/payments', { credentials: 'include' });
    if (!res.ok) {
      return [];
    }

    const data = (await res.json()) as PaymentData[];
    paymentsData.set(data);
    return data;
  } catch {
    return [];
  }
}

export interface PaymentData {
  id: number;
  amount: number;
  currency: string;
  status: string;
  plan_id: number;
  created_at: string;
  paid_at?: string | null;
}