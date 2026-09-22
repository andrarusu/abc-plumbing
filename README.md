
# ABC Plumbing Services

A responsive plumbing website and staff management system built as a web development portfolio project.

I created this project to practise building a website that connects a customer-facing interface to a PHP and MySQL backend.

**This is a fictional business and a demonstration project.** The reviews, prices and customer examples are not real.

## Features

### Customer website
- Responsive layout for desktop and mobile
- Services and interactive service-area map
- Prices & Estimates page with an interactive example-price calculator
- Frequently Asked Questions section
- Quote request form with input validation
- Quote requests saved to a MySQL database
- Confirmation message after submission

### Staff area
- Staff login with password verification
- Protected dashboard showing quote requests
- Search and status filters
- Request statuses: New, In progress, Quoted and Completed
- Individual request details and internal staff notes
- Administrator-only page for creating staff accounts

## Technologies used

- HTML5 and CSS3
- Bootstrap
- JavaScript
- PHP
- MySQL
- Leaflet and OpenStreetMap for the interactive map
- XAMPP for local development

## Running the project locally

The project was developed and tested locally using XAMPP.

1. Install XAMPP and start **Apache** and **MySQL**.
2. Place the project folder in `C:\xampp\htdocs\abc-plumbing`.
3. Configure the local MySQL database and the private `config.local.php` file.
4. Open `http://localhost/abc-plumbing/index.html` in your browser.

**Note:** The database setup is not yet included in this repository, so the PHP features are not ready for one-click installation on another computer. The website must be opened through a PHP-capable server, not VS Code Live Server.

## Security and demo information

Database credentials are stored in `config.local.php`, which is excluded from Git. Staff passwords are stored as hashes.

This repository contains the source code, not a publicly hosted version of the PHP/MySQL application. Please use fictional customer information when testing the forms.

## Project status

The customer form, local database connection and staff management features have been tested locally. I plan to improve the installation instructions and add screenshots of the finished project.
