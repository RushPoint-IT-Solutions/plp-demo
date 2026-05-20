SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM student_profile_option_values;
DELETE FROM student_profile_images;
DELETE FROM student_profiles;
DELETE FROM student_subject_grades;
DELETE FROM student_subject;
DELETE FROM student_grade_records;
DELETE FROM student_requirement_statuses;
DELETE FROM student_deficiencies;
DELETE FROM student_medical_records;
DELETE FROM student_clinic_records;
DELETE FROM student_discipline_records;
DELETE FROM student_discipline_students;
DELETE FROM student_promotions;
DELETE FROM student_section_assignments;
DELETE FROM student_scholastic_comments;
DELETE FROM parent_student_links;
DELETE FROM parent_contact_requests;
DELETE FROM cancellation_waivers;
DELETE FROM cross_enrollment_requests;
DELETE FROM certificates_issued;
DELETE FROM grade_correction_requests;
DELETE FROM graduate_taggings;
DELETE FROM master_student_profiles;
DELETE FROM master_student_grade_files;
DELETE FROM bed_student_statuses;
DELETE FROM student_update_runs;
DELETE FROM system_cutoff_entries;
DELETE FROM applicant_requirement_submission_files;
DELETE FROM applicant_requirement_submissions;
DELETE FROM applicant_photo_uploads;
DELETE FROM applicant_onboarding_acknowledgements;
DELETE FROM applicant_application_preferences;
DELETE FROM applicant_educational_backgrounds;
DELETE FROM applicant_family_backgrounds;
DELETE FROM users WHERE module IN ('student', 'applicant') OR student_id IS NOT NULL OR applicant_id IS NOT NULL;
DELETE FROM students;
DELETE FROM applicants;

DELETE FROM curriculum_subject_requisites;
DELETE FROM course_curriculum_subjects;
DELETE FROM course_curricula;
DELETE FROM curriculum_years;
DELETE FROM class_room_assignments;
DELETE FROM academic_setup_generation_logs;
DELETE FROM academic_setup_pending_issues;
DELETE FROM academic_term_lifecycle_logs;
DELETE FROM program_term_offerings;
DELETE FROM room_course_assignments;
DELETE FROM section_merging_operations;
DELETE FROM slot_monitorings;
DELETE FROM subjects;
DELETE FROM transmutation_rules;
DELETE FROM system_curriculum_display_settings;
DELETE FROM courses;
DELETE FROM departments;

ALTER TABLE students AUTO_INCREMENT = 1;
ALTER TABLE student_profiles AUTO_INCREMENT = 1;
ALTER TABLE student_subject_grades AUTO_INCREMENT = 1;
ALTER TABLE student_grade_records AUTO_INCREMENT = 1;
ALTER TABLE student_requirement_statuses AUTO_INCREMENT = 1;
ALTER TABLE student_section_assignments AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE applicants AUTO_INCREMENT = 1;
ALTER TABLE applicant_application_preferences AUTO_INCREMENT = 1;
ALTER TABLE applicant_educational_backgrounds AUTO_INCREMENT = 1;
ALTER TABLE applicant_family_backgrounds AUTO_INCREMENT = 1;
ALTER TABLE applicant_requirement_submissions AUTO_INCREMENT = 1;
ALTER TABLE applicant_requirement_submission_files AUTO_INCREMENT = 1;
ALTER TABLE course_curricula AUTO_INCREMENT = 1;
ALTER TABLE course_curriculum_subjects AUTO_INCREMENT = 1;
ALTER TABLE curriculum_years AUTO_INCREMENT = 1;
ALTER TABLE subjects AUTO_INCREMENT = 1;
ALTER TABLE courses AUTO_INCREMENT = 1;
ALTER TABLE departments AUTO_INCREMENT = 1;

INSERT INTO departments (code, description, created_at, updated_at) VALUES
('CCS', 'College of Computer Studies', NOW(), NOW()),
('CBA', 'College of Business and Accountancy', NOW(), NOW()),
('COE', 'College of Education', NOW(), NOW()),
('CON', 'College of Nursing', NOW(), NOW());

INSERT INTO courses
(code, name, program_type, college_id, department_id, description, total_units, academic_year, slots, track_category, non_filipino, program_file, created_at, updated_at)
VALUES
('BSCS', 'Bachelor of Science in Computer Science', 'College', 3, 1, 'Bachelor of Science in Computer Science', 162.00, '2026-2027', 120, 'Academic', 0, 'Approved', NOW(), NOW()),
('BSIT', 'Bachelor of Science in Information Technology', 'College', 3, 1, 'Bachelor of Science in Information Technology', 159.00, '2026-2027', 160, 'Academic', 0, 'Approved', NOW(), NOW()),
('BSA', 'Bachelor of Science in Accountancy', 'College', 6, 2, 'Bachelor of Science in Accountancy', 174.00, '2026-2027', 100, 'Academic', 0, 'Approved', NOW(), NOW()),
('BSED-ENG', 'Bachelor of Secondary Education Major in English', 'College', 7, 3, 'Bachelor of Secondary Education Major in English', 150.00, '2026-2027', 80, 'Academic', 0, 'Approved', NOW(), NOW()),
('BSN', 'Bachelor of Science in Nursing', 'College', 1, 4, 'Bachelor of Science in Nursing', 176.00, '2026-2027', 120, 'Academic', 0, 'Approved', NOW(), NOW());

INSERT INTO curriculum_years (code, label, is_active, created_at, updated_at) VALUES
('2026', 'Curriculum Year 2026', 1, NOW(), NOW());

INSERT INTO curriculum_requisite_types (code, name, created_at, updated_at) VALUES
('pre', 'Pre-requisite', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), updated_at = NOW();

INSERT INTO subjects
(code, name, is_subject_file_record, units, hours, course_type, required_lecture_room_type, required_laboratory_room_type, room_requirement_status, lec, lab, is_core, is_applied, is_specialized, created_at, updated_at)
VALUES
('GE101', 'Understanding the Self', 1, 3.0, 3.00, 'GE', 'Lecture Room', NULL, 'Pending', 3, 0, 1, 0, 0, NOW(), NOW()),
('GE102', 'Readings in Philippine History', 1, 3.0, 3.00, 'GE', 'Lecture Room', NULL, 'Pending', 3, 0, 1, 0, 0, NOW(), NOW()),
('GE103', 'Mathematics in the Modern World', 1, 3.0, 3.00, 'GE', 'Lecture Room', NULL, 'Pending', 3, 0, 1, 0, 0, NOW(), NOW()),
('NSTP101', 'National Service Training Program 1', 1, 3.0, 3.00, 'GE', 'Lecture Room', NULL, 'Pending', 3, 0, 1, 0, 0, NOW(), NOW()),
('PE101', 'Physical Activities Toward Health and Fitness 1', 1, 2.0, 2.00, 'Minor', 'PE Area', NULL, 'Pending', 2, 0, 1, 0, 0, NOW(), NOW()),
('CS101', 'Introduction to Computing', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('CS102', 'Computer Programming 1', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('IT101', 'Human Computer Interaction', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('IT102', 'Web Systems and Technologies 1', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('ACC101', 'Fundamentals of Accounting', 1, 6.0, 6.00, 'Major', 'Lecture Room', NULL, 'Pending', 6, 0, 0, 0, 1, NOW(), NOW()),
('ACC102', 'Managerial Economics', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('ED101', 'The Teaching Profession', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('ED102', 'Child and Adolescent Development', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('NCM101', 'Anatomy and Physiology', 1, 5.0, 7.00, 'Major', 'Lecture Room', 'Science Laboratory', 'Pending', 3, 2, 0, 0, 1, NOW(), NOW()),
('NCM102', 'Health Assessment', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Science Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW());

INSERT INTO subjects
(code, name, is_subject_file_record, units, hours, course_type, required_lecture_room_type, required_laboratory_room_type, room_requirement_status, lec, lab, is_core, is_applied, is_specialized, created_at, updated_at)
VALUES
('GE104', 'Purposive Communication', 1, 3.0, 3.00, 'GE', 'Lecture Room', NULL, 'Pending', 3, 0, 1, 0, 0, NOW(), NOW()),
('GE105', 'Science, Technology, and Society', 1, 3.0, 3.00, 'GE', 'Lecture Room', NULL, 'Pending', 3, 0, 1, 0, 0, NOW(), NOW()),
('GE106', 'Ethics', 1, 3.0, 3.00, 'GE', 'Lecture Room', NULL, 'Pending', 3, 0, 1, 0, 0, NOW(), NOW()),
('NSTP102', 'National Service Training Program 2', 1, 3.0, 3.00, 'GE', 'Lecture Room', NULL, 'Pending', 3, 0, 1, 0, 0, NOW(), NOW()),
('PE102', 'Physical Activities Toward Health and Fitness 2', 1, 2.0, 2.00, 'Minor', 'PE Area', NULL, 'Pending', 2, 0, 1, 0, 0, NOW(), NOW()),
('CS201', 'Data Structures and Algorithms', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('CS202', 'Object-Oriented Programming', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('CS203', 'Discrete Structures', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('CS301', 'Software Engineering 1', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('CS302', 'Database Systems', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('CS303', 'Operating Systems', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('CS401', 'Computer Science Capstone Project 1', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('CS402', 'Computer Science Capstone Project 2', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('IT201', 'Networking 1', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('IT202', 'Information Management', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('IT203', 'Web Systems and Technologies 2', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('IT301', 'Systems Integration and Architecture', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('IT302', 'Information Assurance and Security', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('IT401', 'Information Technology Capstone Project 1', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('IT402', 'Information Technology Capstone Project 2', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Computer Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('ACC201', 'Intermediate Accounting 1', 1, 6.0, 6.00, 'Major', 'Lecture Room', NULL, 'Pending', 6, 0, 0, 0, 1, NOW(), NOW()),
('ACC202', 'Cost Accounting and Control', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('ACC301', 'Auditing Theory', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('ACC302', 'Income Taxation', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('ACC401', 'Accounting Research', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('ACC402', 'Accounting Internship', 1, 6.0, 6.00, 'Major', 'Lecture Room', NULL, 'Pending', 6, 0, 0, 0, 1, NOW(), NOW()),
('ED201', 'Facilitating Learner-Centered Teaching', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('ED202', 'Assessment in Learning 1', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('ENG201', 'Structure of English', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('ED301', 'Curriculum Development', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('ENG301', 'Teaching and Assessment of Literature', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('ED401', 'Practice Teaching', 1, 6.0, 6.00, 'Major', 'Lecture Room', NULL, 'Pending', 6, 0, 0, 0, 1, NOW(), NOW()),
('NCM201', 'Fundamentals of Nursing Practice', 1, 5.0, 8.00, 'Major', 'Lecture Room', 'Science Laboratory', 'Pending', 3, 2, 0, 0, 1, NOW(), NOW()),
('NCM202', 'Health Education', 1, 3.0, 5.00, 'Major', 'Lecture Room', 'Science Laboratory', 'Pending', 2, 1, 0, 0, 1, NOW(), NOW()),
('NCM301', 'Care of Mother, Child, and Adolescent', 1, 6.0, 9.00, 'Major', 'Lecture Room', 'Science Laboratory', 'Pending', 4, 2, 0, 0, 1, NOW(), NOW()),
('NCM302', 'Community Health Nursing', 1, 5.0, 8.00, 'Major', 'Lecture Room', 'Science Laboratory', 'Pending', 3, 2, 0, 0, 1, NOW(), NOW()),
('NCM401', 'Nursing Leadership and Management', 1, 3.0, 3.00, 'Major', 'Lecture Room', NULL, 'Pending', 3, 0, 0, 0, 1, NOW(), NOW()),
('NCM402', 'Intensive Nursing Practicum', 1, 8.0, 12.00, 'Major', 'Lecture Room', 'Science Laboratory', 'Pending', 4, 4, 0, 0, 1, NOW(), NOW());

INSERT INTO course_curricula
(course_id, curriculum_year_id, curriculum_year_code, date_from, title, approval_status, is_published, published_at, is_active, created_at, updated_at)
SELECT id, 1, '2026', '2026-08-01', CONCAT(code, ' Curriculum 2026'), 'Registrar Approved', 1, NOW(), 1, NOW(), NOW()
FROM courses;

INSERT INTO course_curriculum_subjects
(course_curriculum_id, subject_id, year_block_id, semester_id, credited_units, display_order, created_at, updated_at)
SELECT cc.id, s.id, 1, 1, s.units, x.display_order, NOW(), NOW()
FROM course_curricula cc
JOIN courses c ON c.id = cc.course_id
JOIN (
    SELECT 'ALL' program_code, 'GE101' subject_code, 1 display_order UNION ALL
    SELECT 'ALL', 'GE102', 2 UNION ALL
    SELECT 'ALL', 'GE103', 3 UNION ALL
    SELECT 'ALL', 'NSTP101', 4 UNION ALL
    SELECT 'ALL', 'PE101', 5 UNION ALL
    SELECT 'BSCS', 'CS101', 6 UNION ALL
    SELECT 'BSCS', 'CS102', 7 UNION ALL
    SELECT 'BSIT', 'CS101', 6 UNION ALL
    SELECT 'BSIT', 'IT101', 7 UNION ALL
    SELECT 'BSIT', 'IT102', 8 UNION ALL
    SELECT 'BSA', 'ACC101', 6 UNION ALL
    SELECT 'BSA', 'ACC102', 7 UNION ALL
    SELECT 'BSED-ENG', 'ED101', 6 UNION ALL
    SELECT 'BSED-ENG', 'ED102', 7 UNION ALL
    SELECT 'BSN', 'NCM101', 6 UNION ALL
    SELECT 'BSN', 'NCM102', 7
) x ON x.program_code = 'ALL' OR x.program_code = c.code
JOIN subjects s ON s.code = x.subject_code;

INSERT INTO course_curriculum_subjects
(course_curriculum_id, subject_id, year_block_id, semester_id, credited_units, display_order, created_at, updated_at)
SELECT cc.id, s.id, x.year_block_id, x.semester_id, s.units, x.display_order, NOW(), NOW()
FROM course_curricula cc
JOIN courses c ON c.id = cc.course_id
JOIN (
    SELECT 'ALL' program_code, 'GE104' subject_code, 1 year_block_id, 5 semester_id, 1 display_order UNION ALL
    SELECT 'ALL', 'GE105', 1, 5, 2 UNION ALL
    SELECT 'ALL', 'GE106', 1, 5, 3 UNION ALL
    SELECT 'ALL', 'NSTP102', 1, 5, 4 UNION ALL
    SELECT 'ALL', 'PE102', 1, 5, 5 UNION ALL
    SELECT 'BSCS', 'CS201', 2, 1, 1 UNION ALL
    SELECT 'BSCS', 'CS202', 2, 1, 2 UNION ALL
    SELECT 'BSCS', 'CS203', 2, 5, 1 UNION ALL
    SELECT 'BSCS', 'CS301', 3, 1, 1 UNION ALL
    SELECT 'BSCS', 'CS302', 3, 1, 2 UNION ALL
    SELECT 'BSCS', 'CS303', 3, 5, 1 UNION ALL
    SELECT 'BSCS', 'CS401', 4, 1, 1 UNION ALL
    SELECT 'BSCS', 'CS402', 4, 5, 1 UNION ALL
    SELECT 'BSIT', 'IT201', 2, 1, 1 UNION ALL
    SELECT 'BSIT', 'IT202', 2, 1, 2 UNION ALL
    SELECT 'BSIT', 'IT203', 2, 5, 1 UNION ALL
    SELECT 'BSIT', 'IT301', 3, 1, 1 UNION ALL
    SELECT 'BSIT', 'IT302', 3, 5, 1 UNION ALL
    SELECT 'BSIT', 'IT401', 4, 1, 1 UNION ALL
    SELECT 'BSIT', 'IT402', 4, 5, 1 UNION ALL
    SELECT 'BSA', 'ACC201', 2, 1, 1 UNION ALL
    SELECT 'BSA', 'ACC202', 2, 5, 1 UNION ALL
    SELECT 'BSA', 'ACC301', 3, 1, 1 UNION ALL
    SELECT 'BSA', 'ACC302', 3, 5, 1 UNION ALL
    SELECT 'BSA', 'ACC401', 4, 1, 1 UNION ALL
    SELECT 'BSA', 'ACC402', 4, 5, 1 UNION ALL
    SELECT 'BSED-ENG', 'ED201', 2, 1, 1 UNION ALL
    SELECT 'BSED-ENG', 'ED202', 2, 1, 2 UNION ALL
    SELECT 'BSED-ENG', 'ENG201', 2, 5, 1 UNION ALL
    SELECT 'BSED-ENG', 'ED301', 3, 1, 1 UNION ALL
    SELECT 'BSED-ENG', 'ENG301', 3, 5, 1 UNION ALL
    SELECT 'BSED-ENG', 'ED401', 4, 1, 1 UNION ALL
    SELECT 'BSN', 'NCM201', 2, 1, 1 UNION ALL
    SELECT 'BSN', 'NCM202', 2, 5, 1 UNION ALL
    SELECT 'BSN', 'NCM301', 3, 1, 1 UNION ALL
    SELECT 'BSN', 'NCM302', 3, 5, 1 UNION ALL
    SELECT 'BSN', 'NCM401', 4, 1, 1 UNION ALL
    SELECT 'BSN', 'NCM402', 4, 5, 1
) x ON x.program_code = 'ALL' OR x.program_code = c.code
JOIN subjects s ON s.code = x.subject_code;

INSERT INTO curriculum_subject_requisites
(course_curriculum_subject_id, requisite_subject_id, curriculum_requisite_type_id, sort_order, created_at, updated_at)
SELECT target_ccs.id, prerequisite_subject.id, requisite_type.id, x.sort_order, NOW(), NOW()
FROM (
    SELECT 'BSCS' program_code, 'CS201' target_code, 'CS102' prerequisite_code, 1 sort_order UNION ALL
    SELECT 'BSCS', 'CS202', 'CS102', 1 UNION ALL
    SELECT 'BSCS', 'CS203', 'GE103', 1 UNION ALL
    SELECT 'BSCS', 'CS301', 'CS201', 1 UNION ALL
    SELECT 'BSCS', 'CS302', 'CS201', 1 UNION ALL
    SELECT 'BSCS', 'CS303', 'CS201', 1 UNION ALL
    SELECT 'BSCS', 'CS401', 'CS301', 1 UNION ALL
    SELECT 'BSCS', 'CS401', 'CS302', 2 UNION ALL
    SELECT 'BSCS', 'CS402', 'CS401', 1 UNION ALL
    SELECT 'BSIT', 'IT201', 'CS101', 1 UNION ALL
    SELECT 'BSIT', 'IT202', 'IT102', 1 UNION ALL
    SELECT 'BSIT', 'IT203', 'IT102', 1 UNION ALL
    SELECT 'BSIT', 'IT301', 'IT202', 1 UNION ALL
    SELECT 'BSIT', 'IT302', 'IT201', 1 UNION ALL
    SELECT 'BSIT', 'IT401', 'IT301', 1 UNION ALL
    SELECT 'BSIT', 'IT402', 'IT401', 1 UNION ALL
    SELECT 'BSA', 'ACC201', 'ACC101', 1 UNION ALL
    SELECT 'BSA', 'ACC202', 'ACC101', 1 UNION ALL
    SELECT 'BSA', 'ACC301', 'ACC201', 1 UNION ALL
    SELECT 'BSA', 'ACC302', 'ACC201', 1 UNION ALL
    SELECT 'BSA', 'ACC401', 'ACC301', 1 UNION ALL
    SELECT 'BSA', 'ACC402', 'ACC401', 1 UNION ALL
    SELECT 'BSED-ENG', 'ED201', 'ED101', 1 UNION ALL
    SELECT 'BSED-ENG', 'ED202', 'ED102', 1 UNION ALL
    SELECT 'BSED-ENG', 'ENG201', 'GE104', 1 UNION ALL
    SELECT 'BSED-ENG', 'ED301', 'ED201', 1 UNION ALL
    SELECT 'BSED-ENG', 'ENG301', 'ENG201', 1 UNION ALL
    SELECT 'BSED-ENG', 'ED401', 'ED301', 1 UNION ALL
    SELECT 'BSED-ENG', 'ED401', 'ED202', 2 UNION ALL
    SELECT 'BSN', 'NCM201', 'NCM102', 1 UNION ALL
    SELECT 'BSN', 'NCM202', 'NCM102', 1 UNION ALL
    SELECT 'BSN', 'NCM301', 'NCM201', 1 UNION ALL
    SELECT 'BSN', 'NCM302', 'NCM201', 1 UNION ALL
    SELECT 'BSN', 'NCM401', 'NCM301', 1 UNION ALL
    SELECT 'BSN', 'NCM402', 'NCM401', 1
) x
JOIN courses c ON c.code = x.program_code
JOIN course_curricula cc ON cc.course_id = c.id AND cc.curriculum_year_code = '2026'
JOIN subjects target_subject ON target_subject.code = x.target_code
JOIN course_curriculum_subjects target_ccs ON target_ccs.course_curriculum_id = cc.id AND target_ccs.subject_id = target_subject.id
JOIN subjects prerequisite_subject ON prerequisite_subject.code = x.prerequisite_code
JOIN curriculum_requisite_types requisite_type ON requisite_type.code = 'pre';

INSERT INTO applicants
(applicant_id, lrn, last_name, first_name, middle_name, gender, nationality, religion, date_of_birth, place_of_birth, age, civil_status, mobile_number, email_address, present_street, present_barangay, present_zipcode, present_municipality, present_province, present_region, same_as_present, permanent_street, permanent_barangay, permanent_zipcode, permanent_municipality, permanent_province, permanent_region, college_id, application_draft_step, application_submitted_at, application_portal_stage, created_at, updated_at)
VALUES
('APP-2026-0001', '234567890001', 'Dizon', 'Sophia', 'Mercado', 'Female', 'Filipino', 'Catholic', '2008-03-22', 'Pasig City', 18, 'Single', '09270000001', 'sophia.dizon@example.com', 'Caruncho Avenue', 'San Nicolas', '1600', 'Pasig City', 'Metro Manila', 'NCR', 1, 'Caruncho Avenue', 'San Nicolas', '1600', 'Pasig City', 'Metro Manila', 'NCR', 3, 4, NOW(), 4, NOW(), NOW()),
('APP-2026-0002', '234567890002', 'Torres', 'Nathan', 'Lim', 'Male', 'Filipino', 'Christian', '2007-12-02', 'Pasig City', 18, 'Single', '09270000002', 'nathan.torres@example.com', 'Market Avenue', 'Palatiw', '1600', 'Pasig City', 'Metro Manila', 'NCR', 1, 'Market Avenue', 'Palatiw', '1600', 'Pasig City', 'Metro Manila', 'NCR', 3, 4, NOW(), 4, NOW(), NOW()),
('APP-2026-0003', '234567890003', 'Villanueva', 'Isabel', 'Santos', 'Female', 'Filipino', 'Catholic', '2008-06-09', 'Pasig City', 17, 'Single', '09270000003', 'isabel.villanueva@example.com', 'Shaw Boulevard', 'Oranbo', '1600', 'Pasig City', 'Metro Manila', 'NCR', 1, 'Shaw Boulevard', 'Oranbo', '1600', 'Pasig City', 'Metro Manila', 'NCR', 6, 4, NOW(), 4, NOW(), NOW()),
('APP-2026-0004', '234567890004', 'Navarro', 'Joshua', 'Cruz', 'Male', 'Filipino', 'Catholic', '2007-10-14', 'Pasig City', 18, 'Single', '09270000004', 'joshua.navarro@example.com', 'Sandoval Avenue', 'Pinagbuhatan', '1602', 'Pasig City', 'Metro Manila', 'NCR', 1, 'Sandoval Avenue', 'Pinagbuhatan', '1602', 'Pasig City', 'Metro Manila', 'NCR', 7, 4, NOW(), 4, NOW(), NOW()),
('APP-2026-0005', '234567890005', 'Ramos', 'Claire', 'Ocampo', 'Female', 'Filipino', 'Christian', '2008-02-17', 'Pasig City', 18, 'Single', '09270000005', 'claire.ramos@example.com', 'Mercedes Avenue', 'San Miguel', '1600', 'Pasig City', 'Metro Manila', 'NCR', 1, 'Mercedes Avenue', 'San Miguel', '1600', 'Pasig City', 'Metro Manila', 'NCR', 1, 4, NOW(), 4, NOW(), NOW());

INSERT INTO applicant_application_preferences
(applicant_id, apply_program, apply_course_id, entry_classification, year_level_id, academic_term_id, application_date, campus, created_at, updated_at)
VALUES
(1, 'college', 1, 'Freshman', 1, 1, CURDATE(), 'Pasig', NOW(), NOW()),
(2, 'college', 2, 'Freshman', 1, 1, CURDATE(), 'Pasig', NOW(), NOW()),
(3, 'college', 3, 'Freshman', 1, 1, CURDATE(), 'Pasig', NOW(), NOW()),
(4, 'college', 4, 'Freshman', 1, 1, CURDATE(), 'Pasig', NOW(), NOW()),
(5, 'college', 5, 'Freshman', 1, 1, CURDATE(), 'Pasig', NOW(), NOW());

INSERT INTO applicant_educational_backgrounds
(applicant_id, junior_school, senior_school, shs_track_strand, no_k12, learner_reference_number, created_at, updated_at)
VALUES
(1, 'Rizal High School', 'Rizal High School', 'STEM', 0, '234567890001', NOW(), NOW()),
(2, 'Pasig City Science High School', 'Pasig City Science High School', 'STEM', 0, '234567890002', NOW(), NOW()),
(3, 'Eusebio High School', 'Eusebio High School', 'ABM', 0, '234567890003', NOW(), NOW()),
(4, 'Pasig National High School', 'Pasig National High School', 'HUMSS', 0, '234567890004', NOW(), NOW()),
(5, 'Rizal High School', 'Rizal High School', 'STEM', 0, '234567890005', NOW(), NOW());

INSERT INTO applicant_family_backgrounds
(applicant_id, mother_last_name, mother_first_name, mother_nationality, mother_mobile_number, mother_occupation, father_last_name, father_first_name, father_nationality, father_mobile_number, father_occupation, created_at, updated_at)
VALUES
(1, 'Dizon', 'Maria', 'Filipino', '09180000001', 'Teacher', 'Dizon', 'Roberto', 'Filipino', '09190000001', 'Engineer', NOW(), NOW()),
(2, 'Torres', 'Liza', 'Filipino', '09180000002', 'Accountant', 'Torres', 'Antonio', 'Filipino', '09190000002', 'Technician', NOW(), NOW()),
(3, 'Villanueva', 'Grace', 'Filipino', '09180000003', 'Entrepreneur', 'Villanueva', 'Daniel', 'Filipino', '09190000003', 'Manager', NOW(), NOW()),
(4, 'Navarro', 'Cecilia', 'Filipino', '09180000004', 'Nurse', 'Navarro', 'Ramon', 'Filipino', '09190000004', 'Driver', NOW(), NOW()),
(5, 'Ramos', 'Angela', 'Filipino', '09180000005', 'Pharmacist', 'Ramos', 'Jose', 'Filipino', '09190000005', 'Clerk', NOW(), NOW());

INSERT INTO students
(student_no, name, sex, age, college, course_id, curriculum, year_block_id, is_withdrawn, scholarship, registration_no, academic_term_id, created_at, updated_at)
VALUES
('2026-000001', 'Ana Reyes', 'Female', 18, 'College of Computer Studies', 1, '2026', 1, 0, 'None', 'REG-2026-000001', 1, NOW(), NOW()),
('2026-000002', 'Miguel Santos', 'Male', 19, 'College of Computer Studies', 2, '2026', 1, 0, 'None', 'REG-2026-000002', 1, NOW(), NOW()),
('2026-000003', 'Bianca Cruz', 'Female', 18, 'College of Business and Accountancy', 3, '2026', 1, 0, 'Academic', 'REG-2026-000003', 1, NOW(), NOW()),
('2026-000004', 'Carlo Mendoza', 'Male', 20, 'College of Education', 4, '2026', 1, 0, 'None', 'REG-2026-000004', 1, NOW(), NOW()),
('2026-000005', 'Elena Garcia', 'Female', 19, 'College of Nursing', 5, '2026', 1, 0, 'None', 'REG-2026-000005', 1, NOW(), NOW());

INSERT INTO student_profiles
(student_id, student_no, first_name, last_name, middle_name, gender, nationality, religion, date_of_birth, civil_status, mobile_number, student_email, present_street, present_barangay, present_zipcode, present_municipality, present_province, present_region, same_as_present, junior_school, senior_school, shs_track_strand, lrn, profile_complete, created_at, updated_at)
VALUES
(1, '2026-000001', 'Ana', 'Reyes', 'Dela Cruz', 'Female', 'Filipino', 'Catholic', '2008-04-12', 'Single', '09170000001', 'ana.reyes@plp.edu.ph', 'A. Mabini Street', 'San Jose', '1600', 'Pasig City', 'Metro Manila', 'NCR', 1, 'Pasig City Science High School', 'Pasig City Science High School', 'STEM', '123456789001', 1, NOW(), NOW()),
(2, '2026-000002', 'Miguel', 'Santos', 'Rivera', 'Male', 'Filipino', 'Catholic', '2007-09-20', 'Single', '09170000002', 'miguel.santos@plp.edu.ph', 'Rizal Avenue', 'Kapitolyo', '1603', 'Pasig City', 'Metro Manila', 'NCR', 1, 'Rizal High School', 'Rizal High School', 'STEM', '123456789002', 1, NOW(), NOW()),
(3, '2026-000003', 'Bianca', 'Cruz', 'Flores', 'Female', 'Filipino', 'Christian', '2008-01-08', 'Single', '09170000003', 'bianca.cruz@plp.edu.ph', 'Dr. Sixto Antonio Avenue', 'Caniogan', '1606', 'Pasig City', 'Metro Manila', 'NCR', 1, 'Eusebio High School', 'Eusebio High School', 'ABM', '123456789003', 1, NOW(), NOW()),
(4, '2026-000004', 'Carlo', 'Mendoza', 'Aquino', 'Male', 'Filipino', 'Catholic', '2006-11-18', 'Single', '09170000004', 'carlo.mendoza@plp.edu.ph', 'C. Raymundo Avenue', 'Maybunga', '1607', 'Pasig City', 'Metro Manila', 'NCR', 1, 'Pasig National High School', 'Pasig National High School', 'HUMSS', '123456789004', 1, NOW(), NOW()),
(5, '2026-000005', 'Elena', 'Garcia', 'Lopez', 'Female', 'Filipino', 'Catholic', '2007-07-15', 'Single', '09170000005', 'elena.garcia@plp.edu.ph', 'Ortigas Avenue Extension', 'Rosario', '1609', 'Pasig City', 'Metro Manila', 'NCR', 1, 'Rizal High School', 'Rizal High School', 'STEM', '123456789005', 1, NOW(), NOW());

INSERT INTO users
(name, username, email, password, module, force_password_reset, student_id, created_at, updated_at)
SELECT name, student_no, CONCAT(LOWER(REPLACE(name, ' ', '.')), '@plp.edu.ph'), '$2y$10$k9lgxVpt9SaJ4TF7Ik7AZuFz0S7QxPJHW2OmxFU31c/BLIq5BRHq6', 'student', 1, id, NOW(), NOW()
FROM students;

INSERT INTO student_subject (student_id, subject_id, created_at, updated_at)
SELECT st.id, ccs.subject_id, NOW(), NOW()
FROM students st
JOIN course_curricula cc ON cc.course_id = st.course_id AND cc.curriculum_year_code = st.curriculum
JOIN course_curriculum_subjects ccs ON ccs.course_curriculum_id = cc.id AND ccs.year_block_id = st.year_block_id AND ccs.semester_id = 1;

INSERT INTO student_subject_grades
(student_id, subject_id, prelim, midterm, final, final_average, remarks, created_at, updated_at)
SELECT student_id, subject_id, NULL, NULL, NULL, NULL, 'Not Yet Graded', NOW(), NOW()
FROM student_subject;

SET FOREIGN_KEY_CHECKS = 1;
