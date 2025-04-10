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
                console.log('year:', survey.year ? survey.year.name : 'N/A');
                console.log('department:', survey.department ? survey.department.name : 'N/A');
                console.log('grade_level:', survey.grade_level ? survey.grade_level.name : 'N/A');
                console.log('class:', survey.class ? survey.class.name : 'N/A');
                console.log('subject:', survey.subject ? survey.subject.name : 'N/A');
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
                const checkProperty = (filterKey, relationName, propName = 'name') => {
                    // If this specific filter is not active, the survey passes this check
                    if (!this.filters[filterKey]) return true;

                    const filterValue = this.filters[filterKey];
                    const relationObject = survey[relationName];

                    // Check if the relationship object exists and has the property
                    if (!relationObject || relationObject[propName] === undefined || relationObject[propName] === null) {
                        console.log(`Survey ${survey.id} missing or has null value for ${relationName}.${propName} (Filter: ${filterKey})`);
                        // If the filter is set, but the survey property is missing/null, it shouldn't match
                        return false;
                    }

                    const surveyValue = relationObject[propName];

                    // Compare filter value with the property value from the related object
                    const match = String(filterValue) === String(surveyValue);
                    if (!match) {
                         console.log(`Survey ${survey.id} excluded by ${filterKey}: Filter='${filterValue}' (type: ${typeof filterValue}), Survey='${surveyValue}' (type: ${typeof surveyValue}) from ${relationName}.${propName}`);
                    }
                    return match;
                };

                // Track which filter excluded this survey
                let passed = true;

                // Check year (using the 'year' relation and its 'name' property)
                if (!checkProperty('schoolYear', 'year', 'name')) {
                    passed = false;
                }

                // Check department
                if (passed && !checkProperty('department', 'department', 'name')) {
                    passed = false;
                }

                // Check grade level
                if (passed && !checkProperty('gradeLevel', 'grade_level', 'name')) {
                    passed = false;
                }

                // Check class
                if (passed && !checkProperty('class', 'class', 'name')) {
                    passed = false;
                }

                // Check subject
                if (passed && !checkProperty('subject', 'subject', 'name')) {
                    passed = false;
                }

                if (!passed) return false;

                // Apply status filters (OR logic between them)
                const statusFiltersActive = this.filters.expired || this.filters.running;

                if (!statusFiltersActive) return true; // No status filters active, show all

                if (this.filters.expired && survey.isExpired) return true;
                if (this.filters.running && survey.isRunning) return true;

                // If status filters are active but survey doesn't match any
                if (statusFiltersActive) {
                    // No need for extra log here, checkProperty already logs failures
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
                    console.log('Year object:', survey.year);
                    console.log('Year name:', survey.year ? survey.year.name : 'N/A');
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