<?php

namespace App\Imports;

use App\AdviserAdvisee;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;

class StudentAdviserRelationImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (isset($row[0]) && isset($row[1]) && isset($row[2]) && isset($row[3])
            && $row[1] != 'name') {

            $student = $this->getUserByAuid($row[0]);
            $adviser = $this->getUserByAuid($row[3]);

            if ($student && $adviser && !$this->studentIsConnectedToAdviser($student, $adviser)) {

                return new AdviserAdvisee([
                    'adviser_id' => $adviser->id,
                    'advisee_id' => $student->id,
                    'director_id' => auth()->user()->id,
                ]);
            }
        }

        return null;
    }

    private function studentIsConnectedToAdviser($student, $adviser)
    {
        return AdviserAdvisee::where('adviser_id', $adviser->id)->
            where('advisee_id', $student->id)->count() > 0;
    }

    private function getUserByAuid($auId)
    {
        return User::where('au_id', $auId)->first();
    }
}
