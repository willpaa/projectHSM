# 🏥 SERVICE TYPES — SIRNA BULCHIINSA HOSPITAALAA

> **Kaayyoo:** Tarree `service_type` hundaa — kan amma jiru fi kan dabalama qabu — akka
> tajaajilli (services) sirriitti `Finance > Service Catalogue` jalatti galmaa'u.
>
> **Bakka madda (source of truth):** `database/migrations/2026_04_30_145816_create_services_table.php`

---

## 1️⃣ AMA JIRU — ENUM KAN AMMA JIRU (8)

Kunneen `services` teebilii keessatti `enum` ta'ee jiru. Koodii kana jijjiiruu yoo barbaadde,
migration haaraa uumuu qabda (enum dabalachuuf) — sirna MySQL keessatti `ALTER TABLE` barbaachisa.

| # | `service_type` | Label (UI) | Department | Eenyu raawwata | Notification roles (`ServiceRequestNotifier`) |
|---|---|---|---|---|---|
| 1 | `lab` | Laboratory | Laboratory | Lab Technician | `lab_staff`, `laboratory_staff`, `lab_technician` |
| 2 | `radiology` | Radiology | Radiology | Radiology Technician | `radiology_staff`, `radiology_technician` |
| 3 | `pharmacy` | Pharmacy | Pharmacy | Pharmacist | `pharmacy_staff`, `pharmacist` |
| 4 | `consultation` | Consultation | OPD | Doctor | `opd_staff`, `doctor`, `nurse` |
| 5 | `physiotherapy` | Physiotherapy | Physiotherapy | Physiotherapist | **default** → `admin`, `super_admin` |
| 6 | `surgery` | Surgery | Operation Theatre | Surgeon | `ipd_staff`, `nurse` |
| 7 | `bed_charges` | Bed Charges | IPD / Ward | Nurse / IPD Staff | `ipd_staff`, `nurse` |
| 8 | `emergency` | Emergency | Triage / Emergency | Triage Nurse | `opd_staff`, `doctor`, `nurse` |

### Bakka `enum` kun koodii keessatti yaadamu (8 bakka)

| Fayilii | Maal jedha |
|---|---|
| `database/migrations/2026_04_30_145816_create_services_table.php` | **MADDA** — `enum('service_type', [...])` |
| `app/Filament/Resources/Services/Schemas/ServiceForm.php` | Dropdown `->options([...])` — galmee haaraa |
| `app/Filament/Resources/Services/Tables/ServicesTable.php` | Filter `TableFilters::select('service_type', [...])` |
| `app/Filament/Resources/ServiceRequests/Schemas/ServiceRequestForm.php` | Filter — `lab`, `radiology`, `pharmacy` **qofa** |
| `app/Support/ServiceRequestNotifier.php` | `match ($serviceRequest->service?->service_type)` → roles |
| `app/Models/Service.php` | Scopes: `scopeLabServices`, `scopeRadiologyServices`, `scopePharmacyServices` |
| `app/Filament/Resources/Laboratories/Tables/LaboratoriesTable.php` | `->where('service_type', 'lab')` |
| `app/Filament/Resources/Radiologies/Tables/RadiologiesTables.php` | `->where(...)` radiology service filachuuf |

> ⚠️ **HUBADHU:** Yeroo `service_type` haaraa dabaltu, **bakka hunda** (keessumaa
> `ServiceForm.php` **fi** `ServicesTable.php` **fi** migration) waliin jijjiiruu qabda.
> Yoo tokko hafte, Filament "options" hin argitu, garuu DB keessa jiraata — kun bug fidu.

---

## 2️⃣ SERVICE REQUEST ↔ FINANCE — AKKAMITTI WAL QABU

Kun qaama "Laboratory ↔ Finance" hojii keetiif baay'ee barbaachisa:

```
[Doctor / OPD]                    [Finance / Cashier]              [Laboratory]
     │                                    │                              │
     │ 1. ServiceRequest uuma            │                              │
     │    (service_type = 'lab')         │                              │
     ├───────────────────────────────────>│                              │
     │    payment_status = 'pending'      │                              │
     │                                    │ 2. Verify (cashier)          │
     │                                    │    payment_status='verified' │
     │                                    │                              │
     │                                    │ 3. Payment create           │
     │                                    │    payment_status='paid'     │
     ├────────────────────────────────────┼─────────────────────────────>│
     │                                    │  4. Lab galmee banu          │
     │                                    │     Laboratory::paymentCleared() = true
     │                                    │     (service_request_id)      │
```

**Qabiyyeewwan ijoo (key fields):**

| Teebilii | Dirree | Hiika |
|---|---|---|
| `service_requests` | `service_id` | Tajaajila filatame (`services.id`) |
| `service_requests` | `patient_id` | Dhukkubsataa |
| `service_requests` | `total_amount` | Gatii guutuu (`services.price`) |
| `service_requests` | `patient_share` | Kan dhukkubsataan kaffalu |
| `service_requests` | `insurance_share` | Kan insuraansiin kaffalu |
| `service_requests` | `discount` | Hir'isaa |
| `service_requests` | `payment_status` | `pending` → `verified` → `paid` / `insurance` |
| `laboratories` | `service_request_id` | Request kaffaltii waliin hidhu |
| `laboratories` | `cost` | Gatii qorannoo |
| `works` | `paymentCleared()` | `serviceRequest?->canProceedForService()` |

> ⚠️ **RAKKOO AMMA JIRU:** `ServiceRequestForm.php` keessatti `service_id` dropdown
> `whereIn('service_type', ['lab', 'radiology'])` **qofa** filata. Kanaaf `pharmacy`,
> `consultation`, `surgery`, n.k. tajaajilli `ServiceRequest` uumuun **hin danda'amu** —
> garuu filter `pharmacy` ni agarsiisa. Kun wal-dhabdee (inconsistency) qulqulluu ta'e.

---
