CREATE DATABASE EducEnroll;
USE EducEnroll;
CREATE TABLE STUDENT (
    STUD_ID     VARCHAR(10) NOT NULL PRIMARY KEY, -- Format: YEAR + 6 unique digits
    STUD_FNAME  VARCHAR(50) NOT NULL,
    STUD_LNAME  VARCHAR(50) NOT NULL,
    STUD_DOB    DATE NOT NULL, -- Range: 1925 - 2020+
    STUD_PASS   VARCHAR(20) NOT NULL,
    STUD_EMAIL  VARCHAR(50) NOT NULL UNIQUE, -- Ensures no duplicate emails
    STUD_PNUM   CHAR(11) NOT NULL, -- Format: 09111111111 - 09999999999
    STUD_GENDER ENUM('M','F') NOT NULL,
    STUD_ADD    VARCHAR(50) NOT NULL
);

CREATE TABLE ENROLLMENT (
    ENR_ID    VARCHAR(12) NOT NULL PRIMARY KEY, -- Format: ER_xxxxxxxxx
    ENR_STAT  ENUM('PENDING','COMPLETED','DROPPED','ENROLLED') NOT NULL,
    STUD_ID   VARCHAR(10) NOT NULL, -- FK to STUDENT.STUD_ID
    SEC_ID    VARCHAR(10) NOT NULL, -- Format: x_XX-X
    ENR_YR    INT NOT NULL, -- CONTRAINT 1-4
    PROG_ID   VARCHAR(8) NOT NULL, -- Format: PROG_xxx
    ENR_DATE  DATE NOT NULL
);

CREATE TABLE SECTION (
    SEC_ID       VARCHAR(10) NOT NULL PRIMARY KEY, -- Format: YearLevel_ProgramInitials-SectionLabel
    SEC_NAME     VARCHAR(50) NOT NULL,             -- Format: YearLevel_ProgramName-SectionLabel
    SEC_YR       INT NOT NULL, -- Year level constraint (1-4)
    SEC_NUM_STUD INT NOT NULL, -- Section size constraint (30-40)
    SEC_PROG	 VARCHAR(50)
);

CREATE TABLE PROGRAM (
    PROG_ID    VARCHAR(8) NOT NULL PRIMARY KEY, -- Format: PROG_xxx (e.g., PROG_101 - PROG_410)
    PROG_NAME  VARCHAR(50) NOT NULL,            -- Program's Name
    PROG_DESC  VARCHAR(100),                    -- Optional description of the program
    PROG_DEP   VARCHAR(50) NOT NULL             -- Department/Branch (e.g., Dept. of Computer Science, Dept. of I.T.)
);

CREATE TABLE SUBJECT (
    SUB_ID     VARCHAR(7) NOT NULL PRIMARY KEY, -- Format: SUB_xxx (SUB_101 - SUB_410)
    SUB_NAME   VARCHAR(50) NOT NULL,            -- Subject name
    SUB_UNITS  INT NOT NULL, -- Units per subject
    SUB_PREREQ VARCHAR(7),                      -- Prerequisite subject(s), references other SUB_IDs
    SUB_DESC   VARCHAR(100)                     -- Optional description of the subject
);

CREATE TABLE INSTRUCTOR (
    INST_ID    VARCHAR(8) NOT NULL PRIMARY KEY, -- Format: INST_xxx (INST_101 - INST_410)
    INST_FNAME VARCHAR(50) NOT NULL,            -- Instructor's first name
    INST_LNAME VARCHAR(50) NOT NULL,            -- Instructor's last name
    INST_EMAIL VARCHAR(50) NOT NULL UNIQUE,     -- Instructor's email (must be unique)
    INST_PNUM  CHAR(11) NOT NULL                -- Phone number (09111111111 - 09999999999)
);

CREATE TABLE TEACHING_ASSIGNMENT (
    TA_ID   VARCHAR(6) NOT NULL PRIMARY KEY, -- Format: TA_001 - TA_999
    INST_ID VARCHAR(8) NOT NULL,             -- FK to INSTRUCTOR.INST_ID
    SUB_ID  VARCHAR(7) NOT NULL,             -- FK to SUBJECT.SUB_ID
    SEC_ID  VARCHAR(10) NOT NULL           -- FK to SECTION.SEC_ID
);

-- ADDING FOREIGN KEYS --

-- ENROLLMENT TABLE --
-- Add FK to STUDENT
ALTER TABLE ENROLLMENT
ADD CONSTRAINT fk_enrollment_student
FOREIGN KEY (STUD_ID) REFERENCES STUDENT(STUD_ID);

-- Add FK to PROGRAM
ALTER TABLE ENROLLMENT
ADD CONSTRAINT fk_enrollment_program
FOREIGN KEY (PROG_ID) REFERENCES PROGRAM(PROG_ID);

-- Add FK to SECTION
ALTER TABLE ENROLLMENT
ADD CONSTRAINT fk_enrollment_section
FOREIGN KEY (SEC_ID) REFERENCES SECTION(SEC_ID);

-- TEACHING ASSIGNMENT TABLE --

-- Add FK to INSTRUCTOR
ALTER TABLE TEACHING_ASSIGNMENT
ADD CONSTRAINT fk_ta_instructor
FOREIGN KEY (INST_ID) REFERENCES INSTRUCTOR(INST_ID);

-- Add FK to SUBJECT
ALTER TABLE TEACHING_ASSIGNMENT
ADD CONSTRAINT fk_ta_subject
FOREIGN KEY (SUB_ID) REFERENCES SUBJECT(SUB_ID);

-- Add FK to SECTION
ALTER TABLE TEACHING_ASSIGNMENT
ADD CONSTRAINT fk_ta_section
FOREIGN KEY (SEC_ID) REFERENCES SECTION(SEC_ID);

-- INSERT VALUES
INSERT INTO STUDENT (STUD_ID, STUD_FNAME, STUD_LNAME, STUD_DOB, STUD_PASS, STUD_EMAIL, STUD_PNUM, STUD_GENDER, STUD_ADD)
VALUES
('2025000001', 'Maria', 'Santos', '2002-05-14', 'passMaria22', 'maria.santos@schoolname.edu.ph', '09123456789', 'F', 'Quezon City'),
('2025000002', 'Juan', 'Reyes', '2001-11-03', 'juanPass01', 'juan.reyes@schoolname.edu.ph', '09234567890', 'M', 'Manila');

INSERT INTO PROGRAM (PROG_ID, PROG_NAME, PROG_DESC, PROG_DEP)
VALUES
('PROG_101', 'Computer Science', 'Emphasizes algorithms, programming languages, and computational theory.', 'Department of Computer Science'),
('PROG_102', 'Information Technology', 'Focuses on managing computer networks, databases, and enterprise IT infrastructure.', 'Department of Information Technology'),
('PROG_103', 'Information Systems', 'Integrates business processes with technology, covering systems analysis, and data management.', 'Department of Information Systems'),
('PROG_104', 'Data Science', 'Specializes in statistical analysis, machine learning for decision-making.', 'Department of Data Science'),
('PROG_105', 'Cybersecurity', 'Concentrates on protecting systems, ethical hacking, and risk management.', 'Department of Cybersecurity');

INSERT INTO SECTION (SEC_ID, SEC_NAME, SEC_YR, SEC_NUM_STUD, SEC_PROG)
VALUES
-- Computer Science
('1_CS-A', '1-Computer Science-A', 1, 0, 'Computer Science'),
('1_CS-B', '1-Computer Science-B', 1, 0, 'Computer Science'),
('2_CS-A', '2-Computer Science-A', 2, 0, 'Computer Science'),
('2_CS-B', '2-Computer Science-B', 2, 0, 'Computer Science'),
('3_CS-A', '3-Computer Science-A', 3, 0, 'Computer Science'),
('3_CS-B', '3-Computer Science-B', 3, 0, 'Computer Science'),
('4_CS-A', '4-Computer Science-A', 4, 0, 'Computer Science'),
('4_CS-B', '4-Computer Science-B', 4, 0, 'Computer Science'),

-- Information Technology
('1_IT-A', '1-Information Technology-A', 1, 0, 'Information Technology'),
('1_IT-B', '1-Information Technology-B', 1, 0, 'Information Technology'),
('2_IT-A', '2-Information Technology-A', 2, 0, 'Information Technology'),
('2_IT-B', '2-Information Technology-B', 2, 0, 'Information Technology'),
('3_IT-A', '3-Information Technology-A', 3, 0, 'Information Technology'),
('3_IT-B', '3-Information Technology-B', 3, 0, 'Information Technology'),
('4_IT-A', '4-Information Technology-A', 4, 0, 'Information Technology'),
('4_IT-B', '4-Information Technology-B', 4, 0, 'Information Technology'),

-- Information Systems
('1_IS-A', '1-Information Systems-A', 1, 0, 'Information Systems'),
('1_IS-B', '1-Information Systems-B', 1, 0, 'Information Systems'),
('2_IS-A', '2-Information Systems-A', 2, 0, 'Information Systems'),
('2_IS-B', '2-Information Systems-B', 2, 0, 'Information Systems'),
('3_IS-A', '3-Information Systems-A', 3, 0, 'Information Systems'),
('3_IS-B', '3-Information Systems-B', 3, 0, 'Information Systems'),
('4_IS-A', '4-Information Systems-A', 4, 0, 'Information Systems'),
('4_IS-B', '4-Information Systems-B', 4, 0, 'Information Systems'),

-- Data Science
('1_DS-A', '1-Data Science-A', 1, 0, 'Data Science'),
('1_DS-B', '1-Data Science-B', 1, 0, 'Data Science'),
('2_DS-A', '2-Data Science-A', 2, 0, 'Data Science'),
('2_DS-B', '2-Data Science-B', 2, 0, 'Data Science'),
('3_DS-A', '3-Data Science-A', 3, 0, 'Data Science'),
('3_DS-B', '3-Data Science-B', 3, 0, 'Data Science'),
('4_DS-A', '4-Data Science-A', 4, 0, 'Data Science'),
('4_DS-B', '4-Data Science-B', 4, 0, 'Data Science'),

-- Cybersecurity
('1_CY-A', '1-Cybersecurity-A', 1, 0, 'Cybersecurity'),
('1_CY-B', '1-Cybersecurity-B', 1, 0, 'Cybersecurity'),
('2_CY-A', '2-Cybersecurity-A', 2, 0, 'Cybersecurity'),
('2_CY-B', '2-Cybersecurity-B', 2, 0, 'Cybersecurity'),
('3_CY-A', '3-Cybersecurity-A', 3, 0, 'Cybersecurity'),
('3_CY-B', '3-Cybersecurity-B', 3, 0, 'Cybersecurity'),
('4_CY-A', '4-Cybersecurity-A', 4, 0, 'Cybersecurity'),
('4_CY-B', '4-Cybersecurity-B', 4, 0, 'Cybersecurity');


INSERT INTO INSTRUCTOR (INST_ID, INST_FNAME, INST_LNAME, INST_EMAIL, INST_PNUM)
VALUES
('INST_101', 'Carlos', 'Dela Cruz', 'carlos.delacruz@schoolname.edu.ph', '09345678901'),
('INST_102', 'Anna', 'Lopez', 'anna.lopez@schoolname.edu.ph', '09456789012');

INSERT INTO SUBJECT (SUB_ID, SUB_NAME, SUB_UNITS, SUB_PREREQ, SUB_DESC)
VALUES
('SUB_101', 'Introduction to Programming', 3, NULL, 'Basic programming concepts and logic.'),
('SUB_102', 'Data Structures', 3, 'SUB_101', 'Covers arrays, linked lists, stacks, and queues.'),
('SUB_103', 'Database Systems', 3, 'SUB_101', 'Introduction to relational databases, SQL, and normalization.'),
('SUB_104', 'Algorithms', 3, 'SUB_102', 'Design and analysis of algorithms including sorting, searching, and graph traversal.'),
('SUB_105', 'Operating Systems', 3, 'SUB_101', 'Concepts of processes, memory management, file systems, and concurrency.'),
('SUB_106', 'Software Engineering', 3, 'SUB_102', 'Principles of software design, development methodologies, and project management.'),
('SUB_107', 'Computer Networks', 3, 'SUB_101', 'Covers network models, protocols, and communication systems.');

INSERT INTO ENROLLMENT (ENR_ID, ENR_STAT, STUD_ID, SEC_ID, ENR_YR, PROG_ID, ENR_DATE)
VALUES
('ER_000000001', 'ENROLLED', '2025000001', '1_CS-A', 1, 'PROG_101', '2025-06-01'),
('ER_000000002', 'PENDING', '2025000002', '2_IT-B', 2, 'PROG_102', '2025-06-02');

INSERT INTO TEACHING_ASSIGNMENT (TA_ID, INST_ID, SUB_ID, SEC_ID)
VALUES
('TA_001', 'INST_101', 'SUB_101', '1_CS-A'),
('TA_002', 'INST_102', 'SUB_102', '2_IT-B');











