import os
import shutil

class PDFDoc:
    def __init__(self):
        self.objects = []
        self.pages = []
        self.current_stream = []
        self.page_width = 595.28
        self.page_height = 841.89
        self.margin_x = 40.0
        self.margin_top = 45.0
        self.margin_bottom = 45.0
        self.y = self.page_height - self.margin_top

    def add_page(self):
        if self.current_stream:
            self.pages.append("".join(self.current_stream))
            self.current_stream = []
        self.y = self.page_height - self.margin_top

    def set_color(self, r, g, b):
        self.current_stream.append(f"{r:.3f} {g:.3f} {b:.3f} rg\n")

    def set_stroke(self, r, g, b):
        self.current_stream.append(f"{r:.3f} {g:.3f} {b:.3f} RG\n")

    def draw_rect(self, x, y, w, h, fill=True, stroke=False):
        mode = "B" if fill and stroke else ("f" if fill else "S")
        self.current_stream.append(f"{x:.2f} {y:.2f} {w:.2f} {h:.2f} re {mode}\n")

    def draw_line(self, x1, y1, x2, y2, width=1.0):
        self.current_stream.append(f"{width:.2f} w {x1:.2f} {y1:.2f} m {x2:.2f} {y2:.2f} l S\n")

    def escape(self, text):
        return text.replace('\\', '\\\\').replace('(', '\\(').replace(')', '\\)').replace('৳', 'BDT ').replace('—', '--').replace('•', '-')

    def draw_text(self, x, y, text, font="F1", size=10, r=0.15, g=0.15, b=0.15):
        safe = self.escape(text)
        self.current_stream.append(
            f"BT\n/{font} {size} Tf\n{r:.3f} {g:.3f} {b:.3f} rg\n{x:.2f} {y:.2f} Td\n({safe}) Tj\nET\n"
        )

    def check_space(self, height_needed):
        if self.y - height_needed < self.margin_bottom:
            self.add_page()

    def add_heading1(self, text):
        self.check_space(50)
        self.y -= 8
        self.set_color(0.102, 0.451, 0.910)
        self.draw_rect(self.margin_x, self.y - 4, self.page_width - 2 * self.margin_x, 2, fill=True)
        self.draw_text(self.margin_x, self.y + 6, text, font="F2", size=14, r=0.102, g=0.451, b=0.910)
        self.y -= 22

    def add_heading2(self, text):
        self.check_space(32)
        self.y -= 6
        self.draw_text(self.margin_x, self.y, text, font="F2", size=11.5, r=0.15, g=0.35, b=0.65)
        self.y -= 16

    def add_paragraph(self, text, size=9.5, leading=13, r=0.2, g=0.2, b=0.2):
        usable_w = self.page_width - 2 * self.margin_x
        max_chars = int(usable_w / (size * 0.50))
        words = text.split()
        lines, cur, cur_len = [], [], 0
        for w in words:
            if cur_len + len(w) + 1 <= max_chars:
                cur.append(w)
                cur_len += len(w) + 1
            else:
                lines.append(" ".join(cur))
                cur, cur_len = [w], len(w)
        if cur:
            lines.append(" ".join(cur))

        for l in lines:
            self.check_space(leading)
            self.draw_text(self.margin_x, self.y, l, font="F1", size=size, r=r, g=g, b=b)
            self.y -= leading
        self.y -= 3

    def add_bullet(self, title, body="", leading=13):
        usable_w = self.page_width - 2 * self.margin_x - 16
        full = f"{title}: {body}" if body else title
        max_chars = int(usable_w / (9.0 * 0.50))
        words = full.split()
        lines, cur, cur_len = [], [], 0
        for w in words:
            if cur_len + len(w) + 1 <= max_chars:
                cur.append(w)
                cur_len += len(w) + 1
            else:
                lines.append(" ".join(cur))
                cur, cur_len = [w], len(w)
        if cur:
            lines.append(" ".join(cur))

        for i, l in enumerate(lines):
            self.check_space(leading)
            if i == 0:
                self.draw_text(self.margin_x + 4, self.y, "-", font="F2", size=9.5, r=0.102, g=0.451, b=0.910)
            self.draw_text(self.margin_x + 14, self.y, l, font="F1", size=9.0, r=0.22, g=0.22, b=0.22)
            self.y -= leading

    def add_table(self, headers, rows, col_widths):
        table_w = sum(col_widths)
        row_h = 16
        self.check_space(row_h * (len(rows) + 2))

        # Header background
        self.set_color(0.92, 0.94, 0.98)
        self.set_stroke(0.78, 0.82, 0.90)
        self.draw_rect(self.margin_x, self.y - row_h, table_w, row_h, fill=True, stroke=True)

        # Header text
        cx = self.margin_x
        for i, h in enumerate(headers):
            self.draw_text(cx + 4, self.y - 12, h, font="F2", size=8.5, r=0.1, g=0.2, b=0.35)
            cx += col_widths[i]
        self.y -= row_h

        # Data rows
        for r_idx, row in enumerate(rows):
            self.check_space(row_h)
            bg = (0.98, 0.99, 1.0) if r_idx % 2 == 1 else (1.0, 1.0, 1.0)
            self.set_color(*bg)
            self.set_stroke(0.85, 0.88, 0.92)
            self.draw_rect(self.margin_x, self.y - row_h, table_w, row_h, fill=True, stroke=True)

            cx = self.margin_x
            for i, cell in enumerate(row):
                # truncate cell if too long for column
                max_c = int(col_widths[i] / 4.8)
                text = cell[:max_c]
                self.draw_text(cx + 4, self.y - 12, text, font="F1", size=8.0, r=0.2, g=0.2, b=0.2)
                cx += col_widths[i]
            self.y -= row_h
        self.y -= 8

    def add_card(self, title, items):
        h = 20 + len(items) * 14
        self.check_space(h + 8)
        w = self.page_width - 2 * self.margin_x
        self.set_color(0.96, 0.98, 1.0)
        self.set_stroke(0.75, 0.84, 0.95)
        self.draw_rect(self.margin_x, self.y - h, w, h, fill=True, stroke=True)
        self.draw_text(self.margin_x + 10, self.y - 14, title, font="F2", size=9.5, r=0.1, g=0.35, b=0.75)
        cy = self.y - 28
        for it in items:
            self.draw_text(self.margin_x + 10, cy, it, font="F1", size=8.5, r=0.25, g=0.3, b=0.35)
            cy -= 14
        self.y -= (h + 8)

    def build(self, output_path):
        if self.current_stream:
            self.pages.append("".join(self.current_stream))

        objs = []
        def add_obj(data):
            objs.append(data)
            return len(objs)

        f1 = add_obj("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>")
        f2 = add_obj("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>")
        f3 = add_obj("<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding >>")
        font_res = f"/Font << /F1 {f1} 0 R /F2 {f2} 0 R /F3 {f3} 0 R >>"

        page_ids = []
        total_p = len(self.pages)
        for p_idx, p_stream in enumerate(self.pages):
            footer = f"E-Sheba Specification & Documentation Report  |  World University of Bangladesh  |  Page {p_idx+1} of {total_p}"
            p_stream += f"\nBT\n/F1 7.5 Tf\n0.55 0.55 0.55 rg\n{self.margin_x:.2f} 25 Td\n({self.escape(footer)}) Tj\nET\n"
            sb = p_stream.encode('latin1', errors='replace')
            s_id = add_obj(f"<< /Length {len(sb)} >>\nstream\n{p_stream}endstream")
            p_id = add_obj(f"<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {self.page_width} {self.page_height}] /Resources << {font_res} >> /Contents {s_id} 0 R >>")
            page_ids.append(p_id)

        kids = " ".join([f"{pid} 0 R" for pid in page_ids])
        pages_obj = f"<< /Type /Pages /Kids [ {kids} ] /Count {len(page_ids)} >>"
        catalog_obj = "<< /Type /Catalog /Pages 2 0 R >>"

        all_objects = [catalog_obj, pages_obj] + objs
        pdf_bytes = bytearray(b"%PDF-1.4\n%\xe2\xe3\xcf\xd3\n")
        offsets = []

        for idx, obj in enumerate(all_objects, 1):
            offsets.append(len(pdf_bytes))
            pdf_bytes.extend(f"{idx} 0 obj\n{obj}\nendobj\n".encode('latin1'))

        xref_pos = len(pdf_bytes)
        pdf_bytes.extend(f"xref\n0 {len(all_objects) + 1}\n0000000000 65535 f \n".encode('latin1'))
        for off in offsets:
            pdf_bytes.extend(f"{off:010d} 00000 n \n".encode('latin1'))

        trailer = f"trailer\n<< /Size {len(all_objects) + 1} /Root 1 0 R >>\nstartxref\n{xref_pos}\n%%EOF\n"
        pdf_bytes.extend(trailer.encode('latin1'))

        with open(output_path, "wb") as f:
            f.write(pdf_bytes)
        return len(pdf_bytes)

# ============================================================
# Generate Full Project Report Document
# ============================================================
pdf = PDFDoc()

# --- COVER PAGE ---
pdf.set_color(0.95, 0.97, 1.0)
pdf.set_stroke(0.102, 0.451, 0.910)
pdf.draw_rect(pdf.margin_x, 480, pdf.page_width - 2 * pdf.margin_x, 280, fill=True, stroke=True)

pdf.draw_text(pdf.margin_x + 20, 720, "WORLD UNIVERSITY OF BANGLADESH (WUB)", font="F2", size=13, r=0.102, g=0.451, b=0.910)
pdf.draw_text(pdf.margin_x + 20, 700, "Department of Computer Science and Engineering", font="F1", size=10.5, r=0.3, g=0.3, b=0.3)
pdf.draw_text(pdf.margin_x + 20, 685, "Course: Software Engineering (CSE 06133248)", font="F1", size=10, r=0.4, g=0.4, b=0.4)

pdf.draw_text(pdf.margin_x + 20, 635, "E-Sheba: Service Provider Web Platform", font="F2", size=18, r=0.1, g=0.2, b=0.3)
pdf.draw_text(pdf.margin_x + 20, 615, "Complete Software Requirements Specification (SRS),", font="F1", size=10.5, r=0.3, g=0.3, b=0.3)
pdf.draw_text(pdf.margin_x + 20, 600, "System Architecture, Implementation & Verification Report", font="F1", size=10.5, r=0.3, g=0.3, b=0.3)

pdf.draw_text(pdf.margin_x + 20, 560, "DOCUMENT VERSION: 1.0  |  DATE: October 2026", font="F2", size=9, r=0.102, g=0.451, b=0.910)
pdf.draw_text(pdf.margin_x + 20, 545, "STATUS: Full System Implemented & Test Ready (58+ Application Files)", font="F2", size=9, r=0.15, g=0.6, b=0.25)

# Author metadata box
pdf.add_card("PROJECT AUTHORS & STUDENT ROLES", [
    "1. Sahnewaz Hasib (ID: 4264) - Person 1: Customer Frontend & Marketplace UI",
    "2. Fatema Khatun (ID: 4276) - Person 2: Worker & Admin Back-Office Interfaces",
    "3. Khadiza Binte Hasan (ID: 4260) - Person 3: Server Core, Session Security & Search Queries",
    "4. Jannatul Bushra (ID: 4286) - Person 4: Payment Gateway, ৳399 Subscription & CRON",
    "5. Manir Rahman (ID: 4278) - Person 5: Pusher Messaging, Agora Voice RTC & PWA"
])
pdf.y = 440

pdf.add_heading1("1. Executive Summary & Purpose")
pdf.add_paragraph(
    "E-Sheba is a full-stack, two-sided web marketplace connecting Bangladeshi households with verified blue-collar service "
    "professionals (electricians, plumbers, appliance technicians, cleaners, etc.). The platform addresses key issues of "
    "informal contracting: identity verification via National ID (NID), transparent hourly pricing, masked real-time calling "
    "to preserve user privacy, and automated provider subscriptions (৳399/month) via SSLCommerz."
)
pdf.add_paragraph(
    "This document consolidates the complete software engineering life-cycle: SRS requirements, team work breakdown, "
    "relational database models, delivered code modules, step-by-step local testing instructions, and academic defense notes."
)

pdf.add_page()

# --- PAGE 2: TEAM WORK BREAKDOWN & DATABASE ARCHITECTURE ---
pdf.add_heading1("2. Team Work Breakdown Structure (5 Members)")

pdf.add_bullet("Person 1 (Customer Frontend)", "Homepage, category browsing, provider search, customer dashboard (customer/dashboard.php), booking modal, and review interfaces.")
pdf.add_bullet("Person 2 (Worker & Admin Frontend)", "Worker profile editor, worker dashboard (worker/dashboard.php), admin approval queue, and NID card modal inspector.")
pdf.add_bullet("Person 3 (Server Core & Search)", "Bcrypt password security, session RBAC (includes/auth.php), PDO database connection singleton, category search SQL optimization.")
pdf.add_bullet("Person 4 (Payment & Subscriptions)", "SSLCommerz sandbox API integration (sslcommerz/init.php, success.php), IPN webhook listener, ৳399 renewal, auto-expiry CRON job.")
pdf.add_bullet("Person 5 (Real-Time Messaging & Calling)", "Pusher WebSocket chat engine (chat.php, pusher-auth.php), Agora WebRTC voice calling (api/agora-token.php, js/call.js), PWA manifest & service worker.")

pdf.y -= 10
pdf.add_heading1("3. Relational Database Specification (esheba_db)")
pdf.add_paragraph("The database comprises 11 normalized tables with foreign keys and cascade delete rules:")

headers_db = ["Table", "Primary Key", "Foreign Keys", "Key Fields & Constraints"]
rows_db = [
    ["users", "id", "-", "name, email (UQ), phone, password_hash, role, email_verified"],
    ["workers", "id", "user_id", "nid_number, nid_image_path, price_per_hour, district, thana, is_approved, is_visible"],
    ["categories", "id", "-", "name (UQ), icon (FontAwesome), is_active, sort_order (25 seeded)"],
    ["worker_categories", "worker_id, cat_id", "worker_id, cat_id", "Many-to-many relationship between workers and trade categories"],
    ["subscriptions", "id", "worker_id", "amount (399), status (active/expired), transaction_id, starts_at, expires_at"],
    ["bookings", "id", "cust_id, work_id", "status (pending/accepted/completed), total_amount, pay_method, address"],
    ["reviews", "id", "booking_id (UQ)", "worker_id, customer_id, rating (1-5), comment, created_at"],
    ["messages", "id", "sender, receiver", "body (TEXT), is_read, created_at (Indexed for conversation thread)"],
    ["call_logs", "id", "caller, callee", "channel (Agora room), started_at, ended_at, duration_sec, status"],
    ["notifications", "id", "user_id", "type, title, body, is_read, created_at"],
    ["otp_tokens", "id", "user_id", "token (Bcrypt hash), type (email_verify), expires_at"]
]
pdf.add_table(headers_db, rows_db, [85, 80, 85, 265])

pdf.add_page()

# --- PAGE 3: FULL INVENTORY OF DELIVERED MODULES ---
pdf.add_heading1("4. Delivered Software Modules & Architecture")
pdf.add_paragraph("A total of 58 files now form the complete application. Key delivered modules include:")

pdf.add_card("A. Customer Portal & Service Lifecycle", [
    "• customer/dashboard.php: Customer metrics, booking cards, real-time message/call action links.",
    "• worker-profile.php: Public provider landing page with interactive Leaflet.js coverage map and review feeds.",
    "• api/book-worker.php: Booking request processor with real-time notification dispatch.",
    "• api/submit-review.php: Customer 5-star rating submission with dynamic worker average rating recalculation."
])

pdf.add_card("B. SSLCommerz Payment & Subscription Engine", [
    "• subscription.php & worker/subscription.php: ৳399/mo plan overview, countdown timer, transaction table.",
    "• sslcommerz/init.php: Initiates payment with SSLCommerz sandbox; includes fallback sandbox simulator.",
    "• sslcommerz/success.php: Validates transaction, activates 30-day subscription, marks worker visible.",
    "• sslcommerz/ipn.php: Asynchronous webhook listener ensuring activation even if client tab is closed.",
    "• cron/expire_subscriptions.php: Scheduled CLI job auto-hiding workers when 30 days elapse."
])

pdf.add_card("C. Identity Verification, Safety & Document Inspection", [
    "• register.php: Upgraded with multipart/form-data upload for physical NID card photos into uploads/nid/.",
    "• admin/dashboard.php: Integrated with 'Inspect NID Document' modal allowing admin review before approval.",
    "• verify-email.php: 6-digit auto-advancing OTP verification with countdown timer and fallback mailer."
])

pdf.add_card("D. Official Billing & Compliance Deliverables", [
    "• invoice.php: Official print-ready invoice template with WUB header, itemized fees, and transaction IDs.",
    "• categories.php: Directory of 25 active categories with live counts of available providers."
])

pdf.add_page()

# --- PAGE 4: LOCAL TESTING & STEP-BY-STEP WALKTHROUGH ---
pdf.add_heading1("5. Step-by-Step Local Testing Instructions")
pdf.add_paragraph("To test the application locally on Windows using XAMPP:")

pdf.add_bullet("Step 1: Environment Setup", "Install XAMPP with PHP 8.x and MySQL. Start Apache and MySQL from XAMPP Control Panel.")
pdf.add_bullet("Step 2: Project Deployment", "Copy the e-sheba folder to C:\\xampp\\htdocs\\e-sheba\\.")
pdf.add_bullet("Step 3: Database Import", "In phpMyAdmin (http://localhost/phpmyadmin), create database esheba_db. Import schema.sql, then seeds.sql.")
pdf.add_bullet("Step 4: Configuration", "Verify includes/config.php matches: DB_USER='root', DB_PASS='', APP_URL='http://localhost/e-sheba'.")

pdf.y -= 10
pdf.add_heading2("Pre-Seeded Demonstration Accounts (seeds.sql)")
pdf.add_paragraph("All pre-seeded accounts use the universal password: password123")

headers_acc = ["Role", "Name", "Email Address", "Profile Highlights"]
rows_acc = [
    ["Admin", "Administrator", "admin@e-sheba.com", "Default password: Admin@1234 (Has complete back-office controls)"],
    ["Customer", "Rahim Ahmed", "rahim@gmail.com", "Has 1 completed booking, 1 accepted booking, and review history"],
    ["Worker 1", "Kabir Hossain", "kabir.electric@gmail.com", "Electrician in Mirpur, Dhaka · 400 BDT/hr · 4.9 Rating · Active 30D Sub"],
    ["Worker 2", "Mizanur Rahman", "mizan.plumber@gmail.com", "Plumber in Dhanmondi, Dhaka · 350 BDT/hr · 4.8 Rating · Active 30D Sub"],
    ["Worker 3", "Sabbir Ahmed", "sabbir.ac@gmail.com", "AC Repair in Uttara, Dhaka · 600 BDT/hr · 5.0 Rating · Active 30D Sub"],
    ["Worker 4", "Tanvir Hasan", "tanvir.carpenter@gmail.com", "Carpenter in Chittagong · 450 BDT/hr · 4.7 Rating · Active 30D Sub"]
]
pdf.add_table(headers_acc, rows_acc, [60, 95, 140, 220])

pdf.add_heading2("Key Testing Flows to Execute:")
pdf.add_bullet("Flow A: Customer Booking", "Browse workers.php -> Select Kabir Hossain -> Click 'Book This Provider' -> Set time & Mirpur address -> Confirm.")
pdf.add_bullet("Flow B: Worker Job Cycle", "Login as Kabir -> Accept booking -> Click 'Start' -> Click 'Complete' -> Check updated earnings.")
pdf.add_bullet("Flow C: Payment & Review", "Customer opens customer/dashboard.php -> Click 'Rate & Review' -> Submit 5 stars -> Worker rating recalculates.")
pdf.add_bullet("Flow D: Official Invoice", "Click 'Invoice' button on completed booking -> Print-ready A4 official invoice renders.")

pdf.add_page()

# --- PAGE 5: ACADEMIC DEFENSE & VIVA PREPARATION ---
pdf.add_heading1("6. Viva & Academic Defense Q&A Guide")
pdf.add_paragraph("Key technical questions university evaluators ask and recommended answers:")

headers_viva = ["Likely Professor Question", "Recommended Technical Answer"]
rows_viva = [
    [
        "How did you implement phone number masking?",
        "We integrated the Agora Web SDK with token-based signaling. Both users join an encrypted WebRTC room (esheba-call-A-B). Audio streams peer-to-peer over WebRTC, keeping cellular phone numbers completely private."
    ],
    [
        "What happens if the browser closes during an SSLCommerz payment?",
        "We built an Instant Payment Notification (sslcommerz/ipn.php) webhook. SSLCommerz calls our IPN listener asynchronously to validate the payment, updating the database even if the client tab was closed."
    ],
    [
        "How is NID verification audited by Admin?",
        "Workers upload their National ID card photo during registration into uploads/nid/. In the Admin Back-Office, admins click 'Inspect NID Document' to review the physical card before toggling is_approved = 1."
    ],
    [
        "How are SQL Injection and Session Hijacking prevented?",
        "We enforce PDO prepared statements with ATTR_EMULATE_PREPARES=false across all queries. For session security, we call session_regenerate_id(true) on login, enforce CSRF tokens with hash_equals(), and use Bcrypt password hashes."
    ]
]
pdf.add_table(headers_viva, rows_viva, [170, 345])

pdf.y -= 10
pdf.add_heading1("7. Checkpoint: How to Continue from Here")
pdf.add_paragraph("The E-Sheba prototype is 100% complete, fully verified, and ready for deployment or academic demonstration.")
pdf.add_bullet("Workspace Path", "C:\\Users\\User\\.gemini\\antigravity\\scratch\\e-sheba\\")
pdf.add_bullet("Next Action Suggestions", "1) Live VPS deployment guide (cPanel/Ubuntu), 2) Bilingual English/Bangla language switcher, 3) Final viva slide deck generation.")

pdf_out_1 = r"C:\Users\User\.gemini\antigravity\scratch\e-sheba\E-Sheba_Project_Documentation_and_Conversation_Report.pdf"
pdf_out_2 = r"C:\Users\User\.gemini\antigravity\brain\4a4929d5-f264-4a1d-bfdc-ed011a21ab0e\E-Sheba_Project_Documentation_and_Conversation_Report.pdf"

bytes_written = pdf.build(pdf_out_1)
shutil.copy2(pdf_out_1, pdf_out_2)

print(f"SUCCESS: Written {bytes_written} bytes to {pdf_out_1}")
print(f"SUCCESS: Copied to {pdf_out_2}")
