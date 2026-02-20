# Pet Adoption System

A web-based portal for pet adoption, allowing users to browse, search, and apply for pets, while admins can manage pet listings and applications.

## Features

- User registration and login
- Browse available pets with filtering (species, gender, age, search)
- Pet details page
- Adoption application system
- Admin panel for managing pets and applications
- User profile and application tracking
- Responsive design
- Secure password hashing and input validation

## Technologies Used

- PHP (with PDO for database access)
- MySQL
- HTML5, CSS3, Bootstrap 4
- JavaScript (vanilla)
- Font Awesome for icons

## Setup Instructions

1. **Clone the repository**
   ```
   git clone https://github.com/yourusername/pet_adoption_system.git
   ```
2. **Import the database**
   - Create a MySQL database (e.g., `pet_adoption_system`).
   - Import the provided SQL file (if available) to set up tables and sample data.

3. **Configure the database connection**
   - Edit `config/database.php` with your MySQL credentials.

4. **Set up file permissions**
   - Ensure the `assets/images/pets/` directory is writable for image uploads.

5. **Run the application**
   - Place the project in your web server's root (e.g., `htdocs` for XAMPP).
   - Access via `http://localhost/pet_adoption_system/`.

## Admin Access
- Default admin credentials (change after first login!):
  - **Username:** admin
  - **Password:** admin123

## Folder Structure
```
pet_adoption_system/
├── admin/              # Admin panel files
├── assets/             # CSS, JS, images
├── config/             # Database config
├── includes/           # Common PHP includes (header, footer, functions)
├── uploads/            # Uploaded pet images
├── index.php           # Home page
├── pets.php            # Pet listing page
├── pet_details.php     # Pet details page
├── login.php           # User login
├── register.php        # User registration
├── profile.php         # User profile
└── ...
```

## Security Notes
- Passwords are hashed using PHP's `password_hash()`
- Input is sanitized and validated
- Admin routes are protected

## Credits
- [Bootstrap](https://getbootstrap.com/)
- [Font Awesome](https://fontawesome.com/)
- [Pexels](https://pexels.com/) for sample images

## License
This project is for educational purposes. 