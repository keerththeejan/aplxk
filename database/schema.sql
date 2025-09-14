-- Create database and tables
CREATE DATABASE IF NOT EXISTS aplx CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aplx;

-- Services
CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  summary TEXT,
  icon_class VARCHAR(100) DEFAULT 'fa fa-truck',
  sort_order INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO services (title, summary, icon_class, sort_order) VALUES
 ('Freight Shipping', 'Domestic and international freight solutions.', 'fa fa-truck-fast', 1),
 ('Warehousing', 'Secure, scalable storage with inventory management.', 'fa fa-warehouse', 2),
 ('Last-Mile Delivery', 'Fast urban delivery to your customers.', 'fa fa-location-dot', 3);

-- Portfolios
CREATE TABLE IF NOT EXISTS portfolios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  category VARCHAR(100) DEFAULT 'Logistics',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO portfolios (title, image_url, category) VALUES
 ('E-commerce Delivery Network', 'https://picsum.photos/600/400?random=11', 'Delivery'),
 ('Cold Chain Project', 'https://picsum.photos/600/400?random=12', 'Cold Chain'),
 ('Warehouse Automation', 'https://picsum.photos/600/400?random=13', 'Warehousing');

-- Pricing Plans
CREATE TABLE IF NOT EXISTS pricing_plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  period VARCHAR(30) NOT NULL DEFAULT 'mo',
  features TEXT,
  is_featured TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO pricing_plans (name, price, period, features, is_featured) VALUES
 ('Starter', 49.00, 'mo', 'Up to 100 shipments/month\nStandard support', 0),
 ('Business', 149.00, 'mo', 'Up to 1,000 shipments/month\nPriority support\nTracking API', 1),
 ('Enterprise', 399.00, 'mo', 'Unlimited shipments\nDedicated manager\nCustom SLAs', 0);

-- Brands
CREATE TABLE IF NOT EXISTS brands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  logo_url VARCHAR(500) NOT NULL,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO brands (name, logo_url, sort_order) VALUES
 ('Acme Inc', 'https://dummyimage.com/140x50/cccccc/000&text=Acme', 1),
 ('Globex', 'https://dummyimage.com/140x50/cccccc/000&text=Globex', 2),
 ('Soylent', 'https://dummyimage.com/140x50/cccccc/000&text=Soylent', 3),
 ('Initech', 'https://dummyimage.com/140x50/cccccc/000&text=Initech', 4);

-- Testimonials
CREATE TABLE IF NOT EXISTS testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  author_name VARCHAR(120) NOT NULL,
  author_role VARCHAR(120),
  content TEXT NOT NULL,
  avatar_url VARCHAR(500) DEFAULT 'https://i.pravatar.cc/100'
) ENGINE=InnoDB;

INSERT INTO testimonials (author_name, author_role, content, avatar_url) VALUES
 ('Jane Smith', 'E-commerce Manager', 'Logistip improved our delivery times by 30%!', 'https://i.pravatar.cc/100?img=5'),
 ('John Doe', 'Operations Lead', 'Reliable and professional service across regions.', 'https://i.pravatar.cc/100?img=12');

-- Blog Posts
CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  excerpt TEXT,
  image_url VARCHAR(500) NOT NULL,
  published_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO posts (title, excerpt, image_url) VALUES
 ('How to Optimize Your Supply Chain', 'Practical steps to improve logistics efficiency.', 'https://picsum.photos/600/400?random=21'),
 ('Choosing the Right Freight Option', 'Air, sea, or road? Here is how to decide.', 'https://picsum.photos/600/400?random=22'),
 ('Warehouse KPIs You Should Track', 'Measure what matters to improve throughput.', 'https://picsum.photos/600/400?random=23');

-- Call Requests
CREATE TABLE IF NOT EXISTS call_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(60) NOT NULL,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Subscriptions
CREATE TABLE IF NOT EXISTS subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_email (email)
) ENGINE=InnoDB;
