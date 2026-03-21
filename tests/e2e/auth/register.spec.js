import { test, expect } from '@playwright/test';
import {isLoginModalActive, isRegisterModalActive, isTooltip, register} from "../helpers/auth-helper.js";

const userData = {
    name: 'New Company',
    type: 'factory',
    first_name: 'New',
    last_name: 'Company',
    email: `company${Date.now()}${Math.floor(Math.random() * 1000)}@highbase.com`,
    phone: '1122338844',
    password: '12345678',
    password_confirmation: '12345678',
    terms: true
};

test.describe('Register Page', () => {
    test.beforeEach(async ({page}) => {
        await page.goto('/register');
    });

    test('should register with valid data', async ({ page }) => {
        await register(page, {userData});

        await expect(page).toHaveURL('/email/verify');
    });

    test('should show email has been taken', async ({ page }) => {
        await register(page, {
            userData: {
                ...userData,
                email: 'company@mail.com' // put email already exist
            }
        });
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The email has already been taken.');
    });

    test('should show name field is required', async ({ page }) => {
        // name field
        await register(page, { userData : {...userData, name: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The name field is required.');
    });

    test('should show first name field is required', async ({ page }) => {
        // first_name field
        await register(page, { userData : {...userData, first_name: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The first name field is required.');
    });

    test('should show last name field is required', async ({ page }) => {
        // last_name field
        await register(page, { userData : {...userData, last_name: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The last name field is required.');
    });

    test('should show email field is required', async ({ page }) => {
        // email field
        await register(page, { userData : {...userData, email: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The email field is required.');
    });

    test('should show phone field is required', async ({ page }) => {
        // phone field
        await register(page, { userData : {...userData, phone: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The phone field is required.');
    });

    test('should show phone field is required when not valid input number', async ({ page }) => {
        // phone field
        await register(page, { userData : {...userData, phone: '123456'}}); // not valid number
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The phone field is required.');
    });

    test('should show password field is required', async ({ page }) => {
        // password field
        await register(page, { userData : {...userData, password: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The password field is required.');
    });

    test('should show password field must be at least 8 characters', async ({ page }) => {
        // password field
        await register(page, { userData : {...userData, password: '12345'}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The password field must be at least 8 characters.');
    });

    test('should show password field confirmation does not match', async ({ page }) => {
        // password_confirmation field
        await register(page, { userData : {...userData, password_confirmation: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The password field confirmation does not match.');
    });

    test('should show type field is required', async ({ page }) => {
        // type field
        await register(page, { userData }, false);
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The type field is required.');
    });

    test('should show terms must be accepted', async ({ page }) => {
        // terms field
        await register(page, { userData }, true,false);
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The terms field must be accepted.');
    });

    test('go to terms of service page (new tab)', async ({ page, context }) => {
        const [newPage] = await Promise.all([
            context.waitForEvent('page'),
            page.getByTestId('terms-link').click(),
        ]);

        await newPage.waitForLoadState();
        await expect(newPage).toHaveURL(/terms-of-service/);
    });

    test('go to login page', async ({ page }) => {
        await page.getByTestId('goto-login-button').click();
        await expect(page).toHaveURL('/login');
    });
});

test.describe('Register Modal', () => {
    test.beforeEach(async ({page}) => {
        await page.goto('/');
        await isRegisterModalActive(page);
    });

    test('should open register modal', async ({ page }) => {});

    test('should close opened modal & open register modal again', async ({ page }) => {
        await page.getByTestId('close-register-modal-button').click();
        isTooltip(page);

        await page.getByTestId('open-register-modal').click();
        await isRegisterModalActive(page);
    });

    test('should show email has been taken', async ({ page }) => {
        await register(page, {
            userData: {
                ...userData,
                email: 'company@mail.com' // input email already exist
            }
        });
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The email has already been taken.');
    });

    test('should show name field is required', async ({ page }) => {
        await register(page, { userData : {...userData, name: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The name field is required.');
    });

    test('should show first name field is required', async ({ page }) => {
        await register(page, { userData : {...userData, first_name: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The first name field is required.');
    });

    test('should show last name field is required', async ({ page }) => {
        await register(page, { userData : {...userData, last_name: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The last name field is required.');
    });

    test('should show email field is required', async ({ page }) => {
        await register(page, { userData : {...userData, email: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The email field is required.');
    });

    test('should show phone field is required', async ({ page }) => {
        // phone field
        await register(page, { userData : {...userData, phone: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The phone field is required.');
    });

    test('should show phone field is required when not valid input number', async ({ page }) => {
        // phone field
        await register(page, { userData : {...userData, phone: '123456'}}); // not valid number
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The phone field is required.');
    });

    test('should show password field is required', async ({ page }) => {
        // password field
        await register(page, { userData : {...userData, password: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The password field is required.');
    });

    test('should show password field must be at least 8 characters', async ({ page }) => {
        // password field
        await register(page, { userData : {...userData, password: '12345'}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The password field must be at least 8 characters.');
    });

    test('should show password field confirmation does not match', async ({ page }) => {
        // password_confirmation field
        await register(page, { userData : {...userData, password_confirmation: ''}});
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The password field confirmation does not match.');
    });

    test('should show type field is required', async ({ page }) => {
        // type field
        await register(page, { userData }, false);
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The type field is required.');
    });

    test('should show terms must be accepted', async ({ page }) => {
        // terms field
        await register(page, { userData }, true,false);
        await expect(page.getByTestId('toast-content'))
            .toHaveText('The terms field must be accepted.');
    });

    test('should go to terms of service page (new tab)', async ({ page, context }) => {
        const [newPage] = await Promise.all([
            context.waitForEvent('page'),
            page.getByTestId('terms-link').click(),
        ]);

        await newPage.waitForLoadState();
        await expect(newPage).toHaveURL(/terms-of-service/);
    });

    test('should switch to login modal', async ({ page }) => {
        await page.getByTestId('goto-login-button').click();
        await isLoginModalActive(page)
    });
});
