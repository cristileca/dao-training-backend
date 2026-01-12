<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function tree()
    {
        $users = User::all()->map(function ($user) {
            return [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'parentId' => $user->referral_id,
            ];
        });

        return response()->json($users);
    }

    public function subtree($id)
    {
        $users = User::all();

        $getDescendants = function ($parentId) use ($users, &$getDescendants) {
            $children = $users->where('referral_id', $parentId)->values();
            $descendants = collect();
            foreach ($children as $child) {
                $descendants->push($child);
                $descendants = $descendants->concat($getDescendants($child->id));
            }
            return $descendants;
        };

        $tree = $getDescendants($id);

        $root = $users->where('id', $id)->first();
        return response()->json(collect([$root])->concat($tree));
    }

}
