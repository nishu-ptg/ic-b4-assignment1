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
        // $students[] = array_merge($data, [
        //     // 'id' => time(),
        //     'id' => $this->generateId($students),
        // ]);

        // explicit mapping with desired order
        $students[] = [
            'id'     => $this->generateId($students),
            'name'   => trim($data['name']),
            'email'  => trim($data['email']),
            'phone'  => trim($data['phone']),
            'status' => trim($data['status']),
        ];

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
        // validate the data first
        $validation = $this->validate($data, true);
        if (!$validation['success']) {
            return $validation; // if validation fails, no need to proceed
        }

        // get all students
        $students = $this->getAllStudents();

        foreach ($students as $i => $student) { 
            if ($student['id'] == $id) {    // id matched
                // merge it
                // $students[$i] = array_merge($student, $data);
                $students[$i] = [
                    'id'     => $student['id'], // keep the same id
                    'name'   => trim($data['name']),
                    'email'  => trim($data['email']),
                    'phone'  => trim($data['phone']),
                    'status' => trim($data['status']),
                ];
                
                // try to save
                if (file_put_contents('students.json', json_encode($students, JSON_PRETTY_PRINT))) {
                    return [
                        'success' => true,
                        'message' => "Student '{$data['name']}' updated successfully.",
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
                $name = $student['name'];   // save name for message
                // remove it
                array_splice($students, $i, 1);

                // try to save
                if (file_put_contents('students.json', json_encode($students, JSON_PRETTY_PRINT))) {
                    return [
                        'success' => true,
                        'message' => "Student '{$name}' deleted successfully.",
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
    private function validate(array $data,bool $isUpdate = false): array
    {
        // required fields
        $required = ['name', 'email', 'phone', 'status'];
        foreach ($required as $field) {
            if (empty($data[$field])) { // missing or empty
                return [
                    'success' => false,
                    'message' => ucfirst($field) . ' is required.'
                ];
            }
        }

        // email validation
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) { // invalid email format
            return [
                'success' => false,
                'message' => 'Invalid email format.'
            ];
        }

        // optional: dropdown validation
        $validStatuses = ["Active", "On Leave", "Graduated", "Inactive"];
        if (!in_array($data['status'], $validStatuses)) { // status not in dropdown
            return [
                'success' => false,
                'message' => 'Invalid status value.'
            ];
        }

        // optional: unique when create
        if (!$isUpdate) {
            $students = $this->getAllStudents();
            // unique fields, not sure if name and phone should be unique too
            // so just email for now, array structure ensures easy to add/remove fields
            $uniqueFields = ['email',];

            foreach ($students as $student) {
                foreach ($uniqueFields as $field) {
                    if (strtolower($student[$field]) == strtolower($data[$field])) {
                        return [
                            'success' => false, 
                            'message' => ucfirst($field) . " '{$data[$field]}' already exists."
                        ];
                    }
                }
            }
        }

        // all good
        return ['success' => true];
    }

    /**
     * 
     * @param array $students
     * @return int
     */
    private function generateId(array $students): int 
    {
        if (empty($students)) { // no students
            return 1;
        }

        // array of all ids
        $ids = array_column($students, 'id');
        
        // max + 1
        return max($ids) + 1;
    }
}