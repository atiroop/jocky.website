# Jocky — Personal Writer’s Journal

A single-author literary journal built with Laravel 13, Blade, and Tailwind CSS 4. The public foundation includes a homepage, writing archive, article template, and about page.

## Local setup

- PHP 8.3 or later and Composer 2.9 or later
- Node.js and npm for the Vite/Tailwind asset build
- MariaDB settings belong in a private, untracked `.env`; see `.env.example` for the non-secret template

Install PHP dependencies with `composer install`, JavaScript dependencies with `npm install`, and build assets with `npm run build`. Do not run database migrations until the schema and migration plan are reviewed.

The sample writing and biography copy is temporary design content. This foundation has not been deployed. The private CMS and content persistence are future work.
