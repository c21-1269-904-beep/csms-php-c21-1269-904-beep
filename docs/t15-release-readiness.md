\# T15 – System Hardening and Regression Testing



\## 1. Developer Information



\- \*\*Name:\*\* Carl Angelo Hernandez

\- \*\*GitHub Username:\*\* To be confirmed

\- \*\*Tech Stack:\*\* PHP, Laravel, SQLite, PHPUnit

\- \*\*T15 Branch:\*\* `feature/t15-system-hardening`



\## 2. Release Candidate Baseline



The T14 implementation was merged into `main`, and the local `main` branch was updated before beginning T15.



| Verification | Result | Evidence |

|---|---|---|

| Application startup | PASS | Application started using `php artisan serve`. |

| Health endpoint | PASS | `/health` returned status `ok`, application name, and version `0.1.0`. |

| Dashboard | PASS | Dashboard page rendered successfully. |

| Resident Management | PASS | Resident Management page rendered successfully. |

| Service Request Management | PASS | Service Request Management page rendered successfully. |

| Baseline regression tests | PASS | 119 tests and 352 assertions passed before T15 changes. |



\## 3. Hardening Changes



| Area | Issue or Risk Found | Action Taken | Files Changed | Test/Evidence |

|---|---|---|---|---|

| Full regression behavior | Risk of regressions across existing features | Ran the complete test suite twice consecutively after T15 changes and once again after the PHPUnit configuration cleanup | Test suite | All three runs passed |

| Invalid/missing input | Array input for service request status caused an array-to-string conversion error | Added a string-type check before passing the submitted status to the service | `app/Http/Controllers/ServiceRequestController.php` | T15 Test 1 |

| Persistence integrity | Rejected transitions must not modify existing request data | Added a database before-and-after comparison | `tests/Feature/T15SystemHardeningTest.php` | T15 Test 2 |

| HTTP/mutation safety | GET requests must remain read-only | Added a test comparing request and resident records before and after a GET request | `tests/Feature/T15SystemHardeningTest.php` | T15 Test 3 |

| Query/database safety | Reviewed existing database interaction and parameter handling | Reviewed within the T15 scope; no separate query change made | No additional file changed | Existing test suite |

| Workflow/lifecycle protection | Inactive residents must not submit new service requests; repeated deactivation must not duplicate records | Added cross-feature and repeated-deactivation tests | `tests/Feature/T15SystemHardeningTest.php` | T15 Tests 4 and 5 |

| Interface/navigation consistency | Existing management pages were previously checked for rendering | No interface changes made during T15 | No additional file changed | Earlier manual page checks |

| Test reliability | Need repeatable regression results | Ran the complete PHPUnit suite twice consecutively and performed an additional verification run | Test suite | All runs passed |

| Repository/release hygiene | PHPUnit configuration contained an obsolete coverage attribute that caused an XML validation warning | Removed the obsolete coverage configuration and verified that the warning no longer appears | `phpunit.xml` | Latest PHPUnit run passed without configuration warnings |



\## 4. Five New T15 Hardening Tests



| Test Name | Category | What It Verifies | Why It Adds Value |

|---|---|---|---|

| `test\_01\_malformed\_status\_input\_is\_rejected\_without\_changing\_request` | Invalid/malformed input | Malformed array status input is rejected and the request remains Pending | Prevents malformed input from causing an application error or unintended update |

| `test\_02\_rejected\_transition\_preserves\_all\_service\_request\_fields` | Persistence integrity | A rejected Pending-to-Completed transition leaves all request fields unchanged | Verifies that rejected mutations do not alter persisted data |

| `test\_03\_get\_request\_details\_does\_not\_modify\_request\_or\_resident` | Read-only request safety | GET request details does not modify the request or resident records | Protects read-only HTTP behavior |

| `test\_04\_deactivated\_resident\_cannot\_submit\_new\_service\_request` | Cross-feature regression | A resident deactivated through the resident workflow cannot submit a new service request | Verifies resident eligibility across features |

| `test\_05\_repeated\_deactivation\_does\_not\_duplicate\_or\_modify\_resident` | Repeatability/boundary behavior | Repeated deactivation preserves the existing inactive resident record without duplication | Protects lifecycle and persistence integrity |



\## 5. Full Regression Test Results



\*\*Command:\*\*



```powershell

vendor\\bin\\phpunit

```



| Metric | Run 1 | Run 2 | Additional Run After Configuration Fix |

|---|---:|---:|---:|

| Tests | 124 | 124 | 124 |

| Assertions | 372 | 372 | 372 |

| Failures | 0 | 0 | 0 |

| Errors | 0 | 0 | 0 |

| Execution time | 6.537 seconds | 4.841 seconds | 5.405 seconds |

| Result | PASS | PASS | PASS |



The first two full regression runs passed consecutively. An additional full regression run after removing the obsolete PHPUnit coverage configuration also passed.



The five new T15 tests passed independently:



\- Tests: 5

\- Assertions: 20

\- Failures: 0

\- Errors: 0



Focused test command:



```powershell

vendor\\bin\\phpunit --filter T15SystemHardeningTest

```



\## 6. Manual Release Verification



| Verification Item | Status | Notes |

|---|---|---|

| Application startup | PASS | Previously verified using `php artisan serve`. |

| `/health` endpoint | PASS | Returned `status: ok`. |

| Dashboard rendering | PASS | Previously checked in browser. |

| Resident list and search | PENDING | Requires T15 manual verification. |

| Valid resident registration | PENDING | Requires T15 manual verification. |

| Invalid resident registration | PENDING | Requires T15 manual verification. |

| Resident update | PENDING | Requires T15 manual verification. |

| Soft deactivation and inactive search | PENDING | Requires T15 manual verification. |

| Valid service request submission | PENDING | Requires T15 manual verification. |

| Missing/inactive resident rejection | PARTIAL | Automated inactive-resident rejection test passed; manual verification pending. |

| Service request search and filters | PENDING | Requires T15 manual verification. |

| Service request details | PASS | GET details behavior covered by automated Test 3. |

| Valid lifecycle transitions | PENDING | Existing automated tests cover valid transitions; manual verification pending. |

| Invalid lifecycle transitions | PASS | Existing automated tests and T15 Test 2 cover rejected transitions. |

| Dashboard count synchronization | PENDING | Requires T15 manual verification. |

| GET read-only safety | PASS | Verified by T15 Test 3 and existing automated tests. |



\## 7. Repository Hygiene Review



\- T15 changes are limited to the service request controller, PHPUnit configuration, the new T15 test file, and this release-readiness document.

\- Existing T01–T14 tests have been retained.

\- No existing tests were deleted or weakened.

\- No new major features or unrelated architecture changes were introduced.

\- The obsolete PHPUnit coverage configuration was removed from `phpunit.xml`.

\- The latest PHPUnit execution completed without the previous XML configuration warning.

\- `.env` is ignored by Git and is not tracked.

\- `.env.example` is tracked as the configuration template.

\- Generated files and local development artifacts, including the SQLite database, PHPUnit cache, Laravel caches, sessions, logs, and `vendor/`, are ignored by Git.

\- A debug statement scan across `app`, `routes`, and `tests` found no `dd()`, `dump()`, `var\_dump()`, or `print\_r()` statements.

\- `git diff --check` completed without reporting whitespace errors.

\- No temporary PHPUnit backup file remains.

\- The four intended working-tree changes were verified before staging.



\## 8. Known Limitations



\- The complete manual release verification matrix has not yet been completed.

\- The GitHub username still needs to be confirmed and entered in the developer information.

\- The final commit, pull request, merge, and post-merge regression verification remain to be completed.



\## 9. Release Candidate Statement



\*\*NOT YET READY FOR T16 FINAL EXAM\*\*



The automated regression suite passed twice consecutively, including all five new T15 hardening tests. An additional full regression run after the PHPUnit configuration cleanup also passed with 124 tests and 372 assertions.



The repository hygiene checks completed so far are satisfactory. However, the remaining manual release verification, GitHub submission details, pull request, merge, and post-merge regression verification must be completed before declaring the release candidate ready for T16 Final Exam.
