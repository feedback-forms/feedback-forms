const surveyButtons = document.querySelectorAll('[id^="surveys-filter-"]');

// This function is no longer needed as Livewire handles the click events
// function watchSurveys() {
//   surveyButtons.forEach(button => {
//     button.addEventListener('click', () => {
//       button.classList.toggle('active');
//       filterSurveys();
//     });
//   });
// }

// This function is no longer needed as Livewire handles the filtering
// function filterSurveys() {
//   let activeFilters = [];
//   let surveys = document.querySelectorAll('.survey-wrapper');

//   surveyButtons.forEach(button => {
//     if (button.classList.contains('active')) {
//       activeFilters.push(button.getAttribute('filter-type'));
//     }
//   });

//   surveys.forEach(survey => survey.classList.add('hidden'));

//   if (activeFilters.length > 0) {
//     activeFilters.forEach(activeFilter => displaySurveys(activeFilter));
//   }
//   else {
//     displaySurveys(null);
//   }
// }


// This function is no longer needed as Livewire handles the display
// function displaySurveys(activeFilter) {
//   let surveys = document.querySelectorAll('.survey-wrapper');

//   surveys.forEach(survey => {
//     if (activeFilter == null) {
//       survey.classList.remove('hidden');
//     }

//     if (survey.getAttribute('filter-type') === activeFilter) {
//       survey.classList.remove('hidden');
//     }
//   });
// }

// watchSurveys();

// Add CSS class to buttons based on aria-pressed attribute
document.addEventListener('DOMContentLoaded', () => {
  // Initial setup
  updateButtonStyles();

  // Listen for Livewire updates
  document.addEventListener('livewire:update', updateButtonStyles);
});

function updateButtonStyles() {
  const filterButtons = document.querySelectorAll('[id^="surveys-filter-"]');
  filterButtons.forEach(button => {
    if (button.getAttribute('aria-pressed') === 'true') {
      button.classList.add('bg-blue-500', 'text-white', 'dark:bg-blue-600');
      button.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-800', 'dark:text-gray-200');
    } else {
      button.classList.remove('bg-blue-500', 'text-white', 'dark:bg-blue-600');
      button.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-800', 'dark:text-gray-200');
    }
  });
}