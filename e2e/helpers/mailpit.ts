const MAILPIT_URL = process.env.MAILPIT_URL ?? 'http://localhost:8025';

export interface MailpitMessage {
  ID: string;
  From: { Address: string; Name: string };
  To: { Address: string; Name: string }[];
  Subject: string;
  Created: string;
}

interface MailpitListResponse {
  messages: MailpitMessage[];
  total: number;
}

interface MailpitMessageDetail {
  ID: string;
  HTML: string;
  Text: string;
}

async function fetchJson<T>(url: string): Promise<T> {
  const res = await fetch(url);
  if (!res.ok) {
    throw new Error(`Mailpit request failed (${res.status.toString()}): ${url}`);
  }
  return (await res.json()) as T;
}

export async function waitForEmailTo(
  toAddress: string,
  timeoutMs = 15_000,
  pollMs = 500,
): Promise<MailpitMessage> {
  const deadline = Date.now() + timeoutMs;
  const target = toAddress.toLowerCase();

  while (Date.now() < deadline) {
    const list = await fetchJson<MailpitListResponse>(`${MAILPIT_URL}/api/v1/messages`);
    const match = list.messages.find((m) => m.To.some((to) => to.Address.toLowerCase() === target));
    if (match) return match;
    await new Promise((r) => setTimeout(r, pollMs));
  }

  throw new Error(`Timed out waiting for email to ${toAddress} (after ${timeoutMs.toString()}ms)`);
}

/**
 * Extracts the first link in the message body matching the given pattern.
 * Reads the plain-text body to avoid HTML entity encoding (e.g. "&amp;").
 */
export async function extractLinkMatching(messageId: string, pattern: RegExp): Promise<string> {
  const message = await fetchJson<MailpitMessageDetail>(
    `${MAILPIT_URL}/api/v1/message/${messageId}`,
  );
  const body = message.Text || message.HTML;
  const match = pattern.exec(body);

  if (!match) {
    throw new Error(`No link matching ${pattern.toString()} found in message ${messageId}`);
  }
  return match[0];
}
