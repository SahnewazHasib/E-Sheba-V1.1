# -*- coding: utf-8 -*-
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx_builder_base import (
    set_cell_background, set_cell_margins, add_header_banner,
    add_heading_1, add_heading_2, add_paragraph, add_bullet,
    add_callout, add_styled_table
)
import os

def create_doc_person_1():
    doc = docx.Document()
    for s in doc.sections:
        s.top_margin = Inches(1)
        s.bottom_margin = Inches(1)
        s.left_margin = Inches(1)
        s.right_margin = Inches(1)
        
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
    
    doc.save("C:/xampp/htdocs/e-sheba/team_work_docs/Person_1_Sahnewaz_Hasib_Customer_Frontend.docx")
    print("Created Person 1 Doc")

create_doc_person_1()
