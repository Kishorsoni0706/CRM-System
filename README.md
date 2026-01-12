# CRM System

A basic CRM application built with **Laravel 10**, **Laravel UI**, and **Bootstrap**, featuring dynamic contacts management, custom fields, and contact merging functionality.

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Usage](#usage)
- [CRUD Operations](#crud-operations)
- [Custom Fields](#custom-fields)
- [Contact Merge Feature](#contact-merge-feature)
- [AJAX Integration](#ajax-integration)
- [Filtering and Search](#filtering-and-search)
- [Technical Details](#technical-details)
- [License](#license)

---

## Features

- Full CRUD functionality for Contacts
- Upload profile images and additional documents
- Support for dynamic custom fields (e.g., Birthday, Company, Address)
- Merge two contacts with conflict resolution
- AJAX-based operations for seamless user experience
- Filter and search contacts by standard and custom fields
- Extensible database schema for future enhancements

---

## Requirements

- PHP >= 8.1
- Composer
- Laravel 10
- MySQL / MariaDB
- Node.js & NPM

---

## Installation

1. **Clone the repository:**
    ```bash
    git clone <repository_url>
    cd <project_folder>
    ```

2. **Install PHP dependencies:**
    ```bash
    composer install
    ```

3. **Install Node.js dependencies and compile assets:**
    ```bash
    npm install
    npm run dev
    ```

4. **Copy the `.env.example` file and configure environment variables:**
    ```bash
    cp .env.example .env
    ```

5. **Update `.env` with your database credentials.**

6. **Generate the application key:**
    ```bash
    php artisan key:generate
    ```

---

## Database Setup

1. **Run migrations to create necessary tables:**
    ```bash
    php artisan migrate
    ```

2. **Optional: Seed sample data:**
    ```bash
    php artisan db:seed
    ```

**Database Tables Overview:**

- `contacts` – stores basic contact information
- `contact_custom_fields` – stores additional dynamic fields
- `merged_contacts` – tracks merged contacts for data integrity

---

## Usage

Start the development server:

```bash
php artisan serve
Access the application at: http://localhost:8000

CRUD Operations
Create: Add new contacts with standard and custom fields

Read: View contact lists and details

Update: Edit existing contacts dynamically

Delete: Remove contacts via AJAX without page reload

Custom Fields
Administrators can define custom fields via UI

Fields are stored in either:

contact_custom_fields table, or

JSON column in the contacts table (configurable)

UI dynamically renders these fields for create/edit forms

Values are merged intelligently during contact merge

Contact Merge Feature
Select two contacts to merge from the contact list

Choose a master contact; secondary contact data is merged without data loss

Conflict Resolution Logic:

Standard fields: master contact data preserved

Emails/Phones: additional values appended if unique

Custom fields: merged intelligently (existing master values preserved, new values added)

Secondary contact marked as merged/inactive instead of being deleted

Clear UI shows merged fields and any overridden values

AJAX Integration
AJAX is used for:

Insert (create contact)

Update (edit contact)

Delete (remove contact)

Benefits:

Success/error messages displayed dynamically

No full-page reload for seamless UX

Filtering and Search
Filter contacts by Name, Email, Gender

AJAX-powered search updates results dynamically

(Optional) Filter by custom fields

Technical Details
Framework: Laravel 10

Frontend: Bootstrap, Laravel UI

Database Design: Extensible to handle dynamic custom fields

Code Structure:

Controllers handle CRUD and merge logic

Models handle relationships between contacts and custom fields

Views render forms, lists, modals, and search results

Merge Tracking: Merged contacts are flagged, preserving historical data