import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  use: {
    baseURL: 'http://127.0.0.1:8176',
    headless: true,
  },
  webServer: {
    command: 'php artisan serve --host=127.0.0.1 --port=8176',
    url: 'http://127.0.0.1:8176/items/create',
    reuseExistingServer: !process.env.CI,
    timeout: 30_000,
  },
});
