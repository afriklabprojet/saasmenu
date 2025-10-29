-- Script SQL pour ajouter l'administrateur eMenu
-- Email: admin@emenu.com
-- Password: admin123 (hashé avec bcrypt)

INSERT INTO `users` VALUES (
    2,                                    -- id
    'eMenu Admin',                        -- name
    'emenu-admin',                        -- slug
    NULL,                                 -- image
    0,                                    -- whatsapp_number
    NULL,                                 -- delivery_charge
    'admin@emenu.com',                    -- email
    '+225 07 12 34 56 78',               -- phone
    NOW(),                                -- email_verified_at
    1,                                    -- is_active
    2,                                    -- role_id (admin)
    NULL,                                 -- description
    NULL,                                 -- address
    1,                                    -- delivery_zone_id
    NULL,                                 -- lat
    NULL,                                 -- lng
    NULL,                                 -- restaurant_charges
    NULL,                                 -- wallet_amount
    NULL,                                 -- comission_amount
    NULL,                                 -- vat_amount
    NULL,                                 -- auto_accpet
    NULL,                                 -- is_notificaiton
    1,                                    -- city_id
    2,                                    -- area_id (default)
    2,                                    -- status
    NULL,                                 -- avg_price
    NULL,                                 -- avg_delivery_time
    0,                                    -- is_pure_veg
    NULL,                                 -- license_code
    NULL,                                 -- copyright_text
    '$2y$10$6KvkjQJpVZvF.E8BYeZ8CO.lWHr2mH3L6jYhVQWK8FiRMTYfWYP6W', -- password (admin123)
    NULL,                                 -- is_verified
    NULL,                                 -- otp
    NULL,                                 -- device_type
    'manual',                             -- source
    NULL,                                 -- social_id
    NULL,                                 -- remember_token
    NOW(),                                -- created_at
    NOW()                                 -- updated_at
) ON DUPLICATE KEY UPDATE 
    password = '$2y$10$6KvkjQJpVZvF.E8BYeZ8CO.lWHr2mH3L6jYhVQWK8FiRMTYfWYP6W',
    updated_at = NOW();

-- Vérification que l'utilisateur a été créé
SELECT id, name, email, role_id, is_active FROM users WHERE email = 'admin@emenu.com';