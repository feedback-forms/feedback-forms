// resources/js/survey-filter.js
document.addEventListener('alpine:init', () => {
    Alpine.data('surveysFilter', () => ({
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
                // Helper function to check property value regardless of type
                const checkProperty = (filterKey, propName) => {
                    if (!this.filters[filterKey]) return true; // No filter active

                    // Cast to string/number as needed for comparison
                    const filterValue = this.filters[filterKey];
                    let surveyValue = survey[propName];

                    // Handle missing properties gracefully
                    if (surveyValue === undefined || surveyValue === null) {
                        return false;
                    }

                    // Type-agnostic comparison (handles string/number mismatches)
                    return String(filterValue) === String(surveyValue);
                };

                // Track which filter excluded this survey
                let passed = true;

                // Check school year
                if (this.filters.schoolYear && !checkProperty('schoolYear', 'school_year')) {
                    passed = false;
                }

                // Check department
                if (passed && this.filters.department && !checkProperty('department', 'department')) {
                    passed = false;
                }

                // Check grade level
                if (passed && this.filters.gradeLevel && !checkProperty('gradeLevel', 'grade_level')) {
                    passed = false;
                }

                // Check class
                if (passed && this.filters.class && !checkProperty('class', 'class')) {
                    passed = false;
                }

                // Check subject
                if (passed && this.filters.subject && !checkProperty('subject', 'subject')) {
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
    }));
});