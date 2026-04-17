@extends('layouts.parent')

@section('title', 'PLP - Parent Contact Us')
@section('page-title', 'CONTACT US')

@section('content')
<div class="parent-contact-page">
    <div class="parent-contact-grid">
        <article class="parent-contact-card">
            <div class="parent-contact-icon-wrap" aria-hidden="true">
                <div class="parent-contact-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="86" height="86" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                </div>
            </div>
            <p class="parent-contact-card-title">Call Support</p>
            <h3 class="parent-contact-value">+63 912 3456 789</h3>
            <p class="parent-contact-note">Our support team is available <strong>Monday to Friday, 8:00 AM - 5:00 PM</strong>. Keep your <strong>Student ID</strong> ready so we can verify records faster.</p>
        </article>

        <article class="parent-contact-card">
            <div class="parent-contact-icon-wrap" aria-hidden="true">
                <div class="parent-contact-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="86" height="86" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <polyline points="3 7 12 13 21 7"></polyline>
                </svg>
                </div>
            </div>
            <p class="parent-contact-card-title">Email Support</p>
            <h3 class="parent-contact-value"><a href="mailto:test@gmail.com">test@gmail.com</a></h3>
            <p class="parent-contact-note">Please allow <strong>24-48 hours</strong> for a response. Include your <strong>Full Name, Student ID, and Department</strong> in the subject line for quicker routing.</p>
        </article>
    </div>

    <section class="parent-contact-guidelines">
        <h4>Before You Contact Us</h4>
        <p>For faster assistance, we recommend using the In-System Messaging tool. It lets staff access your records while responding to your concern.</p>
        <ul>
            <li>For calls: prepare Student ID, full name, and contact number for verification.</li>
            <li>For email: expect a response within 1-2 business days during office hours.</li>
        </ul>
    </section>
</div>
@endsection
