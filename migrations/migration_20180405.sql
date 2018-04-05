
ALTER TABLE users ADD role VARCHAR(255) NOT NULL, ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, CHANGE last_password_change last_password_change DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)';
