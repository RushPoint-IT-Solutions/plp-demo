# SASS Discipline Review Checklist

Use this checklist for Registrar UI pull requests to enforce SASS-only styling and no inline behavior.

1. No `<style>` blocks in Blade files.
2. No inline `style="..."` attributes in Blade files.
3. No inline JS handlers (`onclick`, `onchange`, etc.) in Blade files.
4. New styles are authored in `resources/sass/` as `.scss` files.
5. Page-specific styles are compiled as dedicated entries in `webpack.mix.js`.
6. Blade pages include only compiled CSS assets needed for that page.
7. Legacy `public/css/styles.css` is not linked from layout files.
8. Do not edit compiled `public/css/*.css` files by hand.
9. Run `npm run dev` after style changes and verify no compile errors.
10. Confirm target pages render correctly on screen and print preview.
