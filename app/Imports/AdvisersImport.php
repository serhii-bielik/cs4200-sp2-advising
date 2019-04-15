<?php

namespace App\Imports;

use App\User;
use App\UserGroup;
use Maatwebsite\Excel\Concerns\ToModel;

class AdvisersImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (isset($row[0]) && isset($row[1]) && isset($row[2]) && isset($row[3])
            && $row[2] != 'email') {

            if (!$this->isUserExists($row[0], $row[2])) {
                return new User([
                    'au_id' => strval($row[0]),
                    'name' => $row[1],
                    'email' => $row[2],
                    'group_id' => intval($row[3]) == 1 ? UserGroup::Director : UserGroup::Adviser,
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
