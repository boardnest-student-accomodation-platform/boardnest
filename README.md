# BoardNest
### A Verified Student Boarding Platform for Sri Lanka
<p> <b>BoardNest</b> is a web-based student boarding platform built specifically for the Sri Lankan university context. It enables landlords to list verified rooms, allows students to search and book accommodation, empowers Field Agents to physically verify properties before they go live, and gives Admin full oversight of the platform including listing approvals, user management, and monetisation controls. </p>

<img width="1280" height="832" alt="Landing page" src="https://github.com/user-attachments/assets/ac17142a-ca5b-4ffd-947b-976be9dab16a" />

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [The Problem](#2-the-problem)
3. [The Solution](#3-the-solution)
4. [Actors & Roles](#4-actors--roles)
5. [Core Features](#5-core-features)
   - [Student Module](#51-student-module)
   - [Landlord Module](#52-landlord-module)
   - [Field Agent Module](#53-field-agent-module)
   - [Admin Module](#54-admin-module)
6. [Key Business Logic](#6-key-business-logic)
7. [Monetisation Model](#7-monetisation-model)
8. [Technology Stack](#8-technology-stack)
9. [System Architecture](#9-system-architecture)
10. [Database Structure](#10-database-structure)
11. [Development Methodology](#11-development-methodology)
12. [Project Constraints & Assumptions](#12-project-constraints--assumptions)
13. [Out of Scope](#13-out-of-scope)
14. [Team & Timeline](#14-team--timeline)

---

## 1. Project Overview

**BoardNest** is a web-based student boarding platform built specifically for the Sri Lankan university context. It enables landlords to list verified rooms, allows students to search and book accommodation, empowers Field Agents to physically verify properties before they go live, and gives Admin full oversight of the platform including listing approvals, user management, and monetisation controls.

| | |
|---|---|
| **Platform Type** | Web-based Information System |
| **Target Domain** | Student accommodation / University boarding |
| **Geographical Scope** | Island-wide Sri Lanka, with city-level Field Agent coverage |
| **Tech Stack** | HTML5, CSS3, JavaScript, PHP, MySQL |
| **Payment** | PayHere sandbox (subscription only) |
| **Notifications** | Internal platform + native PHP mail() |

---

## 2. The Problem

Every year, thousands of Sri Lankan undergraduates must secure boarding near universities they've never visited, within weeks of receiving their admission letters. The current landscape is dominated by:

- Facebook Marketplace and Groups
- ikman.lk classifieds
- University WhatsApp groups
- Word-of-mouth referrals

None of these channels offer identity verification for landlords, physical inspection of properties, structured booking processes, or any formal accountability for complaints. Students routinely encounter listings that misrepresent room conditions, pricing, or availability. There is no platform where a student can trust that what they see matches what actually exists.

---

## 3. The Solution

BoardNest's central and defining feature is its **Field Verification System**. Every room listing submitted by a landlord is assigned to a Field Agent employed by the platform and stationed in that city. The agent physically visits the property, completes a structured verification checklist, and submits a report. Only after the Admin reviews and approves that report does the listing become visible to students.

This human-in-the-loop verification model is what fundamentally distinguishes BoardNest from every existing local alternative.

---

## 4. Actors & Roles

### Student
The primary end user. Searches for and books verified accommodation near their university. Can browse publicly without an account but must be verified to apply or contact landlords.

### Landlord
Registers properties and rooms, manages bookings, tracks occupancy, and manages their subscription. Must have at least one property and room registered at the time of account creation. All listings must pass Field Agent verification and Admin approval before going live.

### Field Agent
A platform-employed staff member assigned to a specific city. Physically visits properties, completes structured inspection checklists, submits verification reports, investigates complaints, and submits area reports. Registers through the platform with Admin approval, same as other roles.

### Admin
Single account created directly in the database. Manages agent roster, approves listings and registrations, moderates complaints and reviews, publishes area profiles, and oversees the full monetisation layer.

---

## 5. Core Features

### 5.1 Student Module

#### Account & Registration
- Public browsing without login — listings, area guides, search, and reviews are all accessible without an account
- Account created with email and password only when the student first clicks Apply or attempts to contact a landlord
- One-time identity step completed inline at first application: full name, mobile number, NIC scan, selected university, academic year, and proof of studentship document
- All submitted information saved to profile and pre-filled for all subsequent applications
- **Tier 1 (Pending Verification):** profile submitted, admin spot-check not yet completed; landlord sees this status
- **Tier 2 (Verified):** admin has confirmed the NIC and document combination; verified badge shown to landlords
- Nameless invitation/selection letters auto-flagged to admin queue for manual spot-check
- Temporary accounts (pre-enrolment students) have a one-month deadline to submit a named document before auto-suspension

#### Search & Discovery
Three primary search modes:
- **By city/town** — dropdown selection from available cities in the database
- **By KM radius** — Haversine formula against stored landmark coordinates (university gates, bus stands, train stations pre-seeded in the database); no map API required
- **Around [University Name]** — groups results by area and shows student saturation counts (e.g. "14 listings near Katubedda, 9 near Moratuwa town") generated from a GROUP BY query against the AreaGuide table

Additional filters:
- Price range (min/max)
- Gender preference (male/female/mixed)
- Room type (single/shared)
- Type of stay (apartment, boarding house, hostel, convent/church-based, house, annex)
- Minimum review rating
- Number of members per group (for shared rooms — 2-person or 3-person)

**Area smart cards** display approximate travel time to university, known bus routes, nearby grocery access, and safety tag — all sourced from admin-maintained AreaGuide and AreaSafetyProfile tables. No external API required.

**Safety flags:** areas with a Caution Advised or Under Review classification display a visible warning badge on the area card and on all individual listings from that area.

Only listings with Live status and a valid Field Verified badge appear in any search result.

#### Listing Detail View
- Room photos and videos uploaded by landlord
- Room type, price, security deposit, furnishing status, bathroom type, Wi-Fi, utility bill structure
- House rules including gender preference and curfew
- Field Verified badge and verified amenity confirmations
- Aggregated review score and all past reviews
- For partially occupied shared rooms: existing occupant's public profile (name, university, academic year) shown to assess compatibility
- **Phone number and Apply button are server-side withheld** — only injected into the page HTML for logged-in verified students; not just visually hidden, preventing scraping

#### Saved Listings
- Bookmark icon on any listing card saves it to the student's dashboard
- Clicking bookmark while not logged in prompts account creation
- Accessible from student dashboard for later reference

#### Booking
- Student submits a booking request for a single room or a single slot in a shared room
- Student's full profile (name, university, academic year, NIC reference, verification tier) auto-attaches to the request
- Landlord receives notification and reviews the application
- Student tracks status from dashboard: Pending, Accepted, Rejected
- Notifications sent at every status transition

#### Roommate Matching & Dynamic Slot Pricing
- When a student opts into a partially occupied shared room, they see the existing occupant's profile before applying
- **Partial occupancy pricing rule:**
  - Existing occupant pays 75% of full room rate (their 50% share + 25% vacancy buffer)
  - Landlord absorbs the remaining 25% as a vacancy subsidy
  - When second student books, both revert to standard 50% from the next full monthly cycle
  - Mid-month arrivals take effect from the next invoice cycle
- For 3-person rooms: landlord absorbs 25% of each empty slot's per-person rate; existing occupants share the remaining vacancy burden proportionally
- Landlord dashboard shows a "Current vacancy subsidy" figure aggregating across all partially occupied rooms

#### Reviews & Ratings
- Available after a confirmed stay is completed
- Star rating (1–5) and written comment
- Publishes immediately and updates the listing's aggregate score (running average of all review scores via SQL AVG())
- Visible to all future students browsing the listing

#### Complaints
Complaint status tracking chain: New → Under Moderation → Assigned to Agent → Under Investigation → Resolved / Dismissed

- **Dismissed complaints:** student notified to resolve directly with landlord; these do not count toward the three-complaint auto-flag
- **Valid complaints:** assigned to regional Field Agent; student notified investigation will follow
- **Fee discrepancy complaints** (landlord charging differently to listed price) bypass standard moderation queue and go directly to Admin as a priority Fee Discrepancy Alert
- Only admin-upheld complaints count toward the three-complaint landlord auto-flag

#### Notifications
In-platform notifications plus PHP mail() emails for:
- Booking accepted or rejected
- Complaint status updates at every stage
- Platform-wide announcements from Admin

---

### 5.2 Landlord Module

#### Registration (Three Stages)
**Stage 1 — Minimal account:** NIC number, full name, email, password, contact number. Account saved as draft immediately — landlord can take breaks and return. Account invisible to all users until verification complete. Non-verified accounts have a **30-day window** to complete all stages (7-day warning in profile, 1-day final notification, then auto-deleted).

**Stage 2 — Identity verification:** NIC/passport scan upload and permanent address. Account moves to Pending Identity Verification.

**Stage 3 — Property and room setup:**
- **Property:** address, structural type, GPS coordinates (manually copied from Google Maps), Google Maps share link, shared building-level facilities
- **Room:** room type (single or shared with slot capacity of 2 or 3), partial occupancy toggle, price, security deposit, square footage, furnishing, bathroom access, Wi-Fi, house rules, gender preference, curfew policies
- Photo upload optional at this stage — Field Agent on-site photos form the verified record
- Once complete: account moves to Pending Confirmation, Admin notified

#### Property & Room Management
- Two-level hierarchy: Property (address, GPS, structural type) → Room (price, type, photos, occupancy state)
- Even a single-room property follows this structure — one Property row, one Room row
- Landlords can add further rooms to existing properties or register new properties after confirmation
- **Standard tier limit:** 1 property, maximum 4 rooms — system blocks and prompts Pro upgrade if exceeded
- Each new submission: status set to Pending, Field Agent in that city automatically notified

#### Booking Management
- Receives real-time notification for each incoming booking request
- Reviews applicant's name, university, academic year, NIC reference, and verification tier
- Accepts or declines; acceptance marks the slot Occupied
- If last slot filled: room automatically hidden from search as Fully Occupied
- Can mark a slot Vacant when a tenant moves out to reopen it for search

#### Rent & Fee Management
- Rent figures displayed on the platform for reference only — all payments are physical cash between landlord and student
- Fee change requests require mandatory written reason and supporting proof (e.g. municipal tax notice) submitted to Admin
- Listing displays "Fee update pending verification" until Admin approves
- Unannounced fee changes reported by students or discovered by agents trigger a priority Fee Discrepancy Alert to Admin

#### Subscription Management
- **Standard tier:** 1 property, 4 rooms max, standard search visibility, verified badge, basic dashboard
- **Pro tier:** unlimited properties and rooms, premium search boost, priority Field Agent scheduling, detailed analytics, vacancy subsidy cap protection, full complaint resolution trail, ability to highlight up to 2 listings in area search results
- Upgrade handled through PayHere sandbox payment integration

#### Verification Lifecycle Tracking
Landlord can track their listing's status from dashboard:
Pending → Under Verification → Agent On-Site → Awaiting Admin Approval → Live / Rejected / Suspended

---

### 5.3 Field Agent Module

#### Registration & Assignment
- Self-registers through the platform using the same role-selection flow as students and landlords
- Enters name, NIC, email, contact number, and preferred city
- Waits for Admin approval before account is activated
- Once approved: locked to assigned city; system filters all tasks to that region only

#### Task Queue Dashboard
Three pipeline states:
- **Pending Inspections:** newly submitted or modified properties awaiting physical audit
- **In-Progress Tasks:** properties currently under field evaluation
- **Completed History:** immutable, read-only archive of all past submissions

#### GPS Geofence Verification
- When agent taps "Start Verification," browser's native Geolocation API (`navigator.geolocation.getCurrentPosition()`) captures live device coordinates — no external map API needed
- PHP backend runs Haversine formula comparing agent's coordinates against property's stored coordinates
- **If distance ≤ 100m:** system unlocks the verification checklist form; listing status auto-updates to Agent On-Site
- **If distance > 100m:** form remains locked; agent must physically relocate
- **Frontend hides the form for UX; backend re-validates coordinates at submission time** — prevents form tampering via browser dev tools
- Gallery access disabled for photo capture; native rear camera only — prevents reused or fraudulent images

#### Two-Tiered Verification Checklist
**Property level:**
- Structural safety and perimeter security
- Electrical wiring safety
- Fire exit pathways
- GPS coordinate match confirmation
- Neighbourhood safety assessment

**Room level (per room):**
- Furnishing matches declared status
- Bathroom access as declared
- Wi-Fi coverage confirmation
- Rent figures, security deposit, and utility charges match listing

Any discrepancy in rent or amenities is recorded and flagged. Minimum two on-site photos required before report can be submitted.

#### Report Submission
- Completed report submitted to Admin queue
- Listing status automatically moves to Awaiting Admin Approval
- Submission logged as immutable record in Completed History

#### Area Reports
- Periodic submission of ground-level observations about the assigned city
- Covers transport (bus routes, railway, three-wheelers), amenities (supermarkets, pharmacies), safety concerns (scam activity, isolated roads, poor lighting)
- Goes to Admin review queue — not published publicly until Admin curates and approves

#### Complaint Investigation
- Admin-assigned complaints appear in task queue
- Agent visits property, assesses specific issue, records findings
- Submits recommendation: dismiss, uphold, or escalate
- If landlord denies access: recorded as uncooperative, complaint auto-escalated
- Visit fee logged against landlord's account for every valid investigation visit

#### Emergency Suspension
- High-clearance override button available during any visit
- If severe immediate safety hazard found (broken locks, structural damage, unsafe conditions): agent triggers instant Suspended state
- Listing immediately hidden from public search
- Mandatory written reason required
- Pending Admin review before any reinstatement

---

### 5.4 Admin Module

#### Authentication
- Single account created directly in the database — no self-registration path
- Secure login with session management

#### Field Agent Management (Agent Roster)
- Creates new Field Agent accounts after reviewing self-registration applications
- Assigns city, views full activity history per agent
- Monitors agents with overdue verification tasks
- Edits agent details, reassigns cities
- Deactivates or reactivates accounts
- System warns if a city already has an active agent before allowing a second assignment

#### Listing Approval Workflow
- Reviews pending approvals queue: landlord details side by side with Field Agent's full report
- **Approve button is disabled if no verification report exists** — cannot be bypassed
- Three available actions: Approve (listing goes Live, landlord notified), Reject (mandatory written reason, landlord notified), Request Re-verification (agent notified to revisit)
- Views all approved listings with current status and all rejected listings with history

#### User Management
- View all registered users filterable by type, city, registration date, status
- View full profile and activity for any user
- Suspend account with duration and mandatory reason (landlord's listings auto-hidden)
- Reactivate suspended account (landlord's listings auto-restored)
- Permanently ban with mandatory reason recorded
- Monitor 30-day landlord countdown and 1-month student deadline queues
- System-triggered auto-suspensions appear in admin awareness queue

#### Student Verification Queue
- Accounts flagged for spot-check (nameless invitation letters) appear in a dedicated queue
- Admin reviews NIC and document combination
- Confirms Tier 2 status or rejects with notification to student

#### Complaint & Review Moderation
- Reviews all New complaints in moderation queue
- **Dismiss** (non-actionable: personal disputes, roommate conflicts): recorded reason, student notified to resolve directly
- **Assign to Field Agent** (valid complaints): agent notified, student notified of investigation
- **Fee Discrepancy Alerts** arrive as priority items bypassing standard queue
- Reviews Field Agent investigation reports, makes final decision: resolved, upheld with landlord action, or escalated
- If landlord accumulates **3 upheld complaints within 6 months**: system automatically flags account for Admin review
- Reviews flagged reviews: removes with recorded reason or dismisses flag keeping review live
- Every moderation action logged in full history

#### Area Profile Management
- Reviews Field Agent area reports
- Selects transport tags and amenity tags to publish
- Assigns safety classification: **Standard**, **Caution Advised**, or **Under Review**
- Writes area description
- Publishes to Area Guide visible to students
- **Caution Advised:** automatically triggers warning badge on all listings from that city
- **Under Review:** automatically pauses new listing approvals for that city
- If multiple complaints from same area filed in short period: system auto-flags that city for Admin attention

#### Analytics Dashboard
- Total registered users by type (Students, Landlords, Field Agents)
- Total listings by status (Pending / Live / Suspended / Rejected)
- Bookings by city and month
- Listings by city
- Complaints filed and resolution rates
- Field Agent performance: verifications completed per agent

#### Notifications & Announcements
- Platform-wide announcements to all users
- Targeted notifications to specific user groups (e.g. all landlords in Kandy, all students)
- System-generated notifications (auto-alerts like three-complaint flag)
- View all sent notifications history

#### Monetisation Management
- Configures commission rate percentage
- Sets subscription tier pricing for Standard and Pro
- Views full commission ledger across all landlords
- Monitors visit fee balances logged against landlord accounts
- Oversees subscription payment records via PayHere

---

## 6. Key Business Logic

### Listing Lifecycle State Machine
```
Pending → Under Verification → Agent On-Site → Awaiting Admin Approval → Live
                                                                        → Rejected
                                                                        → Suspended
```

### GPS Geofence Enforcement
1. Agent triggers verification on mobile browser
2. Browser Geolocation API captures raw device coordinates
3. PHP Haversine formula compares against stored property coordinates
4. If within 100m: form unlocks, Agent On-Site status auto-triggered
5. If outside 100m: form remains locked
6. Backend re-validates coordinates at report submission — frontend bypass is impossible

### Dynamic Slot Pricing
| Room state | Occupant pays | Landlord absorbs |
|---|---|---|
| Partially occupied (1 of 2) | 75% of full room rate | 25% |
| Partially occupied (1 of 3) | 50% of full room rate | 50% |
| Partially occupied (2 of 3) | 41.6% each | 16.6% |
| Fully occupied (2 of 2) | 50% each | 0% |
| Fully occupied (3 of 3) | 33.3% each | 0% |

### Three-Complaint Auto-Flag Rule
- Only admin-upheld complaints count toward the flag
- Dismissed complaints do not count
- If 3 upheld complaints within 6 months: system automatically flags landlord account for Admin review

### Approval Gate
- Admin cannot approve a listing unless a Field Agent verification report exists for it
- The approve button is disabled at UI level and rejected at server level if no report exists in the database

### Area Saturation Grouping
- "Around [University Name]" search mode runs a GROUP BY query against AreaGuide table
- Counts listings per area within a defined radius of the university's stored coordinates
- Displays as: "14 listings near Katubedda, 9 near Moratuwa town"
- No external API — pure SQL aggregation against internal data

---

## 7. Monetisation Model

### Landlord Subscription Tiers

| Feature | Standard | Pro |
|---|---|---|
| Properties | 1 | Unlimited |
| Rooms per property | 4 max | Unlimited |
| Search visibility | Standard | Premium search boost |
| Verified badge | ✓ | ✓ |
| Analytics | Basic | Detailed (trends, conversion, tenure) |
| Complaint history | Outcome only | Full resolution trail |
| Featured listings | — | Up to 2 highlighted |
| Priority Field Agent scheduling | — | ✓ |
| Vacancy subsidy cap protection | — | ✓ |

Payment processed via **PayHere sandbox** for demonstration purposes.

### Per-Booking Commission
- Percentage of first month's rent logged in the ledger when tenancy is confirmed
- Tracked internally — no payment gateway involved
- Landlord settles physically with Admin; running balance visible on dashboard

### Field Agent Visit Fee
- Fixed visit fee logged against landlord's account for every Field Agent visit:
  - Initial verification visit
  - Re-verification visit
  - Valid complaint investigation visit (only if complaint passes Admin moderation)
- Dismissed complaints cost the landlord nothing
- Recorded as payable balance in ledger — not collected digitally

---

## 8. Technology Stack

### Frontend
- HTML5
- CSS3
- JavaScript (vanilla, no frameworks)
- Browser Geolocation API (native, for Field Agent GPS capture)
- `tel:` links for landlord call button (no API required)
- Google Maps URLs (stored in DB, opened in new tab — no Maps API)

### Backend
- PHP (no frameworks)
- Haversine formula implemented in PHP for all distance calculations
- Native PHP `mail()` function for email notifications (no third-party email service)

### Database
- MySQL

### Payment
- PayHere payment gateway (sandbox mode) for landlord subscription fees only

### Development Tools
- Visual Studio Code
- Git + GitHub
- Browser developer tools
- Manual test cases

### Deployment
- Locally hosted Apache server on team members' machines

---

## 9. System Architecture

BoardNest follows a **modular architecture** organised around four functional domains. Each domain is independently developed, tested, and maintainable:

| Module | Responsibility |
|---|---|
| Identity & Access Management | Registration, authentication, session management, verification tiers, account lifecycle |
| Listing & Verification Pipeline | Property/room CRUD, listing state machine, geofence validation, checklist, approval gate, fee management |
| Search, Discovery & Booking | Haversine distance, university proximity grouping, area guide, filters, booking lifecycle, slot pricing |
| Reviews, Complaints & Platform Management | Review aggregation, complaint pipeline, moderation, auto-flags, commission ledger, analytics, notifications, PHP mail |

### Three-Tier Web Application
- **Client Layer:** Browser-based UI (HTML/CSS/JS) across four role-specific interfaces plus a public interface
- **Server Layer:** PHP backend handling all business logic, validation, and database operations
- **Data Layer:** MySQL relational database

### External Services
| Service | Usage | How |
|---|---|---|
| PayHere | Subscription payments | Sandbox API integration |
| Device GPS | Field Agent location capture | Browser native Geolocation API |
| Google Maps | Property location display for students | Stored URL opened in new browser tab |
| PHP mail() | External email notifications | Built-in PHP function, no third-party service |

---

## 10. Database Structure

### Core Tables

| Group | Tables |
|---|---|
| Users & Roles | students, landlords, field_agents, admin |
| Property & Listings | properties, rooms, listing_status_log |
| Bookings & Occupancy | booking_requests, room_slots, saved_listings |
| Verification | verification_reports, agent_tasks, checklist_responses, area_reports, area_profiles |
| Reviews & Complaints | reviews, complaints, complaint_moderation_log, flagged_reviews |
| Finance | invoices, commission_ledger, visit_fee_ledger, subscription_records |
| Notifications | internal_notifications, email_log |
| Reference Data | universities, landmarks, area_guide, area_safety_profiles |

### Key Design Decisions
- **Property → Room hierarchy:** every listing follows a two-level structure regardless of whether a property has one room or many. This supports the Field Agent's two-tiered checklist (property-level and room-level verification) and keeps GPS coordinates at the property level where they belong
- **AreaGuide and AreaSafetyProfile are admin-curated tables** fed by Field Agent area reports — no external data source
- All distance calculations use stored latitude/longitude coordinates with the Haversine formula — no map API dependency anywhere in the schema

---

## 11. Development Methodology

**Agile** with two-week sprints aligned to the biweekly supervisor meeting cycle.

Each sprint begins with a planning meeting and ends with a review plus an updated progress report.

### Development Phases

| Phase | Duration | Focus |
|---|---|---|
| Foundation | Weeks 1–6 | Problem identification, requirement analysis, system design, use case diagram, project proposal |
| Core Modules | Weeks 7–16 | Each member builds their functional module's core CRUD features; minimum 4 CRUD operations per member by interim deadline |
| Business Logic | Weeks 17–26 | Complaint pipeline, subscription enforcement, listing lifecycle state machine, account flags, review moderation |
| Integration & Testing | Weeks 27–36 | Cross-module integration, test case documentation, viva preparation, final demonstration build, project report |

### Work Distribution (by functional module, not by actor)
1. Identity & Access Management
2. Listing & Verification Pipeline
3. Search, Discovery & Booking
4. Reviews, Complaints & Platform Management

---

## 12. Project Constraints & Assumptions

### Constraints
- Project must be completed within one academic year
- No external frameworks or libraries — plain HTML, CSS, JavaScript, PHP, MySQL only
- No third-party map APIs (Google Maps, OpenStreetMap, etc.)
- No AI-based recommendation engine
- No native mobile application — web-only, mobile-responsive
- No third-party email service — PHP mail() only
- GPS coordinate accuracy subject to hardware sensor limitations; 100m geofence radius chosen to accommodate typical smartphone GPS drift in urban environments
- Physical property verification requires Field Agents to physically travel — introduces manual processing delays

### Assumptions
- Landlords have access to Google Maps via browser to obtain property GPS coordinates during listing creation
- Field Agents have smartphone access with a browser that supports the native Geolocation API
- Sufficient student and landlord adoption to sustain the platform given the documented market problem
- Students without a student ID at registration will obtain official verification within the one-month grace period

---

## 13. Out of Scope

| Item | Reason |
|---|---|
| Rent payment processing | All rent is physical cash between landlord and student; platform acts as ledger reference only |
| Third-party payment gateway for rent | Not applicable given cash-based market |
| Native iOS/Android application | Web-only; `tel:` links and Geolocation API cover mobile needs via browser |
| Third-party map APIs | Haversine formula on stored coordinates covers all distance features |
| AI-based recommendation engine | Manual filters are sufficient for stated use case |
| Third-party email service (SendGrid, Mailgun, etc.) | PHP mail() covers required notification scope |
| Multi-language support | English only |
| Social login (Google, Facebook OAuth) | Out of technical scope |
| Real-time chat | Excluded to ensure core Field Verification System is fully functional within timeline |

---

## 14. Team & Timeline

**Team size:** 4 members  
**Duration:** 1 academic year  
**Repository:** GitHub (version controlled throughout)  
**Supervisor meetings:** Biweekly with progress reports

### Milestone Schedule

| Milestone | Target | Deliverables |
|---|---|---|
| Biweekly Reports | Every 2 weeks | Meeting log, individual contribution summary, next sprint plan |
| Preliminary Presentation | Semester 1, Week 4–5 | Proposal document, feasibility study, initial requirements, use case identification |
| Interim Presentation | Semester 1, after exams | Finalised UIs for all modules, minimum 4 CRUD operations per member |
| Final Presentation | Semester 2, after exams | Fully working system, test case document, contribution log |
| Individual Code Check | Semester 2, after exams | 30-minute individual session, code explanation, task walkthrough |

---

*BoardNest — transforming an informal and unreliable process into a transparent and accountable platform for Sri Lankan university students.*
