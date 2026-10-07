# Midterm Checkpoint Document (T10)

## Section 1 - Developer Information
- **Name:** Student
- **GitHub Username:** c21-1269-904-beep
- **Primary Technology Stack:** PHP / Laravel
- **T10 Branch:** feature/t10-service-request-status

## Section 2 - My T10 Implementation
For Ticket T10, I extended the Service Request subsystem by creating `ServiceRequestStatusService` and `ServiceRequestStatusResult`. When an update request is received, the service retrieves the target `ServiceRequest` model using `ServiceRequestRepository::findById()`. It validates whether the requested target status is supported and checks if the transition from the current state is allowed according to the application state matrix. If valid, it updates only the `$status` property and persists it to SQLite. If invalid or unsupported, it halts execution and returns a failure result without touching the database. The final updated entity or structured error result is returned to the caller.

## Section 3 - My Transition Rules
The implementation enforces the following lifecycle transition matrix:
- **Pending** may transition to **In Progress** or **Cancelled**.
- **In Progress** may transition to **Completed** or **Cancelled**.
- **Pending to Completed** is rejected because a request must undergo active processing prior to completion.
- **Completed** is a terminal state and cannot transition to any other status.
- **Cancelled** is a terminal state and cannot be reopened.
- **Same-status requests** (e.g., Pending to Pending) are rejected as invalid transitions to ensure real state modifications occur.

## Section 4 - Files I Changed
1. **File:** `app/Services/ServiceRequestStatusService.php`
   - **Purpose:** Implemented business rules for Service Request status transitions and updated persistence.
2. **File:** `app/Services/ServiceRequestStatusResult.php`
   - **Purpose:** Created a structured result container for status transition outcomes (success, not_found, unsupported_status, invalid_transition).
3. **File:** `tests/Feature/ServiceRequestStatusTest.php`
   - **Purpose:** Implemented 14 automated tests covering all required state transitions and edge cases.

## Section 5 - Problem I Encountered
During integration testing, I encountered an issue where `ResidentUpdateTest` failed due to missing helper methods (`isNotFound()`, `getResident()`) on `ResidentUpdateResult`. I investigated the error trace from PHPUnit and resolved it by adding explicit getter and boolean checking methods to `ResidentUpdateResult.php`, ensuring complete interface compatibility across the test suite.

## Section 6 - My Student-Designed Test
- **Test Name:** `test_14_student_designed_sequential_lifecycle_journey`
- **What the Test Verifies:** Verifies a complete sequential lifecycle journey for a Service Request (`Pending` -> `In Progress` -> `Completed`) and confirms that once `Completed`, terminal protection blocks subsequent attempts to transition to `Cancelled`.
- **Why I Added This Test:** To prove that multi-step state progression works end-to-end and that terminal lock properties hold across sequential state updates on the same record.

## Section 7 - Tools and References Used
- Laravel 11 Documentation (Eloquent & Testing)
- PHPUnit Documentation
- VS Code & Integrated PowerShell
- AI Collaborative Assistant