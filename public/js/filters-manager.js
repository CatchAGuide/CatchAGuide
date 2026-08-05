const FilterManager = {
    // Initialize filters on page load
    initFilters: function() {
        // Hide zero-count options on initial page load
        function hideZeroCountOptions(name) {
            const checkboxes = document.querySelectorAll(`input[name="${name}[]"]`);
            if (!checkboxes.length) return;

            // First pass: categorize checkboxes
            const checkedItems = [];
            const uncheckedWithCount = [];
            const uncheckedZeroCount = [];

            checkboxes.forEach(checkbox => {
                const container = checkbox.closest('.form-check');
                if (!container) return;

                const countSpan = container.querySelector('.count');
                if (!countSpan) return;

                // Extract the count from the span text (format: "(X)")
                const countText = countSpan.textContent.trim();
                const countMatch = countText.match(/\((\d+)\)/);
                if (!countMatch) return;

                const count = parseInt(countMatch[1]);

                // Categorize items
                if (checkbox.checked) {
                    checkedItems.push(container);
                } else if (count > 0) {
                    uncheckedWithCount.push(container);
                } else {
                    uncheckedZeroCount.push(container);
                }
            });

            const targetVisible = 7;
            let visibleCount = 0;

            // Show checked items first (up to the target)
            checkedItems.forEach(container => {
                if (visibleCount < targetVisible) {
                    container.classList.remove('d-none');
                    container.style.display = '';
                    visibleCount++;
                } else {
                    container.classList.add('d-none');
                    container.style.display = 'none';
                }
            });

            // Then show unchecked items with count > 0 (up to the target)
            uncheckedWithCount.forEach(container => {
                if (visibleCount < targetVisible) {
                    container.classList.remove('d-none');
                    container.style.display = '';
                    visibleCount++;
                } else {
                    container.classList.add('d-none');
                    container.style.display = 'none';
                }
            });

            // Always hide zero-count items (unless they are checked, handled above)
            uncheckedZeroCount.forEach(container => {
                container.classList.add('d-none');
                container.style.display = 'none';
                // Remove potentially conflicting classes
                container.classList.remove('show');
                container.classList.remove('extra-filter');
            });
        }

        // Initialize all filter groups - desktop version
        hideZeroCountOptions('target_fish');
        hideZeroCountOptions('water');
        hideZeroCountOptions('methods');
        // Don't hide duration options
        hideZeroCountOptions('num_persons');

        // Also initialize mobile filters if they exist
        this.initMobileFilters();

        // Initialize person filter clear buttons
        this.initPersonFilterClearButtons();

        // Initialize filter removal buttons
        this.attachFilterRemoveListeners();

        // Initialize active filters
        this.initActiveFilters();

        // Initialize person checkboxes
        this.initPersonCheckboxes();
    },

    // Add a new method for mobile filters
    initMobileFilters: function() {
        // Only run if we're on a page with mobile filters
        if (!document.getElementById('filterContainerOffCanvas')) return;

        function hideZeroCountOptionsMobile(name) {
            // Target mobile-specific checkboxes
            const checkboxes = document.querySelectorAll(`#filterContainerOffCanvas input[name="${name}[]"]`);
            if (!checkboxes.length) return;

            // First pass: categorize checkboxes
            const checkedItems = [];
            const uncheckedWithCount = [];
            const uncheckedZeroCount = [];

            checkboxes.forEach(checkbox => {
                const container = checkbox.closest('.form-check');
                if (!container) return;

                const countSpan = container.querySelector('.count');
                if (!countSpan) return;

                // Extract the count from the span text (format: "(X)")
                const countText = countSpan.textContent.trim();
                const countMatch = countText.match(/\((\d+)\)/);
                if (!countMatch) return;

                const count = parseInt(countMatch[1]);

                // Categorize items
                if (checkbox.checked) {
                    checkedItems.push(container);
                } else if (count > 0) {
                    uncheckedWithCount.push(container);
                } else {
                    uncheckedZeroCount.push(container);
                }
            });

            const targetVisible = 7;
            let visibleCount = 0;

            // Show checked items first (up to the target)
            checkedItems.forEach(container => {
                if (visibleCount < targetVisible) {
                    container.classList.remove('d-none');
                    container.style.display = '';
                    visibleCount++;
                } else {
                    container.classList.add('d-none');
                    container.style.display = 'none';
                }
            });

            // Then show unchecked items with count > 0 (up to the target)
            uncheckedWithCount.forEach(container => {
                if (visibleCount < targetVisible) {
                    container.classList.remove('d-none');
                    container.style.display = '';
                    visibleCount++;
                } else {
                    container.classList.add('d-none');
                    container.style.display = 'none';
                }
            });

            // Always hide zero-count items (unless they are checked, handled above)
            uncheckedZeroCount.forEach(container => {
                container.classList.add('d-none');
                container.style.display = 'none';
                // Remove potentially conflicting classes
                container.classList.remove('show');
                container.classList.remove('extra-filter');
            });
        }

        // Initialize all mobile filter groups
        hideZeroCountOptionsMobile('target_fish');
        hideZeroCountOptionsMobile('water');
        hideZeroCountOptionsMobile('methods');
        // Don't hide duration options
        hideZeroCountOptionsMobile('num_persons');
    },

    // Store slider instances for later updates
    sliders: {},
    histograms: {},

    // Track if any filters are active
    hasActiveFilters: function() {
        // Check for active filters in various categories
        const hasTargetFish = document.querySelectorAll('input[name="target_fish[]"]:checked').length > 0;
        const hasWaterTypes = document.querySelectorAll('input[name="water[]"]:checked').length > 0;
        const hasMethods = document.querySelectorAll('input[name="methods[]"]:checked').length > 0;
        const hasDuration = document.querySelectorAll('input[name="duration_types[]"]:checked').length > 0;
        const hasPersons = document.querySelectorAll('input[name="num_persons"]:checked').length > 0;

        // Check for location/radius filters
        const placeInput = document.querySelector('input[name="place"]');
        const radiusInput = document.querySelector('input[name="radius"]');
        const hasPlace = placeInput ? placeInput.value : null;
        const hasRadius = radiusInput ? radiusInput.value : null;

        return hasTargetFish || hasWaterTypes || hasMethods || hasDuration || hasPersons ||
            (hasPlace && hasRadius);
    },

    // Initialize price slider with histogram
    initPriceSlider: function(sliderId, minDisplayId, maxDisplayId, minInputId, maxInputId, updateCallback, histogramId) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;

        // Create label elements
        const minLabel = document.createElement('div');
        const maxLabel = document.createElement('div');
        minLabel.className = 'slider-label';
        maxLabel.className = 'slider-label';
        slider.appendChild(minLabel);
        slider.appendChild(maxLabel);

        const urlParams = new URLSearchParams(window.location.search);
        const initialMin = urlParams.get('price_min') || 50;
        const initialMax = urlParams.get('price_max') || (window.maxPrice > 1000 ? window.maxPrice : 1000);

        // Use the server-provided price data
        // const priceData = window.priceHistogramData || [];

        // Initialize histogram if ID is provided
        // if (histogramId) {
        //     const canvas = document.getElementById(histogramId);
        //     if (canvas) {
        //         // Store the original data for later updates
        //         const chartData = {
        //             labels: priceData.map(item => item.min),
        //             data: priceData
        //         };

        //         const ctx = canvas.getContext('2d');
        //         const chart = new Chart(ctx, {
        //             type: 'bar',
        //             data: {
        //                 labels: chartData.labels,
        //                 datasets: [{
        //                     data: chartData.data.map(item => item.count),
        //                     backgroundColor: '#313041 ',
        //                     borderColor: 'white',
        //                     borderWidth: 1,
        //                     barPercentage: 1,
        //                     categoryPercentage: 1
        //                 }]
        //             },
        //             options: {
        //                 responsive: true,
        //                 maintainAspectRatio: false,
        //                 plugins: {
        //                     legend: {
        //                         display: false
        //                     },
        //                     tooltip: {
        //                         callbacks: {
        //                             title: function(tooltipItems) {
        //                                 const item = tooltipItems[0];
        //                                 const min = chartData.data[item.dataIndex].min;
        //                                 const max = chartData.data[item.dataIndex].max;
        //                                 return `€${min} - €${max}`;
        //                             },
        //                             label: function(context) {
        //                                 return `${context.raw} guidings`;
        //                             }
        //                         }
        //                     }
        //                 },
        //                 scales: {
        //                     x: {
        //                         display: false
        //                     },
        //                     y: {
        //                         display: false
        //                     }
        //                 }
        //             }
        //         });
        //     }
        // }

        // Add custom CSS for smaller, rounded slider and positioned labels
        const sliderStyles = `
            #${sliderId} .noUi-connect {
                background: #E85B40;
                border-radius: 4px;
            }
            #${sliderId}.noUi-horizontal {
                height: 6px;
            }
            #${sliderId} .noUi-handle {
                width: 16px;
                height: 16px;
                border-radius: 50%;
                top: -5px;
                right: -8px;
                background: #313041;
                border: 2px solid white;
                box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            }
            #${sliderId} .noUi-handle:before, 
            #${sliderId} .noUi-handle:after {
                display: none;
            }
            #${sliderId} .slider-label {
                position: absolute;
                top: 10px;
                transform: translateX(-50%);
                font-size: 12px;
                color: #666;
                white-space: nowrap;
            }
        `;

        // Add the styles to the document
        const styleElement = document.createElement('style');
        styleElement.textContent = sliderStyles;
        document.head.appendChild(styleElement);

        // Determine the max range value for the slider
        const maxRangeValue = window.maxPrice > 1000 ? window.maxPrice : 1000;

        // Create the slider
        noUiSlider.create(slider, {
            start: [parseInt(initialMin), parseInt(initialMax)],
            connect: true,
            step: 50,
            range: {
                'min': 50,
                'max': maxRangeValue
            },
            format: {
                to: value => Math.round(value),
                from: value => Number(value)
            }
        });

        // Store the slider instance for later updates
        this.sliders[sliderId] = slider.noUiSlider;

        const minDisplay = document.getElementById(minDisplayId);
        const maxDisplay = document.getElementById(maxDisplayId);
        const minInput = document.getElementById(minInputId);
        const maxInput = document.getElementById(maxInputId);

        slider.noUiSlider.on('update', function(values, handle) {
            const value = values[handle];
            if (handle === 0) {
                minInput.value = value;
                minLabel.textContent = `€${numberWithCommas(value)}`;
                minLabel.style.left = `${slider.noUiSlider.get()[0] / slider.noUiSlider.options.range.max * 100}%`;
            } else {
                maxInput.value = value;
                maxLabel.textContent = `€${numberWithCommas(value)}`;
                maxLabel.style.left = `${slider.noUiSlider.get()[1] / slider.noUiSlider.options.range.max * 100}%`;
            }

            // Update histogram if available
            if (histogramId && FilterManager.histograms && FilterManager.histograms[histogramId]) {
                const minPrice = parseInt(values[0]);
                const maxPrice = parseInt(values[1]);
                const histogram = FilterManager.histograms[histogramId];

                // Filter the data to only include items within the current price range
                const filteredData = histogram.data.map(item => {
                    return (item.min >= minPrice && item.max <= maxPrice) ? item.count : 0;
                });

                // Update the histogram with filtered data
                histogram.chart.data.datasets[0].data = filteredData;

                // Update the x-axis to reflect the current price range
                histogram.chart.options.scales.x.min = minPrice;
                histogram.chart.options.scales.x.max = maxPrice;

                // Add labels below the graph
                histogram.chart.options.plugins.tooltip.callbacks.footer = function(tooltipItems) {
                    const item = tooltipItems[0];
                    const min = histogram.data[item.dataIndex].min;
                    const max = histogram.data[item.dataIndex].max;
                    return `Range: €${min} - €${max}`;
                };

                histogram.chart.update();
            }
        });

        slider.noUiSlider.on('end', updateCallback);

        return slider.noUiSlider;
    },

    // Handle duration unit switching
    initDurationSwitch: function(switchId, textId, inputId) {
        const durationSwitch = document.getElementById(switchId);
        const durationText = document.getElementById(textId);
        const durationInput = document.getElementById(inputId);

        if (!durationSwitch || !durationText || !durationInput) return;

        durationSwitch.addEventListener('change', function() {
            const isHours = !this.checked;
            durationText.textContent = isHours ? 'Hours' : 'Days';

            if (durationInput.value) {
                if (isHours) {
                    durationInput.value = Math.round(parseFloat(durationInput.value) * 24);
                } else {
                    durationInput.value = Math.round(parseFloat(durationInput.value) / 24);
                }
            }

            durationInput.min = isHours ? '1' : '0.5';
            durationInput.step = isHours ? '1' : '0.5';
        });
    },

    // Initialize see more/less functionality
    initSeeMoreButtons: function() {
        document.querySelectorAll('.see-more').forEach(button => {
            button.addEventListener('click', (event) => {
                const checkboxGroup = event.currentTarget.closest('.checkbox-group');
                const isExpanded = event.currentTarget.textContent.includes('Less');

                // Get all form-check items in this group
                const allFilters = checkboxGroup.querySelectorAll('.form-check');

                // Categorize items
                const checkedItems = [];
                const uncheckedWithCount = [];
                const zeroCountItems = [];

                allFilters.forEach(filter => {
                    const checkbox = filter.querySelector('input[type="checkbox"]');
                    const countSpan = filter.querySelector('.count');

                    if (!checkbox || !countSpan) return;

                    const countText = countSpan.textContent.trim();
                    const countMatch = countText.match(/\((\d+)\)/);
                    const count = countMatch ? parseInt(countMatch[1]) : 0;

                    if (checkbox.checked) {
                        checkedItems.push(filter);
                    } else if (count > 0) {
                        uncheckedWithCount.push(filter);
                    } else {
                        zeroCountItems.push(filter);
                    }
                });

                if (isExpanded) {
                    // Collapsing - show only first 7 items with priority
                    let visibleCount = 0;
                    const maxVisible = 7;

                    // Show checked items first
                    checkedItems.forEach(filter => {
                        if (visibleCount < maxVisible) {
                            filter.classList.remove('d-none');
                            filter.style.display = '';
                            visibleCount++;
                        } else {
                            filter.classList.add('d-none');
                            filter.style.display = 'none';
                        }
                    });

                    // Then show unchecked items with count > 0
                    uncheckedWithCount.forEach(filter => {
                        if (visibleCount < maxVisible) {
                            filter.classList.remove('d-none');
                            filter.style.display = '';
                            visibleCount++;
                        } else {
                            filter.classList.add('d-none');
                            filter.style.display = 'none';
                        }
                    });

                    // Always hide zero count items
                    zeroCountItems.forEach(filter => {
                        filter.classList.add('d-none');
                        filter.style.display = 'none';
                    });

                } else {
                    // Expanding - show all items except those with zero count
                    checkedItems.forEach(filter => {
                        filter.classList.remove('d-none');
                        filter.style.display = '';
                    });

                    uncheckedWithCount.forEach(filter => {
                        filter.classList.remove('d-none');
                        filter.style.display = '';
                    });

                    // Keep zero count items hidden
                    zeroCountItems.forEach(filter => {
                        filter.classList.add('d-none');
                        filter.style.display = 'none';
                    });
                }

                // Update button text
                event.currentTarget.textContent = isExpanded ? 'See More' : 'See Less';

                // Check button visibility
                this.checkButtonVisibility(event.currentTarget);
            });

            // Initial visibility check
            this.checkButtonVisibility(button);
        });
    },

    // New method to check button visibility
    checkButtonVisibility: function(button) {
        if (!button) return;

        const checkboxGroup = button.closest('.checkbox-group');
        if (!checkboxGroup) return;

        const allItems = checkboxGroup.querySelectorAll('.form-check');
        const visibleItems = Array.from(allItems).filter(item => {
            // Count only items that are checked or have a non-zero count
            const checkbox = item.querySelector('input[type="checkbox"]');
            const countSpan = item.querySelector('.count');

            if (!checkbox || !countSpan) return false;

            const isChecked = checkbox.checked;
            const countText = countSpan.textContent.trim();
            const countMatch = countText.match(/\((\d+)\)/);
            const count = countMatch ? parseInt(countMatch[1]) : 0;

            return isChecked || count > 0;
        });

        // If there are 7 or fewer visible items, hide the button
        if (visibleItems.length <= 7) {
            button.style.display = 'none';
        } else {
            button.style.display = '';
        }
    },

    // Handle increment/decrement for number inputs
    incrementValue: function(id) {
        const input = document.getElementById(id);
        const newValue = parseInt(input.value || 0) + 1;
        input.value = newValue;
        input.dispatchEvent(new Event('change'));
    },

    decrementValue: function(id) {
        const input = document.getElementById(id);
        const minValue = parseFloat(input.min || 1);
        const currentValue = parseInt(input.value || 0);
        if (currentValue > minValue) {
            input.value = currentValue - 1;
            input.dispatchEvent(new Event('change'));
        }
    },

    // Update price histogram and slider
    updatePriceHistogram: function(histogramData, maxPrice) {
        // Update global variables
        // window.priceHistogramData = histogramData;

        // Check if any filters are active and store globally
        window.hasFilters = this.hasActiveFilters();

        // If no filters are active, reset to default max price
        // if (!window.hasFilters) {
        //     window.maxPrice = 4000; // Default max price
        // } else {
        // window.maxPrice = maxPrice;
        // }
        window.maxPrice = maxPrice > 1000 ? maxPrice : 1000;

        // Update all histograms
        Object.keys(this.histograms).forEach(histogramId => {
            const histogram = this.histograms[histogramId];
            const chart = histogram.chart;

            // Update the data
            histogram.data = histogramData;
            chart.data.labels = histogramData.map(item => item.min);
            chart.data.datasets[0].data = histogramData.map(item => item.count);

            // Update the chart
            chart.update();
        });

        // Update all sliders
        Object.keys(this.sliders).forEach(sliderId => {
            const slider = this.sliders[sliderId];
            const currentValues = slider.get();

            // Get the appropriate max value
            // const newMaxPrice = window.hasFilters ? maxPrice : 4000;
            const newMaxPrice = window.maxPrice > 1000 ? window.maxPrice : 1000;

            // Only update the max range if it's different
            if (slider.options.range.max !== newMaxPrice) {
                // Update the slider range
                slider.updateOptions({
                    range: {
                        'min': 50,
                        'max': newMaxPrice
                    }
                }, false); // Don't fire events

                // If current max value is higher than new max, adjust it
                if (parseInt(currentValues[1]) > newMaxPrice) {
                    slider.set([currentValues[0], newMaxPrice]);
                } else {
                    // Otherwise keep current values
                    slider.set(currentValues);
                }
            }
        });
    },

    // Update filters based on results
    updateFilters: function(data) {
        if (!data.filterCounts) {
            console.error('No filter counts data received');
            return;
        }

        this.updateFilterCounts('target_fish', data.filterCounts.targetFish || {})
        this.updateFilterCounts('water', data.filterCounts.waters || {});
        this.updateFilterCounts('methods', data.filterCounts.methods || {});
        this.updateDurationCounts(data.filterCounts.durations || {});
        this.updatePersonCounts(data.filterCounts.persons || {});

        // Update price histogram and slider if data is available
        // if (data.priceHistogramData) {
        //     this.updatePriceHistogram(data.priceHistogramData, data.maxPrice || window.maxPrice > 4000 ? window.maxPrice : 4000);
        // }

        // Refresh filter visibility without closing modals
        this.refreshFilterVisibility();

        document.querySelectorAll('.see-more').forEach(button => {
            this.checkButtonVisibility(button);
        });

        // Call updateActiveFilterCounter after updating filters
        if (typeof updateActiveFilterCounter === 'function') {
            updateActiveFilterCounter();
        }
    },

    // New method to refresh filter visibility without simulating clicks
    refreshFilterVisibility: function() {
        // Process each filter group
        const filterGroups = ['target_fish', 'water', 'methods', 'num_persons'];

        filterGroups.forEach(filterName => {
            // Process desktop filters
            this.refreshFilterGroupVisibility(filterName, false);

            // Process mobile filters
            this.refreshFilterGroupVisibility(filterName, true);
        });
    },

    // Helper method to refresh a specific filter group's visibility
    refreshFilterGroupVisibility: function(filterName, isMobile) {
        const selector = isMobile ?
            `#filterContainerOffCanvas input[name="${filterName}[]"]` :
            `#filterContainer input[name="${filterName}[]"]`;

        const checkboxes = document.querySelectorAll(selector);
        if (!checkboxes.length) return;

        const checkboxGroup = checkboxes[0] ? checkboxes[0].closest('.checkbox-group') : null;
        if (!checkboxGroup) return;

        // Categorize items
        const checkedItems = [];
        const uncheckedWithCount = [];
        const uncheckedZeroCount = [];

        checkboxes.forEach(checkbox => {
            const container = checkbox.closest('.form-check');
            if (!container) return;

            const countSpan = container.querySelector('.count');
            if (!countSpan) return;

            const countText = countSpan.textContent.trim();
            const countMatch = countText.match(/\((\d+)\)/);
            if (!countMatch) return;

            const count = parseInt(countMatch[1]);

            // Categorize items
            if (checkbox.checked) {
                checkedItems.push(container);
            } else if (count > 0) {
                uncheckedWithCount.push(container);
            } else {
                uncheckedZeroCount.push(container);
            }
        });

        const seeMoreButton = checkboxGroup.querySelector('.see-more');
        const isExpanded = seeMoreButton ? seeMoreButton.textContent.includes('Less') : false;

        // Apply visibility based on current state
        if (isExpanded) {
            // If expanded, show all items with count > 0
            checkedItems.forEach(container => {
                container.classList.remove('d-none');
                container.style.display = '';
            });

            uncheckedWithCount.forEach(container => {
                container.classList.remove('d-none');
                container.style.display = '';
            });

            // Keep zero count items hidden
            uncheckedZeroCount.forEach(container => {
                container.classList.add('d-none');
                container.style.display = 'none';
            });
        } else {
            // If collapsed, show only first 7 items with priority
            const maxVisible = 7;
            let visibleCount = 0;

            // Show checked items first
            checkedItems.forEach(container => {
                if (visibleCount < maxVisible) {
                    container.classList.remove('d-none');
                    container.style.display = '';
                    visibleCount++;
                } else {
                    container.classList.add('d-none');
                    container.style.display = 'none';
                }
            });

            // Then show unchecked items with count > 0
            uncheckedWithCount.forEach(container => {
                if (visibleCount < maxVisible) {
                    container.classList.remove('d-none');
                    container.style.display = '';
                    visibleCount++;
                } else {
                    container.classList.add('d-none');
                    container.style.display = 'none';
                }
            });

            // Always hide zero count items
            uncheckedZeroCount.forEach(container => {
                container.classList.add('d-none');
                container.style.display = 'none';
            });
        }

        // Update button visibility
        if (seeMoreButton) {
            const totalVisible = checkedItems.length + uncheckedWithCount.length;
            seeMoreButton.style.display = totalVisible > 7 ? '' : 'none';
        }
    },

    // Generic function to update filter counts
    updateFilterCounts: function(filterName, counts) {
        const checkboxes = document.querySelectorAll(`input[name="${filterName}[]"]`);
        if (!checkboxes.length) return;

        // First pass: update counts and collect items
        const checkedItems = [];
        const uncheckedItemsWithCount = [];
        const uncheckedItemsZeroCount = [];

        checkboxes.forEach(checkbox => {
            const container = checkbox.closest('.form-check');
            if (!container) return;

            const countSpan = container.querySelector('.count');
            if (!countSpan) return;

            const id = checkbox.value;
            const count = counts[id] || 0;
            const isChecked = checkbox.checked;

            // Update the count display
            countSpan.textContent = `(${count})`;

            // Categorize items
            if (isChecked) {
                checkedItems.push(container);
            } else if (count > 0) {
                uncheckedItemsWithCount.push(container);
            } else {
                uncheckedItemsZeroCount.push(container);
            }
        });

        // Second pass: show/hide items based on priority
        const maxVisible = 7;
        let visibleCount = 0;

        // Always show checked items first
        checkedItems.forEach(container => {
            container.classList.remove('d-none', 'extra-filter');
            container.style.display = '';
            visibleCount++;
        });

        // Then show unchecked items with count > 0, up to maxVisible
        uncheckedItemsWithCount.forEach(container => {
            if (visibleCount < maxVisible) {
                container.classList.remove('d-none', 'extra-filter');
                container.style.display = '';
                visibleCount++;
            } else {
                container.classList.add('extra-filter');
                container.classList.remove('d-none');
                container.style.display = 'none';
            }
        });

        // Hide zero-count unchecked items
        uncheckedItemsZeroCount.forEach(container => {
            container.classList.add('d-none');
            container.style.display = 'none';
        });

        // Find and update the see more button for this filter group
        const checkboxGroup = checkboxes[0].closest('.checkbox-group');
        if (checkboxGroup) {
            const seeMoreButton = checkboxGroup.querySelector('.see-more');
            if (seeMoreButton) {
                // Show button only if there are more than maxVisible items with count > 0 or checked
                const totalVisible = checkedItems.length + uncheckedItemsWithCount.length;
                if (totalVisible <= maxVisible) {
                    seeMoreButton.style.display = 'none';
                } else {
                    seeMoreButton.style.display = '';
                }
            }
        }
    },

    // Special handling for duration counts
    updateDurationCounts: function(counts) {
        // Map duration types to their checkbox values
        const durationMap = {
            'half_day': 'half_day',
            'full_day': 'full_day',
            'multi_day': 'multi_day'
        };

        // First pass: update counts and collect items
        const checkedItems = [];
        const uncheckedItemsWithCount = [];
        const uncheckedItemsZeroCount = [];

        for (const [durationType, value] of Object.entries(durationMap)) {
            const count = counts[durationType] || 0;
            const checkboxes = document.querySelectorAll(`input[name="duration_types[]"][value="${value}"]`);

            checkboxes.forEach(checkbox => {
                const container = checkbox.closest('.form-check');
                if (!container) return;

                const countSpan = container.querySelector('.count');
                if (!countSpan) return;

                // Update the count display
                countSpan.textContent = `(${count})`;

                // Categorize items
                if (checkbox.checked) {
                    checkedItems.push(container);
                } else if (count > 0) {
                    uncheckedItemsWithCount.push(container);
                } else {
                    uncheckedItemsZeroCount.push(container);
                }
            });
        }

        // Second pass: show/hide items based on priority
        // Always show checked items
        checkedItems.forEach(container => {
            container.classList.remove('d-none');
            container.style.display = '';
        });

        // Show unchecked items with count > 0
        uncheckedItemsWithCount.forEach(container => {
            container.classList.remove('d-none');
            container.style.display = '';
        });

        // Hide zero-count unchecked items
        uncheckedItemsZeroCount.forEach(container => {
            container.classList.add('d-none');
            container.style.display = 'none';
        });
    },

    // Special handling for person counts
    updatePersonCounts: function(counts) {
        // Get all person radio buttons
        // Handle both desktop and mobile person checkbox groups
        const personCheckboxGroups = [
            document.querySelector('#person-checkbox-group'),
            document.querySelector('#mobile-person-checkbox-group')
        ];

        personCheckboxGroups.forEach(personCheckboxGroup => {
            if (!personCheckboxGroup) return;

            // Process each possible person count (1-8)
            for (let i = 1; i <= 8; i++) {
                const count = counts[i] || 0;
                const isMobile = personCheckboxGroup.id === 'mobile-person-checkbox-group';
                const inputId = isMobile ? `mobile_persons_${i}` : `persons_${i}`;
                const inputClass = isMobile ? 'form-check-input mobile-filter-checkbox' : 'form-check-input filter-checkbox';

                let container = personCheckboxGroup.querySelector(`.form-check input[value="${i}"]`) ?
                    personCheckboxGroup.querySelector(`.form-check input[value="${i}"]`).closest('.form-check') : null;

                if (!container) {
                    // Create new form-check div if it doesn't exist
                    container = document.createElement('div');
                    container.className = 'form-check';

                    // Create checkbox input
                    const input = document.createElement('input');
                    input.type = 'checkbox';
                    input.className = inputClass;
                    input.name = 'num_persons';
                    input.id = inputId;
                    input.value = i;

                    // Create label
                    const label = document.createElement('label');
                    label.className = isMobile ? 'form-check-label d-flex justify-content-between' : 'form-check-label';
                    label.htmlFor = inputId;

                    if (isMobile) {
                        label.innerHTML = `Up to ${i} person${i > 1 ? 's' : ''} <span class="count">(${count})</span>`;
                    } else {
                        label.innerHTML = `Up to ${i} person${i > 1 ? 's' : ''} <span class="count">(${count})</span>`;
                    }

                    container.appendChild(input);
                    container.appendChild(label);
                    personCheckboxGroup.appendChild(container);
                } else {
                    // Update existing count span
                    const countSpan = container.querySelector('.count');
                    if (countSpan) {
                        countSpan.textContent = `(${count})`;
                    }
                }

                // Show/hide based on count
                const checkbox = container.querySelector('input[type="checkbox"]');
                if (count > 0 || (checkbox && checkbox.checked)) {
                    container.classList.remove('d-none');
                    container.style.display = '';
                } else {
                    container.classList.add('d-none');
                    container.style.display = 'none';
                }
            }
        });
    },

    // Add new method to handle map marker updates
    updateMapMarkers: function(data) {
        if (typeof updateMapMarkers === 'function' && data.allGuidings) {
            // Convert guidings data to marker format
            const markers = data.allGuidings.map(guiding => ({
                id: guiding.id,
                lat: parseFloat(guiding.lat),
                lng: parseFloat(guiding.lng),
                title: guiding.title,
                price: guiding.price,
                location: guiding.location,
                slug: guiding.slug,
                thumbnail: guiding.thumbnail_path
            })).filter(marker => marker.lat && marker.lng);

            // Call the existing map update function
            updateMapMarkers(markers);
        }
    },

    // Add loading overlay functionality
    showLoadingOverlay: function() {
        // Check if overlay already exists
        let overlay = document.getElementById('filter-loading-overlay');

        if (!overlay) {
            // Create overlay if it doesn't exist
            overlay = document.createElement('div');
            overlay.id = 'filter-loading-overlay';
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
            overlay.style.zIndex = '9999';
            overlay.style.display = 'flex';
            overlay.style.justifyContent = 'center';
            overlay.style.alignItems = 'center';

            // Create spinner
            const spinner = document.createElement('div');
            spinner.className = 'spinner-border text-primary';
            spinner.setAttribute('role', 'status');
            spinner.style.width = '3rem';
            spinner.style.height = '3rem';

            // Create loading text
            const loadingText = document.createElement('span');
            loadingText.className = 'ms-3 fw-bold';
            // loadingText.textContent = 'Loading...';

            // Create container for spinner and text
            const container = document.createElement('div');
            container.style.display = 'flex';
            container.style.alignItems = 'center';
            container.appendChild(spinner);
            container.appendChild(loadingText);

            overlay.appendChild(container);
            document.body.appendChild(overlay);
        } else {
            overlay.style.display = 'flex';
        }
    },

    hideLoadingOverlay: function() {
        const overlay = document.getElementById('filter-loading-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    },

    /**
     * Refresh sidebar/mobile filter counts after AJAX filter requests.
     * Must not throw — callers remap the map after this.
     */
    updateFilters: function(data) {
        if (!data || !data.filterCounts) {
            return;
        }

        const countMaps = {
            'target_fish[]': data.filterCounts.targetFish || {},
            'methods[]': data.filterCounts.methods || {},
            'water[]': data.filterCounts.waters || {},
            'duration_types[]': data.filterCounts.durations || {},
            'num_persons': data.filterCounts.persons || {},
            'num_persons[]': data.filterCounts.persons || {},
        };

        Object.keys(countMaps).forEach((inputName) => {
            const counts = countMaps[inputName];
            document.querySelectorAll(`input[name="${inputName}"]`).forEach((input) => {
                const container = input.closest('.form-check');
                if (!container) {
                    return;
                }

                const count = parseInt(counts[input.value] ?? counts[String(input.value)] ?? 0, 10) || 0;
                const countSpan = container.querySelector('.count');
                if (countSpan) {
                    countSpan.textContent = `(${count})`;
                }

                if (count === 0 && !input.checked) {
                    container.classList.add('d-none');
                    container.style.display = 'none';
                } else if (count > 0 && !container.classList.contains('extra-filter')) {
                    container.classList.remove('d-none');
                    container.style.display = '';
                }
            });
        });
    },

    // Initialize person filter clear buttons
    initPersonFilterClearButtons: function() {
        // Desktop version
        const clearPersonFilterBtn = document.getElementById('clearPersonFilter');
        if (clearPersonFilterBtn) {
            clearPersonFilterBtn.addEventListener('click', function() {
                // Uncheck all person radio buttons
                document.querySelectorAll('.person-radio').forEach(radio => {
                    radio.checked = false;
                });

                // Update results
                if (typeof updateResults === 'function') {
                    updateResults();
                }
            });
        }

        // Mobile version
        const clearPersonFilterMobileBtn = document.getElementById('clearPersonFilterMobile');
        if (clearPersonFilterMobileBtn) {
            clearPersonFilterMobileBtn.addEventListener('click', function() {
                // Uncheck all person radio buttons
                document.querySelectorAll('.person-radio-mobile').forEach(radio => {
                    radio.checked = false;
                });

                // Update results
                if (typeof updateResults === 'function') {
                    updateResults();
                }
            });
        }
    },

    // Add this new method to handle the person checkbox behavior
    initPersonCheckboxes: function() {
        // For desktop version
        const personCheckboxes = document.querySelectorAll('#person-checkbox-group input[type="checkbox"]');
        personCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                // Prevent the default form submission behavior
                e.preventDefault();

                if (this.checked) {
                    // Uncheck all other checkboxes in the group
                    personCheckboxes.forEach(otherCheckbox => {
                        if (otherCheckbox !== this) {
                            otherCheckbox.checked = false;
                        }
                    });

                    // Create a new URL with the current parameters
                    const currentUrl = new URL(window.location.href);
                    const params = new URLSearchParams(currentUrl.search);

                    // Set the num_persons parameter
                    params.delete('num_persons');
                    params.append('num_persons', this.value);

                    // Update the URL and navigate to it
                    currentUrl.search = params.toString();
                    window.location.href = currentUrl.toString();
                } else {
                    // If unchecked, remove the parameter
                    const currentUrl = new URL(window.location.href);
                    const params = new URLSearchParams(currentUrl.search);

                    // Remove the num_persons parameter
                    params.delete('num_persons');

                    // Update the URL and navigate to it
                    currentUrl.search = params.toString();
                    window.location.href = currentUrl.toString();
                }
            });
        });

        // For mobile version
        const mobilePersonCheckboxes = document.querySelectorAll('#mobile-person-checkbox-group input[type="checkbox"]');
        mobilePersonCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                // Prevent the default form submission behavior
                e.preventDefault();

                if (this.checked) {
                    // Uncheck all other checkboxes in the group
                    mobilePersonCheckboxes.forEach(otherCheckbox => {
                        if (otherCheckbox !== this) {
                            otherCheckbox.checked = false;
                        }
                    });

                    // Create a new URL with the current parameters
                    const currentUrl = new URL(window.location.href);
                    const params = new URLSearchParams(currentUrl.search);

                    // Set the num_persons parameter
                    params.delete('num_persons');
                    params.append('num_persons', this.value);

                    // Update the URL and navigate to it
                    currentUrl.search = params.toString();
                    window.location.href = currentUrl.toString();
                } else {
                    // If unchecked, remove the parameter
                    const currentUrl = new URL(window.location.href);
                    const params = new URLSearchParams(currentUrl.search);

                    // Remove the num_persons parameter
                    params.delete('num_persons');

                    // Update the URL and navigate to it
                    currentUrl.search = params.toString();
                    window.location.href = currentUrl.toString();
                }
            });
        });
    },

    // Add a method to handle active filters
    initActiveFilters: function() {
        // Get all active filter badges
        const activeFilters = document.querySelectorAll('.active-filters .badge');

        // Add click event listeners to each badge's close button
        activeFilters.forEach(badge => {
            const closeButton = badge.querySelector('.btn-close');
            if (closeButton) {
                // Remove any existing event listeners first to prevent duplicates
                closeButton.removeEventListener('click', this.handleFilterRemoval);
                // Add the event listener
                closeButton.addEventListener('click', this.handleFilterRemoval);
            }
        });
    },

    // Handler function for filter removal
    handleFilterRemoval: function() {
        const filterType = this.dataset.filterType;
        const filterId = this.dataset.filterId;

        // Special handling for price range filter
        if (filterType === 'price_range') {
            // Reset price slider to default values
            Object.keys(FilterManager.sliders).forEach(sliderId => {
                const slider = FilterManager.sliders[sliderId];
                slider.set([50, window.maxPrice > 1000 ? window.maxPrice : 1000]); // Reset to default min and max values
            });

            // Reset hidden input fields
            const minInputs = document.querySelectorAll('input[name="price_min"], input[name="price_min_mobile"]');
            const maxInputs = document.querySelectorAll('input[name="price_max"], input[name="price_max_mobile"]');

            minInputs.forEach(input => input.value = 50);
            maxInputs.forEach(input => input.value = window.maxPrice > 1000 ? window.maxPrice : 1000);

            // Create a new URL with the current parameters
            const currentUrl = new URL(window.location.href);
            const params = new URLSearchParams(currentUrl.search);

            // Remove price parameters
            params.delete('price_min');
            params.delete('price_max');

            // Update the URL and navigate to it
            currentUrl.search = params.toString();
            window.location.href = currentUrl.toString();
            return;
        }

        // Special handling for num_persons filter
        if (filterType === 'num_persons') {
            // Find and uncheck the corresponding checkbox in both desktop and mobile filter panels
            const desktopCheckboxes = document.querySelectorAll('#person-checkbox-group input[type="checkbox"]');
            const mobileCheckboxes = document.querySelectorAll('#mobile-person-checkbox-group input[type="checkbox"]');

            // Uncheck only the checkbox that matches the filter ID
            desktopCheckboxes.forEach(checkbox => {
                if (checkbox.value === filterId) {
                    checkbox.checked = false;
                }
            });

            mobileCheckboxes.forEach(checkbox => {
                if (checkbox.value === filterId) {
                    checkbox.checked = false;
                }
            });
        } else {
            // Regular filter handling for other filter types
            const desktopCheckbox = document.querySelector(`#filterContainer input[name="${filterType}[]"][value="${filterId}"]`);
            const mobileCheckbox = document.querySelector(`#filterContainerOffCanvas input[name="${filterType}[]"][value="${filterId}"]`);

            // Uncheck only the specific checkbox that matches this filter
            if (desktopCheckbox) {
                desktopCheckbox.checked = false;
            }

            if (mobileCheckbox) {
                mobileCheckbox.checked = false;
            }
        }

        // Remove only the specific filter tag that was clicked
        document.querySelectorAll(`.active-filters .badge`).forEach(badge => {
            const badgeButton = badge.querySelector('.btn-close');
            if (badgeButton &&
                badgeButton.dataset.filterType === filterType &&
                badgeButton.dataset.filterId === filterId) {
                badge.remove();
            }
        });

        // Create a new URL with the current parameters
        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);

        // Remove only the specific parameter
        if (filterType === 'num_persons') {
            params.delete('num_persons');
        } else {
            // For array parameters, we need to rebuild the parameter list without the removed value
            const values = params.getAll(`${filterType}[]`);
            params.delete(`${filterType}[]`);

            values.forEach(value => {
                if (value !== filterId) {
                    params.append(`${filterType}[]`, value);
                }
            });
        }

        // Update the URL and navigate to it
        currentUrl.search = params.toString();
        window.location.href = currentUrl.toString();
    },

    // Update attachFilterRemoveListeners to use the new handler
    attachFilterRemoveListeners: function() {
        document.querySelectorAll('.active-filters .btn-close').forEach(button => {
            // Remove any existing event listeners first to prevent duplicates
            button.removeEventListener('click', this.handleFilterRemoval);
            // Add the event listener
            button.addEventListener('click', this.handleFilterRemoval);
        });
    }
};

// Utility functions
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Initialize filters when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    FilterManager.initFilters();
});

window.FilterManager = FilterManager;