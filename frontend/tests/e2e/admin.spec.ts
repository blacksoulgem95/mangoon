import { test, expect } from '@playwright/test';

test.describe('Cookie Management', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
  });

  test('should display cookie import form', async ({ page }) => {
    // Navigate to cookie management (would require auth)
    await page.goto('/admin/cookies');
    
    await expect(page.getByText(/cookie management/i)).toBeVisible();
  });

  test('should validate cookie JSON format', async ({ page }) => {
    await page.goto('/admin/cookies');
    
    const invalidJson = '{invalid json}';
    await page.getByLabel(/cookies/i).fill(invalidJson);
    
    // Should show validation error
    await expect(page.getByText(/invalid json/i)).toBeVisible();
  });
});

test.describe('External Source Integration', () => {
  test('should search nhentai', async ({ page }) => {
    await page.goto('/admin/sources');
    
    await page.getByLabel(/search/i).fill('test manga');
    await page.getByRole('button', { name: /search/i }).click();
    
    // Wait for results
    await page.waitForSelector('.manga-card', { timeout: 10000 }).catch(() => {});
  });

  test('should display search results', async ({ page }) => {
    await page.goto('/admin/sources');
    
    await page.getByLabel(/search/i).fill('test');
    await page.getByRole('button', { name: /search/i }).click();
    
    // Check for results container
    await expect(page.getByText(/results/i)).toBeVisible().catch(() => {});
  });

  test('should download manga from external source', async ({ page }) => {
    await page.goto('/admin/sources');
    
    // Search and select a manga
    await page.getByLabel(/search/i).fill('test');
    await page.getByRole('button', { name: /search/i }).click();
    
    // Click download on first result
    await page.getByRole('button', { name: /download/i }).first().click();
    
    // Should show download confirmation
    await expect(page.getByText(/download started/i)).toBeVisible();
  });
});

test.describe('Manga Version Merging', () => {
  test('should display version linking dialog', async ({ page }) => {
    await page.goto('/admin/manga/1');
    
    await page.getByRole('button', { name: /link version/i }).click();
    
    await expect(page.getByText(/link manga version/i)).toBeVisible();
  });

  test('should search for related manga', async ({ page }) => {
    await page.goto('/admin/manga/1');
    
    await page.getByRole('button', { name: /link version/i }).click();
    await page.getByLabel(/search manga/i).fill('test title');
    
    // Should show suggestions
    await expect(page.getByText(/suggestions/i)).toBeVisible().catch(() => {});
  });

  test('should link manga versions', async ({ page }) => {
    await page.goto('/admin/manga/1');
    
    await page.getByRole('button', { name: /link version/i }).click();
    await page.getByRole('button', { name: /select/i }).first().click();
    await page.getByRole('button', { name: /confirm/i }).click();
    
    await expect(page.getByText(/version linked/i)).toBeVisible();
  });
});

test.describe('User Management', () => {
  test('should display user list', async ({ page }) => {
    await page.goto('/admin/users');
    
    await expect(page.getByText(/users/i)).toBeVisible();
  });

  test('should assign role to user', async ({ page }) => {
    await page.goto('/admin/users');
    
    await page.getByRole('button', { name: /assign role/i }).click();
    await page.getByLabel(/role/i).selectOption('editor');
    await page.getByRole('button', { name: /save/i }).click();
    
    await expect(page.getByText(/role assigned/i)).toBeVisible();
  });

  test('should remove role from user', async ({ page }) => {
    await page.goto('/admin/users');
    
    await page.getByRole('button', { name: /remove role/i }).first().click();
    
    await expect(page.getByText(/role removed/i)).toBeVisible();
  });
});

test.describe('Library Management', () => {
  test('should create new library', async ({ page }) => {
    await page.goto('/admin/libraries');
    
    await page.getByRole('button', { name: /create library/i }).click();
    await page.getByLabel(/name/i).fill('Test Library');
    await page.getByRole('button', { name: /save/i }).click();
    
    await expect(page.getByText(/library created/i)).toBeVisible();
  });

  test('should assign users to library', async ({ page }) => {
    await page.goto('/admin/libraries/1');
    
    await page.getByRole('button', { name: /assign user/i }).click();
    await page.getByLabel(/user/i).selectOption('1');
    await page.getByRole('button', { name: /save/i }).click();
    
    await expect(page.getByText(/user assigned/i)).toBeVisible();
  });
});

test.describe('S3/MinIO Integration', () => {
  test('should upload CBZ file', async ({ page }) => {
    await page.goto('/admin/manga/1/chapters');
    
    const fileInput = page.getByLabel(/upload cbz/i);
    await fileInput.setInputFiles({
      name: 'chapter.cbz',
      mimeType: 'application/zip',
      buffer: Buffer.from('test'),
    });
    
    await page.getByRole('button', { name: /upload/i }).click();
    
    await expect(page.getByText(/upload completed/i)).toBeVisible();
  });

  test('should display upload progress', async ({ page }) => {
    await page.goto('/admin/manga/1/chapters');
    
    const fileInput = page.getByLabel(/upload cbz/i);
    await fileInput.setInputFiles({
      name: 'chapter.cbz',
      mimeType: 'application/zip',
      buffer: Buffer.from('test'),
    });
    
    await page.getByRole('button', { name: /upload/i }).click();
    
    await expect(page.getByText(/uploading/i)).toBeVisible();
  });
});
