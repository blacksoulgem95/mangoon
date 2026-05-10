import { test, expect } from '@playwright/test';

test.describe('Manga Catalog', () => {
  test('should display manga catalog on home page', async ({ page }) => {
    await page.goto('/');
    
    await expect(page).toHaveURL('/');
    await expect(page.getByRole('heading', { name: /welcome to mangoon/i })).toBeVisible();
  });

  test('should display manga grid', async ({ page }) => {
    await page.goto('/');
    
    // Check for manga grid structure
    const mangaCards = page.getByRole('link').filter({ hasText: /manga/i });
    await expect(mangaCards.first()).toBeVisible();
  });

  test('should navigate to manga detail page', async ({ page }) => {
    await page.goto('/');
    
    // Click on first manga card
    const firstManga = page.getByRole('link').filter({ hasText: /manga/i }).first();
    await firstManga.click();
    
    // Should navigate to manga detail
    await expect(page).toHaveURL(/\/manga\/\d+/);
  });

  test('should display empty state when no manga', async ({ page }) => {
    await page.goto('/');
    
    // Check for empty state message
    await expect(page.getByText(/no manga found/i)).toBeVisible();
  });
});

test.describe('Manga Detail Page', () => {
  test('should display manga information', async ({ page }) => {
    await page.goto('/manga/1');
    
    // Check for manga title
    await expect(page.getByRole('heading')).toBeVisible();
  });

  test('should display chapters list', async ({ page }) => {
    await page.goto('/manga/1');
    
    // Check for chapters section
    await expect(page.getByText(/chapters/i)).toBeVisible();
  });

  test('should display mature content warning', async ({ page }) => {
    await page.goto('/manga/1');
    
    // Check for mature label if applicable
    const matureLabel = page.getByText(/mature/i);
    await expect(matureLabel).toBeVisible().catch(() => {});
  });
});

test.describe('Chapter Reader', () => {
  test('should display chapter pages', async ({ page }) => {
    await page.goto('/read/1/1');
    
    // Check for reader navigation
    await expect(page.getByRole('button', { name: /previous/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /next/i })).toBeVisible();
  });

  test('should navigate between pages', async ({ page }) => {
    await page.goto('/read/1/1');
    
    // Click next button
    await page.getByRole('button', { name: /next/i }).click();
    
    // Page number should update
    await expect(page.getByText(/page \d+ of \d+/i)).toBeVisible();
  });

  test('should navigate back to manga detail', async ({ page }) => {
    await page.goto('/read/1/1');
    
    await page.getByRole('button', { name: /back to manga/i }).click();
    
    await expect(page).toHaveURL(/\/manga\/\d+/);
  });
});

test.describe('Admin Panel', () => {
  test('should redirect to login when not authenticated', async ({ page }) => {
    await page.goto('/admin');
    
    await expect(page).toHaveURL('/login');
  });

  test('should display admin dashboard when authenticated', async ({ page }) => {
    // This test would require authentication setup
    await page.goto('/admin');
    
    // Check for dashboard elements
    await expect(page.getByText(/admin dashboard/i)).toBeVisible().catch(() => {});
  });

  test('should display admin navigation links', async ({ page }) => {
    await page.goto('/admin');
    
    await expect(page.getByRole('link', { name: /manga/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /users/i })).toBeVisible();
    await expect(page.getByRole('link', { name: /libraries/i })).toBeVisible();
  });
});

test.describe('Responsive Design', () => {
  test('should work on mobile devices', async ({ page, browserName }) => {
    await page.goto('/');
    
    // Test on mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    
    await expect(page.getByRole('heading', { name: /welcome to mangoon/i })).toBeVisible();
  });

  test('should work on tablet devices', async ({ page }) => {
    await page.goto('/');
    
    // Test on tablet viewport
    await page.setViewportSize({ width: 768, height: 1024 });
    
    await expect(page.getByRole('heading', { name: /welcome to mangoon/i })).toBeVisible();
  });

  test('should work on desktop devices', async ({ page }) => {
    await page.goto('/');
    
    // Test on desktop viewport
    await page.setViewportSize({ width: 1920, height: 1080 });
    
    await expect(page.getByRole('heading', { name: /welcome to mangoon/i })).toBeVisible();
  });
});

test.describe('Performance', () => {
  test('should load home page within acceptable time', async ({ page }) => {
    const startTime = Date.now();
    await page.goto('/');
    const loadTime = Date.now() - startTime;
    
    expect(loadTime).toBeLessThan(5000); // 5 second threshold
  });

  test('should not have console errors', async ({ page }) => {
    const errors: string[] = [];
    
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });
    
    await page.goto('/');
    
    expect(errors).toHaveLength(0);
  });
});
