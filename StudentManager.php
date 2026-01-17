<?php

class StudentManager {

    private string $filePath;

    public function __construct(string $filePath = 'students.json') {
        $this->filePath = $filePath;
    }

    /**
     * @return array
     */
    public function getAllStudents(): array
    {
        return $this->readFile();
    }
    
    /**
     * @param array $data
     * @return array ['success' => bool, 'message' => string]
     */
    public function create(array $data): array
    {
        $data = array_map('trim', $data);

        $students = $this->getAllStudents();
        // validate the data first
        $validation = $this->validate($data, false, $students);
        if (!$validation['success']) {
            return $validation; // if validation fails, no need to proceed
        }

        // explicit mapping with desired order
        $students[] = [
            'id'     => $this->generateId($students),
            'name'   => trim($data['name']),
            'email'  => trim($data['email']),
            'phone'  => trim($data['phone']),
            'status' => trim($data['status']),
        ];

        if($this->writeFile($students)) {
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
        $data = array_map('trim', $data);
        
        // validate the data first
        $validation = $this->validate($data, true);
        if (!$validation['success']) {
            return $validation; // if validation fails, no need to proceed
        }

        // get all students
        $students = $this->getAllStudents();

        foreach ($students as $i => $student) { 
            if ($student['id'] == $id) {    // id matched
                $students[$i] = [
                    'id'     => $student['id'], // keep the same id
                    'name'   => trim($data['name']),
                    'email'  => trim($data['email']),
                    'phone'  => trim($data['phone']),
                    'status' => trim($data['status']),
                ];
                
                if ($this->writeFile($students)) {
                    return [
                        'success' => true,
                        'message' => "Student '{$data['name']}' updated successfully.",
                    ];
                }
            }
        }

        return [
            'success' => false,
            'message' => "Student with ID '{$id}' not found.",
        ];
    }

    /**
     * @param string $id
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

                if ($this->writeFile($students)) {
                    return [
                        'success' => true,
                        'message' => "Student '{$name}' deleted successfully.",
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => "Failed to delete student.",
                ];
            }
        }

        return [
            'success' => false,
            'message' => "Student with ID '{$id}' not found.",
        ];
    }

    /**
     * @return array
     */
    private function readFile(): array 
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $content = file_get_contents($this->filePath);
        
        return json_decode($content, true) ?? [];
    }

    /**
     * 
     * @param array $data
     * @return bool
     */
    private function writeFile(array $data): bool 
    {
        $json = json_encode(array_values($data), JSON_PRETTY_PRINT);
        $write = file_put_contents($this->filePath, $json);

        return (bool) $write;
    }

    /**
     * @param array $data
     * @return array ['success' => bool, 'message' => string (optional, if any)]
     */
    private function validate(array $data,bool $isUpdate = false, array $existingStudents = []): array
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
            $students = $existingStudents ?: $this->getAllStudents();
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
        if (empty($students)) return 1;
    
        return max(array_column($students, 'id')) + 1;
    }
}