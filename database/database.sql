-- =====================================================================
--  Rentora — Campus Equipment Exchange & Rental Hub
--  Consolidated schema (base schema + migration 002 + migration 003
--  merged into one file). This REPLACES the old database/database.sql —
--  that file was a legacy version generated before the schema was
--  finalized and creates a database named `rentora` (wrong — the app
--  uses `rentora_db`) with outdated columns.
--
--  Every teammate should run this once in their own local phpMyAdmin
--  (Server SQL tab, or Import) to get an identical structure. This
--  does not share live data between teammates — everyone still runs
--  their own local MySQL — it just guarantees the same tables/columns.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS rentora_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE rentora_db;

-- ---------------------------------------------------------------------
-- MEMBER — regular platform users (renters / owners / exchangers)
-- ---------------------------------------------------------------------
CREATE TABLE member (
    member_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name        VARCHAR(50)  NOT NULL,
    last_name         VARCHAR(50)  NOT NULL,
    student_id        VARCHAR(20)  NULL,
    dob               DATE         NULL,
    gender             ENUM('Male', 'Female', 'Other') NULL,
    university_email  VARCHAR(100) NOT NULL,
    phone_number      VARCHAR(20)  NULL,
    campus_address    VARCHAR(255) NULL,
    account_balance   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status            ENUM('Pending', 'Verified', 'Rejected') NOT NULL DEFAULT 'Pending',
    username          VARCHAR(50)  NOT NULL,
    password_hash     VARCHAR(255) NOT NULL,
    created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_member_email      UNIQUE (university_email),
    CONSTRAINT uq_member_username   UNIQUE (username),
    CONSTRAINT uq_member_student_id UNIQUE (student_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- ADMIN — separate, standalone table for the admin panel login only.
-- Not linked to member; exactly one seeded row is inserted below.
-- ---------------------------------------------------------------------
CREATE TABLE admin (
    admin_id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_username        VARCHAR(50)  NOT NULL,
    admin_password_hash   VARCHAR(255) NOT NULL,
    admin_email           VARCHAR(100) NULL,
    last_password_change  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_admin_username UNIQUE (admin_username)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- CATEGORY — equipment categories (Camera, Sports Gear, Electronics...)
-- ---------------------------------------------------------------------
CREATE TABLE category (
    category_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name  VARCHAR(50) NOT NULL,
    CONSTRAINT uq_category_name UNIQUE (category_name)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- EQUIPMENT — items listed by members
-- ---------------------------------------------------------------------
CREATE TABLE equipment (
    equipment_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_name       VARCHAR(100) NOT NULL,
    description          TEXT NULL,
    condition_status     ENUM('New', 'Good', 'Fair', 'Poor') NOT NULL DEFAULT 'Good',
    availability_status  ENUM('Available', 'Rented', 'Exchanged', 'Unavailable')
                          NOT NULL DEFAULT 'Available',
    rental_rate          DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    security_deposit     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    campus_spot          VARCHAR(100) NULL,
    image_url            VARCHAR(255) NULL,
    owner_id             INT UNSIGNED NOT NULL,
    category_id          INT UNSIGNED NULL,
    created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipment_owner
        FOREIGN KEY (owner_id) REFERENCES member(member_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_equipment_category
        FOREIGN KEY (category_id) REFERENCES category(category_id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- RENTAL_AGREEMENT — one member renting one piece of equipment
-- ---------------------------------------------------------------------
CREATE TABLE rental_agreement (
    rental_id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    start_date          DATE NOT NULL,
    expected_end_date   DATE NOT NULL,
    actual_end_date     DATE NULL,
    total_cost          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    fine                DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status              ENUM('Pending', 'Active', 'Completed', 'Cancelled')
                        NOT NULL DEFAULT 'Pending',
    handover_token      VARCHAR(20)  NULL,
    deposit_amount      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    pickup_spot         VARCHAR(100) NULL,
    renter_id           INT UNSIGNED NOT NULL,
    equipment_id        INT UNSIGNED NOT NULL,
    CONSTRAINT fk_rental_renter
        FOREIGN KEY (renter_id) REFERENCES member(member_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_rental_equipment
        FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id)
        ON DELETE RESTRICT,
    CONSTRAINT uq_rental_handover_token UNIQUE (handover_token),
    CONSTRAINT chk_rental_dates CHECK (expected_end_date >= start_date)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- EXCHANGE_AGREEMENT — two members swapping two pieces of equipment
-- ---------------------------------------------------------------------
CREATE TABLE exchange_agreement (
    exchange_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exchange_date    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status           ENUM('Pending', 'Accepted', 'Rejected', 'Completed')
                     NOT NULL DEFAULT 'Pending',
    lender_a_id      INT UNSIGNED NOT NULL,
    lender_b_id      INT UNSIGNED NOT NULL,
    equipment_a_id   INT UNSIGNED NOT NULL,
    equipment_b_id   INT UNSIGNED NOT NULL,
    CONSTRAINT fk_exchange_lender_a
        FOREIGN KEY (lender_a_id) REFERENCES member(member_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_exchange_lender_b
        FOREIGN KEY (lender_b_id) REFERENCES member(member_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_exchange_equipment_a
        FOREIGN KEY (equipment_a_id) REFERENCES equipment(equipment_id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_exchange_equipment_b
        FOREIGN KEY (equipment_b_id) REFERENCES equipment(equipment_id)
        ON DELETE RESTRICT,
    CONSTRAINT chk_exchange_different_members  CHECK (lender_a_id <> lender_b_id),
    CONSTRAINT chk_exchange_different_items    CHECK (equipment_a_id <> equipment_b_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- SEED DATA (shared, structural — not personal test/dev data)
-- ---------------------------------------------------------------------

-- The one fixed admin account. Username: admin  |  Password: Rentora@Admin123
-- This hash was generated with PHP's password_hash() (bcrypt), so
-- password_verify('Rentora@Admin123', $hash) will succeed in your PHP code.
-- CHANGE THIS PASSWORD after first login via admin/change_password.php.
INSERT INTO admin (admin_username, admin_password_hash, admin_email)
VALUES (
    'admin',
    '$2y$10$mHv7idMAbSDyO1SNadCPM.5r.Ywrh/jMi.LgMhNUKnIlbsLCzL2yO',
    'admin@rentora.local'
);

-- Starter categories so the equipment form has something to pick from
INSERT INTO category (category_name) VALUES
    ('Electronics'),
    ('Sports & Fitness'),
    ('Books & Stationery'),
    ('Musical Instruments'),
    ('Lab & Project Equipment'),
    ('Furniture');