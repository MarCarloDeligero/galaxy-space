-- sql/setup.sql
-- Run this file to create the database and initial tables / sample data.

CREATE DATABASE IF NOT EXISTS user_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE user_management;

-- Genders table
CREATE TABLE IF NOT EXISTS genders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample genders
INSERT INTO genders (name) VALUES ('Male'), ('Female'), ('Other')
  ON DUPLICATE KEY UPDATE name = name;

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample courses
INSERT INTO courses (name) VALUES
  ('Computer Science'),
  ('Information Technology'),
  ('Mathematics')
  ON DUPLICATE KEY UPDATE name = name;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  firstname VARCHAR(100) NOT NULL,
  middlename VARCHAR(100) DEFAULT NULL,
  lastname VARCHAR(100) NOT NULL,
  gender_id INT DEFAULT NULL,
  course_id INT DEFAULT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL, -- hashed password
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (gender_id) REFERENCES genders(id) ON DELETE SET NULL,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admins table (admin users)
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL -- hashed password
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note: Insert admin via the provided create_admin.php script
