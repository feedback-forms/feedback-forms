// resources/js/survey-filter.js
document.addEventListener('alpine:init', function() {
    Alpine.data('surveysFilter', function() {
        return {
            allSurveys: [],
            filterOptions: {
                schoolYears: [],
                departments: [],
                gradeLevels: [],
                schoolClasses: [],
                subjects: []
            },

            filters: {
                schoolYear: null,
                department: null,
                gradeLevel: null,
                class: null,
                subject: null,
                expired: false,
                running: true
            },

            get filteredSurveys() {
                const results = this.allSurveys.filter(survey => {
                    // Helper function to check relationship property value
                    const checkRelationship = (filterKey, relationshipKey) => {
                        if (!this.filters[filterKey]) return true; // No filter active

                        // Cast to string/number as needed for comparison
                        const filterValue = this.filters[filterKey];

                        // Check if the relationship exists
                        if (!survey[relationshipKey]) {
                            return false;
                        }

                        // Get the id property from the relationship object
                        const surveyValue = survey[relationshipKey].id;

                        // Type-agnostic comparison (handles string/number mismatches)
                        return String(filterValue) === String(surveyValue);
                    };

                    // Track which filter excluded this survey
                    let passed = true;

                    // Check school year (uses 'year' relationship)
                    if (this.filters.schoolYear && !checkRelationship('schoolYear', 'year')) {
                        passed = false;
                    }

                    // Check department
                    if (passed && this.filters.department && !checkRelationship('department', 'department')) {
                        passed = false;
                    }

                    // Check grade level (uses 'gradeLevel' relationship)
                    if (passed && this.filters.gradeLevel && !checkRelationship('gradeLevel', 'gradeLevel')) {
                        passed = false;
                    }

                    // Check class
                    if (passed && this.filters.class && !checkRelationship('class', 'class')) {
                        passed = false;
                    }

                    // Check subject
                    if (passed && this.filters.subject && !checkRelationship('subject', 'subject')) {
                        passed = false;
                    }

                    if (!passed) return false;

                    // Apply status filters (OR logic between them)
                    const statusFiltersActive = this.filters.expired || this.filters.running;

                    if (!statusFiltersActive) return true; // No status filters active, show all

                    // Check status filters against survey status flags
                    if (this.filters.expired && survey.isExpired) return true;
                    if (this.filters.running && survey.isRunning) return true;

                    // If status filters are active but survey doesn't match any
                    if (statusFiltersActive) {
                        return false;
                    }

                    return true;
                });

                return results;
            },

            toggleFilter(filter) {
                this.filters[filter] = !this.filters[filter];
            },

            logFilters() {
                // This function is called when filter dropdowns change
                console.log('Current filters:', this.filters);
            },

            init() {
                try {
                    // Initialize from Livewire data
                    const surveysData = this.$el.getAttribute('data-surveys');
                    const filterOptionsData = this.$el.getAttribute('data-filter-options');

                    this.allSurveys = JSON.parse(surveysData);
                    this.filterOptions = JSON.parse(filterOptionsData);

                    // Set initial filter state from Livewire
                    this.filters.expired = this.$el.getAttribute('data-expired') === 'true';
                    this.filters.running = this.$el.getAttribute('data-running') === 'true';
                } catch (error) {
                    console.error('Error initializing survey filter:', error);
                    console.log('data-surveys:', this.$el.getAttribute('data-surveys'));
                    console.log('data-filter-options:', this.$el.getAttribute('data-filter-options'));
                }
            }
        };
    });
});