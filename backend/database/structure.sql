CREATE TABLE
 `infotess_attendance`.`students` 
 (`id` INT NOT NULL AUTO_INCREMENT ,
  `student_id` INT NOT NULL , `programme`
   INT NOT NULL , `group_id` INT NOT NULL ,
    `active` ENUM('0','1','2','') NOT NULL ,
     `updated_at` INT NOT NULL ,
      `created_at` INT NOT NULL ,
       PRIMARY KEY (`id`), UNIQUE (`student_id`))
        ENGINE = InnoDB;


ALTER TABLE `students`
ADD `student_mail`
VARCHAR(255) NOT NULL AFTER `student_id`, 
ADD `recovery_mail` VARCHAR(111) NOT NULL 
AFTER `student_mail`;

//the student index number must be text datatype or varchar 
ALTER TABLE `students` CHANGE `student_id` `student_id` VARCHAR(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `students` CHANGE `created_at` `created_at` DATETIME NOT NULL;

CREATE TABLE tokens ( id int AUTO_INCREMENT, student_id VARCHAR(10), token VARCHAR(200) NOT NULL, PRIMARY KEY (id), FOREIGN KEY (student_id) REFERENCES students(student_id) );

ALTER TABLE `tokens` ADD `updated_at` DATETIME NOT NULL AFTER `token`, ADD `created_at` DATETIME NOT NULL AFTER `updated_at`;

CREATE TABLE `infotess_attendance`.`programme` (`id` INT NOT NULL AUTO_INCREMENT , `programme_code` VARCHAR(5) NOT NULL , `num_of_Stu` VARCHAR NOT NULL , `year` VARCHAR NOT NULL , `created_at` INT NOT NULL , PRIMARY KEY (`programme_code`, `id`)) ENGINE = InnoDB;

ALTER TABLE `students` ADD `role` ENUM('user','rep','admin','ta','lec') NOT NULL AFTER `group_id`;
CREATE TABLE QRCode ( id int NOT NULL AUTO_INCREMENT , QRcode VARCHAR(255) NOT NULL, session_code VARCHAR(255), is_active VARCHAR(2) NOT NULL, created_by VARCHAR(255) NOT NULL, created_at VARCHAR(255) NOT NULL, PRIMARY KEY (id), FOREIGN KEY (created_by) REFERENCES students(student_id) );
SELECT s.student_id, s.roles, s.programme, g.group_id, c.course_id, c.course_name
FROM students s
JOIN groups g 
    ON s.group_id = g.group_id 
   AND s.programme = g.programme_id
JOIN courses c 
    ON c.programme_id = s.programme 
   AND (c.group_id IS NULL OR c.group_id = g.group_id)
WHERE s.student_id = ? 
  AND s.active = 1;
