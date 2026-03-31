Certificate GWA styles moved to SCSS
===================================

Summary
-------

The inline styles previously in `resources/views/registrar/forms/certificates/certificate-gwa.blade.php` were moved into a dedicated SCSS partial and imported into the main SCSS build.

- SCSS partial: `resources/sass/_certificate-gwa.scss`
- Imported in: `resources/sass/app.scss`
- Blade updated: `resources/views/registrar/forms/certificates/certificate-gwa.blade.php`

Reason
------

This follows the repository's styling rules documented in `.github/skills/sass-css-disciplinarian/SKILL.md`: keep all custom CSS in `resources/assets/sass/` (or `resources/sass/`) and avoid `<style>` tags or `style=` attributes in Blade templates.

Rebuild
-------

Run the local asset build to regenerate `public/css/app.css`:

```bash
npm install
npm run dev
```

If you want this to be a page-scoped CSS file instead of importing into `app.scss`, I can create a separate Mix entry and a conditional `mix()` include in the Blade.
