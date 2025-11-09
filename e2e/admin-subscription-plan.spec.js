import { test, expect } from '@playwright/test';

test.describe('Admin Subscription Plan Create/Edit UI', () => {
  test('cycle_months readonly dan otomatis dari billing_cycle', async ({ page }) => {
    // Login sebagai admin
    await page.goto('/login');

    await page.fill('#email', 'admin@gaweagency.com');
    await page.fill('#password', 'password123');
    await page.click('button[type="submit"]');

    // Buka halaman create subscription plan
    await page.goto('/admin/subscription-plans/create');

    const billingCycleSelect = page.locator('select[name="billing_cycle"]');
    const cycleMonthsInput = page.locator('input[name="cycle_months"]');

    // Pastikan input readonly
    await expect(cycleMonthsInput).toHaveAttribute('readonly', '');

    // Monthly -> 1
    await billingCycleSelect.selectOption('monthly');
    await expect(cycleMonthsInput).toHaveValue('1');

    // Quarterly -> 3
    await billingCycleSelect.selectOption('quarterly');
    await expect(cycleMonthsInput).toHaveValue('3');

    // Semi Annual -> 6
    await billingCycleSelect.selectOption('semi_annual');
    await expect(cycleMonthsInput).toHaveValue('6');

    // Annual -> 12
    await billingCycleSelect.selectOption('annual');
    await expect(cycleMonthsInput).toHaveValue('12');
  });
});