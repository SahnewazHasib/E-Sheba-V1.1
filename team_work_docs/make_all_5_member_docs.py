# -*- coding: utf-8 -*-
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from doc_builder_base import (
    set_cell_background, set_cell_margins, add_header_banner,
    add_heading_1, add_heading_2, add_paragraph, add_bullet,
    add_callout, add_styled_table
)
import os

def create_doc_person_1():
    doc = docx.Document()
    for s in doc.sections:
        s.top_margin = Inches(1); s.bottom_margin = Inches(1)
        s.left_margin = Inches(1); s.right_margin = Inches(1)
        
    add_header_banner(
        doc,
        title="PERSON 1: CUSTOMER-SIDE FRONTEND & MARKETPLACE UI",
        subtitle="Individual Project Contribution & Technical Defense Handbook",
        member_name="Sahnewaz Hasib",
        student_id="4264",
        role_title="Lead Frontend Engineer (Customer Platform & Marketplace Experience)"
    )
    
    add_heading_1(doc, "1. Executive Summary & Project Motive")
    add_paragraph(doc, "In Bangladesh, finding reliable and skilled local technicians (such as electricians, plumbers, and mechanics) has traditionally relied on word-of-mouth or roadside searches. This manual process is time-consuming, lacks price transparency, and offers no safety assurance. The primary motive of my role as Person 1 was to create an intuitive, modern, and accessible web marketplace that empowers regular citizens to search, filter, evaluate, and book verified service technicians in seconds.")
    add_paragraph(doc, "My role focused on building the entire public-facing marketplace, consumer booking flow, responsive navigation, service category discovery, interactive map visualization, and the dedicated customer dashboard where users track their job statuses.")
    
    add_heading_1(doc, "2. Core Goals & Technical Objectives")
    add_bullet(doc, "Design a responsive, visually appealing landing page that immediately communicates trust, service offerings, and local coverage.", "Primary User Journey")
    add_bullet(doc, "Implement a comprehensive search and filtering engine allowing customers to locate technicians by trade, district, thana, and rating.", "Discovery System")
    add_bullet(doc, "Build the interactive technician profile page containing verified badges, ratings, hourly pricing, service coverage maps, and direct contact buttons.", "Worker Transparency")
    add_bullet(doc, "Develop the customer booking modal with asynchronous AJAX submission to guarantee zero page reload during service ordering.", "Seamless Booking")
    add_bullet(doc, "Provide a clean customer dashboard where clients can track ongoing bookings, cancel pending orders, leave ratings/reviews, and launch payments.", "Post-Order Management")
    
    add_heading_1(doc, "3. Complete Inventory of Code Files Created & Managed")
    add_paragraph(doc, "Below is the complete inventory of files designed, coded, and maintained under my subsystem:")
    
    headers = ["File Name", "Location", "Key Functions / Technical Scope"]
    data = [
        ["index.php", "Root", "Marketplace home page: hero search bar, 25 service category cards, featured top-rated workers carousel, interactive coverage map, PWA install prompt, client testimonials."],
        ["categories.php", "Root", "Comprehensive directory of all 25 service categories with custom FontAwesome icons, active counters, and instant filtering redirects."],
        ["workers.php", "Root", "Public worker search directory with dynamic GET filters for category, district, and thana. Includes sorting by rating and price per hour."],
        ["worker-profile.php", "Root", "Technician detail page: renders bio, experience years, verified badge, Leaflet.js service map, verified reviews list, and the Book Service modal."],
        ["customer/dashboard.php", "customer/", "Customer command hub: summary counters (active, completed, cancelled jobs), recent booking records table, status badges, cancel button, and review dialog."],
        ["customer/bookings.php", "customer/", "Full customer booking history view with detailed time stamps, addresses, assigned workers, and payment status indicators."],
        ["how-it-works.php", "Root", "Educational consumer page illustrating the 4-step workflow: Search -> Compare -> Book -> Pay & Review."],
        ["about.php", "Root", "Institutional information page presenting the World University of Bangladesh CSE department project credentials and team member roster."],
        ["contact.php", "Root", "Customer support portal with a form for submitting help requests, inquiries, and customer feedback."],
        ["faq.php", "Root", "Interactive accordion-style Frequently Asked Questions addressing booking policies, safety, and payment methods."],
        ["privacy.php", "Root", "Terms of use, customer privacy statements, and platform guidelines for consumer transparency."],
        ["css/style.css", "css/", "Central CSS design system: Google modern color palette, responsive breakpoints, flexbox grid, custom badges, card hover animations, and modal stylings."]
    ]
    add_styled_table(doc, headers, data, [Inches(1.8), Inches(1.2), Inches(3.5)])
    
    add_heading_1(doc, "4. Programming Languages & Concepts to Learn for Defense")
    add_paragraph(doc, "To confidently explain and showcase your work during academic defense or technical interviews, you must master the following fundamentals:")
    add_bullet(doc, "Semantic tags (<header>, <nav>, <section>, <article>, <footer>), form elements, meta viewport for mobile friendliness, and accessible modal structures.", "HTML5 & UI Architecture")
    add_bullet(doc, "Bootstrap 5 grid system (container, row, col-md-*, col-lg-*), utility classes (d-flex, gap-*, rounded-pill, shadow-sm), and custom CSS overrides (variables, flexbox, box-shadows, animations).", "CSS3 & Bootstrap 5")
    add_bullet(doc, "DOM manipulation via jQuery ($('#id').on('click')), FormData API, reading input values, validating phone numbers/dates, and handling dynamic modal popups.", "JavaScript & jQuery")
    add_bullet(doc, "How fetch() and $.post() send JSON/POST payloads to PHP backend endpoints (such as api/book-worker.php) without reloading the page, handling success and error callbacks.", "AJAX & Asynchronous HTTP")
    add_bullet(doc, "Initializing Leaflet map via L.map(), adding OpenStreetMap tiles, and placing custom markers using L.marker() with dynamic latitude/longitude coordinates.", "Leaflet.js Mapping")
    add_bullet(doc, "Using PHP echo, foreach loops, null coalescing (??), and ternary operators to render database records dynamically into HTML tables and cards.", "Basic PHP Templating")
    
    add_heading_1(doc, "5. Step-by-Step Live Project Showcase Walkthrough")
    add_paragraph(doc, "Follow this exact sequence when demonstrating your contribution on screen:")
    add_bullet(doc, "Open http://localhost/e-sheba/index.php. Explain the clean modern layout, the hero search bar, and click on any category pill (e.g., 'Electrician') to show instant filtering.", "Step 1: Homepage & Search")
    add_bullet(doc, "Navigate to workers.php. Demonstrate selecting District 'Dhaka' and Category 'Plumber'. Show how the worker cards dynamically filter and display ratings, pricing, and area.", "Step 2: Filterable Worker Directory")
    add_bullet(doc, "Click 'View Profile' on any worker card (worker-profile.php?id=2). Show the technician's biography, verified badge, the Leaflet coverage map, and customer reviews.", "Step 3: Worker Profile & Leaflet Map")
    add_bullet(doc, "Click the 'Book This Provider' button. Select a service category, choose a preferred schedule date/time, type a service address (e.g. 'Mirpur-10, Dhaka'), and click 'Confirm Booking'. Explain how AJAX submits the form instantly.", "Step 4: Booking Modal AJAX Demonstration")
    add_bullet(doc, "Log in as Customer (Shahnewaz Khan) and open customer/dashboard.php. Show the newly created booking appearing in the table with a 'Pending' badge.", "Step 5: Customer Dashboard & Order Tracking")
    
    add_heading_1(doc, "6. Oral Defense & Viva Q&A (Sample Questions & Answers)")
    add_bullet(doc, "Answer: I used the Fetch API to post asynchronous JSON requests to api/book-worker.php. When the response arrives, JavaScript parses the JSON and shows a success toast without disrupting the user view.", "Q1: How does your booking modal submit without refreshing the page?")
    add_bullet(doc, "Answer: worker-profile.php reads the worker's latitude and longitude from the database, and injects them into Leaflet.js (L.map().setView([lat, lng], 13)). If coordinates are missing, it defaults to Dhaka center.", "Q2: How does the map on the worker profile display their location?")
    add_bullet(doc, "Answer: We use Bootstrap 5's responsive 12-column grid system along with CSS media queries. Desktop screens show a 3-column worker card layout, while smartphones automatically stack into a single column.", "Q3: How do you guarantee the customer UI works on mobile phones?")
    add_bullet(doc, "Answer: Client-side validation checks date validity and non-empty address fields before sending. Server-side validation in PHP re-verifies these parameters before writing to the bookings table.", "Q4: How do you prevent invalid bookings (e.g., past dates or empty addresses)?")
    
    add_heading_1(doc, "7. System Limitations & Future Improvements")
    add_bullet(doc, "Client GPS detection can be blocked if browser permissions are denied; resolved by defaulting to city center coordinates.", "Technical Limitation")
    add_bullet(doc, "Customers cannot currently reschedule an already accepted booking without cancelling and re-booking.", "Workflow Limitation")
    add_bullet(doc, "Add multi-language localization (Bangla & English toggle) for blue-collar consumers across rural districts.", "Future Scope 1")
    add_bullet(doc, "Integrate live GPS driver/worker real-time tracking similar to Uber or Pathao.", "Future Scope 2")
    
    add_heading_1(doc, "8. Conclusion")
    add_paragraph(doc, "Through this customer-facing marketplace implementation, E-Sheba provides an accessible, transparent, and user-friendly digital bridge between Bangladeshi households and skilled service workers. The responsive architecture, interactive search tools, and zero-reload booking workflows deliver a polished, commercial-grade user experience.")
    
    path = "C:/xampp/htdocs/e-sheba/team_work_docs/Person_1_Sahnewaz_Hasib_Customer_Frontend.docx"
    doc.save(path)
    print("Created:", path)

def create_doc_person_2():
    doc = docx.Document()
    for s in doc.sections:
        s.top_margin = Inches(1); s.bottom_margin = Inches(1)
        s.left_margin = Inches(1); s.right_margin = Inches(1)
        
    add_header_banner(
        doc,
        title="PERSON 2: WORKER & ADMIN BACK-OFFICE SUBSYSTEM",
        subtitle="Individual Project Contribution & Technical Defense Handbook",
        member_name="Fatema Khatun",
        student_id="4276",
        role_title="Lead Backend/Frontend Engineer (Worker Portals, Verification & Admin Back-Office)"
    )
    
    add_heading_1(doc, "1. Executive Summary & Project Motive")
    add_paragraph(doc, "A successful service platform requires reliable, verified professionals and strong administrative oversight. Informal markets suffer from fraud, unvetted service quality, and zero accountability. The primary motive of my role as Person 2 was to construct the operational backbone of E-Sheba: the service provider (worker) control center, the live availability and job lifecycle manager, profile modification suite, and the administrative moderation panel where administrators review credentials and protect consumers.")
    add_paragraph(doc, "I developed the interfaces and operational workflows that enable tradespeople to manage their daily assignments, toggle their live working status, monitor earnings, and allow system admins to inspect National ID (NID) cards before approving worker listings.")
    
    add_heading_1(doc, "2. Core Goals & Technical Objectives")
    add_bullet(doc, "Create a dedicated dashboard for service givers displaying key business metrics (total bookings, completed jobs, pending requests, total earnings, average rating).", "Worker Management Hub")
    add_bullet(doc, "Build an interactive booking lifecycle interface where workers can Accept, Decline, Start, and Complete customer bookings with one click.", "Job State Management")
    add_bullet(doc, "Implement a real-time availability toggle switch with GPS location sync, allowing providers to indicate when they are ready for jobs.", "Live Availability")
    add_bullet(doc, "Construct the comprehensive Worker Profile Editor enabling workers to update their hourly pricing, trade categories, working areas, and bio.", "Provider Self-Service")
    add_bullet(doc, "Engineer the Admin Back-Office Panel featuring an NID document verification modal, one-click worker approval/rejection, and platform-wide user management.", "Safety & Governance")
    
    add_heading_1(doc, "3. Complete Inventory of Code Files Created & Managed")
    add_paragraph(doc, "Below is the complete inventory of files designed, coded, and maintained under my subsystem:")
    
    headers = ["File Name", "Location", "Key Functions / Technical Scope"]
    data = [
        ["worker/dashboard.php", "worker/", "Worker central dashboard: live status cards, instant availability toggle, recent bookings table with dynamic Accept/Decline/Start/Complete buttons, location map, and incoming call dialog."],
        ["worker/profile.php", "worker/", "Profile management suite: update name, phone, bio, hourly rate, district, thana, multiple skill categories, and profile picture."],
        ["worker/earnings.php", "worker/", "Financial reports interface: total earnings summary, paid booking breakdown, earnings history table, and revenue analytics."],
        ["worker/reviews.php", "worker/", "Reputation management view: displays all customer feedback, 1-to-5 star ratings, customer names, and review dates."],
        ["worker/bookings.php", "worker/", "Detailed view of all bookings assigned to the worker, with status filters (Pending, Accepted, In Progress, Completed, Cancelled)."],
        ["admin/dashboard.php", "admin/", "System administrative command center: platform metrics (total users, active providers, pending approvals, total platform volume), pending verification queue, NID preview modal, and worker deactivation controls."],
        ["uploads/nid/", "uploads/nid/", "Secure document storage directory housing uploaded National ID images and identity files required for admin verification."],
        ["api/update-availability.php", "api/", "AJAX endpoint that receives worker availability toggle states (0 or 1) and GPS coordinates (latitude/longitude), saving them directly to the database."]
    ]
    add_styled_table(doc, headers, data, [Inches(2.0), Inches(1.2), Inches(3.3)])
    
    add_heading_1(doc, "4. Programming Languages & Concepts to Learn for Defense")
    add_paragraph(doc, "To confidently explain and showcase your work during academic defense or technical interviews, you must master the following fundamentals:")
    add_bullet(doc, "Enforcing Auth::requireRole('worker') and Auth::requireRole('admin') at the top of protected pages, redirecting unauthenticated or unauthorized users to 403.php.", "Role-Based Access Control (RBAC)")
    add_bullet(doc, "How a booking progresses through pending -> accepted -> in_progress -> completed (or cancelled), and how SQL UPDATE queries update both bookings.status and bookings.pay_status.", "Database State Transitions")
    add_bullet(doc, "Writing secure parameterized SQL statements (e.g. UPDATE workers SET is_approved=1 WHERE id=?) to prevent SQL injection during admin approvals and worker profile updates.", "PDO Prepared Statements")
    add_bullet(doc, "Using Bootstrap 5 Modal API (new bootstrap.Modal(el).show()) to display NID card images without page reloads, and handling AJAX button state changes (disabled, loading spinner).", "Dynamic Modals & UI States")
    add_bullet(doc, "Aggregating metrics using COUNT(*), SUM(total_amount), and AVG(rating) queries in MySQL to populate the dashboard analytics cards.", "SQL Aggregations & Analytics")
    
    add_heading_1(doc, "5. Step-by-Step Live Project Showcase Walkthrough")
    add_paragraph(doc, "Follow this exact sequence when demonstrating your contribution on screen:")
    add_bullet(doc, "Log in as Admin (admin@e-sheba.com / Admin@1234) and navigate to admin/dashboard.php. Show the platform metric cards (total users, registered workers, pending verifications).", "Step 1: Admin Command Center")
    add_bullet(doc, "Scroll to the 'Pending Verification Queue'. Click 'View NID' on any pending applicant. Demonstrate the modal opening with the worker's submitted National ID card and identity number.", "Step 2: NID Inspection Modal")
    add_bullet(doc, "Click 'Approve Worker'. Show the worker instantly moving from the pending queue into the 'Approved Service Providers' directory.", "Step 3: One-Click Worker Verification")
    add_bullet(doc, "Log out and log in as an approved Worker (e.g., Kabir Hossain or Ruins Night). Navigate to worker/dashboard.php.", "Step 4: Worker Dashboard Access")
    add_bullet(doc, "Locate an incoming customer booking in the 'Recent Bookings' table. Click 'Accept'. Watch the badge transition from 'Pending' to 'Accepted' and the button change to 'Start Job'.", "Step 5: Interactive Booking State Change")
    add_bullet(doc, "Toggle the 'Availability' switch. Show the badge dynamically change between 'You are AVAILABLE' and 'You are BUSY'.", "Step 6: Real-Time Availability Switch")
    
    add_heading_1(doc, "6. Oral Defense & Viva Q&A (Sample Questions & Answers)")
    add_bullet(doc, "Answer: In worker/dashboard.php, clicking 'Accept' calls updateBooking(id, 'accepted') which sends an AJAX POST request to api/update-booking.php. The API updates bookings.status in MySQL and dispatches a notification to the customer.", "Q1: What happens behind the scenes when a worker clicks 'Accept'?")
    add_bullet(doc, "Answer: During registration, the worker uploads their National ID scan to uploads/nid/. In admin/dashboard.php, clicking 'View NID' triggers a JavaScript function viewNid(name, number, img) that dynamically injects the file path into a Bootstrap modal image tag.", "Q2: How does the Admin NID verification modal work?")
    add_bullet(doc, "Answer: We enforce Auth::requireRole('admin') at the very first line of admin/dashboard.php. If a customer or worker tries to access this URL directly, the system immediately halts execution and redirects them to 403.php.", "Q3: How do you prevent ordinary customers from accessing the Admin panel?")
    add_bullet(doc, "Answer: When the worker clicks 'Complete' on a job where pay_method='cash', api/update-booking.php updates status='completed' AND simultaneously updates pay_status='paid'.", "Q4: How does cash payment get marked as paid?")
    
    add_heading_1(doc, "7. System Limitations & Future Improvements")
    add_bullet(doc, "Admin approval currently requires manual inspection; automated OCR/Government NID API integration could verify IDs instantly.", "Technical Limitation")
    add_bullet(doc, "Workers currently set a flat hourly rate rather than customizable task-based quotations.", "Pricing Limitation")
    add_bullet(doc, "Implement automated police clearance background verification integration.", "Future Scope 1")
    add_bullet(doc, "Provide a dedicated mobile app dashboard with audio alert notifications when a new job arrives.", "Future Scope 2")
    
    add_heading_1(doc, "8. Conclusion")
    add_paragraph(doc, "By engineering the Worker Operational Hub and Admin Back-Office Panel, my contribution guarantees that E-Sheba operates with maximum integrity, transparency, and safety. Workers possess full autonomy over their daily schedules and revenue, while administrators have the complete governance tools required to build consumer trust.")
    
    path = "C:/xampp/htdocs/e-sheba/team_work_docs/Person_2_Fatema_Khatun_Worker_and_Admin_Frontend.docx"
    doc.save(path)
    print("Created:", path)

def create_doc_person_3():
    doc = docx.Document()
    for s in doc.sections:
        s.top_margin = Inches(1); s.bottom_margin = Inches(1)
        s.left_margin = Inches(1); s.right_margin = Inches(1)
        
    add_header_banner(
        doc,
        title="PERSON 3: SERVER CORE, AUTHENTICATION & DATABASE ENGINE",
        subtitle="Individual Project Contribution & Technical Defense Handbook",
        member_name="Khadiza Binte Hasan",
        student_id="4260",
        role_title="Lead Backend Architect (Server Security, Authentication & Database Systems)"
    )
    
    add_heading_1(doc, "1. Executive Summary & Project Motive")
    add_paragraph(doc, "A multi-sided service platform that handles financial transactions, personal user identities, and technician credentials cannot function without a secure, robust, and scalable backend infrastructure. The primary motive of my role as Person 3 was to architect the relational database schema, design the centralized database connection wrapper, engineer the multi-role authentication system, enforce session security, and implement two-factor email OTP verification.")
    add_paragraph(doc, "I created the foundational database structure, registration onboarding pipeline, password hashing mechanisms, role-based guard middleware, and mail automation engine that power every single subsystem in E-Sheba.")
    
    add_heading_1(doc, "2. Core Goals & Technical Objectives")
    add_bullet(doc, "Design and normalize the MySQL relational database schema comprising 11 relational tables with foreign keys and cascade rules.", "Database Architecture")
    add_bullet(doc, "Build a high-performance Database wrapper class using the PDO Singleton pattern with prepared statements to eliminate SQL injection.", "Database Engine")
    add_bullet(doc, "Develop the Unified Authentication System (Customer, Worker, Admin) with secure password hashing using PHP's BCRYPT algorithm.", "Authentication Security")
    add_bullet(doc, "Create a 3-step registration pipeline featuring real-time duplicate email validation and 6-digit email OTP verification via PHPMailer and Gmail SMTP.", "Account Onboarding & OTP")
    add_bullet(doc, "Implement CSRF protection tokens and role-based session guards (Auth::requireLogin(), Auth::requireRole()) to prevent unauthorized access.", "Session & RBAC Security")
    
    add_heading_1(doc, "3. Complete Inventory of Code Files Created & Managed")
    add_paragraph(doc, "Below is the complete inventory of files designed, coded, and maintained under my subsystem:")
    
    headers = ["File Name", "Location", "Key Functions / Technical Scope"]
    data = [
        ["schema.sql", "Root", "Complete MySQL schema: defines 11 tables (users, workers, categories, worker_categories, subscriptions, bookings, reviews, messages, call_logs, notifications, otp_tokens) with constraints and indexes."],
        ["seeds.sql", "Root", "Seed database script: initializes default admin user, 2 customers, 6 verified workers across multiple districts, active subscriptions, reviews, and bookings."],
        ["includes/db.php", "includes/", "Database connection singleton: establishes UTF-8 PDO connection with error mode exceptions, prepared query helper, fetchOne, fetchAll, and lastInsertId."],
        ["includes/auth.php", "includes/", "Authentication engine: session management, password_verify(), multi-role guards, generateOTP(), storeOTP(), verifyOTP(), and csrfToken() generator."],
        ["includes/config.php", "includes/", "Global configuration: database credentials, base URLs, SMTP server settings, Pusher/Agora/SSLCommerz credentials, subscription pricing, and OTP timeouts."],
        ["includes/mailer.php", "includes/", "Automated email dispatcher: utilizes PHPMailer and Gmail SMTP with HTML templates to deliver verification OTPs and password reset codes."],
        ["login.php", "Root", "Unified authentication portal: role tabs for Customer, Worker, Admin; email format checking; password visibility toggle; and session initialization."],
        ["register.php", "Root", "Multi-stage registration controller: Step 1 (basic info), Step 2 (6-digit OTP verification), Step 3 (worker profile details, NID upload, trade selection)."],
        ["forgot-password.php", "Root", "Self-service credential recovery: sends 6-digit reset OTP to registered email, validates token, and updates password_hash."],
        ["api/check-email.php", "api/", "Real-time AJAX validation endpoint checking user table to alert users instantly if an email is already registered."],
        ["api/logout.php", "api/", "Session invalidation controller: destroys server-side session, clears cookies, and redirects to login."],
        ["403.php", "Root", "Security access denial page presented whenever unauthorized users attempt to bypass role guards."]
    ]
    add_styled_table(doc, headers, data, [Inches(1.8), Inches(1.2), Inches(3.5)])
    
    add_heading_1(doc, "4. Programming Languages & Concepts to Learn for Defense")
    add_paragraph(doc, "To confidently explain and showcase your work during academic defense or technical interviews, you must master the following fundamentals:")
    add_bullet(doc, "Singleton pattern (preventing duplicate DB connections), PDO::ATTR_EMULATE_PREPARES=false, PDO::ERRMODE_EXCEPTION, and why parameter binding prevents SQL Injection.", "PHP PDO & Database Design")
    add_bullet(doc, "password_hash($pass, PASSWORD_DEFAULT) using BCRYPT with adaptive salt, and password_verify() to authenticate without storing plaintext passwords.", "Password Cryptography")
    add_bullet(doc, "Generating cryptographically secure 6-digit numeric codes using random_int(0, 999999), storing hashed tokens in otp_tokens with a 10-minute expires_at timestamp.", "OTP Verification Logic")
    add_bullet(doc, "How session_start(), session_regenerate_id(true) prevent session fixation attacks, and how $_SESSION['user_role'] drives RBAC access checks.", "Session Management & Security")
    add_bullet(doc, "Primary Keys, Foreign Keys with ON DELETE CASCADE, Composite Keys (worker_categories), UNIQUE constraints, and indexes on frequently queried columns (email, role).", "Relational Database Normalization")
    add_bullet(doc, "SMTP handshake, TLS encryption over port 587, sending HTML emails, and securing app passwords through environment variables.", "SMTP & Email Delivery")
    
    add_heading_1(doc, "5. Step-by-Step Live Project Showcase Walkthrough")
    add_paragraph(doc, "Follow this exact sequence when demonstrating your contribution on screen:")
    add_bullet(doc, "Open phpMyAdmin or MySQL CLI. Display the esheba_db database and show the 11 normalized tables, foreign key relationships, and indexes.", "Step 1: Database Architecture Demonstration")
    add_bullet(doc, "Navigate to register.php. Select 'Customer'. Fill in Name, Email, Phone, and Password. Show the real-time check indicating 'Email is available!'.", "Step 2: Real-Time Email Validation")
    add_bullet(doc, "Click 'Continue to Verification'. Show Step 2 opening, requiring the 6-digit OTP. Explain that an email was triggered via mailer.php to the user's inbox.", "Step 3: OTP Generation & Dispatch")
    add_bullet(doc, "Enter the verification code (or developer master code '123456'). Show the user being verified and immediately redirected to their dashboard.", "Step 4: OTP Verification & Auto-Login")
    add_bullet(doc, "Log out. Try to manually navigate to http://localhost/e-sheba/customer/dashboard.php. Show how Auth::requireLogin() instantly intercepts the request and forces redirect to login.php.", "Step 5: Session Guard Interception")
    add_bullet(doc, "Log in as Customer. Attempt to manually browse to http://localhost/e-sheba/admin/dashboard.php. Show how Auth::requireRole('admin') blocks the request and redirects to 403.php.", "Step 6: Role-Based Access Control (RBAC) Test")
    
    add_heading_1(doc, "6. Oral Defense & Viva Q&A (Sample Questions & Answers)")
    add_bullet(doc, "Answer: We use PHP Data Objects (PDO) with prepared statements. SQL statements are compiled separately from the data parameters. User input is never concatenated directly into SQL strings, making SQL injection impossible.", "Q1: How does your database wrapper prevent SQL Injection?")
    add_bullet(doc, "Answer: Plaintext passwords are never saved. We use PHP's password_hash() which implements BCRYPT with a secure computational cost factor and unique automatic cryptographic salts.", "Q2: How are user passwords stored in the database?")
    add_bullet(doc, "Answer: When a user registers, we generate a 6-digit token using random_int() and store its hash in otp_tokens with an expiration time of NOW() + 10 minutes. When entered, we verify it using password_verify() and ensure expires_at > NOW().", "Q3: Explain the lifecycle of an OTP verification code.")
    add_bullet(doc, "Answer: When a user logs in, we call session_regenerate_id(true) to invalidate old session identifiers and prevent session hijacking, then store user_id and user_role in $_SESSION. Every page validates these values using Auth::requireRole().", "Q4: How do you handle session security and role management?")
    
    add_heading_1(doc, "7. System Limitations & Future Improvements")
    add_bullet(doc, "Email OTP delivery depends on Google SMTP network latency; SMS gateway integration (e.g., SSL Wireless) is recommended for immediate cellular OTP delivery.", "Delivery Limitation")
    add_bullet(doc, "Master demo OTP ('123456') was included for local evaluation; must be stripped out before production deployment.", "Development Feature")
    add_bullet(doc, "Implement OAuth 2.0 social sign-in (Google and Facebook login buttons).", "Future Scope 1")
    add_bullet(doc, "Add JSON Web Token (JWT) support to allow native mobile apps to connect to the backend APIs.", "Future Scope 2")
    
    add_heading_1(doc, "8. Conclusion")
    add_paragraph(doc, "By designing the relational schema, connection architecture, and multi-tier authentication engine, my contribution establishes the technical bedrock of E-Sheba. The system enforces stringent security principles, guarantees data integrity across 11 relational tables, and ensures seamless onboarding for customers, workers, and system administrators.")
    
    path = "C:/xampp/htdocs/e-sheba/team_work_docs/Person_3_Khadiza_Binte_Hasan_Server_Auth_Database.docx"
    doc.save(path)
    print("Created:", path)

def create_doc_person_4():
    doc = docx.Document()
    for s in doc.sections:
        s.top_margin = Inches(1); s.bottom_margin = Inches(1)
        s.left_margin = Inches(1); s.right_margin = Inches(1)
        
    add_header_banner(
        doc,
        title="PERSON 4: PAYMENT SYSTEMS, SUBSCRIPTIONS & FINANCIAL ENGINE",
        subtitle="Individual Project Contribution & Technical Defense Handbook",
        member_name="Jannatul Bushra",
        student_id="4286",
        role_title="Lead FinTech & Subscriptions Engineer (SSLCommerz Gateway & Monetization)"
    )
    
    add_heading_1(doc, "1. Executive Summary & Project Motive")
    add_paragraph(doc, "For any marketplace platform to be sustainable and commercially viable, it must incorporate a monetization model, automated billing, and secure digital payment processing. The primary motive of my role as Person 4 was to engineer E-Sheba's revenue engine: integrating the SSLCommerz payment gateway (supporting bKash, Nagad, Visa, Mastercard), building the recurring worker subscription model (BDT 399/month), constructing automated subscription expiration cron jobs, and creating the printable customer invoice generator.")
    add_paragraph(doc, "My work allows customers to pay for bookings online and enables service workers to purchase monthly subscriptions that keep their profiles visible to hiring customers across Bangladesh.")
    
    add_heading_1(doc, "2. Core Goals & Technical Objectives")
    add_bullet(doc, "Integrate Bangladesh's leading payment gateway, SSLCommerz, supporting mobile banking (bKash, Nagad) and debit/credit cards.", "Payment Gateway Integration")
    add_bullet(doc, "Build an interactive Sandbox Payment Simulator allowing complete offline and academic demonstrations without real money deduction.", "Sandbox Test Environment")
    add_bullet(doc, "Design and implement the BDT 399/month Worker Subscription model where only subscribed workers appear in customer search results.", "Monetization Architecture")
    add_bullet(doc, "Create an automated server Cron Job script that runs daily to detect expired subscriptions, hide un-renewed workers, and dispatch warning notices.", "Automated Subscription Expiry")
    add_bullet(doc, "Implement the printable HTML/CSS Invoice engine allowing customers to generate and download professional billing receipts.", "Invoicing & Financial Records")
    
    add_heading_1(doc, "3. Complete Inventory of Code Files Created & Managed")
    add_paragraph(doc, "Below is the complete inventory of files designed, coded, and maintained under my subsystem:")
    
    headers = ["File Name", "Location", "Key Functions / Technical Scope"]
    data = [
        ["sslcommerz/init.php", "sslcommerz/", "Payment gateway initialization and sandbox simulator: formats transaction payload, stores temporary session, and presents interactive bKash/Nagad/Card checkout."],
        ["sslcommerz/success.php", "sslcommerz/", "Payment success callback: validates transaction parameters, updates bookings.pay_status to 'paid', activates subscriptions, records dates, and sends confirmation notifications."],
        ["sslcommerz/fail.php", "sslcommerz/", "Payment failure callback: handles declined transactions, marks records as failed, and provides user retry links."],
        ["sslcommerz/cancel.php", "sslcommerz/", "Payment cancellation callback: gracefully aborts checkout and redirects users back to their dashboard."],
        ["sslcommerz/ipn.php", "sslcommerz/", "Instant Payment Notification (IPN) webhook listener: executes server-to-server transaction validation even if user closes the browser before redirect."],
        ["subscription.php", "Root", "Public and worker subscription landing page: details BDT 399/month benefits, displays active days remaining countdown, and provides renewal CTA."],
        ["worker/subscription.php", "worker/", "Worker panel subscription manager: presents current plan status (Active/Expired), next billing date, and past payment transaction records."],
        ["cron/expire_subscriptions.php", "cron/", "Automated daily scheduled cron script: queries subscriptions where expires_at < CURDATE(), sets status='expired', updates workers.is_visible=0, and creates in-app notification."],
        ["invoice.php", "Root", "Printable booking billing invoice: calculates subtotal, platform fees, taxes, shows customer and worker details, and triggers window.print()."]
    ]
    add_styled_table(doc, headers, data, [Inches(2.2), Inches(1.2), Inches(3.1)])
    
    add_heading_1(doc, "4. Programming Languages & Concepts to Learn for Defense")
    add_paragraph(doc, "To confidently explain and showcase your work during academic defense or technical interviews, you must master the following fundamentals:")
    add_bullet(doc, "Generating unique merchant transaction IDs (e.g. SUB_workerId_timestamp), passing success_url, fail_url, cancel_url, ipn_url, and communicating via HTTPS POST requests.", "SSLCommerz Gateway API Lifecycle")
    add_bullet(doc, "Why client redirects cannot be 100% trusted (user can close tab after paying), and how server-to-server IPN webhooks guarantee payments are verified independently.", "Instant Payment Notifications (IPN)")
    add_bullet(doc, "Using date('Y-m-d', strtotime('+30 days')) to calculate monthly expiration dates, and writing date comparison queries (expires_at >= CURDATE()).", "Subscription Date Arithmetic")
    add_bullet(doc, "Executing PHP scripts in headless CLI mode via Windows Task Scheduler or Linux crontab (e.g. 0 0 * * * php /path/to/expire_subscriptions.php).", "CLI Cron Job Automation")
    add_bullet(doc, "Using CSS @media print { .no-print { display: none; } } to create clean, paper-formatted invoices that hide web headers and footers.", "Printable Web Layouts")
    
    add_heading_1(doc, "5. Step-by-Step Live Project Showcase Walkthrough")
    add_paragraph(doc, "Follow this exact sequence when demonstrating your contribution on screen:")
    add_bullet(doc, "Log in as a Worker. Navigate to worker/subscription.php. Show the active plan banner, the BDT 399 rate, and click 'Renew / Upgrade Subscription'.", "Step 1: Subscription Initiation")
    add_bullet(doc, "Explain how sslcommerz/init.php generates a unique transaction ID (e.g. SUB_2_1795182537) and opens the test payment gateway.", "Step 2: Payment Gateway Launch")
    add_bullet(doc, "Select 'bKash' as the channel and click 'Pay BDT 399.00 (Success)'. Explain how the simulator models real SSLCommerz responses.", "Step 3: Simulating Digital Payment")
    add_bullet(doc, "Show the redirection to sslcommerz/success.php. Point out how the database updates the subscriptions table, sets starts_at to today, and expires_at to +30 days.", "Step 4: Subscription Activation")
    add_bullet(doc, "Open a terminal and run php cron/expire_subscriptions.php. Show the script scanning the database, checking dates, and logging the status output.", "Step 5: Automated Expiry Cron Execution")
    add_bullet(doc, "Navigate to invoice.php?booking_id=1. Demonstrate the clean, printable invoice layout complete with transaction ID, customer name, technician name, and amount.", "Step 6: Printable Billing Invoice")
    
    add_heading_1(doc, "6. Oral Defense & Viva Q&A (Sample Questions & Answers)")
    add_bullet(doc, "Answer: Service givers pay a recurring fee of BDT 399 per month to remain visible in customer search results (workers.is_visible=1). Customers pay the worker's hourly rate directly upon job completion.", "Q1: What is E-Sheba's business and monetization model?")
    add_bullet(doc, "Answer: We initiate the payment by sending amount, transaction ID, customer details, and callback URLs to the gateway. The user completes payment on SSLCommerz, which redirects back to success.php or triggers our ipn.php webhook with verification data.", "Q2: How does the SSLCommerz payment flow operate?")
    add_bullet(doc, "Answer: In cron/expire_subscriptions.php, we query all active subscriptions where expires_at < CURDATE(). For every expired record, we update subscriptions.status='expired' and set workers.is_visible=0 so they no longer appear in search.", "Q3: How does the system automatically hide workers whose subscriptions expire?")
    add_bullet(doc, "Answer: An IPN (Instant Payment Notification) is a direct server-to-server HTTP POST request sent by SSLCommerz to our backend. Even if a user's phone runs out of battery right after payment, the IPN ensures our database still updates.", "Q4: What is the purpose of the IPN webhook?")
    
    add_heading_1(doc, "7. System Limitations & Future Improvements")
    add_bullet(doc, "Currently uses SSLCommerz Sandbox credentials; requires live Store ID and Store Password for production bank transfers.", "Environment Limitation")
    add_bullet(doc, "Subscription renewals currently require manual authorization rather than auto-debiting bank accounts.", "Billing Limitation")
    add_bullet(doc, "Implement automated recurring bKash tokenized auto-debit agreements.", "Future Scope 1")
    add_bullet(doc, "Add multi-currency support and escrow payment holding until customer signs off on work quality.", "Future Scope 2")
    
    add_heading_1(doc, "8. Conclusion")
    add_paragraph(doc, "By implementing the SSLCommerz payment infrastructure, automated recurring worker subscriptions, and financial invoicing suite, my contribution provides E-Sheba with a sustainable commercial engine. The system automates provider monetization, enforces fair business rules, and guarantees verifiable financial auditing across the marketplace.")
    
    path = "C:/xampp/htdocs/e-sheba/team_work_docs/Person_4_Jannatul_Bushra_Payments_and_Subscriptions.docx"
    doc.save(path)
    print("Created:", path)

def create_doc_person_5():
    doc = docx.Document()
    for s in doc.sections:
        s.top_margin = Inches(1); s.bottom_margin = Inches(1)
        s.left_margin = Inches(1); s.right_margin = Inches(1)
        
    add_header_banner(
        doc,
        title="PERSON 5: REAL-TIME MESSAGING, MASKED CALLING & PWA",
        subtitle="Individual Project Contribution & Technical Defense Handbook",
        member_name="Manir Rahman",
        student_id="4278",
        role_title="Lead Real-Time Systems & Mobile Engineer (WebSockets, WebRTC & PWA)"
    )
    
    add_heading_1(doc, "1. Executive Summary & Project Motive")
    add_paragraph(doc, "Modern consumers expect instant communication, privacy protection, and mobile app accessibility. In traditional hiring, sharing personal cellular phone numbers leads to harassment, off-platform price poaching, and privacy violations. The primary motive of my role as Person 5 was to implement E-Sheba's advanced real-time communication stack: instant WebSocket messaging powered by Pusher, privacy-preserving in-app voice calling powered by the Agora RTC Web SDK, and mobile Progressive Web App (PWA) architecture.")
    add_paragraph(doc, "My subsystems allow customers and technicians to chat live with typing indicators, place secure voice calls directly through their web browsers without sharing phone numbers, and install E-Sheba as a native-like mobile app.")
    
    add_heading_1(doc, "2. Core Goals & Technical Objectives")
    add_bullet(doc, "Build a real-time instant chat engine using Pusher WebSockets featuring live message delivery, conversation threads, and typing indicators.", "WebSocket Chat Engine")
    add_bullet(doc, "Implement privacy-masked audio voice calling utilizing the Agora WebRTC SDK, allowing users to call technicians directly inside the browser.", "In-App Voice Calling")
    add_bullet(doc, "Develop server-side token generation and signaling endpoints (agora-token.php, call-signal.php) to authorize and route call invitations.", "Call Signaling & Tokens")
    add_bullet(doc, "Create call logging infrastructure that records call start times, end times, duration, and status (answered/missed/declined) in call_logs.", "Telephony Records")
    add_bullet(doc, "Transform E-Sheba into a Progressive Web App (PWA) using manifest.json and a Service Worker (sw.js) for mobile home-screen installation and offline caching.", "Progressive Web App")
    
    add_heading_1(doc, "3. Complete Inventory of Code Files Created & Managed")
    add_paragraph(doc, "Below is the complete inventory of files designed, coded, and maintained under my subsystem:")
    
    headers = ["File Name", "Location", "Key Functions / Technical Scope"]
    data = [
        ["chat.php", "Root", "Real-time chat center: two-pane responsive messaging layout, thread preview sidebar, unread message badges, chat bubble history, and active peer details."],
        ["js/chat.js", "js/", "Client-side WebSocket script: connects to Pusher channels, subscribes to private-chat-*, handles real-time message DOM rendering, typing debounce, and read receipts."],
        ["api/send-message.php", "api/", "Chat message dispatch API: saves message in messages table and broadcasts 'new-message' event over Pusher private channel."],
        ["api/send-typing.php", "api/", "Typing broadcast API: triggers ephemeral 'typing' WebSocket events to show 'typing...' indicator on peer screens."],
        ["api/mark-read.php", "api/", "Read receipt endpoint: marks messages as read (is_read=1) when recipient opens conversation."],
        ["api/pusher-auth.php", "api/", "Pusher channel authentication: validates session before permitting subscriptions to secure private WebSocket channels."],
        ["api/unread-counts.php", "api/", "Polling/WebSocket helper returning current unread message and notification counts for the navbar badge."],
        ["js/call.js", "js/", "Client-side WebRTC voice calling script: initializes Agora RTC client, manages audio tracks, renders call overlay UI, controls mic mute, and tracks call timer."],
        ["api/agora-token.php", "api/", "Agora token generator: generates RTC access tokens and unique channel names for secure peer-to-peer audio calling."],
        ["api/call-signal.php", "api/", "Call signaling controller: broadcasts call invitations (call-incoming, call-answered, call-declined, call-ended) over Pusher."],
        ["api/log-call.php", "api/", "Call logging API: records call history, duration in seconds, and status in MySQL call_logs table."],
        ["manifest.json", "Root", "PWA Web App Manifest: defines application name, icons (192x192, 512x512), theme color (#1A73E8), and standalone display mode."],
        ["sw.js", "Root", "Service Worker: intercepts network requests, manages offline cache, and enables 'Add to Home Screen' installation."]
    ]
    add_styled_table(doc, headers, data, [Inches(1.8), Inches(1.2), Inches(3.5)])
    
    add_heading_1(doc, "4. Programming Languages & Concepts to Learn for Defense")
    add_paragraph(doc, "To confidently explain and showcase your work during academic defense or technical interviews, you must master the following fundamentals:")
    add_bullet(doc, "Full-duplex WebSocket connections vs HTTP polling, private channel authorization via api/pusher-auth.php, and binding custom event listeners (new-message, typing).", "WebSockets & Pusher Channels")
    add_bullet(doc, "WebRTC peer-to-peer audio streaming, Agora RTC client initialization, microphone track capturing (createMicrophoneAudioTrack), audio playing, and mute/unmute toggling.", "WebRTC & Agora Audio Calling")
    add_bullet(doc, "Generating sorted unique channel identifiers (e.g. esheba-call-minId-maxId) so two participants consistently meet in the same secure audio room.", "Channel Pairing Logic")
    add_bullet(doc, "Service Worker lifecycle (install, activate, fetch), Cache Storage API, and Web App Manifest properties required for browser PWA installability.", "Progressive Web App (PWA)")
    add_bullet(doc, "Using setTimeout/clearTimeout debounce timers to avoid flooding WebSocket servers while a user is typing a message.", "JavaScript Debounce Optimization")
    
    add_heading_1(doc, "5. Step-by-Step Live Project Showcase Walkthrough")
    add_paragraph(doc, "Follow this exact sequence when demonstrating your contribution on screen:")
    add_bullet(doc, "Open two browser windows side-by-side: Window A logged in as Customer (Shahnewaz Khan), Window B logged in as Worker (Ruins Night).", "Step 1: Two-Window Real-Time Setup")
    add_bullet(doc, "In Window A, navigate to chat.php?to=10. In Window B, navigate to chat.php?to=11. Show both users connected to the same private chat channel.", "Step 2: Conversation Thread Launch")
    add_bullet(doc, "In Window A, start typing in the message box. Show Window B immediately displaying the 'typing...' animation without any page reload.", "Step 3: Real-Time Typing Indicator")
    add_bullet(doc, "Send the message 'Hello, can you visit at 4 PM?'. Show the message instantly appearing as a blue bubble in Window A and a white bubble in Window B via Pusher.", "Step 4: WebSocket Instant Messaging")
    add_bullet(doc, "In Window A, click the 'Call' button. Show Window B immediately displaying the incoming call modal with the ringing avatar and 'Shahnewaz Khan is calling'.", "Step 5: Privacy-Masked Audio Call Signaling")
    add_bullet(doc, "Click 'Accept' on Window B. Show the call timer starting, microphone mute button toggling, and the Agora WebRTC audio stream establishing.", "Step 6: Agora WebRTC Voice Call")
    add_bullet(doc, "Open Chrome DevTools -> Application tab. Show manifest.json loaded with valid icons and sw.js running as an active Service Worker.", "Step 7: PWA Inspection & Installation")
    
    add_heading_1(doc, "6. Oral Defense & Viva Q&A (Sample Questions & Answers)")
    add_bullet(doc, "Answer: HTTP is request-response where the client must repeatedly ask the server for updates (polling). WebSockets maintain an open, bidirectional connection where the server pushes new messages to clients instantaneously.", "Q1: What is the difference between standard HTTP polling and WebSockets?")
    add_bullet(doc, "Answer: To prevent unauthorized eavesdropping, we use private channels named private-chat-minId-maxId. When a user connects, Pusher asks api/pusher-auth.php to verify the session before granting channel access.", "Q2: How do you secure private chat conversations?")
    add_bullet(doc, "Answer: Neither party's real phone number is ever revealed. When a call is placed, api/agora-token.php generates a temporary virtual audio channel. Voice is transmitted peer-to-peer over WebRTC, keeping numbers completely private.", "Q3: How does privacy-masked calling protect users?")
    add_bullet(doc, "Answer: A Progressive Web App uses manifest.json and a Service Worker (sw.js). It allows users to click 'Add to Home Screen' on Android or iPhone to install the website as a native-feeling standalone app.", "Q4: What is a Progressive Web App (PWA) and why did you implement it?")
    
    add_heading_1(doc, "7. System Limitations & Future Improvements")
    add_bullet(doc, "Pusher free-tier limit allows up to 100 simultaneous connections; self-hosted WebSockets (e.g. Socket.io or Laravel WebSockets) can support unlimited scale.", "WebSocket Capacity")
    add_bullet(doc, "Microphone access requires HTTPS or localhost; cannot place calls over insecure plain HTTP connections.", "Security Constraint")
    add_bullet(doc, "Expand voice calling to support 1-on-1 video calling for remote appliance troubleshooting.", "Future Scope 1")
    add_bullet(doc, "Add rich media sharing (voice voice-notes and video previews) to the chat engine.", "Future Scope 2")
    
    add_heading_1(doc, "8. Conclusion")
    add_paragraph(doc, "By implementing real-time WebSocket messaging, privacy-preserving Agora WebRTC voice calling, and a mobile Progressive Web App, my contribution elevates E-Sheba from a static web directory into a dynamic, modern, and privacy-respecting communication platform. Users communicate safely and seamlessly across all desktop and mobile devices.")
    
    path = "C:/xampp/htdocs/e-sheba/team_work_docs/Person_5_Manir_Rahman_Realtime_Chat_Calling_PWA.docx"
    doc.save(path)
    print("Created:", path)

if __name__ == "__main__":
    create_doc_person_1()
    create_doc_person_2()
    create_doc_person_3()
    create_doc_person_4()
    create_doc_person_5()
    print("ALL 5 DOCUMENTS GENERATED SUCCESSFULLY!")
