# How to Use the PHP FTP Server

This guide explains how to set up and run a PHP-based FTP server using tools like Laragon, WAMP, or XAMPP, along with Composer for dependency management.

## Table of Contents

- [How to Use the PHP FTP Server](#how-to-use-the-php-ftp-server)
- [Prerequisites](#prerequisites)
- [Step-by-Step Instructions](#step-by-step-instructions)
    - [1. Install Laragon, WAMP, or XAMPP](#1-install-laragon-wamp-or-xampp)
    - [2. Install Composer](#2-install-composer)
        - [2.1 Select the PHP Version](#21-select-the-php-version)
        - [2.2 Complete the Composer Installation](#22-complete-the-composer-installation)
    - [3. Run the PHP FTP Server](#3-run-the-php-ftp-server)
- [Additional Notes](#additional-notes)

## Prerequisites

Before starting, ensure you have the following installed and configured on your system:

- **A local development environment**: Choose one of the following tools:
    - [Laragon](https://laragon.org/)
    - [WAMP](https://www.wampserver.com/)
    - [XAMPP](https://www.apachefriends.org/)
- **PHP**: Included with the tools mentioned above.
- **Composer**: A dependency manager for PHP. Download it from the [official website](https://getcomposer.org/).
- **Basic knowledge of PHP and command-line tools**.

## Step-by-Step Instructions

### 1. Install Laragon, WAMP, or XAMPP

Download and install one of the following local development environments:

- [Laragon](https://laragon.org/)
- [WAMP](https://www.wampserver.com/)
- [XAMPP](https://www.apachefriends.org/)

These tools provide a local server environment with PHP, MySQL, and Apache pre-configured.

### 2. Install Composer

Composer is a dependency manager for PHP. Follow these steps to install it:

1. Download Composer from the [official website](https://getcomposer.org/).
2. During installation, you will be prompted to select the PHP executable.

#### 2.1 Select the PHP Version

Navigate to the folder where your chosen development environment is installed. Locate the PHP executable:

- For Laragon: `C:\laragon\bin\php\`
- For WAMP: `C:\wamp64\bin\php\`
- For XAMPP: `C:\xampp\php\`

Choose the most recent version of PHP available in the folder.

#### 2.2 Complete the Composer Installation

Follow the remaining steps in the Composer installer to complete the setup.

### 3. Run the PHP FTP Server

1. Open a terminal or command prompt.
2. Navigate to the folder containing your PHP project.
3. Start the PHP development server by running the following command:

     ```bash
     php -S 127.0.0.1:8000
     ```

     This will start a local server at [http://127.0.0.1:8000](http://127.0.0.1:8000).

## Additional Notes

- Ensure that the `uploads` directory (or any directory used for file storage) has the correct permissions to allow file uploads and downloads.
- If you encounter issues with dependencies, run `composer install` in the project directory to install required packages.
- For security, avoid running the PHP development server in a production environment. Use a proper web server like Apache or Nginx for production setups.