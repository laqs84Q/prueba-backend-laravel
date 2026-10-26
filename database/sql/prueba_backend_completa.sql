-- PRUEBA BACKEND - BASE DE DATOS COMPLETA (MYSQL 8+)
-- Este archivo es una alternativa a "php artisan migrate --seed".
-- Importar solamente sobre una base vacía. NO importar después de ejecutar migraciones.
-- Las tasas de cambio son ficticias; se incluyen exclusivamente para la prueba.

CREATE DATABASE IF NOT EXISTS prueba_backend
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prueba_backend;

CREATE TABLE migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY personal_access_tokens_token_unique (token),
    INDEX personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE currencies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    exchange_rate DECIMAL(18,6) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY currencies_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    tax_cost DECIMAL(12,2) NOT NULL,
    manufacturing_cost DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    INDEX products_currency_id_foreign (currency_id),
    CONSTRAINT products_currency_id_foreign FOREIGN KEY (currency_id)
        REFERENCES currencies (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_prices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY product_prices_product_id_currency_id_unique (product_id, currency_id),
    INDEX product_prices_currency_id_foreign (currency_id),
    CONSTRAINT product_prices_product_id_foreign FOREIGN KEY (product_id)
        REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT product_prices_currency_id_foreign FOREIGN KEY (currency_id)
        REFERENCES currencies (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Laravel sabrá que las 5 migraciones de este proyecto ya se encuentran aplicadas.
INSERT INTO migrations (migration, batch) VALUES
('2026_10_26_000001_create_users_table', 1),
('2026_10_26_000002_create_personal_access_tokens_table', 1),
('2026_10_26_000003_create_currencies_table', 1),
('2026_10_26_000004_create_products_table', 1),
('2026_10_26_000005_create_product_prices_table', 1);

INSERT INTO currencies (id, name, symbol, exchange_rate, created_at, updated_at) VALUES
(1, 'USD', '$', 1.000000, NOW(), NOW()),
(2, 'CRC', '₡', 500.000000, NOW(), NOW()),
(3, 'EUR', '€', 0.900000, NOW(), NOW());

INSERT INTO products (id, name, description, price, currency_id, tax_cost, manufacturing_cost, created_at, updated_at)
VALUES (1, 'Teclado mecánico', 'Teclado mecánico con conexión USB', 75.50, 1, 9.82, 30.00, NOW(), NOW());

INSERT INTO product_prices (product_id, currency_id, price, created_at, updated_at) VALUES
(1, 2, 37750.00, NOW(), NOW()),
(1, 3, 67.95, NOW(), NOW());

-- No se crean usuarios con contraseñas conocidas.
-- Para crear uno: php artisan app:create-api-user
