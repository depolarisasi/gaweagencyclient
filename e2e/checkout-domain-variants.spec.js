import { test, expect } from '@playwright/test';

test.describe('Checkout Domain Variants', () => {
  test('Flow dengan domain existing (punya sendiri)', async ({ page }) => {
    await page.goto('http://localhost:8000/checkout');
    await expect(page).toHaveURL(/.*\/checkout\/domain/);

    // Isi cepat hidden inputs untuk domain existing
    await page.waitForSelector('#domain_type_input', { state: 'attached' });
    await page.evaluate(() => {
      const typeEl = document.getElementById('domain_type_input');
      const nameEl = document.getElementById('domain_name_input');
      const tldEl = document.getElementById('domain_tld_input');
      if (typeEl && nameEl && tldEl) {
        typeEl.value = 'existing';
        nameEl.value = 'myexistingdomain';
        tldEl.value = 'com';
      }
    });
    await page.locator('button[type="submit"]').click();
    await page.waitForLoadState('networkidle');

    // Guard dapat menuju personal-info atau template → pastikan pilih template dulu
    if (!/\/checkout\/template/.test(page.url())) {
      await page.goto('http://localhost:8000/checkout/template');
    }
    await page.waitForSelector('.template-card', { timeout: 10000 });
    await page.locator('.template-card').first().click();
    await page.locator('button[type="submit"]').click();
    await page.waitForLoadState('networkidle');

    // Personal-info mungkin diperlukan sebelum configure
    if (!/\/checkout\/configure/.test(page.url())) {
      await page.goto('http://localhost:8000/checkout/personal-info');
      await page.fill('input[name="full_name"]', 'Owner User');
      await page.fill('input[name="email"]', 'owner@example.com');
      await page.fill('input[name="phone"]', '081200000001');
      const pwd = page.locator('#password');
      const pwdc = page.locator('#password_confirmation');
      if (await pwd.count()) {
        await pwd.fill('password123');
      }
      if (await pwdc.count()) {
        await pwdc.fill('password123');
      }
      await page.locator('button[type="submit"]').click();
      await page.waitForLoadState('networkidle');
      await expect(page).toHaveURL(/.*\/checkout\/configure/);
    }

    // Configure plan
    await page.locator('.plan-radio').first().check();
    await page.waitForTimeout(300);
    await page.locator('button:has-text("Lanjutkan")').click();
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/.*\/checkout\/addon/);

    // Addons → submit dan navigasi ke summary (tangani kemungkinan revisit domain)
    await page.locator('button[type="submit"]').click();
    await page.waitForLoadState('networkidle');
    if (/\/checkout\/domain/.test(page.url())) {
      await page.waitForSelector('#domain_type_input', { state: 'attached' });
      await page.evaluate(() => {
        const typeEl = document.getElementById('domain_type_input');
        const nameEl = document.getElementById('domain_name_input');
        const tldEl = document.getElementById('domain_tld_input');
        if (typeEl && nameEl && tldEl) {
          typeEl.value = 'existing';
          nameEl.value = 'myexistingdomain';
          tldEl.value = 'com';
        }
      });
      await page.locator('button[type="submit"]').click();
      await page.waitForLoadState('networkidle');
      await page.goto('http://localhost:8000/checkout/summary');
    }

    // Summary: verifikasi domain yang ditampilkan mengandung nama yang diisi
    await expect(page).toHaveURL(/.*\/checkout\/summary/);
    await expect(page.locator('text=/myexistingdomain\.|myexistingdomain/i')).toBeVisible();
  });

  test('Guard configure: tidak bisa lanjut tanpa memilih plan', async ({ page }) => {
    // Siapkan hingga configure tanpa memilih plan, lalu pastikan tidak ke addon
    await page.goto('http://localhost:8000/checkout');
    await expect(page).toHaveURL(/.*\/checkout\/domain/);

    await page.evaluate(() => {
      const typeEl = document.getElementById('domain_type_input');
      const nameEl = document.getElementById('domain_name_input');
      const tldEl = document.getElementById('domain_tld_input');
      if (typeEl && nameEl && tldEl) {
        typeEl.value = 'new';
        nameEl.value = 'guardtest';
        tldEl.value = 'com';
      }
    });
    await page.locator('button[type="submit"]').click();
    await page.waitForLoadState('networkidle');

    // Pilih template, lalu isi personal-info agar masuk configure
    await page.goto('http://localhost:8000/checkout/template');
    await page.locator('.template-card').first().click();
    await page.locator('button[type="submit"]').click();
    await page.waitForLoadState('networkidle');
    if (!/\/checkout\/configure/.test(page.url())) {
      await page.goto('http://localhost:8000/checkout/personal-info');
      await page.fill('input[name="full_name"]', 'Guard User');
      await page.fill('input[name="email"]', 'guard@example.com');
      await page.fill('input[name="phone"]', '081200000002');
      const pwd2 = page.locator('#password');
      const pwdc2 = page.locator('#password_confirmation');
      if (await pwd2.count()) {
        await pwd2.fill('password123');
      }
      if (await pwdc2.count()) {
        await pwdc2.fill('password123');
      }
      await page.locator('button[type="submit"]').click();
      await page.waitForLoadState('networkidle');
      await expect(page).toHaveURL(/.*\/checkout\/configure/);
    }

    // Coba lanjut tanpa memilih plan
    await page.locator('button:has-text("Lanjutkan")').click();
    await page.waitForTimeout(500);
    // Pastikan tidak berpindah ke addon
    await expect(page).not.toHaveURL(/.*\/checkout\/addon/);
  });
});