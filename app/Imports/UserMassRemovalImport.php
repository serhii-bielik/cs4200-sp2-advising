<?php

namespace App\Imports;

use App\User;
use App\UserGroup;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class UserMassRemovalImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row)
        {
            if ($row[0] == 'au id' || $row[0] == '') {
                continue;
            }

            $user = User::where('au_id', $row[0])
                    ->where('group_id', '<>', UserGroup::Admin)->first();

            if ($user) {
                $user->delete();
            }
        }
    }
}
