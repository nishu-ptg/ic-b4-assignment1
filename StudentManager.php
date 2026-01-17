<?php

class StudentManager {

    /**
     * @return array
     */
    public function getAllStudents(): array
    {
        return json_decode(file_get_contents('students.json'), true) ?? [];
    }
    
    /**
     * @param array $data
     * @return array ['success' => bool, 'message' => string]
     */
    public function create(array $data): array
    {
        // validate the data first
        $validation = $this->validate($data);
        if (!$validation['success']) {
            return $validation; // if validation fails, no need to proceed
        }

        // get all existing students list as array
        $students = json_decode(file_get_contents('students.json'), true) ?? [];

        // prepare new student data with id and added it to the array
        $students[] = array_merge($data, [
            'id' => time(),
        ]);

        // try to save back to the file, pretty print will take more space but easier to read
        if (file_put_contents('students.json', json_encode($students, JSON_PRETTY_PRINT))) {
            return [
                'success' => true,
                'message' => "Student '{$data['name']}' created successfully.",
            ];
        }

        // something went wrong
        return [
            'success' => false,
            'message' => 'Failed to create student.',
        ];
    }

    /**
     * @param mixed $id
     * @return array|null
     */
    public function getStudentById($id): ?array
    {
        // get all students
        $students = $this->getAllStudents();
        foreach ($students as $student) {
            // match found, return it
            if ($student['id'] == $id) return $student;
        }

        // no match found
        return null;
    }

    /**
     * @param mixed $id
     * @param mixed $data
     * @return array ['success' => bool, 'message' => string]
     */
    public function update($id, $data): array
    {
        // get all students
        $students = $this->getAllStudents();

        foreach ($students as $i => $student) { 
            if ($student['id'] == $id) {    // id matched
                // merge it
                $students[$i] = array_merge($student, $data);
                
                // try to save
                if (file_put_contents('students.json', json_encode($students, JSON_PRETTY_PRINT))) {
                    return [
                        'success' => true,
                        'message' => 'Student updated successfully.',
                    ];
                }
            }
        }

        // something went wrong
        return [
            'success' => false,
            'message' => 'Failed to update student.',
        ];
    }

    /**
     * @param array $id
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete($id): array
    {
        // get all students
        $students = $this->getAllStudents();
        foreach ($students as $i => $student) {
            if ($student['id'] == $id) {    // match found
                // remove it
                array_splice($students, $i, 1);

                // try to save
                if (file_put_contents('students.json', json_encode($students, JSON_PRETTY_PRINT))) {
                    return [
                        'success' => true,
                        'message' => 'Student deleted successfully.',
                    ];
                }
            }
        }

        // something went wrong
        return [
            'success' => false,
            'message' => 'Failed to delete student.',
        ];
    }

    /**
     * @param array $data
     * @return array ['success' => bool, 'message' => string (optional, if any)]
     */
    private function validate(array $data): array
    {
        // Required fields
        $required = ['name', 'email', 'phone', 'status'];
        foreach ($required as $field) {
            if (empty($data[$field])) { // missing or empty
                return [
                    'success' => false,
                    'message' => ucfirst($field) . ' is required.'
                ];
            }
        }

        // Email validation
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) { // invalid email format
            return [
                'success' => false,
                'message' => 'Invalid email format.'
            ];
        }

        // Optional: dropdown validation
        $validStatuses = ["Active", "On Leave", "Graduated", "Inactive"];
        if (!in_array($data['status'], $validStatuses)) { // status not in dropdown
            return [
                'success' => false,
                'message' => 'Invalid status value.'
            ];
        }

        // all good
        return ['success' => true];
    }
}