-- Orders table to track all ticket orders
-- Run this SQL in your database to enable order tracking

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    amount_tickets INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    order_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Columns for ticket check-in at events
    status ENUM('unused', 'checked-in') NOT NULL DEFAULT 'unused',
    checked_in_at DATETIME DEFAULT NULL,
    event_date DATE DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- If the table already exists and you need to add the new columns, run:
-- ALTER TABLE orders
--   ADD COLUMN status ENUM('unused', 'checked-in') NOT NULL DEFAULT 'unused',
--   ADD COLUMN checked_in_at DATETIME DEFAULT NULL,
--   ADD COLUMN event_date DATE DEFAULT NULL;
