-- Create app user with password
CREATE USER IF NOT EXISTS 'app'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON app.* TO 'app'@'%';
FLUSH PRIVILEGES;
