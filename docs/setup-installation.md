# Setup and Installation Guide

## Technology Stack Used

### Development Environment
- **XAMPP 8.2.4** - Local server environment (Apache, MySQL, PHP)
- **Visual Studio Code** - Code editor with PHP extensions
- **phpMyAdmin** - Database management via XAMPP

### Frontend Technologies
- **HTML5** - Page structure
- **CSS3** - Styling and responsive design
- **JavaScript** - Client-side interactivity
- **FontAwesome Icons** - UI icons from W3Schools reference
  - Source: https://www.w3schools.com/icons/fontawesome5_icons_users_people.asp
- **Bootstrap 5** - Responsive framework (if used)

### Backend Technologies  
- **PHP 8.2** - Server-side logic
- **MySQL 8.0** - Database management
- **PHPMailer** - Email functionality for password reset

### Security & Efficiency Considerations
- **Password hashing** - Using PHP's `password_hash()` function
- **SQL injection prevention** - Prepared statements with PDO
- **Session management** - Secure session handling
- **Input validation** - Server-side validation of all user inputs
- **File upload security** - Validation of file types and sizes

## Complete Setup Instructions

### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install with default settings
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Project Setup in XAMPP
```bash
# Copy project to XAMPP htdocs folder
C:\xampp\htdocs\Mariem_Final_Individual_Project_Webtech\

# Or on Mac/Linux
/Applications/XAMPP/htdocs/Mariem_Final_Individual_Project_Webtech/
