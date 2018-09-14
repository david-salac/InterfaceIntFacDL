/*
 * Author:  David Salac
 * Project: Diploma Thesis (2017)
 * Title:   This is file with SQL table definitions and initial data of system
 */

/* Table user defines users of system */
CREATE TABLE user (
user_name VARCHAR(120) NOT NULL,
user_privilege ENUM('admin', 'user') NOT NULL,
user_description VARCHAR(256),
user_password_hash VARCHAR(50),
PRIMARY KEY(user_name)
) Engine=InnoDb;
/* INSERT FIRST ADMIN USER:  */
INSERT INTO user VALUES('admin', 'admin', 'Website admin', '929590399ec4491050d3fd8cd33fe9585cf64410'); /* login:admin; password:a1b456 */

/* Table log record every log-in to system */
CREATE TABLE log (
log_privilege ENUM('admin', 'user') NOT NULL,
log_time TIMESTAMP NOT NULL,
log_user VARCHAR(120) NOT NULL,
FOREIGN KEY (log_user) REFERENCES user(user_name) ON DELETE CASCADE,
PRIMARY KEY(log_privilege, log_time, log_user)
) Engine=InnoDb;

/* Table of system task */
CREATE TABLE task (
task_id INT NOT NULL AUTO_INCREMENT,
task_time CHAR(21) NOT NULL,
task_last_active CHAR(21) NULL,
task_priority ENUM('suspend', 'low', 'medium', 'high') NOT NULL,
task_type ENUM('rsa', 'elgamal', 'dh') NOT NULL,

task_solved BOOLEAN NOT NULL,
task_solving_time INT,
task_solving_station INT,

task_rsa_n TEXT,
task_rsa_e TEXT,
task_rsa_c TEXT,
task_rsa_m TEXT,
task_rsa_d TEXT,

task_elgamal_p TEXT,
task_elgamal_g TEXT,
task_elgamal_h TEXT,
task_elgamal_c1 TEXT,
task_elgamal_c2 TEXT,
task_elgamal_x TEXT,
task_elgamal_m TEXT,

task_dh_p TEXT,
task_dh_g TEXT,
task_dh_pow_a TEXT,
task_dh_pow_b TEXT,
task_dh_a TEXT,
task_dh_common_key TEXT,

PRIMARY KEY(task_id)
) Engine=InnoDb;

/* Table of system station */
CREATE TABLE station (
station_id INT NOT NULL AUTO_INCREMENT,
station_created CHAR(21) NOT NULL,
station_last_activity CHAR(21) NULL,
station_task INT NULL,
PRIMARY KEY(station_id),
FOREIGN KEY (station_task) REFERENCES task (task_id)
) Engine=InnoDb;

