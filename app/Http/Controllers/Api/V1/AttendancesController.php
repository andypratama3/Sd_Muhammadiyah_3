<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Attendances;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class AttendancesController extends Controller
{
    protected $cloud_id;
    protected $token;
    protected $base_url;
    protected $endpoints = [
        'get_info' => '/api/get_userinfo',
        'set_user' => '/api/set_userinfo',
        'register' => '/api/reg_online',
        'delete' => '/api/delete_userinfo',
        "set_time" => '/api/set_time',
    ];

    public function __construct()
    {
        $this->cloud_id = config('fingerspot.cloud_id');
        $this->token = config('fingerspot.token_fingerspot');
        $this->base_url = 'https://developer.fingerspot.io';

        // Validate required configuration
        if (empty($this->cloud_id) || empty($this->token)) {
            throw new \RuntimeException('FingerSpot configuration is incomplete. Please check cloud_id and token settings.');
        }
    }

    public function webhook(Request $request)
    {
        try {
            $data = $request->json()->all();

            // find user by pin
            $user = Siswa::where('id', $data['data']['pin'])->first();

            if (!$user) {
                return response()->json(['error' => 'Siswa not found'], 404);
            }

            if (empty($data['type'])) {
                return response()->json(['error' => 'Invalid data type'], 400);
            }

            // Validate cloud_id if present
            if (isset($data['cloud_id']) && $data['cloud_id'] !== $branchSetting->cloud_id) {
                return response()->json(['error' => 'Invalid cloud_id'], 400);
            }

            // Create attendance log
            $att_log = Attendances::create([
                'type' => $data['type'],
                'cloud_id' => $data['cloud_id'] ?? null,
                'data' => json_encode($data['data'] ?? null),
                'date' => Carbon::now()->format('Y-m-d'),
                'created_at' => now()
            ]);


            $logData = json_decode($att_log->data, true);

            if ($data['type'] === 'attlog') {
                return $this->handleAttendanceLog($data['data'] ?? []);
            }

            if ($data['type'] === 'delete_userinfo') {
               return $this->deleteUser($user);
            }

            if ($data['type'] === 'register_online') {
                // Handle device status
                return response()->json(['status' => 'success', 'message' => 'User successfully registered to the cloud']);
            }

            if ($data['type'] === 'set_time') {
                return $this->setDeviceTime($data['data']['timezone']);
            }

            if ($data['type'] === 'get_userid_list') {

                foreach ($logData['pin_arr'] as $pin) {
                    $user = User::where('id', $pin)->first();

                    if (!$user) {
                        return response()->json(['status' => 'error', 'message' => 'User not found for pin: ' . $pin]);
                    }
                }

                return response()->json(['status' => 'success', 'message' => 'All users processed successfully']);
            }

            return response()->json(['status' => 'error', 'message' => 'Unknown data type']);

        } catch (\Exception $e) {
            \Log::error('Webhook Processing Error', [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function register($user, $password, $privilege)
    {
        if (!$user) {
            return response()->json(['error' => 'Invalid user data provided'], 400);
        }

        // unix timestamp as transaction id
        $trans_id = $user->trans_id ?? time();

        // Prepare request payloads
        $data_info = [
            "trans_id" => $trans_id,
            "cloud_id" => $this->cloud_id,
            "pin" => $trans_id,
        ];

        $data_register = [
            "trans_id" => $trans_id,
            "cloud_id" => $this->cloud_id,
            "pin" => $trans_id,
            "verification" => "",
        ];

        $data_create = [
            "trans_id" => $trans_id,
            "cloud_id" => $this->cloud_id,
            "data" => [
                "pin" => $trans_id,
                "name" => $user->name,
                "privilege" => $privilege,
                "password" => $password,
                "template" => "",
            ],
        ];

        // 1. Check existing user
        $response_info = $this->sendPostRequest(
            $this->base_url . $this->endpoints['get_info'],
            $data_info
        );

        // Check if user exists
        if (isset($response_info['success']) && $response_info['success'] && !empty($response_info['data'])) {
            return response()->json([
                'message' => 'User already exists',
                'data' => $response_info['data']
            ]);
        }

        // 2. Register user
        $response_register = $this->sendPostRequest(
            $this->base_url . $this->endpoints['register'],
            $data_register
        );

        if (!isset($response_register['success']) || !$response_register['success']) {
            throw new \Exception('Failed to register user: ' . json_encode($response_register));
        }

        // 3. Create user info
        $response_create = $this->sendPostRequest(
            $this->base_url . $this->endpoints['set_user'],
            $data_create
        );

        if (!isset($response_create['success']) || !$response_create['success']) {
            throw new \Exception('Failed to create user info: ' . json_encode($response_create));
        }

        // if trans_id is not set, update the user with
        // the latest transaction id
        if ($user->trans_id == null) {
            $user->update([
                'trans_id' => $trans_id
            ]);
        }

        return response()->json([
            'message' => 'User registered successfully',
            'data' => $response_create
        ]);
    }

     protected function handleAttendanceLog($logData)
    {
        if (!isset($logData['pin'], $logData['scan'])) {
            return response()->json(['error' => 'Invalid attendance log data'], 400);
        }

        $user = User::where('trans_id', $logData['pin'])->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $scanTime = Carbon::parse($logData['scan']);
        error_log('Scan time: ' . $scanTime->format('Y-m-d H:i:s'));

        // First check if there's an existing attendance for today
        $existingAttendance = Attendances::where('user_id', $user->id)
            ->where('date', $scanTime->format('Y-m-d'))
            ->first();

        // If attendance exists, immediately update it as checkout
        if ($existingAttendance) {
            $existingAttendance->update([
                'time_out' => $scanTime->format('H:i:s'),
                'status' => 'OUT',
                'type_at' => 'out'
            ]);

            return response()->json(['status' => 'success', 'data' => $existingAttendance]);
        }

        // If no existing attendance, proceed with creating new check-in
        $day = strtolower($scanTime->locale('id')->dayName);
        $shift = ShiftEmployees::where('user_id', $user->id)
            ->where('work_day', $day)
            ->first();

        if (!$shift) {
            return response()->json(['error' => 'Shift not found for ' . $day], 404);
        }

        $startTime = Carbon::parse($scanTime->format('Y-m-d') . ' ' . $shift->shiftSettings->start_time);
        $endTime = Carbon::parse($scanTime->format('Y-m-d') . ' ' . $shift->shiftSettings->end_time);

        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }

        $lateSeconds = $scanTime->gt($startTime) ? $scanTime->diffInSeconds($startTime) : 0;
        $fine = $this->calculateFine($user->branch_id, $lateSeconds);

        // Create new attendance record for check-in
        $attendance = Attendances::create([
            'user_id' => $user->id,
            'date' => $scanTime->format('Y-m-d'),
            'branch_id' => $user->branch_id,
            'time' => $scanTime->format('H:i:s'),
            'status' => 'H',
            'type_at' => 'in',
            'late_att_fine' => $lateSeconds,
            'fine' => $fine,
        ]);

        return response()->json(['status' => 'success', 'data' => $attendance]);
    }

}
