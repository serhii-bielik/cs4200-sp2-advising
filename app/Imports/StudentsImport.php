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
        if (isset($row[0]) && isset($row[1]) && isset($row[2]) && isset($row[3])
            && $row[1] != 'name' && isset($this->faculties[$row[3]])) {

            $email = "u{$row[0]}@au.edu";

            if (!$this->isUserExists($row[0], $email)) {
                return new User([
                    'au_id' => strval($row[0]),
                    'name' => $row[1],
                    'email' => $email,
                    'faculty_id' => $this->faculties[$row[3]],
                    'credits' => isset($row[4]) ? intval($row[4]) : 0,
                    'gpa' => isset($row[5]) ? floatval($row[5]) : 0,
                    'group_id' => UserGroup::Student,
                ]);
            }
        }

        return null;
    }

    private function isUserExists($auId, $email)
    {
        return User::where('au_id', $auId)->orWhere('email', $email)->count() > 0;
    }
}
