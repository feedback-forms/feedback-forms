Here's a more detailed version of the instructions for the AI Agent:

---

### **AI Agent Instructions for Survey Management System**

#### **User Roles & Authentication**
- **Teachers:**
  - Must log in to access the system.
  - Can create surveys for students.
  - Can manage their existing surveys, including editing, deleting, or viewing responses.

- **Students:**
  - Cannot log in.
  - Receive a unique survey code from their teachers.
  - Enter the survey code to access the survey.
  - Can fill out and submit the survey without authentication.

#### **Survey Creation & Management (Teacher)**
- Teachers should be able to:
  - Create surveys with customizable questions (e.g., multiple-choice, text input, rating scales).
  - Set optional start and end dates for surveys.
  - Specify whether responses should be anonymous.
  - View, edit, and delete their created surveys.
  - Track survey responses in real time or export results for analysis.

#### **Survey Access & Submission (Student)**
- Students enter a unique survey code provided by the teacher.
- The system verifies if the survey is active and accessible.
- Students complete the survey and submit responses.
- A confirmation message is displayed upon successful submission.

#### **Security & Data Integrity**
- Teachers’ accounts must be protected with authentication (e.g., email/password, OAuth).
- Survey links should be secure and prevent unauthorized access.
- Teachers can only access their own surveys and results.
