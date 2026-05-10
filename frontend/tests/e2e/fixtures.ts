import { test as base } from '@playwright/test';

export const test = base.extend<{
  authenticatedPage: {
    goto: (url: string) => Promise<void>;
  };
}>({
  authenticatedPage: async ({ page, context }, use) => {
    // Set up authentication state
    await context.addCookies([
      {
        name: 'auth-token',
        value: 'test-token',
        domain: 'localhost',
        path: '/',
      },
    ]);

    await use({
      goto: async (url: string) => {
        await page.goto(url);
      },
    });
  },
});

export { expect } from '@playwright/test';
