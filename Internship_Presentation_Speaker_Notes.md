# Hospital Management System: Speaker Notes

Replace all bracketed fields before presenting.

## Slide 1: Title
Good morning/afternoon. My name is [Your Name], a fourth-year, second-semester software engineering student. This presentation summarizes the hospital management system I developed during my internship at Oda Bultum University. I will present the problem, objectives, system design, major modules, implementation results, challenges, and future improvements.

## Slide 2: Project Overview
This project is a centralized web application for hospital operations. It brings patient information, clinical workflows, pharmacy, inventory, finance, reports, and user access into one system. The purpose is not only to store data, but also to coordinate work between departments.

## Slide 3: Problem Statement
Before a system like this, information can be spread across paper forms and separate departmental processes. This creates delays, repeated data entry, billing mistakes, and difficulty tracking a patient's history. The project addresses these operational problems through a connected workflow.

## Slide 4: Objectives
The objectives were both technical and operational. Technically, I wanted to build a maintainable Laravel application with a relational database and secure roles. Operationally, I wanted to improve patient flow and ensure that clinical departments, cashier, and management work from consistent information.

## Slide 5: Scope
The scope covers the main hospital journey: registration, triage, OPD, IPD, laboratory, radiology, pharmacy, payments, reports, and administration. It also includes a patient portal for selected patient-facing access.

## Slide 6: System Workflow
This slide shows the complete workflow. A patient is registered and sent to triage. Triage directs the patient to OPD or another pathway. If admission is needed, OPD sends the patient to IPD. Clinical requests move through laboratory or radiology, then cashier, then back to the specialist after payment clearance.

## Slide 7: Department-to-Cashier Design
This is an important design decision in the project. OPD and IPD send a clinical item to the laboratory or radiology queue, but they do not directly create a cashier invoice. The laboratorist or radiologist reviews the item, selects the correct service price, and sends one payment request to cashier. This avoids duplicate requests and gives responsibility to the department that knows the exact service performed.

## Slide 8: Technologies
The backend uses PHP 8.2 and Laravel 12. Filament 5 provides the administrative interface, including forms, tables, actions, filters, and dashboards. MySQL stores the data. Blade, Vite, Tailwind CSS, and JavaScript support the frontend. Authentication and permissions protect access to hospital functions.

## Slide 9: Architecture
The interface is built with Filament resources and pages. Laravel models represent the hospital domain and relationships. Events and notifications connect payment changes to the relevant department. Migrations define the database structure and integrity rules. This separation makes the system easier to maintain and extend.

## Slide 10: Major Modules
The most important modules are patient and triage, OPD and IPD, laboratory, radiology, cashier and payments, pharmacy and inventory, administration, reports, and the patient portal. Each module has its own forms, tables, actions, permissions, and relationships.

## Slide 11: Security and Integrity
Security is especially important in a hospital system. Users should access only the functions appropriate to their roles. Foreign keys protect relationships between patients and clinical records. Patients are not hard-deleted when history exists; instead, they can be marked inactive or deceased. Payment status also controls whether laboratory or radiology processing can continue.

## Slide 12: Results and Benefits
The system provides a single source of operational information. Staff can follow patient queues, cashier can process structured requests, departments receive payment notifications, and management can use reports. The result is better traceability and less repeated manual work.

## Slide 13: Challenges and Solutions
The project had several practical challenges. Different departments needed different workflows, so I used role-specific Filament actions. Duplicate cashier requests were prevented by moving cashier request creation to laboratory and radiology. Patient deletion was restricted to protect history. I also corrected workflow statuses so they match the database rules.

## Slide 14: Testing and Validation
I used PHP syntax checking and editor diagnostics during development. I also reviewed the database constraints and tested the OPD-to-IPD and specialist-to-cashier workflows. The next testing level should use a dedicated test database and automated role-based feature tests.

## Slide 15: Conclusion
This project demonstrates a complete hospital workflow rather than only a collection of forms. It connects patient care, clinical services, finance, pharmacy, inventory, reports, and security. The internship gave me practical experience in requirements analysis, Laravel development, database design, debugging, and software engineering teamwork.

## Slide 16: Future Work
The system can be improved with SMS reminders, audit logs, real-time dashboards, stronger browser testing, insurance integration, and production monitoring. These improvements would make the system more reliable and useful at larger scale.

## Slide 17: Closing
Thank you for your attention. I would like to thank Oda Bultum University, the hosting organization, my academic supervisor, and my hosting supervisor for their guidance and support. I am ready for your questions.

# Likely Questions and Strong Answers

## Why did you choose Laravel and Filament?
Laravel provides routing, validation, authentication, Eloquent relationships, migrations, notifications, and a strong application structure. Filament allowed me to build reliable administrative workflows quickly while still keeping the code organized in resources and pages.

## Why should patients not be deleted?
A patient may be referenced by IPD, OPD, laboratory, radiology, payments, prescriptions, insurance, or history records. Deleting the patient would either destroy medical history or break database relationships. Archiving through inactive or deceased status preserves data integrity.

## How is payment connected to laboratory and radiology?
The specialist selects the service and creates a pending service request. The model notifies cashier users. When cashier completes payment, the service request status changes and the department is notified. The clinical action is enabled only after payment is cleared.

## What is the most important lesson from the internship?
The most important lesson is that software engineering is not only coding screens. It requires understanding real workflows, data ownership, validation, security, error handling, and the effect of a change on other departments.

## What would you improve first?
I would first add a dedicated automated feature-test suite using realistic roles and a test database. This would protect the critical patient-flow and payment workflows as the system grows.
