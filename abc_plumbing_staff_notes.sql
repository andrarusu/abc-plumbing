-- Run once in the existing abc_plumbing database in phpMyAdmin.
-- This does not change quote_requests or existing staff accounts.
CREATE TABLE IF NOT EXISTS quote_staff_notes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    quote_id BIGINT UNSIGNED NOT NULL,
    staff_username VARCHAR(50) NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_quote_staff_notes_quote_id (quote_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
