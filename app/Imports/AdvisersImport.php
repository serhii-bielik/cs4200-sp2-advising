<?php

namespace App\Imports;

use App\Faculty;
use App\User;
use App\UserGroup;
use Maatwebsite\Excel\Concerns\ToModel;

class AdvisersImport implements ToModel
{
    private $faculties;

    public function __construct()
    {
        $this->faculties = [];
        foreach (Faculty::all() as $faculty) {
            $this->faculties[$faculty->code] = $faculty->id;
        }
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (isset($row[0]) && isset($row[1]) && isset($row[2]) && isset($row[3]) && isset($row[4])
            && $row[2] != 'email' && isset($this->faculties[$row[3]])) {

            if (!$this->isUserExists($row[0], $row[2])) {
                return new User([
                    'au_id' => strval($row[0]),
                    'name' => $row[1],
                    'email' => $row[2],
                    'faculty_id' => $this->faculties[$row[3]],
                    'group_id' => intval($row[4]) == 1 ? UserGroup::Director : UserGroup::Adviser,
                ]);
            } else {
                session()->flash('message', "Adviser with #{$row[0]} or email {$row[2]} already exists.");
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
