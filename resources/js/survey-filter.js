const surveyButtons = document.querySelectorAll('[id^="surveys-filter-"]');

function watchSurveys() {
  surveyButtons.forEach(button => {
    button.addEventListener('click', () => {
      button.classList.toggle('active');
      filterSurveys();
    });
  });
}

function filterSurveys() {
  let activeFilters = [];
  let surveys = document.querySelectorAll('.survey-wrapper');

  surveyButtons.forEach(button => {
    if (button.classList.contains('active')) {
      activeFilters.push(button.getAttribute('filter-type'));
    }
  });

  surveys.forEach(survey => survey.classList.add('hidden'));

  if (activeFilters.length > 0) {
    activeFilters.forEach(activeFilter => displaySurveys(activeFilter));
  }
  else {
    displaySurveys(null);
  }
}


function displaySurveys(activeFilter) {
  let surveys = document.querySelectorAll('.survey-wrapper');

  surveys.forEach(survey => {
    if (activeFilter == null) {
      survey.classList.remove('hidden');
    }

    if (survey.getAttribute('filter-type') === activeFilter) {
      survey.classList.remove('hidden');
    }
  });
}

watchSurveys();