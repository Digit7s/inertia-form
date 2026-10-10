import { expect, test } from '@playwright/test';
import { resolve } from 'node:path';

const uploadPath = resolve('tests/fixtures/upload.txt');

const completeRequiredFields = async (page: import('@playwright/test').Page) => {
  await page.getByLabel('Details.reference').fill('Browser reference');
  await page.getByLabel('Category').selectOption('work');
  await page.getByLabel('High').check();
  await page.getByLabel('Start Date').fill('2026-11-03');
  await page.getByLabel('Start Time').fill('13:45');
  await page.getByLabel('Appointment At').fill('2026-11-03T13:45');
};

test('real Inertia create, validation, file-bearing edit, and upload retention', async ({ page }) => {
  await page.goto('/items/create');
  await expect(page.getByRole('heading', { name: 'Create item' })).toBeVisible();
  await page.getByLabel('Title').fill('X'.repeat(121));
  await completeRequiredFields(page);
  await page.getByLabel('Notes').fill('Created through the production Vue adapter');
  await page.getByRole('textbox', { name: 'Password' }).fill('temporary-e2e-secret');
  await page.getByLabel('Urgent').check();
  await page.getByLabel('Attachment').setInputFiles(uploadPath);
  await page.getByRole('button', { name: 'Save item' }).click();

  await expect(page.getByText('The title field must not be greater than 120 characters.')).toBeVisible();
  await expect(page.getByLabel('Details.reference')).toHaveValue('Browser reference');
  await expect(page.getByLabel('Category')).toHaveValue('work');

  await page.getByLabel('Title').fill('Playwright created item');
  await page.getByRole('button', { name: 'Save item' }).click();
  await expect(page).toHaveURL(/\/items\/\d+\/edit$/);
  await expect(page.getByRole('heading', { name: /Edit item/ })).toBeVisible();
  await expect(page.getByRole('textbox', { name: 'Password' })).toHaveValue('');
  await expect(page.locator('input[type="file"]')).toHaveValue('');

  await page.getByLabel('Details.reference').fill('R'.repeat(81));
  await page.getByRole('button', { name: 'Save item' }).click();
  await expect(page.getByText('The details.reference field must not be greater than 80 characters.')).toBeVisible();
  await expect(page.getByLabel('Title')).toHaveValue('Playwright created item');

  await page.getByLabel('Details.reference').fill('Edited reference');
  await page.getByLabel('Attachment').setInputFiles(uploadPath);
  await page.getByRole('button', { name: 'Save item' }).click();
  await expect(page.getByText('The details.reference field must not be greater than 80 characters.')).toHaveCount(0);
  await expect(page.getByRole('textbox', { name: 'Password' })).toHaveValue('');
  await expect(page.locator('input[type="file"]')).toHaveValue('');

  await page.reload();
  await expect(page.getByText(/^Current file: items\//)).toBeVisible();
  await page.getByLabel('Notes').fill('Updated without replacing the upload');
  await page.getByRole('button', { name: 'Save item' }).click();
  await page.reload();
  await expect(page.getByText(/^Current file: items\//)).toBeVisible();
});
