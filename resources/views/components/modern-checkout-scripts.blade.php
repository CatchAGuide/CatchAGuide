<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
function checkoutApp() {
    return {
        // Data
        guiding: null,
        guidingId: {{ $guiding->id ?? 'null' }},
        persons: {{ $persons ?? 1 }},
        selectedDate: '{{ $selectedDate ?? "" }}',
        blockedDates: [],
        selectedExtras: {},
        extraQuantities: {},
        mode: '{{ auth()->check() ? "login" : "guest" }}',
        
        // Calendar translations
        translations: {
            prev: '{{ __('checkout.calendar_prev') }}',
            next: '{{ __('checkout.calendar_next') }}',
            months: [
                '{{ __('checkout.calendar_january') }}',
                '{{ __('checkout.calendar_february') }}',
                '{{ __('checkout.calendar_march') }}',
                '{{ __('checkout.calendar_april') }}',
                '{{ __('checkout.calendar_may') }}',
                '{{ __('checkout.calendar_june') }}',
                '{{ __('checkout.calendar_july') }}',
                '{{ __('checkout.calendar_august') }}',
                '{{ __('checkout.calendar_september') }}',
                '{{ __('checkout.calendar_october') }}',
                '{{ __('checkout.calendar_november') }}',
                '{{ __('checkout.calendar_december') }}'
            ],
            weekdays: [
                '{{ __('checkout.calendar_sunday') }}',
                '{{ __('checkout.calendar_monday') }}',
                '{{ __('checkout.calendar_tuesday') }}',
                '{{ __('checkout.calendar_wednesday') }}',
                '{{ __('checkout.calendar_thursday') }}',
                '{{ __('checkout.calendar_friday') }}',
                '{{ __('checkout.calendar_saturday') }}'
            ]
        },
        form: {
            firstName: '{{ auth()->user()->firstname ?? "" }}',
            lastName: '{{ auth()->user()->lastname ?? "" }}',
            email: '{{ auth()->user()->email ?? "" }}',
            countryCode: '{{ auth()->user()->phone_country_code ?? "+49" }}',
            phone: '{{ auth()->user()->phone ?? "" }}',
            policyAccepted: false
        },
        pricing: {
            guidingPrice: 0,
            totalExtraPrice: 0,
            totalPrice: 0,
            breakdown: { extras: [] }
        },
        alerts: {
            success: '',
            error: ''
        },
        loading: false,
        submitted: false,
        isLoggedIn: {{ auth()->check() ? 'true' : 'false' }},

        // Computed
        get canSubmit() {
            const basicRequirements = this.form.firstName && 
                                     this.form.lastName && 
                                     this.form.email && 
                                     this.form.phone && 
                                     this.selectedDate &&
                                     !this.loading;
            
            // T&C only required for guest users
            if (this.isLoggedIn) {
                return basicRequirements;
            } else {
                return basicRequirements && this.form.policyAccepted;
            }
        },

                getPaymentMethodsText() {
                    if (!this.guiding?.guide?.payment_methods?.length) {
                        return '{{ __('checkout.payment_methods') }}';
                    }
                    return this.guiding.guide.payment_methods.join(', ');
                },

        getPaymentMethodClass(method) {
            const methodLower = method.toLowerCase();
            if (methodLower.includes('cash') || methodLower.includes('bar')) {
                return 'payment-cash';
            } else if (methodLower.includes('bank') || methodLower.includes('transfer')) {
                return 'payment-bank';
            } else if (methodLower.includes('paypal')) {
                return 'payment-paypal';
            }
            return '';
        },

        getPaymentMethodIcon(method) {
            const methodLower = method.toLowerCase();
            if (methodLower.includes('cash') || methodLower.includes('bar')) {
                return 'cash-icon';
            } else if (methodLower.includes('bank') || methodLower.includes('transfer')) {
                return 'bank-icon';
            } else if (methodLower.includes('paypal')) {
                return 'paypal-icon';
            }
            return 'cash-icon';
        },

        getPaymentMethodIconSrc(method) {
            const methodLower = method.toLowerCase();
            if (methodLower.includes('cash') || methodLower.includes('bar')) {
                return '{{ asset("icons/cash.svg") }}';
            } else if (methodLower.includes('bank') || methodLower.includes('transfer')) {
                return '{{ asset("icons/bank.svg") }}';
            } else if (methodLower.includes('paypal')) {
                return '{{ asset("icons/paypal.svg") }}';
            }
            return '{{ asset("icons/cash.svg") }}';
        },

                get modeText() {
                    if (this.isLoggedIn) {
                        return '{{ __('checkout.login_mode_description') }}';
                    }
                    switch(this.mode) {
                        case 'guest': return '{{ __('checkout.guest_mode_description') }}';
                        case 'register': return '{{ __('checkout.register_mode_description') }}';
                        case 'login': return '{{ __('checkout.login_mode_description') }}';
                        default: return '{{ __('checkout.guest_mode_description') }}';
                    }
                },

        // Methods
        async init() {
            // Make instance globally available for calendar clicks
            window.checkoutAppInstance = this;
            await this.loadGuiding();
            await this.loadAvailableDates();
            this.initializeExtras();
            await this.calculatePrice();
        },

        async loadGuiding() {
            try {
                const response = await axios.get(`/api/checkout/guiding/${this.guidingId}`);
                if (response.data.success) {
                    this.guiding = response.data.data;
                    this.processBlockedEvents();
                } else {
                    this.showError('Failed to load guiding details');
                }
            } catch (error) {
                console.error('Error loading guiding:', error);
                this.showError('Error loading guiding details');
            }
        },

        async loadAvailableDates() {
            try {
                const response = await axios.get(`/api/checkout/available-dates/${this.guidingId}`);
                if (response.data.success) {
                    this.initializeCalendar(response.data.data);
                }
            } catch (error) {
                console.error('Error loading available dates:', error);
            }
        },

        initializeExtras() {
            // Initialize API extras
            if (this.guiding?.extras) {
                this.guiding.extras.forEach((extra, index) => {
                    this.selectedExtras[index] = false;
                });
            }
        },

        async calculatePrice() {
            if (!this.guiding) return;

            try {
                const response = await axios.post('/api/checkout/calculate-price', {
                    guiding_id: this.guidingId,
                    persons: this.persons,
                    selected_extras: Object.values(this.selectedExtras)
                });

                if (response.data.success) {
                    this.pricing = response.data.data;
                } else {
                    // Fallback calculation if API fails
                    this.calculateFallbackPrice();
                }
            } catch (error) {
                console.error('Error calculating price:', error);
                // Fallback calculation
                this.calculateFallbackPrice();
            }
        },

                calculateFallbackPrice() {
                    // Calculate guiding price - this should be per tour, not per person
                    console.log('Fallback calculation - guiding data:', this.guiding);
                    console.log('Price type:', this.guiding.price_type);
                    console.log('Price:', this.guiding.price);
                    console.log('Persons:', this.persons);
                    
                    let guidingPrice = 0;
                    if (this.guiding.price_type == 'per_person') {
                        // If price_type is per_person, use the pricing structure
                        const prices = JSON.parse(this.guiding.prices || '[]');
                        if (prices.length > 0) {
                            for (const price of prices) {
                                if (price.person == this.persons) {
                                    guidingPrice = price.amount;
                                    break;
                                }
                            }
                            // If no exact match found, use the last price * persons
                            if (guidingPrice == 0) {
                                const lastPrice = prices[prices.length - 1];
                                guidingPrice = lastPrice.amount * this.persons;
                            }
                        } else {
                            // Fallback to price_per_person * persons
                            guidingPrice = this.guiding.price_per_person * this.persons;
                        }
                    } else {
                        // If price_type is not per_person, it's per tour (total price for the tour)
                        guidingPrice = this.guiding.price;
                    }

                    // Calculate extras total
                    let extrasTotal = 0;
                    let extrasBreakdown = [];

                    if (this.guiding.extras) {
                        this.guiding.extras.forEach((extra, index) => {
                            if (this.selectedExtras[index]) {
                                const total = extra.price * this.persons;
                                extrasTotal += total;
                                extrasBreakdown.push({
                                    name: extra.name,
                                    quantity: this.persons,
                                    total: total
                                });
                            }
                        });
                    }

                    this.pricing = {
                        guidingPrice: guidingPrice,
                        totalExtraPrice: extrasTotal,
                        totalPrice: guidingPrice + extrasTotal,
                        breakdown: { extras: extrasBreakdown }
                    };
                },

        updatePersons(newCount) {
            const maxGuests = this.guiding?.max_guest || 10;
            this.persons = Math.max(1, Math.min(maxGuests, newCount));
            this.calculatePrice();
        },

        setMode(newMode) {
            this.mode = newMode;
            if (newMode === 'login' && this.isLoggedIn) {
                // Keep current form data
            } else if (newMode === 'register') {
                this.form.policyAccepted = false;
            } else {
                this.form.policyAccepted = false;
            }
        },

        async submitBooking() {
            if (!this.canSubmit) return;

            this.loading = true;
            this.alerts.error = '';

            try {
                const response = await axios.post('/api/checkout/submit-booking', {
                    guiding_id: this.guidingId,
                    persons: this.persons,
                    selected_date: this.selectedDate,
                    selected_extras: Object.values(this.selectedExtras),
                    form_data: this.form
                });

                if (response.data.success) {
                    this.submitted = true;
                    this.showSuccess('{{ __('checkout.request_sent') }}');
                    
                    // Redirect to thank you page using the provided URL
                    if (response.data.data.redirect_url) {
                        window.location.href = response.data.data.redirect_url;
                    } else {
                        // Fallback redirect
                        setTimeout(() => {
                            window.location.href = `/modern-checkout/thank-you/${response.data.data.booking_id}`;
                        }, 2000);
                    }
                } else {
                    this.showError(response.data.message || '{{ __('checkout.booking_failed') }}');
                }
            } catch (error) {
                console.error('Error submitting booking:', error);
                if (error.response?.data?.errors) {
                    const errors = Object.values(error.response.data.errors).flat();
                    this.showError(errors.join(', '));
                } else {
                    this.showError('Error submitting booking. Please try again.');
                }
            } finally {
                this.loading = false;
            }
        },

        showSuccess(message) {
            this.alerts.success = message;
            this.alerts.error = '';
            setTimeout(() => this.alerts.success = '', 5000);
        },

        showError(message) {
            this.alerts.error = message;
            this.alerts.success = '';
            setTimeout(() => this.alerts.error = '', 5000);
        },

        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const day = date.getDate();
            const month = this.translations.months[date.getMonth()];
            const year = date.getFullYear();
            const weekday = this.translations.weekdays[date.getDay()];
            return `${weekday}, ${month} ${day}, ${year}`;
        },

        initializeCalendar(data) {
            // Simple calendar implementation
            const container = document.getElementById('calendar-container');
            if (!container) return;

            const today = new Date();
            this.currentMonth = today.getMonth();
            this.currentYear = today.getFullYear();

            // Check if current month has available dates, if not move to next month
            this.findNextAvailableMonth();

            this.renderCalendar();
        },

        findNextAvailableMonth() {
            const today = new Date();
            const currentDate = new Date(this.currentYear, this.currentMonth, 1);
            
            // Check up to 12 months ahead
            for (let i = 0; i < 12; i++) {
                const checkDate = new Date(this.currentYear, this.currentMonth + i, 1);
                const lastDay = new Date(this.currentYear, this.currentMonth + i + 1, 0);
                
                // Check if this month has any available dates
                let hasAvailableDate = false;
                for (let day = 1; day <= lastDay.getDate(); day++) {
                    const dateStr = `${checkDate.getFullYear()}-${String(checkDate.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    const isPast = new Date(dateStr) < today;
                    const isBlocked = this.isDateBlocked(dateStr);
                    
                    if (!isPast && !isBlocked) {
                        hasAvailableDate = true;
                        break;
                    }
                }
                
                if (hasAvailableDate) {
                    this.currentMonth = checkDate.getMonth();
                    this.currentYear = checkDate.getFullYear();
                    break;
                }
            }
        },

        renderCalendar() {
            const container = document.getElementById('calendar-container');
            if (!container) return;

            let calendarHTML = `
                <div class="calendar-header">
                    <button onclick="window.checkoutAppInstance.previousMonth()" class="calendar-nav">← ${this.translations.prev}</button>
                    <div class="calendar-month">${this.translations.months[this.currentMonth]} ${this.currentYear}</div>
                    <button onclick="window.checkoutAppInstance.nextMonth()" class="calendar-nav">${this.translations.next} →</button>
                </div>
                <div class="calendar-weekdays">
                    <div class="weekday">${this.translations.weekdays[0]}</div>
                    <div class="weekday">${this.translations.weekdays[1]}</div>
                    <div class="weekday">${this.translations.weekdays[2]}</div>
                    <div class="weekday">${this.translations.weekdays[3]}</div>
                    <div class="weekday">${this.translations.weekdays[4]}</div>
                    <div class="weekday">${this.translations.weekdays[5]}</div>
                    <div class="weekday">${this.translations.weekdays[6]}</div>
                </div>
                <div class="calendar-days" id="calendar-days"></div>
            `;

            container.innerHTML = calendarHTML;
            this.renderCalendarDays(this.blockedDates);
        },

        previousMonth() {
            this.currentMonth--;
            if (this.currentMonth < 0) {
                this.currentMonth = 11;
                this.currentYear--;
            }
            this.renderCalendar();
        },

        nextMonth() {
            this.currentMonth++;
            if (this.currentMonth > 11) {
                this.currentMonth = 0;
                this.currentYear++;
            }
            this.renderCalendar();
        },

        renderCalendarDays() {
            const container = document.getElementById('calendar-days');
            if (!container) return;

            const today = new Date();
            const firstDay = new Date(this.currentYear, this.currentMonth, 1);
            const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
            const startDate = firstDay.getDay();
            const daysInMonth = lastDay.getDate();

            let html = '';
            
            // Empty cells for days before month starts
            for (let i = 0; i < startDate; i++) {
                html += '<div class="h-10"></div>';
            }

            // Days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isPast = new Date(dateStr) < today;
                const isBlocked = this.isDateBlocked(dateStr);
                const isSelected = this.selectedDate === dateStr;
                const isAvailable = !isPast && !isBlocked;

                // Debug logging for selected date
                if (isSelected) {
                    console.log('Rendering selected date:', dateStr, 'selectedDate:', this.selectedDate);
                }

                let classes = 'calendar-day';
                if (isSelected) classes += ' selected';
                if (isPast) classes += ' past';
                if (isBlocked) classes += ' blocked';

                html += `
                    <button 
                        class="${classes}"
                        ${!isAvailable ? 'disabled' : ''}
                        onclick="window.checkoutAppInstance.selectDate('${dateStr}')"
                        title="${isBlocked ? 'Blocked' : (isPast ? 'Past date' : dateStr)}"
                    >
                        ${day}
                    </button>
                `;
            }

            container.innerHTML = html;
        },

        selectDate(dateStr) {
            console.log('Selecting date:', dateStr);
            this.selectedDate = dateStr;
            this.calculatePrice();
            // Re-render calendar to show selected state
            this.renderCalendar();
        },

        openLoginModal() {
            // Trigger login modal
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                const modal = new bootstrap.Modal(loginModal);
                modal.show();
            }
        },

        openRegisterModal() {
            // Trigger register modal
            const registerModal = document.getElementById('registerModal');
            if (registerModal) {
                const modal = new bootstrap.Modal(registerModal);
                modal.show();
            }
        },

        processBlockedEvents() {
            if (!this.guiding?.blocked_events) return;
            
            this.blockedDates = [];
            this.guiding.blocked_events.forEach(event => {
                const fromDate = new Date(event.from);
                const dueDate = new Date(event.due);
                
                // Create an array of all dates in the range
                for (let d = new Date(fromDate); d <= dueDate; d.setDate(d.getDate() + 1)) {
                    this.blockedDates.push(d.toISOString().split('T')[0]); // Format as YYYY-MM-DD
                }
            });
        },

        isDateBlocked(dateStr) {
            return this.blockedDates.includes(dateStr);
        },

        isDatePast(dateStr) {
            const today = new Date();
            const date = new Date(dateStr);
            today.setHours(0, 0, 0, 0);
            date.setHours(0, 0, 0, 0);
            return date < today;
        }
    }
}
</script>
