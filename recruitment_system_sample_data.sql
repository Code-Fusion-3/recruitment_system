-- Sample data for users table
INSERT INTO users (username, email, password, role, first_name, last_name, phone, status) VALUES
('abayosincere11', 'abayosincere11@gmail.com', '$2y$10$dzf5x1R0LjC7iBKhkNOxHu0pjKRxAGU4uq8umMnVV8Gtw.rg7Jp6C', 'employer', 'Abirebeye Abayo Sincere Aime Margot', 'Margot', NULL, 'active'),
('ishimwe', 'ishimwe@gmail.com', '$2y$10$HcB5nHdAGDx5h1sYZlpo9.bsXd1IEt9Y.zm/wu.pO9Bc5LVsHmkFS', 'jobseeker', 'ishimwe', 'jean', '0732286284', 'active'),
('test', 'test@123', '$2y$10$s2YXgPwooeuonlbahqJVF.7bmm3r0TR.fiH7TmhqvTnVs1IdsTx9q', 'jobseeker', 'kamana', 'aime', '', 'active'),

('test1', 'test1@gmail', '$2y$10$U70r6ZPUdjCEHo85S4KQNOtFTWFLqBp2DZLM9QSlfXr4AOHrgjs4i', 'employer', 'Abirebeye', 'Margot', NULL, 'active');

-- Sample data for companies table
INSERT INTO companies (user_id, company_name, description, industry, location, website, logo) VALUES
(1, 'CodeFusion', 'ghdf', 'hdf', 'hdf', 'https://hello.com', 'uploads/company_logos/67cb6b7a73aba.png'),
(4, 'CodeFusion', 'we designs, develops, and maintains software applications, systems, and solutions for businesses and individuals, encompassing everything from mobile apps and websites to complex enterprise software', 'IT', 'kigali Rwanda', 'https://hello.com', NULL);

-- Sample data for jobs table
INSERT INTO jobs (company_id, title, description, requirements, salary_range, location, job_type, status) VALUES
(1, 'dffd', 'fddfdf', 'dfdfdfdf', '230', 'kigali Rwanda', 'part-time', 'open'),
(2, 'software developer', 'designs, develops, and maintains software applications, systems, and solutions for businesses and individuals, encompassing everything from mobile apps and websites to complex enterprise software.', 'designs, develops, and maintains software applications, systems, and solutions for businesses and individuals, encompassing everything from mobile apps and websites to complex enterprise software.\r\ndesigns, develops, and maintains software applications, systems, and solutions for businesses and individuals, encompassing everything from mobile apps and websites to complex enterprise software.', '230', 'kigali Rwanda', 'contract', 'open');

-- Sample data for applications table
INSERT INTO applications (job_id, user_id, resume_path, cover_letter, status, applied_at) VALUES
(1, 2, NULL, NULL, 'shortlisted', '2025-03-07 22:15:49'),
(1, 3, NULL, NULL, 'pending', '2025-03-17 08:40:42');

-- Sample data for resumes table
INSERT INTO resumes (user_id, resume_path) VALUES
(2, 'uploads/resumes/2_67cb75a072fbe.pdf');

-- Sample data for job_preferences table
INSERT INTO job_preferences (user_id, desired_position, preferred_location, skills) VALUES
(2, 'asdksdksd', 'ssdsdsd', 'ksdklsdsdsd');

-- Sample data for notifications table
INSERT INTO notifications (user_id, title, message, is_read) VALUES
(2, 'Application Status Updated', 'Your application for dffd has been shortlisted', 0),
(2, 'Application Status Updated', 'Your application for dffd has been shortlisted', 0);

-- Sample data for skills table (adding some common skills)
INSERT INTO skills (skill_name) VALUES
('JavaScript'),
('Python'),
('Java'),
('SQL'),
('React'),
('Node.js'),
('PHP'),
('HTML/CSS'),
('Project Management'),
('Communication');

-- Sample data for job_skills table
INSERT INTO job_skills (job_id, skill_id) VALUES
(1, 1),
(1, 5),
(1, 6),
(2, 2),
(2, 3),
(2, 4);

-- Sample data for user_skills table
INSERT INTO user_skills (user_id, skill_id) VALUES
(2, 1),
(2, 5),
(2, 8),
(3, 2),
(3, 4),
(3, 7);

-- Sample data for interviews table
INSERT INTO interviews (application_id, interview_date, interview_type, status, notes) VALUES
(1, '2025-03-15 10:00:00', 'online', 'scheduled', 'Initial technical screening'),
(1, '2025-03-20 14:30:00', 'in-person', 'scheduled', 'Follow-up with team lead');
-- Sample data for admin user
INSERT INTO users (username, email, password, role, first_name, last_name, phone, status) VALUES
('admin', 'admin@recruitsystem.com', '$2y$10$HcB5nHdAGDx5h1sYZlpo9.bsXd1IEt9Y.zm/wu.pO9Bc5LVsHmkFS', 'admin', 'System', 'Administrator', '0712345678', 'active');