-- IMSTS Database Schema for PostgreSQL
-- Integrated Market Surveillance Tracking System
-- FDA Upper West Region, Ghana

-- Create ENUM types for PostgreSQL
CREATE TYPE user_role AS ENUM ('administrator', 'supervisor', 'field_officer');
CREATE TYPE account_status AS ENUM ('active', 'inactive');
CREATE TYPE area_type AS ENUM ('municipality', 'district');
CREATE TYPE locality_type AS ENUM ('community', 'village', 'town');
CREATE TYPE team_status AS ENUM ('active', 'inactive');
CREATE TYPE override_status AS ENUM ('active', 'inactive');

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role user_role NOT NULL DEFAULT 'field_officer',
    status account_status NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create trigger for updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Administrative areas table
CREATE TABLE IF NOT EXISTS administrative_areas (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type area_type NOT NULL,
    capital VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_areas_updated_at BEFORE UPDATE ON administrative_areas
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Communities table
CREATE TABLE IF NOT EXISTS communities (
    id SERIAL PRIMARY KEY,
    community_name VARCHAR(100) NOT NULL,
    locality_type locality_type NOT NULL DEFAULT 'community',
    administrative_area_id INT NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    description TEXT,
    status account_status NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (administrative_area_id) REFERENCES administrative_areas(id) ON DELETE RESTRICT,
    UNIQUE (community_name, administrative_area_id)
);

CREATE TRIGGER update_communities_updated_at BEFORE UPDATE ON communities
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Teams table
CREATE TABLE IF NOT EXISTS teams (
    id SERIAL PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    team_identifier VARCHAR(20) NOT NULL UNIQUE,
    supervisor VARCHAR(100) NOT NULL,
    status team_status NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_teams_updated_at BEFORE UPDATE ON teams
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Visit outcomes table
CREATE TABLE IF NOT EXISTS visit_outcomes (
    id SERIAL PRIMARY KEY,
    outcome_name VARCHAR(100) NOT NULL,
    description TEXT,
    status override_status NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Surveillance visits table
CREATE TABLE IF NOT EXISTS surveillance_visits (
    id SERIAL PRIMARY KEY,
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
    UNIQUE (community_id, operational_year, is_override)
);

-- Audit log table
CREATE TABLE IF NOT EXISTS audit_log (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    affected_record_type VARCHAR(50),
    affected_record_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default administrator
INSERT INTO users (full_name, username, email, password, role, status) VALUES
('System Administrator', 'admin', 'admin@fda.gov.gh', '$2y$10$zdpKVlt/uLjAziZ1FGurUOTYkQ80crocBu7lSKZkGe8mBD7dNF8B.', 'administrator', 'active')
ON CONFLICT (username) DO NOTHING;

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
ON CONFLICT DO NOTHING;

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
ON CONFLICT (community_name, administrative_area_id) DO NOTHING;

-- Insert visit outcomes
INSERT INTO visit_outcomes (outcome_name, description) VALUES
('Compliant', 'All regulations met'),
('Minor Violations', 'Minor issues that can be corrected'),
('Major Violations', 'Significant violations requiring follow-up'),
('Severe Violations', 'Critical violations requiring immediate action'),
('No Activity', 'No market activity during visit'),
('Incomplete', 'Visit could not be completed'),
('Follow-up Required', 'Requires additional inspection')
ON CONFLICT DO NOTHING;

-- Insert sample teams
INSERT INTO teams (team_name, team_identifier, supervisor) VALUES
('Wa Central Team', 'TM-001', 'Kwame Mensah'),
('Wa East Team', 'TM-002', 'Abena Ofori'),
('Wa West Team', 'TM-003', 'Kofi Asante'),
('Northern Zone Team', 'TM-004', 'Ama Darko'),
('Southern Zone Team', 'TM-005', 'Emmanuel Addo')
ON CONFLICT (team_identifier) DO NOTHING;

-- Insert sample users
INSERT INTO users (full_name, username, email, password, role, status) VALUES
('Kwame Mensah', 'kmensah', 'kmensah@fda.gov.gh', '$2y$10$zdpKVlt/uLjAziZ1FGurUOTYkQ80crocBu7lSKZkGe8mBD7dNF8B.', 'supervisor', 'active'),
('Abena Ofori', 'aofori', 'aofori@fda.gov.gh', '$2y$10$zdpKVlt/uLjAziZ1FGurUOTYkQ80crocBu7lSKZkGe8mBD7dNF8B.', 'supervisor', 'active'),
('Kofi Asante', 'kasante', 'kasante@fda.gov.gh', '$2y$10$zdpKVlt/uLjAziZ1FGurUOTYkQ80crocBu7lSKZkGe8mBD7dNF8B.', 'field_officer', 'active'),
('Ama Darko', 'adarko', 'adarko@fda.gov.gh', '$2y$10$zdpKVlt/uLjAziZ1FGurUOTYkQ80crocBu7lSKZkGe8mBD7dNF8B.', 'field_officer', 'active')
ON CONFLICT (username) DO NOTHING;

-- Insert sample surveillance visits
INSERT INTO surveillance_visits (community_id, team_id, officer_id, visit_date, visit_time, operational_year, inspection_outcome_id, notes, is_override) VALUES
(1, 1, 3, '2026-01-15', '09:30:00', 2026, 1, 'Routine inspection completed successfully', FALSE),
(2, 1, 3, '2026-02-20', '10:00:00', 2026, 1, 'Minor violations noted, follow-up scheduled', FALSE),
(3, 2, 4, '2026-03-10', '11:15:00', 2026, 1, 'Compliant with all regulations', FALSE),
(4, 3, 3, '2026-04-05', '08:45:00', 2026, 2, 'Major violations detected', FALSE),
(5, 4, 4, '2026-05-12', '14:30:00', 2026, 1, 'No market activity', FALSE)
ON CONFLICT (community_id, operational_year, is_override) DO NOTHING;
