CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE capsules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL
    title VARCHAR(255),
    message TEXT,
    unlock_date DATE NOT NULL,
    visibility ENUM('private','shared','public') DEFAULT 'private',
    status ENUM('locked','unlocked') DEFAULT 'locked',
    encryption TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) on DELETE CASCADE
);

CREATE TABLE capsule_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    capsule_id INT NOT NULL,
    file_path VARCHAR(512) NOT NULL,
    file_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (capsule_id) REFERENCES capsules(id) ON DELETE CASCADE
);

CREATE TABLE capsule_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    capsule_id INT NOT NULL,
    shared_with_user_id INT NULL,
    shared_with_email VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (capsule_id) REFERENCES capsules(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    capsule_id INT,
    message VARCHAR(500),
    is_sent TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);