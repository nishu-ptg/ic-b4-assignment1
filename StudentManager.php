<?php

class StudentManager {

    public function getAllStudents(): array
    {
        return json_decode(file_get_contents('students.json'), true) ?? [];
    }
    
    public function create(array $data): array
    {
        $students = json_decode(file_get_contents('students.json'), true) ?? [];

        $students[] = array_merge($data, [
            'id' => time(),
        ]);

        if (file_put_contents('students.json', json_encode($students, JSON_PRETTY_PRINT))) {
            return [
                'success' => true,
                'message' => 'Student created successfully.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create student.',
        ];
    }
}