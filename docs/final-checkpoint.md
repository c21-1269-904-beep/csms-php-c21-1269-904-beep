# CSMS v1.0.0 — Final Examination Checkpoint

## 1. Developer Information

- Developer: Carl Angelo Hernandez
- Project: Community Services Management System
- Project Short Name: CSMS
- Release Version: 1.0.0
- Final Examination Ticket: T16

## 2. Release Version Source

The authoritative release version is maintained in:

`config/app.php`

Configuration key:

`app.version`

The health endpoint, homepage, and dashboard retrieve the version through Laravel configuration.

This avoids maintaining conflicting version values across application components.

## 3. T16 Implementation

The following changes were implemented:

- Updated the health endpoint to return application identity, version, and status.
- Added the centralized release version `1.0.0`.
- Updated the homepage to retrieve the application name and version from configuration.
- Displayed `v1.0.0` on the dashboard.
- Added automated release verification tests.
- Added a cross-module integration test.
- Created release documentation.

## 4. Changed Files

- `config/app.php`
- `app/Http/Controllers/HealthController.php`
- `app/Http/Controllers/HomeController.php`
- `resources/views/dashboard/index.blade.php`
- `tests/Feature/T16FinalReleaseTest.php`
- `tests/Feature/CsmsApplicationTest.php`
- `docs/release-v1.0.0.md`
- `docs/final-checkpoint.md`

## 5. Student-Designed Integration Test

Test:

`test_02_resident_service_request_lifecycle_is_persisted_and_reported`

The test covers:

1. Creation of an active resident.
2. Submission of a service request.
3. Workflow transition from Pending to In Progress.
4. Workflow transition from In Progress to Completed.
5. Rejection of an invalid reverse transition.
6. Verification of the persisted request.
7. Verification that resident information remains unchanged.
8. Verification of dashboard statistics.

The test uses real database persistence through the application's existing services and repositories.

## 6. Technical Decision

A single authoritative version source was selected in `config/app.php`.

The health endpoint, homepage, and dashboard retrieve the version through Laravel configuration. This avoids duplicated release version values and makes future version updates easier to maintain.

## 7. Problem Encountered and Resolution

The existing health endpoint returned the outdated version `0.1.0`, and the application name initially returned `Laravel`.

The homepage also contained a hardcoded version value.

The application configuration and relevant controllers were updated to use the centralized application name and release version.

The homepage and health endpoint tests now verify the correct CSMS application identity and version `1.0.0`.

## 8. Automated Verification

### Baseline Before T16

- 124 tests passed.
- 372 assertions passed.

### Focused T16 Verification

- 2 tests passed.
- 26 assertions passed.

### Application Verification

- 4 tests passed.
- 9 assertions passed.

### Final Regression Run 1

- 126 tests passed.
- 398 assertions passed.
- 0 failures.

### Final Regression Run 2

- 126 tests passed.
- 398 assertions passed.
- 0 failures.

Both consecutive full regression runs completed successfully.

## 9. Manual Acceptance Verification

The following checks were completed:

- `/health` returned the correct application name, version `1.0.0`, and status `ok`.
- Dashboard displayed `Version v1.0.0`.
- Residents page loaded successfully.
- Service Requests page loaded successfully.
- No visible page-loading errors were observed during these checks.

Still pending:

- Manual verification of valid and invalid service request workflow transitions through the application interface.
- Final GitHub pull request review and merge.
- Verification of the merged `main` branch.
- Creation and push of the annotated `v1.0.0` tag.
- GitHub Release publication.

## 10. Tools and References

- PHP
- Laravel
- PHPUnit 9.6
- PowerShell
- Visual Studio Code
- Git and GitHub
- Existing CSMS application modules and test suite

## 11. Release Acceptance

Automated verification and initial manual acceptance checks are complete.

Final release acceptance remains pending workflow verification, GitHub merge, post-merge regression testing, and release tagging.

## Final Release Statement

CSMS v1.0.0 - READY FOR RELEASE