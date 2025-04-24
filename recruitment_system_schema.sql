CREATE TABLE users (
  user_id INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','employer','jobseeker') NOT NULL,
  first_name VARCHAR(50) DEFAULT NULL,
  last_name VARCHAR(50) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status ENUM('active','inactive','blocked') DEFAULT 'active',
  PRIMARY KEY (user_id),
  UNIQUE KEY username (username),
  UNIQUE KEY email (email)
);
CREATE TABLE companies (
  company_id INT NOT NULL AUTO_INCREMENT,
  user_id INT DEFAULT NULL,
  company_name VARCHAR(100) NOT NULL,
  description TEXT DEFAULT NULL,
  industry VARCHAR(100) DEFAULT NULL,
  location VARCHAR(100) DEFAULT NULL,
  website VARCHAR(255) DEFAULT NULL,
  logo VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (company_id),
  KEY user_id (user_id),
  CONSTRAINT companies_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);

CREATE TABLE jobs (
  job_id INT NOT NULL AUTO_INCREMENT,
  company_id INT DEFAULT NULL,
  title VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  requirements TEXT DEFAULT NULL,
  salary_range VARCHAR(50) DEFAULT NULL,
  location VARCHAR(100) DEFAULT NULL,
  job_type ENUM('full-time','part-time','contract','internship') DEFAULT NULL,
  status ENUM('open','closed','draft') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deadline DATE DEFAULT NULL,
  PRIMARY KEY (job_id),
  KEY company_id (company_id),
  CONSTRAINT jobs_ibfk_1 FOREIGN KEY (company_id) REFERENCES companies (company_id) ON DELETE CASCADE
);
CREATE TABLE applications (
  application_id INT NOT NULL AUTO_INCREMENT,
  job_id INT DEFAULT NULL,
  user_id INT DEFAULT NULL,
  resume_path VARCHAR(255) DEFAULT NULL,
  cover_letter TEXT DEFAULT NULL,
  status ENUM('pending','reviewed','shortlisted','rejected','hired') DEFAULT 'pending',
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (application_id),
  KEY job_id (job_id),
  KEY user_id (user_id),
  CONSTRAINT applications_ibfk_1 FOREIGN KEY (job_id) REFERENCES jobs (job_id) ON DELETE CASCADE,
  CONSTRAINT applications_ibfk_2 FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);
CREATE TABLE interviews (
  interview_id INT NOT NULL AUTO_INCREMENT,
  application_id INT DEFAULT NULL,
  interview_date DATETIME DEFAULT NULL,
  interview_type ENUM('online','in-person','phone') NOT NULL,
  status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
  notes TEXT DEFAULT NULL,
  PRIMARY KEY (interview_id),
  KEY application_id (application_id),
  CONSTRAINT interviews_ibfk_1 FOREIGN KEY (application_id) REFERENCES applications (application_id) ON DELETE CASCADE
);
CREATE TABLE skills (
  skill_id INT NOT NULL AUTO_INCREMENT,
  skill_name VARCHAR(50) NOT NULL,
  PRIMARY KEY (skill_id),
  UNIQUE KEY skill_name (skill_name)
);
CREATE TABLE job_skills (
  job_id INT NOT NULL,
  skill_id INT NOT NULL,
  PRIMARY KEY (job_id,skill_id),
  KEY skill_id (skill_id),
  CONSTRAINT job_skills_ibfk_1 FOREIGN KEY (job_id) REFERENCES jobs (job_id) ON DELETE CASCADE,
  CONSTRAINT job_skills_ibfk_2 FOREIGN KEY (skill_id) REFERENCES skills (skill_id) ON DELETE CASCADE
);
CREATE TABLE user_skills (
  user_id INT NOT NULL,
  skill_id INT NOT NULL,
  PRIMARY KEY (user_id,skill_id),
  KEY skill_id (skill_id),
  CONSTRAINT user_skills_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE,
  CONSTRAINT user_skills_ibfk_2 FOREIGN KEY (skill_id) REFERENCES skills (skill_id) ON DELETE CASCADE
);
CREATE TABLE resumes (
  resume_id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  resume_path VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (resume_id),
  UNIQUE KEY unique_user_resume (user_id),
  CONSTRAINT resumes_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id)
);
CREATE TABLE job_preferences (
  preference_id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  desired_position VARCHAR(100) DEFAULT NULL,
  preferred_location VARCHAR(100) DEFAULT NULL,
  skills TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (preference_id),
  UNIQUE KEY unique_user_preferences (user_id),
  CONSTRAINT job_preferences_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id)
);
CREATE TABLE notifications (
  notification_id INT NOT NULL AUTO_INCREMENT,
  user_id INT DEFAULT NULL,
  title VARCHAR(100) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (notification_id),
  KEY user_id (user_id),
  CONSTRAINT notifications_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);
-- Skills table with category support
ALTER TABLE skills 
ADD COLUMN category VARCHAR(50) DEFAULT 'General' AFTER skill_name;

-- Add index for faster skill lookups
CREATE INDEX idx_skill_name ON skills(skill_name);
CREATE INDEX idx_skill_category ON skills(category);

-- Messages table for internal communication
CREATE TABLE messages (
  message_id INT NOT NULL AUTO_INCREMENT,
  sender_id INT NOT NULL,
  receiver_id INT NOT NULL,
  subject VARCHAR(100) DEFAULT NULL,
  message_text TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (message_id),
  KEY sender_id (sender_id),
  KEY receiver_id (receiver_id),
  CONSTRAINT messages_ibfk_1 FOREIGN KEY (sender_id) REFERENCES users (user_id) ON DELETE CASCADE,
  CONSTRAINT messages_ibfk_2 FOREIGN KEY (receiver_id) REFERENCES users (user_id) ON DELETE CASCADE
);
