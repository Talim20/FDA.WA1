-- IMSTS Database Schema
-- Integrated Market Surveillance Tracking System
-- FDA Upper West Region, Ghana

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS imsts_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE imsts_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('administrator', 'supervisor', 'field_officer') NOT NULL DEFAULT 'field_officer',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Administrative areas table
CREATE TABLE IF NOT EXISTS administrative_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('municipality', 'district') NOT NULL,
    capital VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Communities table
CREATE TABLE IF NOT EXISTS communities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    community_name VARCHAR(100) NOT NULL,
    locality_type ENUM('community', 'village', 'town') NOT NULL DEFAULT 'community',
    administrative_area_id INT NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    description TEXT,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (administrative_area_id) REFERENCES administrative_areas(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_community_area (community_name, administrative_area_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teams table
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    team_identifier VARCHAR(20) NOT NULL UNIQUE,
    supervisor VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Visit outcomes table
CREATE TABLE IF NOT EXISTS visit_outcomes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    outcome_name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Surveillance visits table
CREATE TABLE IF NOT EXISTS surveillance_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    community_id INT NOT NULL,
    team_id INT NOT NULL,
    officer_id INT NOT NULL,
    visit_date DATE NOT NULL,
    visit_time TIME NOT NULL,
    operational_year INT NOT NULL,
    inspection_outcome_id INT NOT NULL,
    notes TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    is_override BOOLEAN NOT NULL DEFAULT FALSE,
    override_reason TEXT,
    override_authorized_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE RESTRICT,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE RESTRICT,
    FOREIGN KEY (officer_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (inspection_outcome_id) REFERENCES visit_outcomes(id) ON DELETE RESTRICT,
    FOREIGN KEY (override_authorized_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_annual_visit (community_id, operational_year, is_override)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit log table
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    affected_record_type VARCHAR(50),
    affected_record_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default administrator
INSERT INTO users (full_name, username, email, password, role, status) VALUES
('System Administrator', 'admin', 'admin@fda.gov.gh', '$2y$10$zdpKVlt/uLjAziZ1FGurUOTYkQ80crocBu7lSKZkGe8mBD7dNF8B.', 'administrator', 'active')
ON DUPLICATE KEY UPDATE username=username;

-- Insert administrative areas for Upper West Region
INSERT INTO administrative_areas (name, type, capital) VALUES
('Wa Municipal', 'municipality', 'Wa'),
('Wa East District', 'district', 'Bulenga'),
('Wa West District', 'district', 'Wechiau'),
('Nadowli-Kaleo District', 'district', 'Nadowli'),
('Jirapa District', 'district', 'Jirapa'),
('Lambussie District', 'district', 'Lambussie'),
('Lawra District', 'district', 'Lawra'),
('Sissala East District', 'district', 'Tumu'),
('Sissala West District', 'district', 'Gwollu'),
('Daffiama-Bussie-Issa District', 'district', 'Daffiama'),
('Nandom District', 'district', 'Nandom')
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample communities
INSERT INTO communities (community_name, locality_type, administrative_area_id, latitude, longitude) VALUES
('Wa Central', 'town', 1, 10.0623, -2.3017),
('Wa Zongo', 'community', 1, 10.0550, -2.2950),
('Kpaguri', 'community', 1, 10.0700, -2.3100),
('Bulenga', 'town', 2, 10.1500, -2.4500),
('Wechiau', 'town', 3, 10.2000, -2.6000),
('Nadowli', 'town', 4, 10.1000, -2.5000),
('Jirapa', 'town', 5, 10.2500, -2.5500),
('Lambussie', 'town', 6, 10.3000, -2.6500),
('Lawra', 'town', 7, 10.3500, -2.7000),
('Tumu', 'town', 8, 10.4000, -2.7500)
ON DUPLICATE KEY UPDATE community_name=community_name;

-- Insert visit outcomes
INSERT INTO visit_outcomes (outcome_name, description) VALUES
('Compliant', 'All regulations met'),
('Minor Violations', 'Minor issues that can be corrected'),
('Major Violations', 'Significant violations requiring follow-up'),
('Severe Violations', 'Critical violations requiring immediate action'),
('No Activity', 'No market activity during visit'),
('Incomplete', 'Visit could not be completed'),
('Follow-up Required', 'Requires additional inspection')
ON DUPLICATE KEY UPDATE outcome_name=outcome_name;

-- Insert sample teams
INSERT INTO teams (team_name, team_identifier, supervisor) VALUES
('Wa Central Team', 'TM-001', 'Kwame Mensah'),
('Wa East Team', 'TM-002', 'Abena Ofori'),
('Wa West Team', 'TM-003', 'Kofi Asante'),
('Northern Zone Team', 'TM-004', 'Ama Darko'),
('Southern Zone Team', 'TM-005', 'Emmanuel Addo')
ON DUPLICATE KEY UPDATE team_identifier=team_identifier;

-- Insert sample users
INSERT INTO users (full_name, username, email, password, role, status) VALUES
('Kwame Mensah', 'kmensah', 'kmensah@fda.gov.gh', '$2y$10$zdpKVlt/uLjAziZ1FGurUOTYkQ80crocBu7lSKZkGe8mBD7dNF8B.', 'supervisor', 'active'),
('Abena Ofori', 'aofori', 'aofori@fda.gov.gh', '$2y$10$zdpKVlt/uLjAziZ1FGurUOTYkQ80crocBu7lSKZkGe8mBD7dNF8B.', 'supervisor', 'active'),
('Kofi Asante', 'kasante', 'kasante@fda.gov.gh', '$2y$10$zdpKVlt/uLjAziZ1FGurUOTYkQ80crocBu7lSKZkGe8mBD7dNF8B.', 'field_officer', 'active'),
('Ama Darko', 'adarko', 'adarko@fda.gov.gh', '$2y$10$zdpKVlt/uLjAziZ1FGurUOTYkQ80crocBu7lSKZkGe8mBD7dNF8B.', 'field_officer', 'active')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample surveillance visits
INSERT INTO surveillance_visits (community_id, team_id, officer_id, visit_date, visit_time, operational_year, inspection_outcome_id, notes, is_override) VALUES
(1, 1, 3, '2026-01-15', '09:30:00', 2026, 1, 'Routine inspection completed successfully', FALSE),
(2, 1, 3, '2026-02-20', '10:00:00', 2026, 1, 'Minor violations noted, follow-up scheduled', FALSE),
(3, 2, 4, '2026-03-10', '11:15:00', 2026, 1, 'Compliant with all regulations', FALSE),
(4, 3, 3, '2026-04-05', '08:45:00', 2026, 2, 'Major violations detected', FALSE),
(5, 4, 4, '2026-05-12', '14:30:00', 2026, 1, 'No market activity', FALSE)
ON DUPLICATE KEY UPDATE community_id=community_id;
