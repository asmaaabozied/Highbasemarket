// tests/e2e/helpers/auth-helpers.ts
import {expect} from "@playwright/test";

export async function login(page, email, password) {
    await page.getByTestId('email').fill(email);
    await page.getByTestId('password').fill(password);
    await page.getByTestId('login-submit-button').click();
}

export async function isTooltip(page){
    const isTooltip = await page.getByTestId('tooltip-modal').isVisible();

    if (isTooltip) { await page.getByTestId('tooltip-finish-button').click(); }
}

export async function isLoginModalActive(page){
    await expect(page.getByTestId('login-modal')).toBeVisible();
}

export async function isRegisterModalActive(page){
    await expect(page.getByTestId('register-modal')).toBeVisible();
}

export async function register(page, {userData}, isCheckType = true, isTerms = true) {
    await page.getByTestId('register-company-name').fill(userData.name);
    await page.getByTestId('register-fname').fill(userData.first_name);
    await page.getByTestId('register-lname').fill(userData.last_name);
    await page.getByTestId('register-email').fill(userData.email);
    await page.getByTestId('phone').fill(userData.phone);
    await page.getByTestId('register-password').fill(userData.password);
    await page.getByTestId('register-password-confirmation').fill(userData.password_confirmation);

    if(isCheckType) await page.getByTestId('register-type-factory').click();

    if(isTerms) await page.getByTestId('register-accept-terms').click();

    await page.getByTestId('register-submit-button').click();
}
