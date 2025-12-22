
# Netralay Hospital Patient Management System

A web-based patient management system for hospitals and clinics, built with PHP (PDO), MySQL, Bootstrap 5, and PHPMailer. It supports user authentication, patient and doctor management, appointment scheduling, password reset, and a responsive UI.

---

## Features

- **User Authentication** (Admin, Doctor, Patient)
- **Profile Management** with image upload
- **Patient Management** (add, edit, view)
- **Doctor Management** (add, edit, view)
- **Appointment Scheduling** (book, manage, track)
- **Enquiries** (manage patient enquiries)
- **Password Reset** (secure, email-based, for users and patients)
- **Responsive Dashboard** for all roles
- **Role-based Navigation** (dynamic navbar)
- **Robust Security** (password hashing, prepared statements, session management)
- **Modern UI** (Bootstrap 5, custom CSS)

---

## Project Structure

```
netralay-hospital/
├── appointments.php
├── dashboard.php
├── dashboard-patient.php
├── doctors.php
├── doctors-list.php
├── enquiries.php
├── enquiries-list.php
├── forgot-password.php
├── index.php
├── login.php
├── logout.php
├── patients.php
├── profile.php
├── register.php
├── reset-password.php
├── schedule-appointment.php
├── setup.sql
├── css/
│   └── style.css
├── images/
│   └── [profile and UI images]
├── includes/
│   ├── config.php
│   ├── database.php
│   └── navbar.php
├── js/
│   ├── dashboard.js
│   └── doctor-availability.js
├── PHPMailer-master/
│   ├── src/
│   ├── language/
│   └── ...
└── README.md
```

---

## Setup Instructions

### Prerequisites
- XAMPP (or similar local server)
- PHP 7.4+
- MySQL 5.7+
- Modern web browser

### Installation Steps
1. **Clone or copy** the `netralay-hospital` folder to `C:/xampp/htdocs/`
2. **Start XAMPP** (Apache & MySQL)
3. **Create the database**:
   - Open phpMyAdmin (`http://localhost/phpmyadmin`)
   - Import `setup.sql` to create tables and triggers
4. **Configure database connection**:
   - Edit `includes/config.php` with your DB credentials
5. **Access the app**:
   - Go to `http://localhost/netralay-hospital/`

---

## Default Credentials

| Role         | Username | Password  |
|--------------|----------|-----------|
| Admin        | admin    | password  |
| Doctor       | (set by admin) | (set by admin) |
| Role         | Username | Password  |
|--------------|----------|-----------|
| Admin        | admin    | password  |
| Doctor       | (set by admin) | (set by admin) |
| Patient      | (register)     | (set on register) |
| Patient      | (register)     | (set on register) |

> **Change all default passwords after first login!**

---

## Usage Guide

### 1. Login
Go to the login page and enter your credentials.

### 2. Dashboard
See an overview of appointments, patients, and system stats.

### 3. Profile
View and update your profile, including uploading a profile image.

### 4. Patients
Add, edit, and view patient details. Search and manage patient records.

### 5. Doctors
Add, edit, and view doctor profiles. Assign appointments.

### 6. Appointments
Schedule, view, and manage appointments. Filter by status, doctor, or patient.

### 7. Enquiries
View and manage patient enquiries.

### 8. Password Reset
Use the "Forgot Password" link to reset your password via email (PHPMailer required, see below).

---

## Email (PHPMailer) Setup

PHPMailer is included in `PHPMailer-master/`. To enable password reset via email:
- Configure SMTP settings in `includes/config.php` (if needed)
- Ensure your server can send emails (use Gmail SMTP or similar for local testing)

---

## Security Features

- Passwords hashed with PHP's `password_hash()`
- All DB queries use PDO prepared statements
- Session-based authentication and role checks
- Input validation and sanitization
- CSRF protection on forms

---

## Customization & Extending

- Add new pages by following the structure in `/includes/` and `/css/`
- Update navigation in `includes/navbar.php`
- Use Bootstrap 5 and `css/style.css` for UI changes
- Update `setup.sql` for DB schema changes

---

## Troubleshooting

- **DB Connection Error**: Check XAMPP, DB credentials in `includes/config.php`, and that DB is imported
- **Email Not Sending**: Check SMTP config and PHPMailer setup
- **404 Errors**: Check file paths and routing in includes/navbar.php
- **Styling Issues**: Ensure `css/style.css` and Bootstrap are loaded

---

## License & Disclaimer

This project is for educational/demo use. For production, add further security, compliance, and professional testing.

---

**Maintained by Netralay Hospital IT Team**

2. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `setup.sql` file to create the database and tables
   - Or run the SQL commands manually from the setup.sql file

3. **File Placement**
   - Copy the `patient-management-system` folder to `C:\xampp\htdocs\`
   - Ensure proper file permissions

4. **Configuration**
   - Open `includes/config.php`
   - Verify database connection settings:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'netralay_hospital');
     ```

5. **Access the System**
   - Open your browser and go to: `http://localhost/netralay-hospital/`
   - You'll be redirected to the login page

## Default Login Credentials

### Administrator
- **Username**: `admin`
- **Password**: `Mriganka@123`

**⚠️ Important**: Change these default passwords after first login!

## System Requirements

### Server Requirements
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx web server
- PDO MySQL extension
- JSON extension

### Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## File Structure

```
patient-management-system/
├── css/
│   └── style.css
├── js/
│   └── dashboard.js
├── images/
│   └── back-img.jpg
├── includes/
│   ├── config.php
│   └── navbar.php
├── dashboard.php
├── login.php
├── logout.php
├── patients.php
├── appointments.php
├── index.php
└── setup.sql
```

## Usage Guide

### Getting Started
1. Login with default credentials
2. Change default passwords
3. Add doctors (admin only)
4. Register patients
5. Schedule appointments

### Patient Management
- Click "Patients" in the navigation bar
- Use "Add New Patient" to register patients
- Search and edit patient information
- View patient details and appointment history

### Appointment Scheduling
- Go to "Appointments" in the navigation bar
- Click "Schedule Appointment"
- Select patient and doctor
- Choose date and time
- Add reason for visit
- Manage appointment status

### User Management (Admin Only)
- Add new doctors
- Manage user permissions

## Database Schema

### Main Tables

- **users**: System users and authentication (user_id as PK)
- **patients**: Patient information and demographics (patient_id as PK, auto-generated)
- **doctors**: Doctor profiles and specializations (doctor_id as PK, auto-generated)
- **appointments**: Appointment scheduling and management
- **medical_history**: Patient medical records

### Key Relationships

- Each doctor is linked to a user account (user_id FK)
- Appointments link patients (patient_id) with doctors (doctor_id)
- Medical history links patients and doctors

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Input sanitization and validation
- Session-based authentication
- Role-based access control
- CSRF protection measures

## Customization

### Adding New Features
1. Create new PHP files following the existing structure
2. Include proper authentication checks
3. Use the established CSS classes for styling
4. Add navigation links in navbar.php
5. Update database schema if needed

### Styling Changes
- Modify `css/style.css` for visual customizations
- Use Bootstrap 5 classes for responsive design
- Custom CSS variables for color scheme changes

### Database Modifications
- Add new tables following the naming convention
- Include proper foreign key relationships
- Create appropriate indexes for performance
- Update triggers for auto-ID generation (see setup.sql for patient_id and doctor_id)

## Troubleshooting

### Common Issues

**Database Connection Failed**
- Check XAMPP MySQL service is running
- Verify database credentials in config.php
- Ensure database exists and tables are created

**Login Issues**
- Verify default credentials
- Check if user exists in database
- Clear browser cache and cookies

**Permission Errors**
- Check file permissions in htdocs folder
- Verify Apache has read/write access
- Check PHP error logs for details

**Styling Issues**
- Verify CSS and JS files are loading
- Check browser developer tools for errors
- Ensure Bootstrap CDN links are accessible

### Getting Help
1. Check PHP error logs in XAMPP
2. Use browser developer tools
3. Verify database connections
4. Review file permissions

## Contributing

### Development Guidelines
- Follow PSR coding standards
- Use meaningful variable names
- Comment complex logic
- Test all functionality
- Maintain responsive design

### Future Enhancements
- Email notifications
- SMS integration
- Advanced reporting
- API development
- Mobile app support

## License

This project is developed for educational and demonstration purposes. Please ensure compliance with healthcare regulations (HIPAA, GDPR, etc.) before using in production environments.

## Support

For technical support or questions:
- Review the troubleshooting section
- Check PHP and MySQL error logs
- Verify system requirements
- Test with default credentials

---

**Note**: This system is designed for demonstration purposes. For production use in healthcare environments, additional security measures, compliance features, and professional testing are required.

---

### Schema Changes (2025)
- Removed prescriptions table and all related triggers/indexes
- doctor_id and patient_id are now the only primary keys for doctors and patients
- doctor_id and patient_id are auto-generated with custom triggers
- All foreign keys reference correct primary keys (user_id, doctor_id, patient_id)
- Receptionist role added