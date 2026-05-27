# Contributing

Thanks for your interest. This is a partner-facing SDK so contributions
must satisfy a high quality bar.

## Quick start

```bash
git clone https://github.com/maviance/smobilpay-php-client
cd smobilpay-php-client
composer install
composer check          # lint + stan + test (the local pre-commit gate)
```

## Style and tooling

- **PSR-12** enforced via PHP-CS-Fixer. Run `composer lint:fix` before
  committing.
- **PHPStan level 8** must pass. `composer stan`.
- **80%+ line coverage** on `src/` (excluding `src/Model/`). The CI
  coverage gate fails the build below this threshold.
- Every public class has at least a one-line PHPDoc summary; complex
  classes should document the *why*, not the *what*.
- `readonly` classes for all DTOs; constructor property promotion
  everywhere.

## Commits

Conventional commit prefixes: `feat:`, `fix:`, `refactor:`, `docs:`,
`test:`, `chore:`, `perf:`, `ci:`, `feat!:` (breaking).

## Pull requests

1. Branch from `develop`. Production tags are cut from `master`.
2. Write a test that fails first, then the change. CI enforces coverage.
3. Update CHANGELOG.md under an `[Unreleased]` section.
4. If your change touches the wire surface, update fixtures in
   `tests/Fixtures/` and re-run the offline smoke test
   (`composer smoke -- --offline`).
5. If your change adjusts the cross-language parity with the Java
   client (`/root/s3p-clients/java` or the public mirror), call it out
   in the PR description and run the diff comparison as documented in
   the README.

## Design changes

For non-trivial architecture changes, write a short design note under
`docs/superpowers/specs/YYYY-MM-DD-<topic>.md`, get review, then
implement. The seed spec for the v3 rewrite lives at
`docs/superpowers/specs/2026-05-27-php-client-rewrite-v3.2-design.md`.

## Reporting security issues

Do not file public GitHub issues for security findings. Email
**support@smobilpay.com** with `[SECURITY]` in the subject. We
coordinate disclosure and credit researchers in the changelog when
appropriate.
