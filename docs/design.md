# System Design Document

## 1. Overview
Digital Time Capsule is a PHP + MySQL web app that lets users create messages or media capsules locked until a future date.

## 2. UI Mockups
| Page | Description |
|------|-------------|
| Landing | Welcome page with login/register. |
| Dashboard | Lists user capsules with create button. |
| Create Capsule | Form to add title, message, media, unlock date.|
| Capsule View | Locked = shows lock; Unlocked = shows content. |
| Profile | Edit profile, change password. |
*(See images in `docs/ui/`)*

## 3. ER Diagram
Relationships among users, capsules, media, shares, and notifications.
*(See `docs/er-diagram.png`)*

## 4. System Architecture
Frontend -> Backend -> Database + File Storage.
Explains how HTTP requests are processed.
*(See `docs/system-architecture.png`)*

## 5. Security and Performance Notes
- Passwords hashed with `password_hash()`
- PDO prepared statements.
- Input validation server-side.
- File type + size restrictions.
- HTTPS in deployment.

## 6. Future Improvements
- Two-factor Authentication (2FA)
- Drag-and-drop media uploads.
- Analytics dashboard for capsule engagement.