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

        // Debug function to log when a filter changes
        logFilters() {
            console.log('Current filters:', JSON.stringify(this.filters));

            // If a survey exists, log its values for comparison
            if (this.allSurveys.length > 0) {
                const survey = this.allSurveys[0];
                console.log('Sample survey property values:');
                console.log('school_year:', survey.school_year);
                console.log('department:', survey.department);
                console.log('grade_level:', survey.grade_level);
                console.log('class:', survey.class);
                console.log('subject:', survey.subject);
            }
        },

        // Debug function to see which properties are available on surveys
        logSurveyProperties() {
            if (this.allSurveys.length > 0) {
                console.log('Survey properties:', Object.keys(this.allSurveys[0]));
                console.log('Sample survey:', this.allSurveys[0]);
            }
        },

        get filteredSurveys() {
            // Log filter values for debugging
            console.log('Active filters:', this.filters);
            console.log('Total surveys before filtering:', this.allSurveys.length);

            const results = this.allSurveys.filter(survey => {
                // Helper function to check property value regardless of type
                const checkProperty = (filterKey, propName) => {
                    if (!this.filters[filterKey]) return true; // No filter active

                    // Cast to string/number as needed for comparison
                    const filterValue = this.filters[filterKey];
                    let surveyValue = survey[propName];

                    // Handle missing properties gracefully
                    if (surveyValue === undefined || surveyValue === null) {
                        console.log(`Survey missing property ${propName} for filter ${filterKey}`);
                        return false;
                    }

                    // Type-agnostic comparison (handles string/number mismatches)
                    return String(filterValue) === String(surveyValue);
                };

                // Track which filter excluded this survey
                let passed = true;

                // Check school year
                if (this.filters.schoolYear && !checkProperty('schoolYear', 'school_year')) {
                    console.log(`Survey ${survey.id} excluded by school year filter: ${this.filters.schoolYear} != ${survey.school_year}`);
                    passed = false;
                }

                // Check department
                if (passed && this.filters.department && !checkProperty('department', 'department')) {
                    console.log(`Survey ${survey.id} excluded by department filter: ${this.filters.department} != ${survey.department}`);
                    passed = false;
                }

                // Check grade level
                if (passed && this.filters.gradeLevel && !checkProperty('gradeLevel', 'grade_level')) {
                    console.log(`Survey ${survey.id} excluded by grade level filter: ${this.filters.gradeLevel} != ${survey.grade_level}`);
                    passed = false;
                }

                // Check class
                if (passed && this.filters.class && !checkProperty('class', 'class')) {
                    console.log(`Survey ${survey.id} excluded by class filter: ${this.filters.class} != ${survey.class}`);
                    passed = false;
                }

                // Check subject
                if (passed && this.filters.subject && !checkProperty('subject', 'subject')) {
                    console.log(`Survey ${survey.id} excluded by subject filter: ${this.filters.subject} != ${survey.subject}`);
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
                    console.log(`Survey ${survey.id} excluded by status filters: status=${survey.statusText}, expired=${survey.isExpired}, running=${survey.isRunning}`);
                    return false;
                }

                return true;
            });

            console.log('Surveys after filtering:', results.length);
            return results;
        },

        toggleFilter(filter) {
            this.filters[filter] = !this.filters[filter];
            this.logFilters(); // Log filter changes
        },

        init() {
            try {
                // Initialize from Livewire data
                const surveysData = this.$el.getAttribute('data-surveys');
                const filterOptionsData = this.$el.getAttribute('data-filter-options');

                this.allSurveys = JSON.parse(surveysData);
                this.filterOptions = JSON.parse(filterOptionsData);

                console.log('Loaded surveys:', this.allSurveys.length);
                console.log('Loaded filter options:', Object.keys(this.filterOptions));

                // Detailed logging of the first survey and filter options
                if (this.allSurveys.length > 0) {
                    const survey = this.allSurveys[0];
                    console.log('First survey:', survey);
                    console.log('First survey ID:', survey.id);
                    console.log('School year value:', survey.school_year);
                    console.log('Type of school_year:', typeof survey.school_year);
                }

                // Log filter options values
                console.log('School year options:', this.filterOptions.schoolYears);

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