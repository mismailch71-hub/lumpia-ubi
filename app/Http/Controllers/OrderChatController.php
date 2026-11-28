<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderChatController extends Controller
{
    public function index(int $orderId)
    {
        $messages = DB::table('order_messages')
            ->where('order_id', $orderId)
            ->orderBy('id')
            ->get()
            ->map(function ($row) {
                return [
                    'id'          => $row->id,
                    'order_id'    => $row->order_id,
                    'sender_id'   => $row->sender_id,
                    'sender_role' => $row->sender_role,
                    'sender_name' => $row->sender_name,
                    'message'     => $row->message,
                    'time'        => date('H:i', strtotime($row->created_at)),
                ];
            });

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $role = 'customer';
        if ($user && method_exists($user, 'hasRole')) {
            if ($user->hasRole('kurir')) {
                $role = 'kurir';
            } elseif ($user->hasRole('admin')) {
                $role = 'admin';
            }
        }

        $senderName = $user->name ?? ucfirst($role);
        $now = now();

        $id = DB::table('order_messages')->insertGetId([
            'order_id'    => $orderId,
            'sender_id'   => $user->id ?? null,
            'sender_role' => $role,
            'sender_name' => $senderName,
            'message'     => trim($validated['message']),
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        return response()->json([
            'message' => [
                'id'          => $id,
                'order_id'    => $orderId,
                'sender_id'   => $user->id ?? null,
                'sender_role' => $role,
                'sender_name' => $senderName,
                'message'     => trim($validated['message']),
                'time'        => $now->format('H:i'),
            ],
        ], 201);
    }
}