import { test, expect } from '@playwright/test';
import {login, isLoginModalActive, isTooltip} from '../helpers/auth-helper.js';

const testUser = {
    first_name: 'Magdy',
    accountType: 'customer',
    email: 'dopaco@highbase.com',
    password: 'password'
};

test.describe('Login Page', () => {
    test.beforeEach(async ({page}) => {
        await page.goto('/login');
    });

    test('should login with valid credentials', async ({ page }) => {
        await login(page, testUser.email, testUser.password);
        await expect(page).toHaveURL('/dashboard' || '/');
    });

    test('should show error for invalid credentials', async ({ page }) => {
        await login(page, 'test@test.com', 'wrong-password');
        await expect(page.getByTestId('toast-content'))
            .toHaveText('These credentials do not match our records.');
    });

    test('should show email field is required', async ({ page }) => {
        await login(page, '', testUser.password);
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The email field is required.');
    });

    test('should show password field is required', async ({ page }) => {
        await login(page, testUser.email, '');
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The password field is required.');
    });

    test('should go to forgot password page', async ({ page }) => {
        await page.getByTestId('forgot-password-link').click();
        await expect(page).toHaveURL('/forgot-password');
    });

    test('should go to register page', async ({ page }) => {
        await page.locator('span.text-hb-primary', { hasText: 'Register' }).click();
        await expect(page).toHaveURL('/register');
    });

    test('should allow to logout from storefront', async ({ page }) => {
        await login(page, testUser.email, testUser.password);
        await expect(page).toHaveURL('/dashboard' || '/');
        await page.goto('/');

        isTooltip(page);

        await page.getByTestId('toggle-user-dropdown-button').click();
        await page.getByTestId('user-dropdown-logout-action').click();

        await expect(page.getByRole('button', { name: 'Sign In' })).toBeVisible();
    });

    test('should allow to logout from dashboard', async ({ page }) => {
        await login(page, testUser.email, testUser.password);
        await expect(page).toHaveURL('/dashboard');

        await page.getByTestId('toggle-dashboard-user-dropdown-button').click();
        await page.getByTestId('dashboard-logout-action').click();

        await expect(page.getByRole('button', { name: 'Sign In' })).toBeVisible();
    });
});

test.describe('Login Modal', () => {
    test.beforeEach(async ({page}) => {
        await page.goto('/');

        await page.getByTestId('close-register-modal-button').click();

        isTooltip(page);

        await page.getByRole('button', { name: 'Sign In' }).click();

        await isLoginModalActive(page);
    });

    test('should show login modal', async ({ page })=>{});

    test('should login with valid credentials', async ({ page }) => {
        await login(page, testUser.email, testUser.password);
        await expect(page).toHaveURL('/dashboard' || '/');
    });

    test('should show error for invalid credentials', async ({ page }) => {
        await login(page, 'test@test.com', 'wrong-password');
        await expect(page.getByTestId('toast-content'))
            .toHaveText('These credentials do not match our records.');
    });

    test('should show email field is required', async ({ page }) => {
        await login(page, '', testUser.password);
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The email field is required.');
    });

    test('should show password field is required', async ({ page }) => {
        await login(page, testUser.email, '');
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The password field is required.');
    });

    test('should go to forgot password page', async ({ page }) => {
        await page.getByRole('link', { name: 'Forgot your password?' }).click();
        await expect(page).toHaveURL('/forgot-password');
    });

    test('should switch to register modal', async ({ page }) => {
        await page.locator('span', { hasText: 'Register' }).click();

        const registerHeading = page.getByRole('heading', { name: 'Create Your Free Account' }).nth(0);
        await expect(registerHeading).toBeVisible();
    });

    test('should allow to logout', async ({ page }) => {
        await login(page, testUser.email, testUser.password);
        await expect(page).toHaveURL('/dashboard' || '/');
        await page.goto('/');

        await page.getByTestId('toggle-user-dropdown').click();
        await page.getByTestId('user-dropdown-logout').click();

        await expect(page.getByRole('button', { name: 'Sign In' })).toBeVisible();
    });
})

// mobile test
test.describe('Login Modal on mobile', () => {
    test.use({ viewport: { width: 375, height: 812 } });

    test.beforeEach(async ({page}) => {
        await page.goto('/');

        await page.getByTestId('close-register-popup-btn').click();
        await page.getByTestId('mobile-open-login-button').click();

        await isLoginModalActive(page);
    })

    test('should show login modal', async ({ page }) => {});

    test('should switch to register modal', async ({ page }) => {
        await page.locator('span', { hasText: 'Register' }).click();

        const registerHeading = page.getByRole('heading', { name: 'Create Your Free Account' }).nth(0);
        await expect(registerHeading).toBeVisible();
    });
})
