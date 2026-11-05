# Preliminary Examination Checkpoint

## Developer Information
- **Name:** Carl Angelo Hernandez
- **GitHub Username:** c21-1269-904-beep
- **Primary Technology Stack:** PHP (Laravel)
- **T03 Branch:** feature/t03-resident-persistence

## My T03 Implementation
Resident data is stored in a SQLite database using Eloquent ORM via the `Resident` model. The `Resident` model class handles database interactions and queries. When a Resident is saved using `Resident::create()`, Eloquent executes an INSERT query. The database automatically generates an auto-incrementing integer `id`. Retrieval is done using `Resident::find($id)` or Eloquent query builder methods. If a Resident ID does not exist, Eloquent returns `null` safely without throwing an exception.

## Files I Changed
1. **`database/migrations/xxxx_xx_xx_xxxxxx_create_residents_table.php`**: Defines the database schema and columns for resident storage.
2. **`app/Models/Resident.php`**: Eloquent Model representing the database table and handling mass assignment.
3. **`tests/Unit/ResidentPersistenceTest.php`**: Automated unit test suite verifying persistence functionality and behavior.

## Problem I Encountered
- **Problem or error:** SQL connection failure during automated unit testing.
- **Cause:** SQLite test database configuration was missing in PHPUnit environment setup.
- **How I resolved it:** Configured `<env name="DB_CONNECTION" value="sqlite"/>` and `<env name="DB_DATABASE" value=":memory:"/>` inside `phpunit.xml`.

## My Student-Designed Test
- **Test name:** `test_leading_zero_in_contact_number_is_preserved_as_string`
- **What it verifies:** Verifies that phone numbers starting with '0' maintain their string type and leading zero when saved and reloaded.
- **Why I chose this scenario:** To prevent data corruption where integer conversion strips the essential leading zero from contact numbers.

## Tools and References Used
- Laravel Framework Documentation (Eloquent ORM & Migrations)
- PHPUnit Documentation
- VS Code & Integrated Terminal