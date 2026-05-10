import { test, expect } from '@playwright/test';

test.describe('Authentication', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should display login link in navigation', async ({ page }) => {
    const loginLink = page.getByRole('link', { name: /login/i });
    await expect(loginLink).toBeVisible();
  });

  test('should navigate to login page', async ({ page }) => {
    await page.getByRole('link', { name: /login/i }).click();
    await expect(page).toHaveURL('/login');
    await expect(page.getByRole('heading', { name: /mangoon/i })).toBeVisible();
  });

  test('should display login form', async ({ page }) => {
    await page.goto('/login');
    
    await expect(page.getByLabel('Email')).toBeVisible();
    await expect(page.getByLabel('Password')).toBeVisible();
    await expect(page.getByRole('button', { name: /sign in/i })).toBeVisible();
  });

  test('should show error on invalid credentials', async ({ page }) => {
    await page.goto('/login');
    
    await page.getByLabel('Email').fill('invalid@example.com');
    await page.getByLabel('Password').fill('wrongpassword');
    await page.getByRole('button', { name: /sign in/i }).click();
    
    await expect(page.getByText(/login failed/i)).toBeVisible();
  });

  test('should support OAuth login button', async ({ page }) => {
    await page.goto('/login');
    
    const oauthButton = page.getByRole('button', { name: /sign in with github/i });
    await expect(oauthButton).toBeVisible();
  });
});

test.describe('Navigation', () => {
  test('should display main navigation', async ({ page }) => {
    await page.goto('/');
    
    await expect(page.getByRole('link', { name: /home/i })).toBeVisible();
    await expect(page.getByText(/mangoon/i)).toBeVisible();
  });

  test('should have responsive mobile menu', async ({ page }) => {
    await page.goto('/');
    
    // Check mobile menu button exists
    const menuButton = page.getByRole('button');
    await expect(menuButton).toBeVisible();
  });

  test('should display footer', async ({ page }) => {
    await page.goto('/');
    
    await expect(page.getByText(/mangoon - manga management platform/i)).toBeVisible();
  });
});
