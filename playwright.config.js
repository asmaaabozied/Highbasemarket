// playwright.config.js
import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 30 * 1000,
    expect: { timeout: 5000 },

    use: {
        headless: false,
        viewport: { width: 1280, height: 720 },
        baseURL: process.env.APP_URL || 'http://127.0.0.1:8000',
    },
});
