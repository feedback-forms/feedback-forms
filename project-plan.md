# Feedback Forms Project Plan

This plan outlines the steps needed to enable teachers to create surveys based on templates and manage them effectively.

## Current State Analysis

The project already has:

- **Models**:
  - `Feedback_template` and `Question_template` for defining survey templates
  - `Feedback` and `Question` for storing actual surveys and their questions
  - School-related models (SchoolYear, Department, GradeLevel, SchoolClass, Subject)

- **Templates**:
  - Several survey templates exist (target, smiley, checkbox, table)
  - Template preview and selection UI is implemented
  - Basic template management is in place

- **Survey Creation**:
  - Basic survey creation form exists
  - Teachers can select templates and configure survey settings
  - SurveyService handles creating surveys from templates
  - After creating a survey, teachers are redirected to the surveys overview page

- **Survey Management**:
  - Basic survey listing with filtering (expired, running, cancelled)
  - Teachers can view and modify survey settings and information

## Fixed Issues

The following issues have been addressed:

1. **SQL Syntax Error**: Fixed the SQL syntax error in the surveys overview page by properly quoting the 'limit' column name in PostgreSQL.

2. **Missing Model Properties**: Added fillable properties to the Feedback and Feedback_template models to allow mass assignment.

3. **Database Schema Updates**: Updated the database schema to include:
   - Added a 'title' column to the feedback_templates table
   - Increased the accesskey length from 6 to 8 characters
   - Added school-related fields to the feedback table

4. **Template Data**: Updated the FeedbackTemplateSeeder to include titles for all templates, including the 'table' template.

5. **Survey Creation**: Fixed the survey creation process by ensuring all required fields are properly stored.

6. **Survey Management**: Implemented functionality for teachers to view and modify survey settings after creation.

7. **School-Related Fields Selection**: Fixed issue where "Jahrgangsstufe" (Grade Level), "Abteilung" (Department), and "Fach" (Subject) fields appeared unselected when editing a newly created survey. The root cause was a mismatch between the values being stored during creation (using code/level) and the values being compared during editing (using name). Modified the create form to consistently use name values for all these fields.

## Implementation Plan

### 1. Bug Fixes and Immediate Improvements
- [x] Fix SQL syntax error with the 'limit' column in PostgreSQL
- [x] Add fillable properties to models to allow mass assignment
- [x] Update database schema to include all necessary fields
- [x] Fix template seeding to include all required templates with titles
- [x] Ensure proper storage of school-related fields in surveys
- [x] Implement survey editing functionality
- [x] Fix survey editing route conflict by excluding edit and index from resource routes and using Livewire components
- [x] Add proper date casting to Feedback model and handle date formatting in Edit component
- [x] Fix survey editing route issue by updating route references from 'surveys.index' to 'surveys.list'
- [x] Fix issue with school-related fields (Grade Level, Department, Subject) not appearing selected in edit view
- [ ] Add error handling to prevent null reference exceptions
- [ ] Implement proper validation for template selection

### 2. Template Management Enhancements
- [ ] Add ability for teachers to view all available templates
- [ ] Implement template details view to preview templates before selection
- [ ] Add template categories or tags for better organization
- [ ] Implement template search functionality
- [ ] Add ability to create custom templates
- [ ] Implement template versioning to track changes

### 3. Survey Creation Improvements
- [ ] Enhance survey creation form with template preview
- [ ] Add ability to customize questions within templates
- [ ] Implement question reordering functionality
- [ ] Add question type selection (multiple choice, rating, open text)
- [ ] Add ability to save draft surveys
- [ ] Implement survey preview before publishing

### 4. Survey Management Dashboard
- [x] Create basic survey management interface
- [x] Implement survey editing functionality
- [ ] Enhance survey management dashboard with more features
- [ ] Implement survey status indicators (draft, active, expired, completed)
- [ ] Add survey analytics and response statistics
- [ ] Implement survey duplication functionality
- [ ] Add bulk actions for surveys (delete, archive, extend)
- [ ] Add filtering and sorting options for surveys

### 5. Response Collection & Management
- [ ] Implement secure survey access via unique links
- [ ] Create response collection interface
- [ ] Add response validation
- [ ] Implement response storage and management
- [ ] Add response export functionality (CSV, PDF)
- [ ] Implement anonymous response option

### 6. Reporting & Analytics
- [ ] Create survey results dashboard
- [ ] Implement visual charts and graphs for survey data
- [ ] Add comparative analysis between surveys
- [ ] Create printable/exportable reports
- [ ] Implement automated insights
- [ ] Add trend analysis for repeated surveys

### 7. User Experience Improvements
- [ ] Enhance UI/UX for template selection
- [ ] Improve survey creation workflow
- [ ] Add guided tour/onboarding for new users
- [ ] Implement responsive design for mobile users
- [ ] Add accessibility improvements
- [ ] Implement localization for multiple languages

### 8. Administrative Features
- [ ] Add user role management (admin, teacher, student)
- [ ] Implement template approval workflow
- [ ] Add system-wide settings and configurations
- [ ] Create audit logs for survey activities
- [ ] Implement data retention policies
- [ ] Add backup and restore functionality

### Template Management Enhancements

1. **Template Listing and Management**
   - Create a dedicated template management interface
   - Implement CRUD operations for templates
   - Add template preview functionality

2. **Template Categorization**
   - Add categories or tags to templates
   - Implement filtering and sorting by category

### Survey Creation Improvements

1. **Enhanced Survey Creation Form**
   - Add template preview during selection
   - Implement question customization interface
   - Add drag-and-drop question reordering

2. **Survey Preview**
   - Add ability to preview survey before publishing
   - Implement survey validation to ensure all required fields are filled

### Survey Management Dashboard

1. **Comprehensive Dashboard**
   - Create a dashboard showing all surveys with status indicators
   - Implement filtering and sorting options
   - Add survey analytics and response statistics
