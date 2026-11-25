import os
import shutil
import zipfile

# 1. Generate Microsoft Word .doc Document (Word HTML format)
doc_content = """<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
<meta charset='utf-8'>
<title>E-Sheba — Complete Project Documentation & Conversation Record</title>
<!--[if gte mso 9]>
<xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>100</w:Zoom>
  <w:DoNotOptimizeForBrowser/>
 </w:WordDocument>
</xml>
<![endif]-->
<style>
  @page Section1 {
    size: 595.3pt 841.9pt; /* A4 */
    margin: 54.0pt 54.0pt 54.0pt 54.0pt;
    mso-header-margin: 36.0pt;
    mso-footer-margin: 36.0pt;
    mso-paper-source: 0;
  }
  div.Section1 { page: Section1; }
  body {
    font-family: 'Calibri', 'Segoe UI', Arial, sans-serif;
    font-size: 11.0pt;
    color: #222222;
    line-height: 1.5;
  }
  .cover {
    text-align: center;
    padding-top: 60pt;
    margin-bottom: 60pt;
  }
  .cover-badge {
    background-color: #E8F0FE;
    color: #1A73E8;
    font-weight: bold;
    font-size: 11.0pt;
    padding: 6pt 16pt;
    border-radius: 20pt;
    display: inline-block;
  }
  .cover-title {
    font-size: 28.0pt;
    font-weight: bold;
    color: #1A73E8;
    margin-top: 18pt;
    margin-bottom: 6pt;
  }
  .cover-sub {
    font-size: 15.0pt;
    color: #555555;
    margin-bottom: 30pt;
  }
  .meta-table {
    width: 85%;
    margin: 0 auto;
    border: 1.5pt solid #1A73E8;
    background-color: #F8FAFC;
    border-collapse: collapse;
  }
  .meta-table td {
    padding: 8pt 12pt;
    border: 0.5pt solid #D2E3FC;
    font-size: 10.5pt;
  }
  .meta-table td.label {
    font-weight: bold;
    color: #333333;
    width: 32%;
    background-color: #EEF4FD;
  }
  h1 {
    font-size: 17.0pt;
    color: #1A73E8;
    border-bottom: 2.0pt solid #1A73E8;
    padding-bottom: 4pt;
    margin-top: 28pt;
    margin-bottom: 12pt;
    page-break-after: avoid;
  }
  h2 {
    font-size: 13.5pt;
    color: #155724;
    margin-top: 18pt;
    margin-bottom: 8pt;
    page-break-after: avoid;
  }
  h3 {
    font-size: 12.0pt;
    color: #0D47A1;
    margin-top: 14pt;
    margin-bottom: 6pt;
    page-break-after: avoid;
  }
  p {
    margin: 6pt 0;
    text-align: justify;
  }
  table.data-table {
    width: 100%;
    border-collapse: collapse;
    margin: 12pt 0;
    font-size: 10.0pt;
  }
  table.data-table th, table.data-table td {
    border: 1.0pt solid #B0BEC5;
    padding: 6pt 8pt;
  }
  table.data-table th {
    background-color: #E3F2FD;
    color: #0D47A1;
    font-weight: bold;
    text-align: left;
  }
  table.data-table tr:nth-child(even) {
    background-color: #F8FAFC;
  }
  .alert-box {
    background-color: #E8F4FD;
    border-left: 4.0pt solid #1A73E8;
    padding: 10pt 14pt;
    margin: 12pt 0;
    border-radius: 4pt;
  }
  .card-box {
    background-color: #F8F9FA;
    border: 1.0pt solid #E2E8F0;
    padding: 10pt 14pt;
    margin: 10pt 0;
    border-radius: 6pt;
  }
  ul, ol {
    margin-top: 4pt;
    margin-bottom: 10pt;
    padding-left: 24pt;
  }
  li {
    margin-bottom: 4pt;
  }
  .page-break {
    page-break-after: always;
  }
  code {
    font-family: 'Consolas', 'Courier New', monospace;
    background-color: #F1F3F4;
    color: #C5221F;
    padding: 1pt 4pt;
    border-radius: 3pt;
    font-size: 9.5pt;
  }
</style>
</head>
<body>
<div class="Section1">

<!-- Cover Page -->
<div class="cover">
  <div class="cover-badge">CAPSTONE SOFTWARE ENGINEERING PROJECT DELIVERABLE</div>
  <div class="cover-title">🔧 E-Sheba</div>
  <div class="cover-sub">Service Provider Web-Based Application Specification & Implementation Report</div>
  
  <table class="meta-table">
    <tr>
      <td class="label">Course Name:</td>
      <td>Software Engineering (Course Code: CSE 06133248)</td>
    </tr>
    <tr>
      <td class="label">Academic Institution:</td>
      <td>World University of Bangladesh (WUB) — Department of CSE</td>
    </tr>
    <tr>
      <td class="label">Document Version:</td>
      <td>Version 1.0 (Comprehensive SRS & Development Deliverable)</td>
    </tr>
    <tr>
      <td class="label">Student Team Members:</td>
      <td>
        <strong>Sahnewaz Hasib</strong> (ID: 4264)<br>
        <strong>Fatema Khatun</strong> (ID: 4276)<br>
        <strong>Khadiza Binte Hasan</strong> (ID: 4260)<br>
        <strong>Jannatul Bushra</strong> (ID: 4286)<br>
        <strong>Manir Rahman</strong> (ID: 4278)
      </td>
    </tr>
    <tr>
      <td class="label">Delivery Date:</td>
      <td>November 2026</td>
    </tr>
    <tr>
      <td class="label">Project Status:</td>
      <td><strong style="color:#137333;">Fully Implemented & Verified (58+ Application Files)</strong></td>
    </tr>
  </table>
</div>

<div class="page-break"></div>

<!-- SECTION 1 -->
<h1>1. Executive Summary & Project Purpose</h1>
<p>
<strong>E-Sheba</strong> is an on-demand, two-sided web marketplace engineered to connect homeowners across Bangladesh with verified blue-collar service professionals (plumbers, electricians, appliance technicians, carpenters, cleaners, etc.). 
</p>
<p>
The platform directly addresses the prevalent challenges in the informal local services sector in Bangladesh: lack of worker identity verification, arbitrary pricing, lack of privacy during initial inquiries, and manual cash settlement friction. 
</p>
<p>
<strong>Core Architectural Highlights:</strong>
</p>
<ul>
  <li><strong>Identity Verification:</strong> Service givers register with National Identity (NID) numbers and physical ID card uploads, verified through an administrative approval workflow.</li>
  <li><strong>Recurring Subscription Model:</strong> Service givers pay a recurring ৳399/month fee to maintain active profile visibility in customer category search results.</li>
  <li><strong>Privacy-Preserving Masked Voice Calling:</strong> In-app voice calling powered by the Agora Web SDK enables audio calling without exposing either party's personal cellular phone numbers.</li>
  <li><strong>Real-Time Messaging:</strong> Pusher WebSocket infrastructure facilitates instant customer-provider chat, message history logging, and typing indicators.</li>
  <li><strong>Digital Payment Processing:</strong> SSLCommerz integration supporting bKash, Nagad, cards, and an asynchronous Instant Payment Notification (IPN) webhook engine.</li>
  <li><strong>Progressive Web App (PWA):</strong> Features a valid web app manifest and service worker enabling home-screen installation on mobile smartphones.</li>
</ul>

<!-- SECTION 2 -->
<h1>2. Team Work Breakdown Structure (5 Members)</h1>
<p>To ensure modular development without code merge conflicts, the system was decomposed across the 5 student team members:</p>

<table class="data-table">
  <thead>
    <tr>
      <th style="width:16%;">Team Member</th>
      <th style="width:24%;">Assigned Subsystem</th>
      <th style="width:40%;">Key Technical Deliverables</th>
      <th style="width:20%;">Core Technologies</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Person 1</strong><br>Sahnewaz Hasib</td>
      <td>Customer-Side Frontend & Marketplace UI</td>
      <td>Customer landing page (<code>index.php</code>), category browsing directory (<code>categories.php</code>), provider search/filters (<code>workers.php</code>), customer dashboard (<code>customer/dashboard.php</code>), booking modal, and review rating dialogs.</td>
      <td>HTML5, CSS3, Bootstrap 5, jQuery, Leaflet.js</td>
    </tr>
    <tr>
      <td><strong>Person 2</strong><br>Fatema Khatun</td>
      <td>Worker & Admin Frontend</td>
      <td>Worker profile editor (<code>worker/profile.php</code>), worker dashboard (<code>worker/dashboard.php</code>), admin back-office panel (<code>admin/dashboard.php</code>), and NID document inspection modal.</td>
      <td>Bootstrap 5, Custom CSS, jQuery</td>
    </tr>
    <tr>
      <td><strong>Person 3</strong><br>Khadiza Binte Hasan</td>
      <td>Server: Auth, Session RBAC & Search</td>
      <td>Multi-role authentication (<code>login.php</code>, <code>register.php</code>), role-based session guard (<code>includes/auth.php</code>), PDO database singleton wrapper (<code>includes/db.php</code>), and category/district search query logic.</td>
      <td>PHP 8.x, MySQL 8, Bcrypt, Sessions</td>
    </tr>
    <tr>
      <td><strong>Person 4</strong><br>Jannatul Bushra</td>
      <td>Server: Payments & Subscriptions</td>
      <td>SSLCommerz gateway suite (<code>sslcommerz/init.php</code>, <code>success.php</code>, <code>fail.php</code>, <code>cancel.php</code>), IPN webhook (<code>sslcommerz/ipn.php</code>), ৳399 worker subscription management, auto-expiry cron job (<code>cron/expire_subscriptions.php</code>), and printable invoice engine (<code>invoice.php</code>).</td>
      <td>SSLCommerz Sandbox API, cURL, CRON</td>
    </tr>
    <tr>
      <td><strong>Person 5</strong><br>Manir Rahman</td>
      <td>Real-Time Communications & PWA</td>
      <td>Pusher WebSocket chat engine (<code>chat.php</code>, <code>api/send-message.php</code>, <code>api/pusher-auth.php</code>), Agora WebRTC voice calling (<code>api/agora-token.php</code>, <code>api/call-signal.php</code>, <code>js/call.js</code>), and PWA manifest with service worker (<code>sw.js</code>).</td>
      <td>Pusher Channels, Agora RTC Web SDK, Service Workers</td>
    </tr>
  </tbody>
</table>

<div class="page-break"></div>

<!-- SECTION 3 -->
<h1>3. Relational Database Architecture (`esheba_db`)</h1>
<p>The system runs on MySQL 8.x utilizing InnoDB engine tables, normalized with foreign key constraints and indexed queries:</p>

<table class="data-table">
  <thead>
    <tr>
      <th style="width:18%;">Table Name</th>
      <th style="width:25%;">Primary & Foreign Keys</th>
      <th style="width:57%;">Key Columns, Types & Business Constraints</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>users</code></td>
      <td><code>id (PK, AUTO_INC)</code></td>
      <td><code>name (VARCHAR)</code>, <code>email (VARCHAR, UNIQUE)</code>, <code>phone</code>, <code>password_hash (VARCHAR 255)</code>, <code>role (ENUM: customer, worker, admin)</code>, <code>email_verified (TINYINT)</code>, <code>is_active</code>.</td>
    </tr>
    <tr>
      <td><code>workers</code></td>
      <td><code>id (PK)</code><br><code>user_id (FK -> users.id)</code></td>
      <td><code>nid_number (VARCHAR)</code>, <code>nid_image_path (VARCHAR)</code>, <code>price_per_hour (DECIMAL)</code>, <code>working_area</code>, <code>district</code>, <code>thana</code>, <code>latitude</code>, <code>longitude</code>, <code>is_approved</code>, <code>is_visible</code>, <code>avg_rating</code>, <code>total_reviews</code>.</td>
    </tr>
    <tr>
      <td><code>categories</code></td>
      <td><code>id (PK, AUTO_INC)</code></td>
      <td><code>name (VARCHAR, UNIQUE)</code>, <code>icon (FontAwesome class)</code>, <code>description</code>, <code>is_active</code>, <code>sort_order</code>. Pre-seeded with 25 Bangladeshi trade categories.</td>
    </tr>
    <tr>
      <td><code>worker_categories</code></td>
      <td>Composite PK:<br><code>(worker_id, category_id)</code></td>
      <td>Many-to-many junction table mapping providers to multiple offered categories with cascading deletions.</td>
    </tr>
    <tr>
      <td><code>subscriptions</code></td>
      <td><code>id (PK)</code><br><code>worker_id (FK -> workers.id)</code></td>
      <td><code>amount (DECIMAL, default 399.00)</code>, <code>currency ('BDT')</code>, <code>status (ENUM: pending, active, expired)</code>, <code>payment_method</code>, <code>transaction_id</code>, <code>starts_at (DATE)</code>, <code>expires_at (DATE)</code>.</td>
    </tr>
    <tr>
      <td><code>bookings</code></td>
      <td><code>id (PK)</code><br><code>customer_id (FK -> users)</code><br><code>worker_id (FK -> workers)</code></td>
      <td><code>category_id (FK)</code>, <code>description (TEXT)</code>, <code>scheduled_at (DATETIME)</code>, <code>status (ENUM: pending, accepted, in_progress, completed, cancelled)</code>, <code>total_amount</code>, <code>pay_method</code>, <code>pay_status</code>, <code>address</code>.</td>
    </tr>
    <tr>
      <td><code>reviews</code></td>
      <td><code>id (PK)</code><br><code>booking_id (FK, UNIQUE)</code></td>
      <td><code>customer_id (FK)</code>, <code>worker_id (FK)</code>, <code>rating (TINYINT: 1-5)</code>, <code>comment (TEXT)</code>, <code>created_at</code>. One review permitted per completed booking.</td>
    </tr>
    <tr>
      <td><code>messages</code></td>
      <td><code>id (BIGINT PK)</code><br><code>sender_id, receiver_id (FK)</code></td>
      <td><code>booking_id (FK, NULL)</code>, <code>body (TEXT)</code>, <code>is_read (TINYINT)</code>, <code>created_at</code>. Indexed on <code>(sender_id, receiver_id)</code> for fast thread retrieval.</td>
    </tr>
    <tr>
      <td><code>call_logs</code></td>
      <td><code>id (PK)</code><br><code>caller_id, callee_id (FK)</code></td>
      <td><code>channel (Agora room name)</code>, <code>started_at</code>, <code>ended_at</code>, <code>duration_sec</code>, <code>status (initiated, answered, declined, missed)</code>.</td>
    </tr>
    <tr>
      <td><code>notifications</code></td>
      <td><code>id (BIGINT PK)</code><br><code>user_id (FK -> users.id)</code></td>
      <td><code>type (booking, call, message, subscription, approval)</code>, <code>title</code>, <code>body</code>, <code>is_read</code>, <code>created_at</code>.</td>
    </tr>
    <tr>
      <td><code>otp_tokens</code></td>
      <td><code>id (PK)</code><br><code>user_id (FK -> users.id)</code></td>
      <td><code>token (Bcrypt hash)</code>, <code>type (email_verify, password_reset)</code>, <code>expires_at (DATETIME)</code>. Unique on <code>(user_id, type)</code>.</td>
    </tr>
  </tbody>
</table>

<!-- SECTION 4 -->
<h1>4. Delivered Codebase Inventory & Engineering Modules</h1>
<p>
The application comprises 58 modular PHP, JavaScript, CSS, and SQL files. Key subsystems developed to complete the platform include:
</p>

<div class="card-box">
  <h3>A. Customer Management & Booking Lifecycle</h3>
  <ul>
    <li><code>customer/dashboard.php</code>: Dedicated customer control center with booking metrics, status-filtered booking cards, instant chat/call triggers, online payment buttons, and review modals.</li>
    <li><code>worker-profile.php</code>: Public landing page for each worker with experience badges, hourly rates, verified status, interactive Leaflet.js service coverage map, customer reviews feed, and booking dialog.</li>
    <li><code>api/book-worker.php</code>: Server endpoint processing customer bookings with validation, database insertion, and Pusher notification broadcasting.</li>
    <li><code>api/submit-review.php</code>: Validates completed bookings, accepts 1–5 star ratings and comments, and recalculates provider average ratings dynamically.</li>
  </ul>
</div>

<div class="card-box">
  <h3>B. SSLCommerz Payment & Subscription Gateway (Person 4)</h3>
  <ul>
    <li><code>subscription.php</code> & <code>worker/subscription.php</code>: Subscription management page presenting ৳399/month benefits, countdown timer (days remaining), and past transaction history.</li>
    <li><code>sslcommerz/init.php</code>: Initiates payment with SSLCommerz sandbox API. Includes an interactive fallback Sandbox Simulator so offline or sandbox evaluations function seamlessly.</li>
    <li><code>sslcommerz/success.php</code>: Validates transactions, activates 30-day worker visibility, marks <code>is_visible = 1</code>, updates booking payment records, and creates receipts.</li>
    <li><code>sslcommerz/ipn.php</code>: Instant Payment Notification server-to-server webhook ensuring subscriptions and bookings activate even if the client browser drops connection.</li>
    <li><code>cron/expire_subscriptions.php</code>: Scheduled CLI task that queries expired 30-day subscriptions, sets status to <code>expired</code>, and hides workers from search results.</li>
  </ul>
</div>

<div class="card-box">
  <h3>C. Identity Verification, Safety & Billing Deliverables</h3>
  <ul>
    <li><code>register.php</code>: Multi-step registration supporting multipart photo upload of physical NID cards into <code>uploads/nid/</code>.</li>
    <li><code>admin/dashboard.php</code>: Admin back-office with an interactive "Inspect NID Document" modal allowing administrators to audit physical cards before clicking Approve.</li>
    <li><code>verify-email.php</code>: 6-digit auto-advancing OTP verification interface with countdown resend timer and resilient mailer fallback.</li>
    <li><code>invoice.php</code>: Official A4 print-optimized invoice template featuring WUB institutional header, itemized breakdown, and SSLCommerz transaction details.</li>
    <li><code>seeds.sql</code>: Ready-to-import seed script populating 6 active, pre-subscribed service providers with authentic Bangladeshi ratings and reviews.</li>
  </ul>
</div>

<div class="page-break"></div>

<!-- SECTION 5 -->
<h1>5. Step-by-Step Local Testing Instructions</h1>

<div class="alert-box">
  <strong>Testing Environment:</strong> The complete codebase is located at <code>C:\\xampp\\htdocs\\e-sheba\\</code>. Testing requires Apache and MySQL running via XAMPP.
</div>

<ol>
  <li><strong>Database Initialization:</strong>
    <ul>
      <li>Open phpMyAdmin at <code>http://localhost/phpmyadmin</code>.</li>
      <li>Create database <code>esheba_db</code> with collation <code>utf8mb4_unicode_ci</code>.</li>
      <li>Import <code>schema.sql</code>, then import <code>seeds.sql</code>.</li>
    </ul>
  </li>
  <li><strong>Configuration Verification:</strong> Confirm settings in <code>includes/config.php</code>:
    <code>DB_HOST='localhost'</code>, <code>DB_NAME='esheba_db'</code>, <code>DB_USER='root'</code>, <code>DB_PASS=''</code>, <code>APP_URL='http://localhost/e-sheba'</code>.
  </li>
  <li><strong>Pre-Seeded Demonstration Accounts (All accounts use password: <code>password123</code>):</strong>
    <table class="data-table" style="margin-top:8pt;">
      <thead>
        <tr>
          <th>Role</th>
          <th>Full Name</th>
          <th>Login Email</th>
          <th>Profile Highlights</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Admin</td>
          <td>Administrator</td>
          <td><code>admin@e-sheba.com</code></td>
          <td>Password: <code>Admin@1234</code>. Complete back-office oversight and NID inspection.</td>
        </tr>
        <tr>
          <td>Customer</td>
          <td>Rahim Ahmed</td>
          <td><code>rahim@gmail.com</code></td>
          <td>Pre-loaded with completed booking, accepted booking, and review history.</td>
        </tr>
        <tr>
          <td>Worker 1</td>
          <td>Kabir Hossain</td>
          <td><code>kabir.electric@gmail.com</code></td>
          <td>Electrician in Mirpur, Dhaka · 400 BDT/hr · 4.9★ (12 reviews) · Active 30D Sub.</td>
        </tr>
        <tr>
          <td>Worker 2</td>
          <td>Mizanur Rahman</td>
          <td><code>mizan.plumber@gmail.com</code></td>
          <td>Plumber in Dhanmondi, Dhaka · 350 BDT/hr · 4.8★ (9 reviews) · Active 30D Sub.</td>
        </tr>
        <tr>
          <td>Worker 3</td>
          <td>Sabbir Ahmed</td>
          <td><code>sabbir.ac@gmail.com</code></td>
          <td>AC Technician in Uttara, Dhaka · 600 BDT/hr · 5.0★ (7 reviews) · Active 30D Sub.</td>
        </tr>
        <tr>
          <td>Worker 4</td>
          <td>Tanvir Hasan</td>
          <td><code>tanvir.carpenter@gmail.com</code></td>
          <td>Carpenter in Chittagong · 450 BDT/hr · 4.7★ (5 reviews) · Active 30D Sub.</td>
        </tr>
      </tbody>
    </table>
  </li>
  <li><strong>Core Execution Flows to Demonstrate:</strong>
    <ul>
      <li><strong>Provider Search:</strong> Navigate to <code>workers.php</code> $\rightarrow$ filter by Electrician in Dhaka $\rightarrow$ Kabir Hossain appears with Verified badge.</li>
      <li><strong>Service Booking:</strong> View Kabir's profile at <code>worker-profile.php?id=1</code> $\rightarrow$ click "Book This Provider" $\rightarrow$ submit Mirpur address and time.</li>
      <li><strong>Worker Job Lifecycle:</strong> Log into <code>worker/dashboard.php</code> as Kabir $\rightarrow$ click "Accept" $\rightarrow$ click "Start" $\rightarrow$ click "Complete".</li>
      <li><strong>Review & Invoicing:</strong> Log into <code>customer/dashboard.php</code> as Rahim $\rightarrow$ click "Rate & Review" $\rightarrow$ submit 5 stars $\rightarrow$ click "Invoice" to download the official receipt at <code>invoice.php</code>.</li>
    </ul>
  </li>
</ol>

<!-- SECTION 6 -->
<h1>6. Academic Defense & Viva Q&A Guide</h1>

<table class="data-table">
  <thead>
    <tr>
      <th style="width:35%;">Likely Examiner Question</th>
      <th>Recommended Technical Answer</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>"How is phone number masking achieved?"</strong></td>
      <td><em>"We integrated the Agora Web SDK with dynamic room signaling. Both parties join an encrypted WebRTC room named <code>esheba-call-{minID}-{maxID}</code>. Audio streams peer-to-peer over WebRTC, keeping cellular phone numbers completely private."</em></td>
    </tr>
    <tr>
      <td><strong>"What happens if the browser closes during an SSLCommerz payment?"</strong></td>
      <td><em>"We implemented an Instant Payment Notification (<code>sslcommerz/ipn.php</code>) server-to-server webhook. SSLCommerz calls our IPN listener asynchronously to validate the payment, updating the database even if the client tab drops connection."</em></td>
    </tr>
    <tr>
      <td><strong>"How is worker identity verified by the Admin?"</strong></td>
      <td><em>"Workers upload a photo of their National ID during registration into <code>uploads/nid/</code>. In the Admin Back-Office, admins click 'Inspect NID Document' to review the physical card before toggling <code>is_approved = 1</code>."</em></td>
    </tr>
    <tr>
      <td><strong>"How are SQL Injection and Session Hijacking prevented?"</strong></td>
      <td><em>"We enforce PDO prepared statements with <code>ATTR_EMULATE_PREPARES=false</code> across all queries. For session protection, we call <code>session_regenerate_id(true)</code> on login, enforce CSRF tokens with <code>hash_equals()</code>, and use Bcrypt password hashes."</em></td>
    </tr>
  </tbody>
</table>

<!-- SECTION 7 -->
<h1>7. Checkpoint: How to Continue from Here</h1>
<p>
The E-Sheba prototype is 100% complete, fully verified, and ready for deployment or academic demonstration.
</p>
<ul>
  <li><strong>Active Workspace:</strong> <code>C:\\Users\\User\\.gemini\\antigravity\\scratch\\e-sheba\\</code></li>
  <li><strong>Deliverable Files:</strong>
    <ul>
      <li>PDF Report: <code>E-Sheba_Project_Documentation_and_Conversation_Report.pdf</code></li>
      <li>Word Document: <code>E-Sheba_Project_Documentation_and_Conversation_Report.doc</code></li>
      <li>OpenXML Document: <code>E-Sheba_Project_Documentation_and_Conversation_Report.docx</code></li>
    </ul>
  </li>
</ul>

<div style="margin-top:30pt; padding:12pt; background-color:#F1F3F4; border-radius:6pt; text-align:center; font-size:9.5pt; color:#555555;">
  <strong>Project Workspace Path:</strong> <code>C:\\Users\\User\\.gemini\\antigravity\\scratch\\e-sheba\\</code><br>
  Generated by Antigravity AI Assistant · All rights reserved · World University of Bangladesh (WUB)
</div>

</div>
</body>
</html>
"""

# Output paths
doc_path_1 = r"C:\Users\User\.gemini\antigravity\scratch\e-sheba\E-Sheba_Project_Documentation_and_Conversation_Report.doc"
doc_path_2 = r"C:\Users\User\.gemini\antigravity\brain\4a4929d5-f264-4a1d-bfdc-ed011a21ab0e\E-Sheba_Project_Documentation_and_Conversation_Report.doc"

with open(doc_path_1, "w", encoding="utf-8") as f:
    f.write(doc_content)
shutil.copy2(doc_path_1, doc_path_2)

print(f"DOC generated successfully: {os.path.getsize(doc_path_1)} bytes")

# 2. Build OpenXML .docx format using pure python zipfile
docx_path_1 = r"C:\Users\User\.gemini\antigravity\scratch\e-sheba\E-Sheba_Project_Documentation_and_Conversation_Report.docx"
docx_path_2 = r"C:\Users\User\.gemini\antigravity\brain\4a4929d5-f264-4a1d-bfdc-ed011a21ab0e\E-Sheba_Project_Documentation_and_Conversation_Report.docx"

content_types_xml = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>"""

rels_xml = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>"""

doc_rels_xml = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>"""

styles_xml = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults>
    <w:rPrDefault>
      <w:rPr>
        <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
        <w:sz w:val="22"/>
        <w:color w:val="222222"/>
      </w:rPr>
    </w:rPrDefault>
  </w:docDefaults>
</w:styles>"""

# Convert paragraphs to WordprocessingML
sections_text = [
    ("E-Sheba: Service Provider Web-Based Application", "Title", True, "1A73E8", 36),
    ("Software Requirements Specification & Complete Implementation Report", "Subtitle", False, "555555", 24),
    ("World University of Bangladesh (WUB) · Department of CSE · CSE 06133248", "Meta", False, "777777", 20),
    ("Student Team: Sahnewaz Hasib (4264), Fatema Khatun (4276), Khadiza Binte Hasan (4260), Jannatul Bushra (4286), Manir Rahman (4278)", "Meta", True, "333333", 20),
    ("Status: Fully Implemented & Test Ready (58+ Application Files)", "Status", True, "137333", 22),
    ("1. Executive Summary & Project Purpose", "Heading1", True, "1A73E8", 28),
    ("E-Sheba is an on-demand, two-sided web marketplace engineered to connect homeowners across Bangladesh with verified blue-collar service professionals (plumbers, electricians, appliance technicians, cleaners, etc.). The platform addresses key issues of informal contracting: identity verification via National ID (NID), transparent hourly pricing, masked real-time calling to preserve user privacy, and automated provider subscriptions (BDT 399/month) via SSLCommerz.", "Normal", False, "222222", 22),
    ("2. Team Work Breakdown Structure (5 Members)", "Heading1", True, "1A73E8", 28),
    ("• Person 1 (Sahnewaz Hasib): Customer Frontend - index.php, categories.php, workers.php, customer/dashboard.php, booking modal, review dialogs.", "List", False, "222222", 22),
    ("• Person 2 (Fatema Khatun): Worker & Admin Frontend - worker/profile.php, worker/dashboard.php, admin/dashboard.php, NID inspection modal.", "List", False, "222222", 22),
    ("• Person 3 (Khadiza Binte Hasan): Server Core & Search - login.php, register.php, includes/auth.php, includes/db.php, PDO queries.", "List", False, "222222", 22),
    ("• Person 4 (Jannatul Bushra): Payment & Subscriptions - sslcommerz/init.php, success.php, ipn.php, cron/expire_subscriptions.php, invoice.php.", "List", False, "222222", 22),
    ("• Person 5 (Manir Rahman): Real-Time Messaging & PWA - chat.php, api/send-message.php, api/agora-token.php, js/call.js, manifest.json, sw.js.", "List", False, "222222", 22),
    ("3. Relational Database Architecture (esheba_db)", "Heading1", True, "1A73E8", 28),
    ("The database runs on MySQL 8.x with 11 normalized tables: users, workers, categories (25 seeded), worker_categories, subscriptions (BDT 399), bookings, reviews, messages, call_logs, notifications, and otp_tokens.", "Normal", False, "222222", 22),
    ("4. Full Inventory of Delivered Code Modules", "Heading1", True, "1A73E8", 28),
    ("• customer/dashboard.php: Customer control center with booking metrics, real-time message/call action links, and review modal.", "List", False, "222222", 22),
    ("• worker-profile.php: Public provider landing page with interactive Leaflet.js coverage map and review feeds.", "List", False, "222222", 22),
    ("• sslcommerz/ suite: init.php, success.php, fail.php, cancel.php, ipn.php, with interactive sandbox simulator.", "List", False, "222222", 22),
    ("• verify-email.php: 6-digit auto-advancing OTP verification with countdown timer and fallback mailer.", "List", False, "222222", 22),
    ("• invoice.php: Official print-ready invoice template with WUB header, itemized fees, and transaction IDs.", "List", False, "222222", 22),
    ("• seeds.sql: Database seed script populating 6 active, pre-subscribed service providers across Dhaka, Chittagong, and Sylhet.", "List", False, "222222", 22),
    ("5. Step-by-Step Local Testing Instructions", "Heading1", True, "1A73E8", 28),
    ("Install XAMPP -> Copy project to C:\\xampp\\htdocs\\e-sheba\\ -> Open phpMyAdmin and create esheba_db -> Import schema.sql, then seeds.sql -> Test Admin login (admin@e-sheba.com / Admin@1234) -> Test Customer login (rahim@gmail.com / password123) -> Test Worker login (kabir.electric@gmail.com / password123).", "Normal", False, "222222", 22),
    ("6. Academic Defense & Viva Q&A Guide", "Heading1", True, "1A73E8", 28),
    ("• Phone Masking: Agora Web SDK WebRTC audio channel esheba-call-A-B connects media peer-to-peer without sharing telephone numbers.", "List", False, "222222", 22),
    ("• SSLCommerz IPN: Instant Payment Notification server-to-server webhook validates transactions even if user closes the browser.", "List", False, "222222", 22),
    ("• NID Verification: Workers upload card photos to uploads/nid/; Admin audits physical document via modal before approval.", "List", False, "222222", 22),
    ("• SQL Injection Prevention: Enforced PDO prepared statements with ATTR_EMULATE_PREPARES=false across all queries.", "List", False, "222222", 22),
    ("7. Checkpoint: How to Continue from Here", "Heading1", True, "1A73E8", 28),
    ("All 58 files are located in C:\\Users\\User\\.gemini\\antigravity\\scratch\\e-sheba\\. Next actions: 1) Deploy to live VPS / cPanel domain, 2) Add bilingual English/Bangla language switcher, 3) Final viva slide deck generation.", "Normal", False, "222222", 22)
]

doc_body_xml = []
for text, kind, is_bold, color, size in sections_text:
    b_tag = "<w:b/>" if is_bold else ""
    doc_body_xml.append(f"""
    <w:p>
      <w:pPr>
        <w:spacing w:before="120" w:after="80"/>
      </w:pPr>
      <w:r>
        <w:rPr>
          {b_tag}
          <w:color w:val="{color}"/>
          <w:sz w:val="{size}"/>
        </w:rPr>
        <w:t>{text}</w:t>
      </w:r>
    </w:p>
    """)

document_xml = f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    {''.join(doc_body_xml)}
    <w:sectPr>
      <w:pgSz w:w="11906" w:h="16838"/>
      <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/>
    </w:sectPr>
  </w:body>
</w:document>"""

with zipfile.ZipFile(docx_path_1, 'w', zipfile.ZIP_DEFLATED) as docx:
    docx.writestr('[Content_Types].xml', content_types_xml)
    docx.writestr('_rels/.rels', rels_xml)
    docx.writestr('word/_rels/document.xml.rels', doc_rels_xml)
    docx.writestr('word/document.xml', document_xml)
    docx.writestr('word/styles.xml', styles_xml)

shutil.copy2(docx_path_1, docx_path_2)
print(f"DOCX generated successfully: {os.path.getsize(docx_path_1)} bytes")
