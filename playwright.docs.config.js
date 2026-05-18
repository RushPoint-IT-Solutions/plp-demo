module.exports = {
  testDir: './tests/browser-docs',
  timeout: 120000,
  fullyParallel: false,
  workers: 1,
  reporter: 'list',
  use: {
    baseURL: 'http://127.0.0.1:8000',
    headless: true,
    viewport: { width: 1440, height: 1000 },
    ignoreHTTPSErrors: true,
  },
  webServer: {
    command: 'php artisan serve --host=127.0.0.1 --port=8000',
    url: 'http://127.0.0.1:8000/login/student',
    reuseExistingServer: true,
    timeout: 120000,
  },
};
