# Contributing

The Parraindex is an open source project, so you can contribute to it, and it will be a pleasure for us to review
your pull request.

## How to contribute

### Reporting a bug

If you find a bug, please open an issue on the [Issues page](https://github.com/LukaMrt/Parraindex/issues).
For the moment there is no template for the issues, so you can write what you want but please be as precise as possible.
(You can add yourself the issue template if you want).

### Suggesting a feature

If you have an idea for a new feature, please open an issue on the
[Issues page](https://github.com/LukaMrt/Parraindex/issues) and describe your idea as precisely as possible.

If you want to implement the feature yourself, please follow the next section.

### Implementing a feature

If you want to implement a feature, please follow these steps:

1. Fork the repository
2. Create a new branch for your feature (for example `feature/my-feature`)
3. Implement your feature
4. Make sure all quality checks pass (see [Before opening a PR](#before-opening-a-pr))
5. Create a pull request **targeting the `develop` branch**
6. Wait for the review of your pull request
7. If your pull request is accepted, your feature will be merged into the `develop` branch, and you can delete your branch
8. If your pull request is rejected, you can discuss it and try to fix the problems

## Project layout

This is a monorepo with three independent toolchains:

- `backend/` — Symfony 8 / PHP 8.5 JSON API + EasyAdmin back-office
- `frontend/` — React 19 + TypeScript SPA (Vite)
- `e2e/` — Playwright end-to-end tests

Each part has its own `CLAUDE.md` documenting its conventions. Please read the one for the area you touch.

## Before opening a PR

The CI (GitHub Actions) is a hard gate: a PR is blocked if any of these fail. Run them locally first.

**Backend** (`cd backend`):

```bash
composer phpstan     # Static analysis (PHPStan level 10)
composer phpcs       # Coding standard
composer rector:dry  # Modernisation check
composer test        # PHPUnit (Unit / Integration / Functional)
```

**Frontend** (`cd frontend`):

```bash
npm run lint         # ESLint (0 warning allowed)
npm run typecheck    # TypeScript
npm run format:check # Prettier
npm test -- --run    # Vitest
npm run build        # Production build
```

**End-to-end** (from the repo root, requires Docker):

```bash
just e2e             # Spin up the test stack + run Playwright
```

## Code style

### PHP

PHP code follows [PSR-12](https://www.php-fig.org/psr/psr-12/) plus the project rules in `backend/phpcs.xml`,
enforced by `composer phpcs` (auto-fix with `composer phpcs:fix`). All files use `declare(strict_types=1)` and
must pass PHPStan at level 10.

### TypeScript / React

The frontend uses ESLint in `strict-type-checked` mode (`no-explicit-any` and `no-non-null-assertion` are errors)
and Prettier for formatting. Run `npm run lint:fix` and `npm run format` to auto-fix.

### Commit messages

The commit messages must follow the [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) standard.

## Pull request

There is no template for the pull requests, so you can write what you want but please be as precise as possible.
(You can add yourself the pull request template if you want).

## License

The Parraindex is licensed under the [MIT License](LICENSE).
