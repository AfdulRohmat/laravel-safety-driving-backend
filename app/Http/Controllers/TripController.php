<?php

namespace App\Http\Controllers;

use App\Enums\ProsesPerjalanan;
use App\Http\Responses\CommonResponse;
use App\Models\FaceMonitoring;
use App\Models\Group;
use App\Models\Trip;
use App\Models\TripMonitoring;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    public function addTrip(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'jadwalPerjalanan' => 'required|date',
                'alamatAwal' => 'required|string|max:255',
                'latitudeAwal' => 'required|string|max:255',
                'longitudeAwal' => 'required|string|max:255',
                'alamatTujuan' => 'required|string|max:255',
                'latitudeTujuan' => 'required|string|max:255',
                'longitudeTujuan' => 'required|string|max:255',
                'namaKendaraan' => 'required|string|max:255',
                'noPolisi' => 'required|string|max:20',
                'groupId' => 'required|integer|exists:groups,id',
                'driverId' => 'required|integer|exists:users,id',
                'tinggiBadan' => 'required|string|max:10',
                'beratBadan' => 'required|string|max:10',
                'tekananDarah' => 'required|string|max:20',
                'riwayatPenyakit' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the user driver by ID
            $userDriver = User::find($request->driverId);
            if (!$userDriver) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Akun tidak valid, silahkan login ulang']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the group by ID
            $group = Group::find($request->groupId);
            if (!$group) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Group tidak ditemukan / tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Create a new trip
            $trip = new Trip();
            $trip->jadwal_perjalanan = $request->jadwalPerjalanan;
            $trip->alamat_awal = $request->alamatAwal;
            $trip->latitude_awal = $request->latitudeAwal;
            $trip->longitude_awal = $request->longitudeAwal;
            $trip->alamat_tujuan = $request->alamatTujuan;
            $trip->latitude_tujuan = $request->latitudeTujuan;
            $trip->longitude_tujuan = $request->longitudeTujuan;
            $trip->nama_kendaraan = $request->namaKendaraan;
            $trip->no_polisi = $request->noPolisi;
            $trip->status = ProsesPerjalanan::BELUM_DIMULAI;
            $trip->driver_id = $userDriver->id;
            $trip->group_id = $group->id;
            $trip->dimulai_pada = null;
            $trip->diakhiri_pada = null;
            $trip->tinggi_badan_driver = $request->tinggiBadan;
            $trip->berat_badan_driver = $request->beratBadan;
            $trip->tekanan_darah_driver = $request->tekananDarah;
            $trip->riwayat_penyakit_driver = $request->riwayatPenyakit;

            // Generate a 6-digit token
            $trip->trip_token = strval(rand(100000, 999999));

            $trip->save();

            $successResponse = new CommonResponse(200, 'Trip berhasil ditambahkan', $trip, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function getAllTrips(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'groupId' => 'required|integer|exists:groups,id',
                'status' => 'nullable|string|max:255', // Status is optional
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }
            // Query trips
            $tripQuery = Trip::query()
                ->with('driver') // Eager load the driver relationship
                ->where('group_id', $request->groupId);

            if (isset($validated['status'])) {
                $tripQuery->where('status', $request->status);
            }

            $trips = $tripQuery->orderBy('created_at', 'desc')->get();

            $successResponse = new CommonResponse(200, 'Trips retrieved successfully', $trips, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function getTripByToken(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'tripToken' => 'required|string|max:6',
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the trip by token
            $trip = Trip::where('trip_token', $request->tripToken)->first();

            if (!$trip) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Data trip tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Return the trip data
            $successResponse = new CommonResponse(200, 'Proses berhasil', $trip, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function changeTripStatus(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'tripToken' => 'required|string|max:6',
                'status' => 'required|in:Belum Dimulai,Dalam Perjalanan,Selesai',
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the trip by token
            $trip = Trip::where('trip_token', $request->tripToken)->first();

            if (!$trip) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Data trip tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Update status and timestamps
            if ($trip->status === 'Belum Dimulai' && $request->status === 'Dalam Perjalanan') {
                $trip->dimulai_pada = now();
            }

            if ($trip->status === 'Dalam Perjalanan' && $request->status === 'Selesai') {
                $trip->diakhiri_pada = now();
            }

            // Update the trip status
            $trip->status = $request->status;

            // Calculate trip duration if both start and end times are set
            if ($trip->dimulaiPada !== null && $trip->diakhiriPada !== null) {
                $differenceInMinutes = $trip->diakhiriPada->diffInMinutes($trip->dimulaiPada);
                $trip->durasiPerjalanan = (string)$differenceInMinutes;
            }

            // Save the updated trip
            $trip->save();

            // Return the updated trip data
            $successResponse = new CommonResponse(200, 'Proses berhasil', $trip, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function deleteTrip(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'tripToken' => 'required|string|max:6',
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the trip by token
            $trip = Trip::where('trip_token', $request->tripToken)->first();

            if (!$trip) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Data trip tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Delete the trip
            $trip->delete();

            // Return success response
            $successResponse = new CommonResponse(200, 'Trip berhasil dihapus', null, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function addTripMonitoring(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'heartRate' => 'required|string',
                'latitude' => 'required|string',
                'longitude' => 'required|string',
                'status' => 'required|string',
                'kecepatan' => 'required|string',
                'rpm' => 'required|string',
                'thurttle' => 'required|string',
                'sudutPostural' => 'required|string',
                'kecepatanPostural' => 'required|string',
                'durasiPostural' => 'required|string',
                'tripToken' => 'required|string|max:6',
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the trip by token
            $trip = Trip::where('trip_token', $request->tripToken)->first();

            if (!$trip) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Data trip tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Create a new TripMonitoring instance
            $tripMonitoring = new TripMonitoring();
            $tripMonitoring->heart_rate = $request->heartRate;
            $tripMonitoring->latitude = $request->latitude;
            $tripMonitoring->longitude = $request->longitude;
            $tripMonitoring->kecepatan = $request->kecepatan;
            $tripMonitoring->rpm = $request->rpm;
            $tripMonitoring->thurttle = $request->thurttle;
            $tripMonitoring->sudut_postural = $request->sudutPostural;
            $tripMonitoring->kecepatan_postural = $request->kecepatanPostural;
            $tripMonitoring->durasi_postural = $request->durasiPostural;
            $tripMonitoring->status = $request->status;
            $tripMonitoring->trip_token = $request->tripToken;

            // Save the trip monitoring data
            $tripMonitoring->save();

            // Return success response
            $successResponse = new CommonResponse(200, 'Proses berhasil', $tripMonitoring, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function addFaceMonitoring(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'perclos' => 'required|string',
                'tripToken' => 'required|string|max:6',
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the trip by token
            $trip = Trip::where('trip_token', $request->tripToken)->first();

            if (!$trip) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Data trip tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Create a new FaceMonitoring instance
            $faceMonitoring = new FaceMonitoring();
            $faceMonitoring->perclos = $request->perclos;
            $faceMonitoring->trip_token = $request->tripToken;

            // Save the face monitoring data
            $faceMonitoring->save();

            // Return success response
            $successResponse = new CommonResponse(200, 'Proses berhasil', $faceMonitoring, null);
            return response()->json($successResponse->toArray(), $successResponse->statusCode);
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function getTripMonitoringSSE(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'tripToken' => 'required|string|max:6',
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the trip by token
            $trip = Trip::where('trip_token', $request->tripToken)->first();
            if (!$trip) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Data trip tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');

            // Create an interval for 1 second and stream data
            while (true) {
                $tripMonitoringData = TripMonitoring::where('trip_token', $request->tripToken)->get();

                echo "data: " . json_encode($tripMonitoringData) . "\n\n";
                ob_flush();
                flush();

                // Delay to control the stream rate
                sleep(5);
            }
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }

    public function getFaceMonitoringSSE(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'tripToken' => 'required|string|max:6',
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Find the trip by token
            $trip = Trip::where('trip_token', $request->tripToken)->first();
            if (!$trip) {
                $errorResponse = new CommonResponse(400, 'Proses gagal', null, ['Data trip tidak valid']);
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }

            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');

            // Create an interval for 10 seconds and stream data
            while (true) {
                // Retrieve face monitoring data
                $faceMonitoringData = FaceMonitoring::where('trip_token', $request->tripToken)->get();

                echo "data: " . json_encode($faceMonitoringData) . "\n\n";
                ob_flush();
                flush();

                // Delay to control the stream rate
                sleep(5);
            }
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }


    public function template(Request $request)
    {
        try {
            $currentUserEmail =  Auth::user()->email;

            $validator = Validator::make($request->all(), [
                'groupId' => 'required|integer|exists:groups,id',
                'status' => 'nullable|string|max:255', // Status is optional
            ]);

            if ($validator->fails()) {
                $errorResponse = new CommonResponse(422, 'Validation error', null, $validator->errors()->all());
                return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
            }
        } catch (\Exception $e) {
            // Handle other exceptions
            $errorResponse = new CommonResponse(500, 'Proses gagal', null, [$e->getMessage()]);
            return response()->json($errorResponse->toArray(), $errorResponse->statusCode);
        }
    }
}
