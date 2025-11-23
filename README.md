# Digital Time Capsule

A web application to **create, lock, unlock, and share personal time capsules**, saving memories for the future. Capsules can be auto-unlocked on a set date or manually unlocked by the user. Clean UI, notifications, and capsule management make the experience smooth and secure.
Save your memories safely in the digital capsule. Lock them until a future date, unlock, share, or delete anytime. A simple, clean, and interactive way to preserve moments.

---

## Table of Contents
- [Project Overview](#project-overview)
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Installation & Set](#installation--setup)
- [Database Architecture](#database-architecture)
- [API Endpoints](#api-endpoints)
- [License](#license)

---

## Project Overview

Digital Time Capsule allows a user to:

- Save personal memories or notes in capsules.
- Lock capsules until a future date.
- Receive notifications when capsules unlock automatically.
- Share capsules with others securely.
- Manage capsules: Create, edit, delete, lock and unlock.

The application emphasizes **simplicity, security, and clean UI** for a smooth user experience.

---

## Features

- **User Authentication**
  - Register new account
  - Login / Logout

- **Capsule Management**
  - Create, edit, delete capsules.
  - Set unlock date.
  - Lock and unlock capsules manually.
  - Auto-unlock capsules when date arrives.

- **Notifications**
  - Alerts for unlocked capsules.
  - Notification bell in header.

**UI / UX**
  - Responsive design for mobile and desktop.
  - Clean card layout for capsules.
  - Color-coded status: red for locked, green for unlocked.

---

## Technologies Used

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 8.x
- **Database:** MySQL
- **Server:** XAMPP (local development)
- **Version Control:** git & Github

## Installation & Setup

1. Clone the repository:

<pre>bash
git clone https://github.com/your-username/digital-time-capsule.git</pre>

2. Navigate to the project directory:

<pre>bash
cd digital-time-capsule</pre>

3. Setup the database:
    - Create a database called ```time_capsule```
    - Import ```database.sql``` (tables: ```users```, ```capsules```, ```notifications```)

4. Configure database connection:
   - Edit ```src/includes/db_connect.php``` with your database credentials.

5. Run the app locally:
   - Open http://localhost/digital-time-capsule/public/

---

## Database Architecture

**users**
<br>
| Column | Type | Description |
|--------|------|-------------|
| id    |  INT | Primary key |
| name | VARCHAR | Full name |
| email | VARCHAR | User email (unique) |
| password | VARCHAR| Hashed password
| created_at | TIMESTAMP | Account creation date |

<br>

**capsules**
<br>
| Column | Type | Description |
|--------|------|-------------|
|id|INT (PK)|Capsule ID
|user_id|INT(FK|Owner user ID)|
|title|VARCHAR|Capsule title|
|content|TEXT|Capsule content
|status|ENUM|locked/unlocked
|unlocked_date|DATETIME|Scheduled unlock date|
|visibility|ENUM|private/shared|

<br>

**notifications**
<br>
| Column | Type | Description |
|--------|------|-------------|
|id|INT (PK)|Notification ID|
|user_id|INT(FK)|Recipient user ID|
|capsule_id|INT (FK)| Related capsule ID|
|message|TEXT|Notification message|
|is_read|BOOLEAN|Read/unread flag
<br>

## License
MIT License — free to use and modify.