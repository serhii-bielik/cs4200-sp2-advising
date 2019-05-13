<?php

namespace App\Imports;

use App\Faculty;
use App\User;
use App\UserGroup;
use Maatwebsite\Excel\Concerns\ToModel;

class StudentsImport implements ToModel
{
    private $faculties;

    public function __construct()
    {
        $this->faculties = [];
        foreach (Faculty::all() as $faculty) {
            $this->faculties[$faculty->code] = $faculty->id;
        }

        //TODO: Clean uploads dir
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (isset($row[0]) && isset($row[1]) && isset($row[2]) && isset($row[4])
            && $row[1] != 'name' && isset($this->faculties[$row[4]])) {

            $email = "u{$row[0]}@au.edu";

            if (!$this->isUserExists($row[0], $email)) {
                return new User([
                    'au_id' => strval($row[0]),
                    'name' => $row[1],
                    'email' => $email,
                    'faculty_id' => $this->faculties[$row[4]],
                    'credits' => isset($row[5]) ? intval($row[5]) : 0,
                    'gpa' => isset($row[6]) ? floatval($row[6]) : 0,
                    'group_id' => UserGroup::Student,
                ]);
            } else {
                session()->flash('message', "Student with #{$row[0]} or email {$email} already exists.");
                session()->flash('alert-class', 'alert-danger');
            }
        }

        return null;
    }

    private function isUserExists($auId, $email)
    {
        return User::where('au_id', $auId)->orWhere('email', $email)->count() > 0;
    }
}
