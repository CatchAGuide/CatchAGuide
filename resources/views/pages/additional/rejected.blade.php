@extends('layouts.app')

@section('title', 'Booking Form Reject')

@section('content')
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>

    <style>
        html,body {
            font-family: 'Raleway', sans-serif;
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
        }
        .thankyou-page ._body ._box {
            margin: auto;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 35px rgba(10, 10, 10, 0.12);
        }
        .thankyou-page ._body ._box h2 {
            font-size: 32px;
            font-weight: 600;
            color: var(--thm-primary);
        }
        
        /* Improved layout styles */
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--thm-primary);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(var(--thm-primary-rgb), 0.1);
        }
        
        .form-group {
            margin-bottom: 2rem;
        }
        
        .form-control {
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            padding: 12px;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: var(--thm-primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--thm-primary-rgb), 0.1);
        }
        
        .char-counter {
            font-size: 0.85rem;
            color: #777;
            margin-top: 0.5rem;
            text-align: right;
        }
        
        .char-counter.invalid {
            color: #dc3545;
        }
        
        .thm-btn {
            padding: 10px 24px;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .thm-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .validation-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }
        
        .validation-message.show {
            display: block;
        }
        
        /* Notification styles */
        .notification {
            padding: 12px 15px;
            border-radius: 6px;
            margin: 15px 0;
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        .notification.show {
            display: block;
        }
        
        .notification-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }
        
        .notification-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .notification-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Calendar styles */
        #lite-datepicker {
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }
        
        .litepicker {
            font-family: 'Raleway', sans-serif;
            font-size: 0.95rem;
            margin: 0 auto;
        }
        
        .litepicker-container {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        
        .litepicker .container__days .day-item {
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        
        .litepicker .container__days .day-item:hover {
            background-color: white;
            color: var(--thm-primary);
            border: 1px solid rgba(var(--thm-primary-rgb), 0.3);
        }
        
        .litepicker .container__days .day-item.is-locked {
            text-decoration: line-through;
            background-color: #f8f8f8;
        }
        
        .litepicker .container__days .day-item.is-today {
            color: var(--thm-primary);
            font-weight: bold;
        }
        
        .litepicker .container__days .day-item.is-in-range,
        .litepicker .container__days .day-item.is-start-date,
        .litepicker .container__days .day-item.is-end-date {
            background-color: var(--thm-primary);
            color: white;
        }
        
        .calendar-container {
            margin-bottom: 30px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        
        .calendar-title {
            margin-bottom: 15px;
            font-weight: 600;
            color: var(--thm-primary);
            font-size: 1.1rem;
        }
        
        /* Selected dates styles */
        .selected-dates-container {
            margin-top: 20px;
            text-align: center;
        }
        
        .selected-dates-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        
        .selected-dates-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .date-tag {
            display: inline-flex;
            align-items: center;
            background-color: rgba(var(--thm-primary-rgb), 0.1);
            border-radius: 20px;
            padding: 6px 12px;
            font-size: 14px;
            color: var(--thm-primary);
            border: 1px solid rgba(var(--thm-primary-rgb), 0.2);
        }
        
        .date-tag .date-text {
            margin-right: 8px;
        }
        
        .date-tag .remove-date {
            margin-left: 8px;
            cursor: pointer;
            font-size: 14px;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: rgba(var(--thm-primary-rgb), 0.1);
            transition: all 0.2s ease;
        }
        
        .date-tag .remove-date:hover {
            background-color: rgba(var(--thm-primary-rgb), 0.3);
        }
        
        /* Hidden input to store selected dates */
        #selected-dates-input {
            display: none;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .litepicker .container__days .day-item.is-selected {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            position: relative;
        }
        
        .litepicker .container__days .day-item.is-selected::after {
            content: '✓';
            position: absolute;
            top: -5px;
            right: -2px;
            font-size: 10px;
            color: white;
        }
        
        /* Add styles for the currently being selected date (like May 9 in the image) */
        .litepicker .container__days .day-item.is-start-date:not(.is-selected),
        .litepicker .container__days .day-item:hover:not(.is-selected):not(.is-locked) {
            background-color: #f0f0f0; /* Light gray highlight for the date being selected */
            color: #212529;
            border: 1px solid #e0e0e0;
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
                    
                    <form id="rejection-form" action="{{route('booking.rejection',$booking)}}" method="POST">
                        @csrf
                        
                        <!-- Calendar section -->
                        <div class="form-group">
                            <p class="section-title">@lang('guidings.available_dates')</p>
                            <div class="info-box mb-4">
                                <p class="mb-0">@lang('message.booking-reject-message-available-dates')</p>
                            </div>

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
                        
                        <div class="form-group">
                            <p class="section-title">@lang('message.booking-reject-additional-comment')</p>
                            
                            <div class="info-box mb-4">
                                <p class="mb-0">@lang('message.booking-reject-message')</p>
                            </div>
                            <textarea class="form-control" name="reason" id="rejection-reason" rows="4" placeholder="@lang('guidings.Rejection_Reason_Placeholder')"></textarea>
                            <div class="char-counter" id="char-counter">
                                <div id="reason-validation-message" class="validation-message">
                                    @lang('guidings.Min_Characters_Message') 
                                </div>0/50 @lang('guidings.Characters')</div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" id="submit-btn" class="thm-btn py-2 my-2" disabled>@lang('message.booking-submit')</button>
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
