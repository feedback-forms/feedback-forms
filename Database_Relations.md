### **Entities and Attributes:**

#### **1. users**
- **Primary Key (PK)**: `id`
- **Foreign Key (FK)**: `registerkey_id` (nullable) - Links to the `registerkeys` table.
- **Attributes**:
  - `name`
  - `password`
  - `is_admin`
  - `email`

This table stores information about users who participate in the feedback system. Some users may be linked to a **registration key**.

---

#### **2. registerkeys**
- **Primary Key (PK)**: `id`
- **Attributes**:
  - `code`

This table holds **registration keys** that might be used for user account creation.

---

#### **3. feedbacks**
- **Primary Key (PK)**: `id`
- **Foreign Keys (FK)**:
  - `user_id` → Links to the `users` table.
  - `template_id` → Links to the `feedback_templates` table.
- **Attributes**:
  - `accesskey`
  - `limit`
  - `answered`
  - `expire_date`

This table represents **feedback submissions** from users, linked to specific feedback templates.

---

#### **4. feedback_templates**
- **Primary Key (PK)**: `id`
- **Attributes**:
  - `name`

This table stores different **feedback templates** that can be used to create questionnaires.

---

#### **5. questions**
- **Primary Key (PK)**: `id`
- **Foreign Keys (FK)**:
  - `template_id` → Links to `feedback_templates`
  - `question_template_id` → Links to `question_template`
  - `feedback_id` → Links to `feedbacks`
- **Attributes**:
  - `question`

This table holds **individual questions** for feedback forms, which are linked to a question template.

---

#### **6. question_template**
- **Primary Key (PK)**: `id`
- **Attributes**:
  - `type`
  - `max_value`
  - `min_value`

This table defines the **types of questions** that can be used in feedback forms, such as multiple-choice, rating scales, etc.

---

#### **7. results**
- **Primary Key (PK)**: `id`
- **Foreign Key (FK)**:
  - `question_id` → Links to `questions`
- **Attributes**:
  - `rating_value`

This table stores **responses or ratings** provided by users for specific questions.

---

### **Relationships:**
1. **users → feedbacks** (1:N)
   - A user can submit **multiple feedbacks**, but each feedback belongs to one user.

2. **registerkeys → users** (1:N)
   - A **registration key** can be associated with multiple users.

3. **feedback_templates → feedbacks** (1:N)
   - A feedback form is based on one **template**, but multiple feedback instances can be linked to a template.

4. **feedbacks → questions** (1:N)
   - Each **feedback session** consists of multiple **questions**.

5. **question_template → questions** (1:N)
   - A **question template** can be used for multiple **questions**.

6. **questions → results** (1:N)
   - Each **question** has multiple **responses** in the results table.
