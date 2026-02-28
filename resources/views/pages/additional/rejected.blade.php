@extends('layouts.app')

@section('title', 'Booking Form Reject')

@section('content')
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>

    <style>
        :root {
            --reject-card-radius: 12px;
            --reject-card-shadow: 0 4px 24px rgba(0, 0, 0, 0.06), 0 2px 8px rgba(0, 0, 0, 0.04);
            --reject-section-gap: 2rem;
            --reject-input-radius: 10px;
            --reject-info-bg: rgba(var(--thm-primary-rgb, 23, 162, 184), 0.06);
            --reject-info-border: rgba(var(--thm-primary-rgb, 23, 162, 184), 0.25);
        }

        html, body {
            font-family: 'Raleway', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .thankyou-page ._header {
            background: var(--thm-primary);
            padding: 100px 30px;
            text-align: center;
            background: var(--thm-primary) url(https://codexcourier.com/images/main_page.jpg) center/cover no-repeat;
        }
        .thankyou-page ._header .logo {
            max-width: 200px;
            margin: 0 auto 50px;
        }
        .thankyou-page ._header .logo img {
            width: 100%;
        }
        .thankyou-page ._header h1 {
            font-weight: 800;
            color: white;
            margin: 0;
        }
        .thankyou-page ._body {
            margin: -70px 0 30px;
            padding: 0 15px;
        }
        .thankyou-page ._body ._box {
            margin: 0 auto;
            max-width: 720px;
            padding: clamp(24px, 5vw, 48px);
            background: #fff;
            border-radius: var(--reject-card-radius);
            box-shadow: var(--reject-card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }
        .thankyou-page ._body ._box h2 {
            font-size: 32px;
            font-weight: 600;
            color: var(--thm-primary);
        }
        .thankyou-page ._body ._box h4 {
            font-size: clamp(1.15rem, 2.5vw, 1.35rem);
            font-weight: 600;
            color: #1a1a1a;
            letter-spacing: -0.02em;
            line-height: 1.35;
        }

        /* Section layout */
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--thm-primary);
            margin-bottom: 0.75rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: var(--reject-section-gap);
            padding-bottom: var(--reject-section-gap);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }
        .form-group:last-of-type {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .form-control {
            border-radius: var(--reject-input-radius);
            border: 1px solid #e5e7eb;
            padding: 14px 16px;
            font-size: 1rem;
            line-height: 1.5;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
        }
        .form-control::placeholder {
            color: #9ca3af;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--thm-primary);
            box-shadow: 0 0 0 3px rgba(var(--thm-primary-rgb), 0.12);
        }

        .char-counter-wrap {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .char-counter {
            font-size: 0.8125rem;
            color: #6b7280;
        }
        .char-counter.invalid {
            color: #dc2626;
        }

        .thm-btn {
            padding: 14px 32px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: var(--reject-input-radius);
            transition: transform 0.15s ease, box-shadow 0.2s ease, opacity 0.2s ease;
            min-width: 180px;
        }
        .thm-btn:not(:disabled):hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(var(--thm-primary-rgb), 0.25);
        }
        .thm-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .validation-message {
            color: #dc2626;
            font-size: 0.8125rem;
            margin-top: 0.25rem;
            display: none;
        }
        .validation-message.show {
            display: block;
        }

        /* Notifications */
        .notification {
            padding: 14px 18px;
            border-radius: var(--reject-input-radius);
            margin: 0 0 1.25rem;
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .notification.show {
            display: block;
        }
        .notification-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }
        .notification-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        .notification-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Info boxes */
        .info-box {
            background: var(--reject-info-bg);
            border-left: 4px solid var(--thm-primary);
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            border-radius: 0 var(--reject-input-radius) var(--reject-input-radius) 0;
            font-size: 0.9375rem;
            line-height: 1.55;
            color: #374151;
        }
        .info-box p {
            margin: 0;
        }

        /* Calendar */
        #lite-datepicker {
            margin: 0 auto 1.25rem;
            border-radius: var(--reject-input-radius);
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .litepicker {
            font-family: 'Raleway', sans-serif;
            font-size: 0.9375rem;
            margin: 0 auto;
        }

        .litepicker .container__days .day-item {
            border-radius: 50%;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.15s ease;
        }
        .litepicker .container__days .day-item:hover:not(.is-locked) {
            background-color: rgba(var(--thm-primary-rgb), 0.1);
            color: var(--thm-primary);
        }
        .litepicker .container__days .day-item:active:not(.is-locked) {
            transform: scale(0.95);
        }
        .litepicker .container__days .day-item.is-locked {
            text-decoration: line-through;
            background-color: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
        }
        .litepicker .container__days .day-item.is-today {
            color: var(--thm-primary);
            font-weight: 600;
        }
        .litepicker .container__days .day-item.is-in-range,
        .litepicker .container__days .day-item.is-start-date,
        .litepicker .container__days .day-item.is-end-date {
            background-color: var(--thm-primary);
            color: white;
        }
        .litepicker .container__days .day-item.is-selected {
            background-color: #059669;
            color: white;
            font-weight: 600;
        }
        .litepicker .container__days .day-item.is-start-date:not(.is-selected),
        .litepicker .container__days .day-item:hover:not(.is-selected):not(.is-locked) {
            background-color: rgba(var(--thm-primary-rgb), 0.12);
            color: #111;
        }

        .calendar-container {
            background: #fafafa;
            border-radius: var(--reject-input-radius);
            padding: clamp(16px, 3vw, 24px);
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .selected-dates-container {
            margin-bottom: 1rem;
            text-align: center;
        }
        .selected-dates-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            min-height: 2rem;
        }
        .date-tag {
            display: inline-flex;
            align-items: center;
            background: rgba(var(--thm-primary-rgb), 0.1);
            border-radius: 9999px;
            padding: 6px 12px;
            font-size: 0.875rem;
            color: var(--thm-primary);
            border: 1px solid rgba(var(--thm-primary-rgb), 0.2);
            transition: background 0.2s ease;
        }
        .date-tag .date-text {
            margin-right: 6px;
        }
        .date-tag .remove-date {
            margin-left: 4px;
            cursor: pointer;
            font-size: 1rem;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(var(--thm-primary-rgb), 0.15);
            transition: background 0.2s ease;
            line-height: 1;
        }
        .date-tag .remove-date:hover {
            background: rgba(var(--thm-primary-rgb), 0.3);
        }
        #selected-dates-input {
            display: none;
        }

        /* Foolproof how-to guide */
        .reject-form-guide {
            background: linear-gradient(135deg, rgba(var(--thm-primary-rgb, 23, 162, 184), 0.08) 0%, rgba(var(--thm-primary-rgb, 23, 162, 184), 0.03) 100%);
            border: 1px solid rgba(var(--thm-primary-rgb), 0.2);
            border-radius: var(--reject-input-radius);
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        .reject-form-guide__title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .reject-form-guide__title::before {
            content: '';
            width: 4px;
            height: 1.1em;
            background: var(--thm-primary);
            border-radius: 2px;
        }
        .reject-form-step {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            padding: 0.6rem 0;
            border-radius: 8px;
            transition: background 0.2s ease;
        }
        .reject-form-step:last-child {
            margin-bottom: 0;
        }
        .reject-form-step__num {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(var(--thm-primary-rgb), 0.2);
            color: var(--thm-primary);
            font-weight: 700;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .reject-form-step--done .reject-form-step__num {
            background: #059669;
            color: #fff;
        }
        .reject-form-step--done .reject-form-step__num::after {
            content: '✓';
        }
        .reject-form-step--done .reject-form-step__num {
            font-size: 0;
        }
        .reject-form-step__body {
            flex: 1;
            min-width: 0;
        }
        .reject-form-step__label {
            font-weight: 600;
            font-size: 0.9375rem;
            color: #374151;
            margin: 0 0 0.25rem;
        }
        .reject-form-step--done .reject-form-step__label {
            color: #059669;
        }
        .reject-form-step__hint {
            font-size: 0.8125rem;
            color: #6b7280;
            line-height: 1.45;
            margin: 0;
        }
        .reject-form-step--current .reject-form-step__hint {
            color: #374151;
        }
        .reject-form-inline-hint {
            font-size: 0.8125rem;
            color: #6b7280;
            margin: -0.5rem 0 0.75rem;
            padding-left: 0.25rem;
        }
        .reject-form-inline-hint strong {
            color: #374151;
        }

        /* Mobile-first responsive */
        @media (max-width: 575.98px) {
            .thankyou-page ._body {
                margin-top: 1rem;
                padding: 0 12px;
            }
            .thankyou-page ._body ._box {
                padding: 20px 16px;
            }
            .reject-form-guide {
                padding: 1rem 1.25rem;
            }
            .reject-form-step__num {
                width: 26px;
                height: 26px;
                font-size: 0.8125rem;
            }
            .reject-form-step__label {
                font-size: 0.875rem;
            }
            .reject-form-step__hint {
                font-size: 0.75rem;
            }
            .form-group {
                margin-bottom: 1.5rem;
                padding-bottom: 1.5rem;
            }
            .info-box {
                padding: 12px 14px;
                font-size: 0.875rem;
            }
            .calendar-container {
                padding: 12px;
            }
            .thm-btn {
                width: 100%;
                min-width: 0;
            }
            .date-tag {
                font-size: 0.8125rem;
                padding: 5px 10px;
            }
        }

        @media (min-width: 576px) and (max-width: 767.98px) {
            .thankyou-page ._body ._box {
                padding: 28px 24px;
            }
        }

        /* Litepicker responsive (touch-friendly cells on mobile) */
        @media (max-width: 767.98px) {
            .litepicker {
                max-width: 100%;
                font-size: 0.875rem;
            }
            .litepicker .container__days .day-item {
                min-height: 40px;
                min-width: 40px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .litepicker .container__months {
                width: 100%;
            }
            .litepicker .container__months .month-item {
                width: 100%;
            }
        }
    </style>
    <!------ Include the above in your HEAD tag ---------->

    <div class="container">
        <div class="thankyou-page">
       
            <div class="_body mt-5 py-5">
                <div class="_box">
                    <h4 class="mb-4">@lang('message.booking-reject-header')</h4>
                    
                    <!-- Notification area -->
                    <div id="notification" class="notification notification-warning">
                        <span id="notification-message"></span>
                    </div>

                    <!-- Foolproof how-to guide -->
                    <div class="reject-form-guide" id="reject-form-guide">
                        <h5 class="reject-form-guide__title">@lang('message.reject-form-how-to-title')</h5>
                        <div class="reject-form-step reject-form-step--current" id="guide-step-1" data-step="dates">
                            <span class="reject-form-step__num">1</span>
                            <div class="reject-form-step__body">
                                <p class="reject-form-step__label">@lang('message.reject-form-step-1')</p>
                                <p class="reject-form-step__hint">@lang('message.reject-form-step-1-hint')</p>
                            </div>
                        </div>
                        <div class="reject-form-step" id="guide-step-2" data-step="message">
                            <span class="reject-form-step__num">2</span>
                            <div class="reject-form-step__body">
                                <p class="reject-form-step__label">@lang('message.reject-form-step-2')</p>
                                <p class="reject-form-step__hint">@lang('message.reject-form-step-2-hint')</p>
                            </div>
                        </div>
                        <div class="reject-form-step" id="guide-step-3" data-step="submit">
                            <span class="reject-form-step__num">3</span>
                            <div class="reject-form-step__body">
                                <p class="reject-form-step__label">@lang('message.reject-form-step-3')</p>
                                <p class="reject-form-step__hint">@lang('message.reject-form-step-3-hint')</p>
                            </div>
                        </div>
                    </div>
                    
                    <form id="rejection-form" action="{{route('booking.rejection',$booking)}}" method="POST">
                        @csrf
                        
                        <!-- Calendar section -->
                        <div class="form-group" id="form-group-dates">
                            <p class="section-title">@lang('guidings.available_dates')</p>
                            <div class="info-box mb-4">
                                <p class="mb-0">@lang('message.booking-reject-message-available-dates')</p>
                            </div>
                            <p class="reject-form-inline-hint" id="inline-hint-dates"><strong>@lang('message.reject-form-step-pending'):</strong> @lang('message.reject-form-step-1-hint')</p>

                            <div class="calendar-container">    
                                <!-- Selected dates container -->
                                <div class="selected-dates-container">
                                    {{-- <p class="selected-dates-title">@lang('guidings.Alternative_Dates')</p> --}}
                                    <div class="selected-dates-tags" id="selected-dates-tags">
                                        <!-- Tags will be added here dynamically -->
                                    </div>
                                    <!-- Hidden input to store selected dates -->
                                    <input type="hidden" name="alternative_dates" id="selected-dates-input">
                                    <div id="date-validation-message" class="validation-message">
                                        @lang('guidings.Select_At_Least_One_Date')
                                    </div>
                                </div>
                                <div id="lite-datepicker"></div>
                            </div>
                        </div>
                        
                        <div class="form-group" id="form-group-message">
                            <p class="section-title">@lang('message.booking-reject-additional-comment')</p>
                            
                            <div class="info-box mb-4">
                                <p class="mb-0">@lang('message.booking-reject-message')</p>
                            </div>
                            <p class="reject-form-inline-hint" id="inline-hint-message"><strong>@lang('message.reject-form-step-pending'):</strong> @lang('message.reject-form-step-2-hint')</p>
                            <textarea class="form-control" name="reason" id="rejection-reason" rows="4" placeholder="@lang('guidings.Rejection_Reason_Placeholder')"></textarea>
                            <div class="char-counter-wrap">
                                <div id="reason-validation-message" class="validation-message">
                                    @lang('guidings.Min_Characters_Message')
                                </div>
                                <span class="char-counter" id="char-counter">0/50 @lang('guidings.Characters')</span>
                            </div>
                        </div>
                        
                        <div class="text-center pt-2 mt-2">
                            <button type="submit" id="submit-btn" class="thm-btn py-3 px-4" disabled>@lang('message.booking-submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Get blocked events from the booking
    const blockedEvents = @json($blocked_events ?? []);
    const bookingDate = @json($booking->book_date ?? null);
    
    // Clean up the booking date to remove time component if it exists
    let cleanBookingDate = bookingDate;
    if (bookingDate && bookingDate.includes(' ')) {
        cleanBookingDate = bookingDate.split(' ')[0]; 
    }
    
    let lockDays = [];
    if (blockedEvents && typeof blockedEvents === 'object') {
        lockDays = Object.values(blockedEvents).flatMap(event => {
            // Make sure event.from and event.due exist before creating Date objects
            if (event && event.from && event.due) {
                const fromDate = new Date(event.from);
                const dueDate = new Date(event.due);

                // Create an array of all dates in the range
                const dates = [];
                for (let d = new Date(fromDate); d <= dueDate; d.setDate(d.getDate() + 1)) {
                    dates.push(new Date(d));
                }
                return dates;
            }
            return [];
        });
    }
    
    // Add booking date to locked days if it exists
    if (cleanBookingDate) {
        try {
            // Try different ways to parse the date to ensure it works
            const bookDate = new Date(cleanBookingDate);
            lockDays.push(bookDate);
        } catch (e) {
            console.error("Error parsing booking date:", e);
        }
    }

    // Array to store selected dates
    let selectedDates = [];
    
    // Function to show notification
    function showNotification(message, type = 'warning') {
        const notification = document.getElementById('notification');
        const notificationMessage = document.getElementById('notification-message');
        
        // Remove all existing classes
        notification.className = 'notification';
        
        // Add appropriate class based on type
        notification.classList.add(`notification-${type}`);
        notification.classList.add('show');
        
        // Set message
        notificationMessage.textContent = message;
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
        }, 5000);
    }

    // Function to format date in a more descriptive way
    function formatDateDescriptive(dateString) {
        try {
            // Force the date to be interpreted as local time by adding a time component
            const localDate = dateString + 'T12:00:00';
            
            // Create a date object that won't be affected by timezone conversion
            const date = new Date(localDate);
            
            // Check if the date is valid
            if (isNaN(date.getTime())) {
                throw new Error('Invalid date');
            }
            
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone // Use browser's timezone
            };
            
            return date.toLocaleDateString('{{app()->getLocale()}}', options);
        } catch (error) {
            console.error("Error formatting date:", error, dateString);
            return dateString; // Fallback to original string if there's an error
        }
    }

    // Function to update the selected dates display and hidden input
    function updateSelectedDates() {
        const tagsContainer = document.getElementById('selected-dates-tags');
        const hiddenInput = document.getElementById('selected-dates-input');
        
        if (!tagsContainer || !hiddenInput) {
            console.error("Could not find tags container or hidden input");
            return;
        }
        
        // Clear current tags
        tagsContainer.innerHTML = '';
        
        // Add new tags for each selected date
        selectedDates.forEach(date => {
            const tag = document.createElement('div');
            tag.className = 'date-tag';
            tag.innerHTML = `
                <span class="date-text">${formatDateDescriptive(date)}</span>
                <span class="remove-date" data-date="${date}">&times;</span>
            `;
            tagsContainer.appendChild(tag);
            
            // Add click event to remove button
            tag.querySelector('.remove-date').addEventListener('click', function() {
                const dateToRemove = this.getAttribute('data-date');
                selectedDates = selectedDates.filter(d => d !== dateToRemove);
                updateSelectedDates();
                highlightSelectedDates();
                validateForm();
            });
        });
        
        // Update hidden input value
        hiddenInput.value = JSON.stringify(selectedDates);
        
        // Show/hide validation message
        const validationMessage = document.getElementById('date-validation-message');
        if (selectedDates.length === 0) {
            validationMessage.classList.add('show');
        } else {
            validationMessage.classList.remove('show');
        }
    }

    // Function to highlight selected dates in the calendar
    function highlightSelectedDates() {
        // First ensure we have the latest calendar days (in case of month navigation)
        const dayItems = document.querySelectorAll('.day-item');
        
        // Remove existing highlights first
        dayItems.forEach(el => {
            if (el.classList.contains('is-selected')) {
                el.classList.remove('is-selected');
            }
        });
        
        // Add highlight to selected dates
        if (selectedDates.length > 0) {
            selectedDates.forEach(dateStr => {
                try {
                    const date = new Date(dateStr + 'T12:00:00');
                    
                    dayItems.forEach(dayEl => {
                        const dayTimestamp = parseInt(dayEl.getAttribute('data-time'));
                        if (dayTimestamp) {
                            const dayDate = new Date(dayTimestamp);
                            if (dayDate.toDateString() === date.toDateString()) {
                                dayEl.classList.add('is-selected');
                            }
                        }
                    });
                } catch (e) {
                    console.error("Error highlighting date:", e);
                }
            });
        }
        
        // Force re-apply of styles to ensure visibility
        dayItems.forEach(el => {
            if (el.classList.contains('is-selected')) {
                el.style.backgroundColor = '#28a745'; // Green color
                el.style.color = 'white';
                el.style.fontWeight = 'bold';
            }
        });
    }

    // Update foolproof guide steps and inline hints
    function updateGuideSteps() {
        const reasonTextarea = document.getElementById('rejection-reason');
        const hasDates = selectedDates.length > 0;
        const hasMessage = reasonTextarea && reasonTextarea.value.length >= 50;
        const step1 = document.getElementById('guide-step-1');
        const step2 = document.getElementById('guide-step-2');
        const step3 = document.getElementById('guide-step-3');
        const hintDates = document.getElementById('inline-hint-dates');
        const hintMessage = document.getElementById('inline-hint-message');

        [step1, step2, step3].forEach(el => {
            if (!el) return;
            el.classList.remove('reject-form-step--done', 'reject-form-step--current');
        });

        step1.classList.add(hasDates ? 'reject-form-step--done' : 'reject-form-step--current');
        if (step2) {
            if (hasDates && !hasMessage) step2.classList.add('reject-form-step--current');
            if (hasMessage) step2.classList.add('reject-form-step--done');
        }
        if (step3 && hasDates && hasMessage) step3.classList.add('reject-form-step--current');

        if (hintDates) hintDates.style.display = hasDates ? 'none' : '';
        if (hintMessage) hintMessage.style.display = hasMessage ? 'none' : '';
    }

    // Function to validate the form
    function validateForm() {
        const submitBtn = document.getElementById('submit-btn');
        const reasonTextarea = document.getElementById('rejection-reason');
        const reasonValidation = document.getElementById('reason-validation-message');
        const dateValidation = document.getElementById('date-validation-message');
        
        const hasSelectedDates = selectedDates.length > 0;
        const hasEnoughChars = reasonTextarea.value.length >= 50;
        
        // Update validation messages
        if (hasEnoughChars) {
            if (reasonValidation) reasonValidation.classList.remove('show');
        } else {
            if (reasonValidation) reasonValidation.classList.add('show');
        }
        
        if (hasSelectedDates) {
            if (dateValidation) dateValidation.classList.remove('show');
        } else {
            if (dateValidation) dateValidation.classList.add('show');
        }
        
        // Enable/disable submit button
        if (submitBtn) submitBtn.disabled = !(hasSelectedDates && hasEnoughChars);

        updateGuideSteps();
    }

    // Character counter for textarea
    const reasonTextarea = document.getElementById('rejection-reason');
    const charCounter = document.getElementById('char-counter');
    
    reasonTextarea.addEventListener('input', function() {
        const charCount = this.value.length;
        charCounter.textContent = `${charCount}/50 @lang('guidings.Characters')`;
        
        if (charCount < 50) {
            charCounter.classList.add('invalid');
        } else {
            charCounter.classList.remove('invalid');
        }
        
        validateForm();
    });

    function initCheckNumberOfColumns() {
        // More granular breakpoints for better mobile experience
        if (window.innerWidth < 576) {
            return 1; // Extra small devices
        } else if (window.innerWidth < 768) {
            return 1; // Small devices
        } else {
            return 2; // Medium devices and larger
        }
    }

    // Check if the lite-datepicker element exists
    const datepickerElement = document.getElementById('lite-datepicker');
    if (!datepickerElement) {
        console.error("Could not find lite-datepicker element");
        return;
    }
    
    // Create a dummy input element for Litepicker
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.id = 'litepicker-hidden-input';
    datepickerElement.appendChild(hiddenInput);
    
    // Initialize Litepicker
    try {
        const picker = new Litepicker({
            element: hiddenInput,
            inlineMode: true,
            parentEl: datepickerElement,
            singleMode: true,
            numberOfColumns: initCheckNumberOfColumns(),
            numberOfMonths: initCheckNumberOfColumns(),
            minDate: new Date(new Date().getTime() + 24 * 60 * 60 * 1000), // Tomorrow
            lockDays: lockDays,
            lang: '{{app()->getLocale()}}',
            mobileFriendly: true,
            showTooltip: false,
            showWeekNumbers: false,
            autoApply: true,
            allowRepick: true,
            switchingMonths: 1, // Allow switching 1 month at a time
            useResetBtn: false,
            resetBtnCallback: () => {},
            autoRefresh: true,
            showMonthArrows: true, // Ensure month arrows are visible
            setup: (picker) => {
                window.addEventListener('resize', () => {
                    const columns = initCheckNumberOfColumns();
                    picker.setOptions({
                        numberOfColumns: columns,
                        numberOfMonths: columns
                    });
                    // Re-highlight dates after resize
                    setTimeout(() => {
                        highlightSelectedDates();
                        highlightBookingDate();
                        ensureNavigationArrows();
                    }, 300);
                });
            },
            onRender: (ui) => {
                setTimeout(() => {
                    highlightSelectedDates();
                    highlightBookingDate();
                    ensureNavigationArrows();
                }, 100);
            },
            onChangeMonth: (date, calendarIdx) => {
                setTimeout(() => {
                    highlightSelectedDates();
                    highlightBookingDate();
                    ensureNavigationArrows();
                }, 100);
            },
            onSelect: (date) => {
                // This helps ensure highlights are applied after date selection
                setTimeout(() => {
                    highlightSelectedDates();
                    highlightBookingDate();
                    ensureNavigationArrows();
                }, 100);
            }
        });

        // Add a separate function to highlight the booking date
        function highlightBookingDate() {
            if (cleanBookingDate) {
                
                const dayItems = document.querySelectorAll('.day-item');
                
                dayItems.forEach(dayEl => {
                    const dayTimestamp = parseInt(dayEl.getAttribute('data-time'));
                    if (dayTimestamp) {
                        const dayDate = new Date(dayTimestamp);
                        
                        // Get the date parts for comparison
                        const dayYear = dayDate.getFullYear();
                        const dayMonth = dayDate.getMonth() + 1;
                        const dayDay = dayDate.getDate();
                        
                        // Parse the clean booking date
                        const bookingParts = cleanBookingDate.split('-');
                        const bookingYear = parseInt(bookingParts[0]);
                        const bookingMonth = parseInt(bookingParts[1]);
                        const bookingDay = parseInt(bookingParts[2]);
                        
                        // Compare the date parts directly
                        if (dayYear === bookingYear && dayMonth === bookingMonth && dayDay === bookingDay) {
                            
                            // Apply strong visual styling
                            dayEl.classList.add('is-locked');
                            dayEl.style.color = '#dc3545'; // Red color
                            dayEl.style.textDecoration = 'line-through';
                            dayEl.style.backgroundColor = '#f8f8f8';
                            dayEl.style.fontWeight = 'bold';
                            dayEl.style.border = '2px solid #dc3545';
                        }
                    }
                });
            }
        }

        // Function to ensure navigation arrows are visible and styled properly
        function ensureNavigationArrows() {
            // Find the navigation buttons
            const prevButton = document.querySelector('.litepicker .button-previous-month');
            const nextButton = document.querySelector('.litepicker .button-next-month');
            
            if (prevButton && nextButton) {
                // Make sure they're visible
                prevButton.style.display = 'block';
                nextButton.style.display = 'block';
                
                // Add some styling to make them more prominent
                [prevButton, nextButton].forEach(btn => {
                    btn.style.padding = '8px';
                    btn.style.cursor = 'pointer';
                    btn.style.color = 'var(--thm-primary)';
                    btn.style.fontSize = '20px';
                    btn.style.fontWeight = 'bold';
                    btn.style.backgroundColor = 'rgba(var(--thm-primary-rgb), 0.1)';
                    btn.style.borderRadius = '50%';
                    btn.style.width = '36px';
                    btn.style.height = '36px';
                    btn.style.display = 'flex';
                    btn.style.alignItems = 'center';
                    btn.style.justifyContent = 'center';
                    btn.style.margin = '0 10px';
                });
            } else {
                // If buttons aren't found, try again after a short delay
                setTimeout(ensureNavigationArrows, 300);
            }
        }

        // Call the function after a delay to ensure the calendar is rendered
        setTimeout(ensureNavigationArrows, 500);
        
        // Add additional CSS to improve mobile display
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            @media (max-width: 767px) {
                .litepicker {
                    max-width: 100%;
                    font-size: 0.9rem;
                }
                
                .litepicker .container__days .day-item {
                    height: 36px;
                    width: 36px;
                    line-height: 36px;
                }
                
                .date-tag {
                    font-size: 12px;
                    padding: 4px 10px;
                }
                
                .selected-dates-tags {
                    flex-direction: column;
                    align-items: center;
                }
                
                .litepicker .container__months {
                    width: 100%;
                }
                
                .litepicker .container__months .month-item {
                    width: 100%;
                }
                
                .litepicker .container__months .month-item-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px 5px;
                }
                
                .litepicker .container__months .month-item-header div {
                    flex: 1;
                    text-align: center;
                }
                
                .litepicker .button-previous-month,
                .litepicker .button-next-month {
                    visibility: visible !important;
                    opacity: 1 !important;
                    display: block !important;
                }
            }
        `;
        document.head.appendChild(styleElement);
        
        // Add a mutation observer to detect DOM changes in the calendar
        const observer = new MutationObserver(() => {
            setTimeout(() => {
                highlightSelectedDates();
                highlightBookingDate();
                ensureNavigationArrows();
            }, 200);
        });

        // Start observing the calendar container for DOM changes
        observer.observe(datepickerElement, { 
            childList: true, 
            subtree: true 
        });
        
        // Initialize the selected dates display
        updateSelectedDates();
        
        // Add a manual click handler to the calendar days as a fallback
        datepickerElement.addEventListener('click', function(e) {
            // Only process clicks on day items
            if (e.target.classList.contains('day-item') && !e.target.classList.contains('is-locked')) {
                const clickedDate = e.target.getAttribute('data-time');
                if (clickedDate) {
                    // Create date from timestamp and adjust for timezone issues
                    const timestamp = parseInt(clickedDate);
                    const date = new Date(timestamp);
                    
                    // Format the date directly without using toISOString to avoid timezone issues
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const formattedDate = `${year}-${month}-${day}`;
                    
                    // Check if date is already selected
                    if (!selectedDates.includes(formattedDate)) {
                        // Limit to 5 alternative dates
                        if (selectedDates.length < 5) {
                            selectedDates.push(formattedDate);
                            updateSelectedDates();
                            highlightSelectedDates();
                            validateForm();
                        } else {
                            // Show notification instead of alert
                            showNotification("{{__('guidings.Max_Three_Dates')}}", 'warning');
                            
                            // Add white background styling to the clicked element when max is reached
                            e.target.style.backgroundColor = 'white';
                            e.target.style.color = 'var(--thm-primary)';
                            e.target.style.border = '1px solid rgba(var(--thm-primary-rgb), 0.3)';
                            
                            // Reset the styling after a short delay
                            setTimeout(() => {
                                e.target.style.backgroundColor = '';
                                e.target.style.color = '';
                                e.target.style.border = '';
                            }, 500);
                        }
                    } else {
                        // If date is already selected, remove it
                        selectedDates = selectedDates.filter(d => d !== formattedDate);
                        updateSelectedDates();
                        highlightSelectedDates();
                        validateForm();
                    }
                }
            }
            // Don't do anything for clicks outside day items
        });
        
        // Add event listeners to ensure highlights persist
        document.addEventListener('click', function(e) {
            // Use setTimeout to ensure this runs after any other click handlers
            setTimeout(highlightSelectedDates, 10);
        });
        
        // Initial form validation
        validateForm();
    } catch (error) {
        console.error("Error initializing calendar:", error);
    }
});
</script>

@endsection
