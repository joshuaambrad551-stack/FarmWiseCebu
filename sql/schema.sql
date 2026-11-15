CREATE EXTENSION IF NOT EXISTS pgcrypto;

DROP TABLE IF EXISTS harvest CASCADE;
DROP TABLE IF EXISTS planting CASCADE;
DROP TABLE IF EXISTS farm CASCADE;
DROP TABLE IF EXISTS crop CASCADE;
DROP TABLE IF EXISTS farmer CASCADE;
DROP TABLE IF EXISTS app_user CASCADE;
DROP TABLE IF EXISTS app_role CASCADE;
DROP TABLE IF EXISTS harvest_remark CASCADE;
DROP TABLE IF EXISTS quality_grade CASCADE;
DROP TABLE IF EXISTS planting_status CASCADE;
DROP TABLE IF EXISTS season CASCADE;
DROP TABLE IF EXISTS crop_category CASCADE;
DROP TABLE IF EXISTS irrigation_type CASCADE;
DROP TABLE IF EXISTS soil_type CASCADE;
DROP FUNCTION IF EXISTS set_updated_at() CASCADE;

CREATE FUNCTION set_updated_at() RETURNS trigger AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TABLE soil_type (
    soil_type_id SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    soil_type_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE irrigation_type (
    irrigation_type_id SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    irrigation_type_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE crop_category (
    crop_category_id SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    crop_category_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE season (
    season_id SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    season_name VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE planting_status (
    status_id SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    status_name VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE quality_grade (
    grade_id SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    grade_name VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE harvest_remark (
    remark_id SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    remark_description VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE app_role (
    role_id SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    role_name VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE app_user (
    user_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_id SMALLINT NOT NULL REFERENCES app_role(role_id) ON DELETE RESTRICT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE farmer (
    farmer_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL CHECK (TRIM(first_name) <> ''),
    last_name VARCHAR(50) NOT NULL CHECK (TRIM(last_name) <> ''),
    contact_number VARCHAR(20) NOT NULL UNIQUE CHECK (contact_number ~ '^[0-9+]{7,20}$'),
    address VARCHAR(150) NOT NULL CHECK (TRIM(address) <> ''),
    date_registered DATE NOT NULL DEFAULT CURRENT_DATE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    created_by INT REFERENCES app_user(user_id) ON DELETE SET NULL,
    updated_by INT REFERENCES app_user(user_id) ON DELETE SET NULL
);

CREATE TABLE farm (
    farm_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    farmer_id INT NOT NULL REFERENCES farmer(farmer_id) ON DELETE RESTRICT,
    farm_name VARCHAR(100) NOT NULL CHECK (TRIM(farm_name) <> ''),
    location VARCHAR(150) NOT NULL CHECK (TRIM(location) <> ''),
    area_hectares NUMERIC(8,2) NOT NULL CHECK (area_hectares > 0),
    soil_type_id SMALLINT NOT NULL REFERENCES soil_type(soil_type_id) ON DELETE RESTRICT,
    irrigation_type_id SMALLINT NOT NULL REFERENCES irrigation_type(irrigation_type_id) ON DELETE RESTRICT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    created_by INT REFERENCES app_user(user_id) ON DELETE SET NULL,
    updated_by INT REFERENCES app_user(user_id) ON DELETE SET NULL,
    CONSTRAINT uq_farm_farmer_name UNIQUE (farmer_id, farm_name)
);

CREATE TABLE crop (
    crop_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    crop_name VARCHAR(80) NOT NULL CHECK (TRIM(crop_name) <> ''),
    variety VARCHAR(80) NOT NULL CHECK (TRIM(variety) <> ''),
    crop_category_id SMALLINT NOT NULL REFERENCES crop_category(crop_category_id) ON DELETE RESTRICT,
    growing_period_days INT NOT NULL CHECK (growing_period_days > 0),
    season_id SMALLINT NOT NULL REFERENCES season(season_id) ON DELETE RESTRICT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    created_by INT REFERENCES app_user(user_id) ON DELETE SET NULL,
    updated_by INT REFERENCES app_user(user_id) ON DELETE SET NULL,
    CONSTRAINT uq_crop_name_variety UNIQUE (crop_name, variety)
);

CREATE TABLE planting (
    planting_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    farm_id INT NOT NULL REFERENCES farm(farm_id) ON DELETE RESTRICT,
    crop_id INT NOT NULL REFERENCES crop(crop_id) ON DELETE RESTRICT,
    planting_date DATE NOT NULL,
    area_planted NUMERIC(8,2) NOT NULL CHECK (area_planted > 0),
    seed_quantity NUMERIC(8,2) NOT NULL CHECK (seed_quantity >= 0),
    expected_harvest_date DATE NOT NULL,
    status_id SMALLINT NOT NULL REFERENCES planting_status(status_id) ON DELETE RESTRICT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    created_by INT REFERENCES app_user(user_id) ON DELETE SET NULL,
    updated_by INT REFERENCES app_user(user_id) ON DELETE SET NULL,
    CONSTRAINT chk_harvest_after_planting CHECK (expected_harvest_date >= planting_date)
);

CREATE TABLE harvest (
    harvest_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    planting_id INT NOT NULL REFERENCES planting(planting_id) ON DELETE RESTRICT,
    harvest_date DATE NOT NULL,
    quantity_kg NUMERIC(10,2) NOT NULL CHECK (quantity_kg >= 0),
    selling_price_per_kg NUMERIC(10,2) NOT NULL CHECK (selling_price_per_kg >= 0),
    grade_id SMALLINT NOT NULL REFERENCES quality_grade(grade_id) ON DELETE RESTRICT,
    remark_id SMALLINT NOT NULL REFERENCES harvest_remark(remark_id) ON DELETE RESTRICT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    created_by INT REFERENCES app_user(user_id) ON DELETE SET NULL,
    updated_by INT REFERENCES app_user(user_id) ON DELETE SET NULL
);

CREATE INDEX idx_farm_farmer ON farm(farmer_id);
CREATE INDEX idx_planting_farm ON planting(farm_id);
CREATE INDEX idx_planting_crop ON planting(crop_id);
CREATE INDEX idx_planting_status ON planting(status_id);
CREATE INDEX idx_planting_date ON planting(planting_date);
CREATE INDEX idx_harvest_planting ON harvest(planting_id);
CREATE INDEX idx_harvest_date ON harvest(harvest_date);

CREATE TRIGGER trg_app_user_updated BEFORE UPDATE ON app_user FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_farmer_updated BEFORE UPDATE ON farmer FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_farm_updated BEFORE UPDATE ON farm FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_crop_updated BEFORE UPDATE ON crop FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_planting_updated BEFORE UPDATE ON planting FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_harvest_updated BEFORE UPDATE ON harvest FOR EACH ROW EXECUTE FUNCTION set_updated_at();

ALTER TABLE soil_type ENABLE ROW LEVEL SECURITY;
ALTER TABLE irrigation_type ENABLE ROW LEVEL SECURITY;
ALTER TABLE crop_category ENABLE ROW LEVEL SECURITY;
ALTER TABLE season ENABLE ROW LEVEL SECURITY;
ALTER TABLE planting_status ENABLE ROW LEVEL SECURITY;
ALTER TABLE quality_grade ENABLE ROW LEVEL SECURITY;
ALTER TABLE harvest_remark ENABLE ROW LEVEL SECURITY;
ALTER TABLE app_role ENABLE ROW LEVEL SECURITY;
ALTER TABLE app_user ENABLE ROW LEVEL SECURITY;
ALTER TABLE farmer ENABLE ROW LEVEL SECURITY;
ALTER TABLE farm ENABLE ROW LEVEL SECURITY;
ALTER TABLE crop ENABLE ROW LEVEL SECURITY;
ALTER TABLE planting ENABLE ROW LEVEL SECURITY;
ALTER TABLE harvest ENABLE ROW LEVEL SECURITY;
