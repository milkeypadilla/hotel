
CREATE TABLE rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_no VARCHAR(20) NOT NULL UNIQUE,
  room_type VARCHAR(50) NOT NULL,
  capacity INT NOT NULL,
  price_per_night DECIMAL(10,2) NOT NULL,
  description VARCHAR(255) DEFAULT ''
);


CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','guest') NOT NULL DEFAULT 'guest',
  phone VARCHAR(30) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  guest_id INT NOT NULL,
  room_id INT NOT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  guests INT NOT NULL DEFAULT 1,
  total_price DECIMAL(10,2) NOT NULL,
  status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (guest_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT
);

INSERT INTO rooms (room_no, room_type, capacity, price_per_night, description) VALUES
('101', 'Standard Single', 1, 1200.00, 'Single bed, air-conditioned, ground floor'),
('102', 'Standard Double', 2, 1800.00, 'Double bed, air-conditioned, with TV'),
('201', 'Deluxe Double',   2, 2500.00, 'Queen bed, city view, own bathroom'),
('202', 'Family Room',     4, 3200.00, 'Two double beds, good for a family'),
('301', 'Suite',           3, 4500.00, 'Living area, king bed, top floor view');

INSERT INTO users (name, email, password, role, phone) VALUES
('Admin',          'admin@hotel.com', '$2b$12$OGDPBS2nSVzDy509xBcOJu0GwIXdFT/3AJf2X/US78oWJVBTMTFWO', 'admin', NULL),
('Juan Dela Cruz', 'guest@hotel.com', '$2b$12$OGDPBS2nSVzDy509xBcOJu0GwIXdFT/3AJf2X/US78oWJVBTMTFWO', 'guest', '09171234567'),
('Maria Lopez',    'guest2@hotel.com','$2b$12$OGDPBS2nSVzDy509xBcOJu0GwIXdFT/3AJf2X/US78oWJVBTMTFWO', 'guest', '09181234567'),
('Pedro Ramos',    'guest3@hotel.com','$2b$12$OGDPBS2nSVzDy509xBcOJu0GwIXdFT/3AJf2X/US78oWJVBTMTFWO', 'guest', '09191234567');

INSERT INTO reservations (guest_id, room_id, check_in, check_out, guests, total_price, status) VALUES
(2, 2, CURDATE(),                          DATE_ADD(CURDATE(), INTERVAL 2 DAY),  2, 3600.00,  'confirmed'),
(3, 3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 6 DAY),  2, 7500.00,  'pending'),
(4, 4, DATE_ADD(CURDATE(), INTERVAL 10 DAY),DATE_ADD(CURDATE(), INTERVAL 12 DAY), 4, 6400.00,  'confirmed'),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 9 DAY), DATE_SUB(CURDATE(), INTERVAL 7 DAY),  1, 2400.00,  'completed'),
(3, 5, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY),  2, 9000.00,  'cancelled');
