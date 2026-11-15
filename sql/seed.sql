INSERT INTO soil_type (soil_type_name) VALUES ('Loam'), ('Clay Loam'), ('Sandy Loam'), ('Clay');
INSERT INTO irrigation_type (irrigation_type_name) VALUES ('Rainfed'), ('Irrigated'), ('Drip Irrigation');
INSERT INTO crop_category (crop_category_name) VALUES ('Cereal'), ('Vegetable'), ('Fruit'), ('Legume'), ('Root Crop');
INSERT INTO season (season_name) VALUES ('Wet Season'), ('Dry Season'), ('Year Round');
INSERT INTO planting_status (status_name) VALUES ('Planted'), ('Growing'), ('Harvested'), ('Failed');
INSERT INTO quality_grade (grade_name) VALUES ('Grade A'), ('Grade B'), ('Grade C');
INSERT INTO harvest_remark (remark_description) VALUES ('Good Harvest'), ('Acceptable Quality'), ('Low Yield'), ('Poor Quality'), ('Damaged Harvest');
INSERT INTO app_role (role_name) VALUES ('Administrator'), ('Staff');

INSERT INTO app_user (username, full_name, password_hash, role_id) VALUES
('admin', 'System Administrator', '$2y$10$MZgowcOitMU69xoyu9RUVOd/fbk9DqLjkJ9dsmGl1sAEJGEDa/JDi', 1),
('staff', 'Staff Member', '$2y$10$bgmqPcZrn9aGC9BnK7CYDe/NmVvYJUL/ucVgTnhVCWsO8QVa0Uj5i', 2);

INSERT INTO farmer (first_name, last_name, contact_number, address, created_by, updated_by) VALUES
('Juan', 'Dela Cruz', '09171234567', 'Cebu City', 1, 1),
('Maria', 'Santos', '09181234567', 'Carcar City, Cebu', 1, 1),
('Pedro', 'Reyes', '09191234567', 'Danao City, Cebu', 1, 1),
('Ana', 'Garcia', '09201234567', 'Argao, Cebu', 1, 1),
('Jose', 'Mendoza', '09211234567', 'Bogo City, Cebu', 1, 1),
('Ramon', 'Flores', '09221234567', 'Toledo City, Cebu', 1, 1),
('Elena', 'Cruz', '09231234567', 'Naga City, Cebu', 1, 1),
('Antonio', 'Lim', '09241234567', 'Minglanilla, Cebu', 1, 1),
('Rosa', 'Fernandez', '09251234567', 'Liloan, Cebu', 1, 1),
('Mario', 'Villanueva', '09261234567', 'Compostela, Cebu', 1, 1),
('Teresa', 'Navarro', '09271234567', 'Talisay City, Cebu', 1, 1),
('Carlos', 'Ramos', '09281234567', 'Consolacion, Cebu', 1, 1),
('Liza', 'Torres', '09291234567', 'Dalaguete, Cebu', 1, 1),
('Roberto', 'Castillo', '09301234567', 'Barili, Cebu', 1, 1),
('Susan', 'Aquino', '09311234567', 'Sibonga, Cebu', 1, 1);

INSERT INTO farm (farmer_id, farm_name, location, area_hectares, soil_type_id, irrigation_type_id, created_by, updated_by)
SELECT v.farmer_id, v.farm_name, v.location, v.area_hectares, s.soil_type_id, i.irrigation_type_id, 1, 1
FROM (VALUES
(1, 'Dela Cruz Farm', 'Cebu City', 2.50, 'Loam', 'Rainfed'),
(2, 'Santos Rice Farm', 'Carcar City, Cebu', 4.20, 'Clay Loam', 'Irrigated'),
(3, 'Reyes Agricultural Farm', 'Danao City, Cebu', 3.80, 'Sandy Loam', 'Rainfed'),
(4, 'Garcia Vegetable Farm', 'Argao, Cebu', 1.75, 'Loam', 'Drip Irrigation'),
(5, 'Mendoza Farm', 'Bogo City, Cebu', 5.00, 'Clay', 'Irrigated'),
(6, 'Flores Corn Farm', 'Toledo City, Cebu', 3.40, 'Sandy Loam', 'Rainfed'),
(7, 'Cruz Family Farm', 'Naga City, Cebu', 2.80, 'Loam', 'Irrigated'),
(8, 'Lim Agricultural Farm', 'Minglanilla, Cebu', 2.25, 'Clay Loam', 'Drip Irrigation'),
(9, 'Fernandez Farm', 'Liloan, Cebu', 3.10, 'Sandy Loam', 'Rainfed'),
(10, 'Villanueva Green Farm', 'Compostela, Cebu', 4.50, 'Loam', 'Irrigated'),
(11, 'Navarro Rice Field', 'Talisay City, Cebu', 3.75, 'Clay', 'Irrigated'),
(12, 'Ramos Farm', 'Consolacion, Cebu', 2.60, 'Loam', 'Rainfed'),
(13, 'Torres Vegetable Farm', 'Dalaguete, Cebu', 1.80, 'Sandy Loam', 'Drip Irrigation'),
(14, 'Castillo Agricultural Farm', 'Barili, Cebu', 5.50, 'Clay Loam', 'Irrigated'),
(15, 'Aquino Farm', 'Sibonga, Cebu', 3.20, 'Loam', 'Rainfed')
) AS v(farmer_id, farm_name, location, area_hectares, soil, irrigation)
JOIN soil_type s ON s.soil_type_name = v.soil
JOIN irrigation_type i ON i.irrigation_type_name = v.irrigation
ORDER BY v.farmer_id ASC;

INSERT INTO crop (crop_name, variety, crop_category_id, growing_period_days, season_id, created_by, updated_by)
SELECT v.crop_name, v.variety, c.crop_category_id, v.days, s.season_id, 1, 1
FROM (VALUES
(1, 'Rice', 'NSIC Rc 222', 'Cereal', 115, 'Wet Season'),
(2, 'Corn', 'Yellow Corn', 'Cereal', 100, 'Dry Season'),
(3, 'Tomato', 'Diamante Max', 'Vegetable', 90, 'Dry Season'),
(4, 'Eggplant', 'Morena', 'Vegetable', 100, 'Year Round'),
(5, 'Banana', 'Lakatan', 'Fruit', 300, 'Year Round'),
(6, 'Mango', 'Carabao', 'Fruit', 150, 'Dry Season'),
(7, 'Peanut', 'BPI Pn 9', 'Legume', 110, 'Dry Season'),
(8, 'Cassava', 'Golden Yellow', 'Root Crop', 240, 'Year Round'),
(9, 'Sweet Potato', 'NSIC Sp 30', 'Root Crop', 120, 'Year Round'),
(10, 'Watermelon', 'Sweet 16', 'Fruit', 85, 'Dry Season')
) AS v(ord, crop_name, variety, category, days, season)
JOIN crop_category c ON c.crop_category_name = v.category
JOIN season s ON s.season_name = v.season
ORDER BY v.ord ASC;

INSERT INTO planting (farm_id, crop_id, planting_date, area_planted, seed_quantity, expected_harvest_date, status_id, created_by, updated_by)
SELECT
    (g.i % 15) + 1,
    (g.i % 10) + 1,
    DATE '2026-01-10' + (g.i * 3),
    0.50 + ((g.i % 10) * 0.10),
    10 + ((g.i % 8) * 5),
    DATE '2026-01-10' + (g.i * 3) + c.growing_period_days,
    CASE WHEN g.i < 20 THEN 3 ELSE 2 END,
    1,
    1
FROM generate_series(0, 49) AS g(i)
JOIN crop c ON c.crop_id = (g.i % 10) + 1
ORDER BY g.i ASC;

INSERT INTO harvest (planting_id, harvest_date, quantity_kg, selling_price_per_kg, grade_id, remark_id, created_by, updated_by)
SELECT
    p.planting_id,
    p.expected_harvest_date + 2,
    900 + ((p.planting_id - 1) * 125),
    22 + (((p.planting_id - 1) % 8) * 3.50),
    ((p.planting_id - 1) % 3) + 1,
    CASE WHEN ((p.planting_id - 1) % 3) = 2 THEN 2 ELSE 1 END,
    1,
    1
FROM planting p
WHERE p.planting_id <= 20
ORDER BY p.planting_id ASC;
