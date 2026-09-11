-- Optional demo data. Run AFTER schema.sql.
USE hrms_db;

INSERT INTO departments (name, description) VALUES
('Մշակում', 'Ինժեներական թիմ'),
('Մարքեթինգ', 'Առաջխաղացում և բրենդ'),
('Վաճառք', 'Աշխատանք հաճախորդների հետ'),
('HR', 'Управление персоналом');

-- Passwords for all demo users below: Demo123!
-- Hash generated with PHP password_hash() using PASSWORD_BCRYPT.
INSERT INTO users (full_name, email, password, role_id, department_id, position, phone, hire_date, salary, status) VALUES
('Աննա Պետրոսյան', 'hr@hrms.local', '$2y$10$by16lYhSKdlft3TTdA3N5O0D5QssUC5Q7uOdCBGArQ3G4uk32Kt9G', 2, 4, 'HR մենեջեր', '+374 55 111 222', '2023-02-01', 350000, 'active'),
('Դավիթ Սարգսյան', 'manager@hrms.local', '$2y$10$by16lYhSKdlft3TTdA3N5O0D5QssUC5Q7uOdCBGArQ3G4uk32Kt9G', 3, 1, 'Մշակման թիմի ղեկավար', '+374 55 222 333', '2022-06-15', 450000, 'active'),
('Գոհար Խաչատրյան', 'employee@hrms.local', '$2y$10$by16lYhSKdlft3TTdA3N5O0D5QssUC5Q7uOdCBGArQ3G4uk32Kt9G', 4, 1, 'PHP ծրագրավորող', '+374 55 333 444', '2024-01-10', 300000, 'active'),
('Տիգրան Ավետիսյան', 'tigran@hrms.local', '$2y$10$by16lYhSKdlft3TTdA3N5O0D5QssUC5Q7uOdCBGArQ3G4uk32Kt9G', 4, 2, 'Մարքեթոլոգ', '+374 55 444 555', '2023-09-01', 280000, 'active'),
('Լիլիթ Գրիգորյան', 'lilit@hrms.local', '$2y$10$by16lYhSKdlft3TTdA3N5O0D5QssUC5Q7uOdCBGArQ3G4uk32Kt9G', 4, 3, 'Վաճառքի մենեջեր', '+374 55 555 666', '2024-03-20', 260000, 'active');

INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, reason, status) VALUES
(4, 1, '2026-09-20', '2026-09-27', 'Семейный отпуск', 'pending'),
(5, 2, '2026-09-14', '2026-09-16', 'Плохое самочувствие', 'approved'),
(6, 1, '2026-10-01', '2026-10-10', 'Поездка', 'pending');

INSERT INTO attendance (user_id, work_date, check_in, check_out, status) VALUES
(3, CURDATE(), '09:02:00', '18:05:00', 'present'),
(4, CURDATE(), '09:20:00', NULL, 'late'),
(5, CURDATE(), '08:55:00', '17:50:00', 'present');
