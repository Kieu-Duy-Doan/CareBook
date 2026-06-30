<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\PatientProfile;
use App\Models\DoctorProfile;
use App\Models\User;
use App\Models\Specialty;
use App\Models\Room;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $bsAn = DoctorProfile::where('doctor_code', 'BS001')->first();
        $bsBich = DoctorProfile::where('doctor_code', 'BS002')->first();
        $tm = Specialty::where('name', 'Tim mạch')->first();
        $rhm = Specialty::where('name', 'Răng Hàm Mặt')->first();
        $p101 = Room::where('room_number', 'P101')->first();
        $p201 = Room::where('room_number', 'P201')->first();
        $today = date('Y-m-d');

        $appointments = [
            [
                'code' => 'APT' . time() . '001',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:00:00', 'status' => 'completed',
                'patient_id' => 1, 'owner_id' => 19
            ],
            [
                'code' => 'APT' . time() . '002',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:15:00', 'status' => 'completed',
                'patient_id' => 1, 'owner_id' => 19
            ],
            [
                'code' => 'APT' . time() . '003',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:30:00', 'status' => 'completed',
                'patient_id' => 2, 'owner_id' => 19
            ],
            [
                'code' => 'APT' . time() . '004',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:45:00', 'status' => 'completed',
                'patient_id' => 2, 'owner_id' => 19
            ],
            [
                'code' => 'APT' . time() . '005',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:00:00', 'status' => 'completed',
                'patient_id' => 3, 'owner_id' => 20
            ],
            [
                'code' => 'APT' . time() . '006',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:15:00', 'status' => 'pending',
                'patient_id' => 3, 'owner_id' => 20
            ],
            [
                'code' => 'APT' . time() . '007',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:30:00', 'status' => 'pending',
                'patient_id' => 4, 'owner_id' => 41
            ],
            [
                'code' => 'APT' . time() . '008',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:45:00', 'status' => 'pending',
                'patient_id' => 4, 'owner_id' => 41
            ],
            [
                'code' => 'APT' . time() . '009',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:00:00', 'status' => 'examining',
                'patient_id' => 5, 'owner_id' => 41
            ],
            [
                'code' => 'APT' . time() . '010',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '09:15:00', 'status' => 'completed',
                'patient_id' => 5, 'owner_id' => 41
            ],
            [
                'code' => 'APT' . time() . '011',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:30:00', 'status' => 'completed',
                'patient_id' => 6, 'owner_id' => 41
            ],
            [
                'code' => 'APT' . time() . '012',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '09:45:00', 'status' => 'completed',
                'patient_id' => 6, 'owner_id' => 41
            ],
            [
                'code' => 'APT' . time() . '013',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '10:00:00', 'status' => 'completed',
                'patient_id' => 7, 'owner_id' => 41
            ],
            [
                'code' => 'APT' . time() . '014',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '10:15:00', 'status' => 'pending',
                'patient_id' => 7, 'owner_id' => 41
            ],
            [
                'code' => 'APT' . time() . '015',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '10:30:00', 'status' => 'completed',
                'patient_id' => 8, 'owner_id' => 42
            ],
            [
                'code' => 'APT' . time() . '016',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '10:45:00', 'status' => 'completed',
                'patient_id' => 8, 'owner_id' => 42
            ],
            [
                'code' => 'APT' . time() . '017',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '13:00:00', 'status' => 'completed',
                'patient_id' => 9, 'owner_id' => 42
            ],
            [
                'code' => 'APT' . time() . '018',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '13:15:00', 'status' => 'completed',
                'patient_id' => 9, 'owner_id' => 42
            ],
            [
                'code' => 'APT' . time() . '019',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '13:30:00', 'status' => 'completed',
                'patient_id' => 10, 'owner_id' => 42
            ],
            [
                'code' => 'APT' . time() . '020',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '13:45:00', 'status' => 'pending',
                'patient_id' => 10, 'owner_id' => 42
            ],
            [
                'code' => 'APT' . time() . '021',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '14:00:00', 'status' => 'completed',
                'patient_id' => 11, 'owner_id' => 42
            ],
            [
                'code' => 'APT' . time() . '022',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '14:15:00', 'status' => 'pending',
                'patient_id' => 11, 'owner_id' => 42
            ],
            [
                'code' => 'APT' . time() . '023',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '14:30:00', 'status' => 'completed',
                'patient_id' => 12, 'owner_id' => 43
            ],
            [
                'code' => 'APT' . time() . '024',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '14:45:00', 'status' => 'examining',
                'patient_id' => 12, 'owner_id' => 43
            ],
            [
                'code' => 'APT' . time() . '025',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '15:00:00', 'status' => 'pending',
                'patient_id' => 13, 'owner_id' => 43
            ],
            [
                'code' => 'APT' . time() . '026',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '15:15:00', 'status' => 'examining',
                'patient_id' => 13, 'owner_id' => 43
            ],
            [
                'code' => 'APT' . time() . '027',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '15:30:00', 'status' => 'pending',
                'patient_id' => 14, 'owner_id' => 43
            ],
            [
                'code' => 'APT' . time() . '028',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '15:45:00', 'status' => 'pending',
                'patient_id' => 14, 'owner_id' => 43
            ],
            [
                'code' => 'APT' . time() . '029',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '16:00:00', 'status' => 'examining',
                'patient_id' => 15, 'owner_id' => 43
            ],
            [
                'code' => 'APT' . time() . '030',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '16:15:00', 'status' => 'pending',
                'patient_id' => 15, 'owner_id' => 43
            ],
            [
                'code' => 'APT' . time() . '031',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '16:30:00', 'status' => 'examining',
                'patient_id' => 16, 'owner_id' => 44
            ],
            [
                'code' => 'APT' . time() . '032',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '16:45:00', 'status' => 'pending',
                'patient_id' => 16, 'owner_id' => 44
            ],
            [
                'code' => 'APT' . time() . '033',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:00:00', 'status' => 'pending',
                'patient_id' => 17, 'owner_id' => 44
            ],
            [
                'code' => 'APT' . time() . '034',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:15:00', 'status' => 'examining',
                'patient_id' => 17, 'owner_id' => 44
            ],
            [
                'code' => 'APT' . time() . '035',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:30:00', 'status' => 'pending',
                'patient_id' => 18, 'owner_id' => 44
            ],
            [
                'code' => 'APT' . time() . '036',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:45:00', 'status' => 'examining',
                'patient_id' => 18, 'owner_id' => 44
            ],
            [
                'code' => 'APT' . time() . '037',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:00:00', 'status' => 'pending',
                'patient_id' => 19, 'owner_id' => 44
            ],
            [
                'code' => 'APT' . time() . '038',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:15:00', 'status' => 'examining',
                'patient_id' => 19, 'owner_id' => 44
            ],
            [
                'code' => 'APT' . time() . '039',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:30:00', 'status' => 'pending',
                'patient_id' => 20, 'owner_id' => 45
            ],
            [
                'code' => 'APT' . time() . '040',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:45:00', 'status' => 'examining',
                'patient_id' => 20, 'owner_id' => 45
            ],
            [
                'code' => 'APT' . time() . '041',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:00:00', 'status' => 'pending',
                'patient_id' => 21, 'owner_id' => 45
            ],
            [
                'code' => 'APT' . time() . '042',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '09:15:00', 'status' => 'pending',
                'patient_id' => 21, 'owner_id' => 45
            ],
            [
                'code' => 'APT' . time() . '043',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:30:00', 'status' => 'pending',
                'patient_id' => 22, 'owner_id' => 45
            ],
            [
                'code' => 'APT' . time() . '044',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '09:45:00', 'status' => 'examining',
                'patient_id' => 22, 'owner_id' => 45
            ],
            [
                'code' => 'APT' . time() . '045',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '10:00:00', 'status' => 'pending',
                'patient_id' => 23, 'owner_id' => 45
            ],
            [
                'code' => 'APT' . time() . '046',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '10:15:00', 'status' => 'pending',
                'patient_id' => 23, 'owner_id' => 45
            ],
            [
                'code' => 'APT' . time() . '047',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '10:30:00', 'status' => 'pending',
                'patient_id' => 24, 'owner_id' => 46
            ],
            [
                'code' => 'APT' . time() . '048',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '10:45:00', 'status' => 'pending',
                'patient_id' => 24, 'owner_id' => 46
            ],
            [
                'code' => 'APT' . time() . '049',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '13:00:00', 'status' => 'examining',
                'patient_id' => 25, 'owner_id' => 46
            ],
            [
                'code' => 'APT' . time() . '050',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '13:15:00', 'status' => 'pending',
                'patient_id' => 25, 'owner_id' => 46
            ],
            [
                'code' => 'APT' . time() . '051',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '13:30:00', 'status' => 'pending',
                'patient_id' => 26, 'owner_id' => 46
            ],
            [
                'code' => 'APT' . time() . '052',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '13:45:00', 'status' => 'pending',
                'patient_id' => 26, 'owner_id' => 46
            ],
            [
                'code' => 'APT' . time() . '053',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '14:00:00', 'status' => 'pending',
                'patient_id' => 27, 'owner_id' => 46
            ],
            [
                'code' => 'APT' . time() . '054',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '14:15:00', 'status' => 'examining',
                'patient_id' => 27, 'owner_id' => 46
            ],
            [
                'code' => 'APT' . time() . '055',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '14:30:00', 'status' => 'pending',
                'patient_id' => 28, 'owner_id' => 47
            ],
            [
                'code' => 'APT' . time() . '056',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '14:45:00', 'status' => 'pending',
                'patient_id' => 28, 'owner_id' => 47
            ],
            [
                'code' => 'APT' . time() . '057',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '15:00:00', 'status' => 'pending',
                'patient_id' => 29, 'owner_id' => 47
            ],
            [
                'code' => 'APT' . time() . '058',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '15:15:00', 'status' => 'pending',
                'patient_id' => 29, 'owner_id' => 47
            ],
            [
                'code' => 'APT' . time() . '059',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '15:30:00', 'status' => 'pending',
                'patient_id' => 30, 'owner_id' => 47
            ],
            [
                'code' => 'APT' . time() . '060',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '15:45:00', 'status' => 'pending',
                'patient_id' => 30, 'owner_id' => 47
            ],
            [
                'code' => 'APT' . time() . '061',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '16:00:00', 'status' => 'pending',
                'patient_id' => 31, 'owner_id' => 47
            ],
            [
                'code' => 'APT' . time() . '062',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '16:15:00', 'status' => 'pending',
                'patient_id' => 31, 'owner_id' => 47
            ],
            [
                'code' => 'APT' . time() . '063',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '16:30:00', 'status' => 'pending',
                'patient_id' => 32, 'owner_id' => 48
            ],
            [
                'code' => 'APT' . time() . '064',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '16:45:00', 'status' => 'pending',
                'patient_id' => 32, 'owner_id' => 48
            ],
            [
                'code' => 'APT' . time() . '065',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:00:00', 'status' => 'pending',
                'patient_id' => 33, 'owner_id' => 48
            ],
            [
                'code' => 'APT' . time() . '066',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:15:00', 'status' => 'pending',
                'patient_id' => 33, 'owner_id' => 48
            ],
            [
                'code' => 'APT' . time() . '067',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:30:00', 'status' => 'pending',
                'patient_id' => 34, 'owner_id' => 48
            ],
            [
                'code' => 'APT' . time() . '068',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:45:00', 'status' => 'pending',
                'patient_id' => 34, 'owner_id' => 48
            ],
            [
                'code' => 'APT' . time() . '069',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:00:00', 'status' => 'pending',
                'patient_id' => 35, 'owner_id' => 48
            ],
            [
                'code' => 'APT' . time() . '070',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:15:00', 'status' => 'pending',
                'patient_id' => 35, 'owner_id' => 48
            ],
            [
                'code' => 'APT' . time() . '071',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:30:00', 'status' => 'pending',
                'patient_id' => 36, 'owner_id' => 49
            ],
            [
                'code' => 'APT' . time() . '072',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:45:00', 'status' => 'pending',
                'patient_id' => 36, 'owner_id' => 49
            ],
            [
                'code' => 'APT' . time() . '073',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:00:00', 'status' => 'pending',
                'patient_id' => 37, 'owner_id' => 49
            ],
            [
                'code' => 'APT' . time() . '074',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '09:15:00', 'status' => 'pending',
                'patient_id' => 37, 'owner_id' => 49
            ],
            [
                'code' => 'APT' . time() . '075',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:30:00', 'status' => 'pending',
                'patient_id' => 38, 'owner_id' => 49
            ],
            [
                'code' => 'APT' . time() . '076',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '09:45:00', 'status' => 'pending',
                'patient_id' => 38, 'owner_id' => 49
            ],
            [
                'code' => 'APT' . time() . '077',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '10:00:00', 'status' => 'pending',
                'patient_id' => 39, 'owner_id' => 49
            ],
            [
                'code' => 'APT' . time() . '078',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '10:15:00', 'status' => 'pending',
                'patient_id' => 39, 'owner_id' => 49
            ],
            [
                'code' => 'APT' . time() . '079',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '10:30:00', 'status' => 'pending',
                'patient_id' => 40, 'owner_id' => 50
            ],
            [
                'code' => 'APT' . time() . '080',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '10:45:00', 'status' => 'pending',
                'patient_id' => 40, 'owner_id' => 50
            ],
            [
                'code' => 'APT' . time() . '081',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '13:00:00', 'status' => 'pending',
                'patient_id' => 41, 'owner_id' => 50
            ],
            [
                'code' => 'APT' . time() . '082',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '13:15:00', 'status' => 'pending',
                'patient_id' => 41, 'owner_id' => 50
            ],
            [
                'code' => 'APT' . time() . '083',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '13:30:00', 'status' => 'pending',
                'patient_id' => 42, 'owner_id' => 50
            ],
            [
                'code' => 'APT' . time() . '084',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '13:45:00', 'status' => 'pending',
                'patient_id' => 42, 'owner_id' => 50
            ],
            [
                'code' => 'APT' . time() . '085',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '14:00:00', 'status' => 'pending',
                'patient_id' => 43, 'owner_id' => 50
            ],
            [
                'code' => 'APT' . time() . '086',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '14:15:00', 'status' => 'pending',
                'patient_id' => 43, 'owner_id' => 50
            ],
            [
                'code' => 'APT' . time() . '087',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '14:30:00', 'status' => 'pending',
                'patient_id' => 44, 'owner_id' => 51
            ],
            [
                'code' => 'APT' . time() . '088',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '14:45:00', 'status' => 'pending',
                'patient_id' => 44, 'owner_id' => 51
            ],
            [
                'code' => 'APT' . time() . '089',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '15:00:00', 'status' => 'pending',
                'patient_id' => 45, 'owner_id' => 51
            ],
            [
                'code' => 'APT' . time() . '090',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '15:15:00', 'status' => 'pending',
                'patient_id' => 45, 'owner_id' => 51
            ],
            [
                'code' => 'APT' . time() . '091',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '15:30:00', 'status' => 'pending',
                'patient_id' => 46, 'owner_id' => 51
            ],
            [
                'code' => 'APT' . time() . '092',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '15:45:00', 'status' => 'pending',
                'patient_id' => 46, 'owner_id' => 51
            ],
            [
                'code' => 'APT' . time() . '093',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '16:00:00', 'status' => 'pending',
                'patient_id' => 47, 'owner_id' => 51
            ],
            [
                'code' => 'APT' . time() . '094',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '16:15:00', 'status' => 'pending',
                'patient_id' => 47, 'owner_id' => 51
            ],
            [
                'code' => 'APT' . time() . '095',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '16:30:00', 'status' => 'pending',
                'patient_id' => 48, 'owner_id' => 52
            ],
            [
                'code' => 'APT' . time() . '096',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '16:45:00', 'status' => 'pending',
                'patient_id' => 48, 'owner_id' => 52
            ],
            [
                'code' => 'APT' . time() . '097',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:00:00', 'status' => 'pending',
                'patient_id' => 49, 'owner_id' => 52
            ],
            [
                'code' => 'APT' . time() . '098',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:15:00', 'status' => 'pending',
                'patient_id' => 49, 'owner_id' => 52
            ],
            [
                'code' => 'APT' . time() . '099',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:30:00', 'status' => 'pending',
                'patient_id' => 50, 'owner_id' => 52
            ],
            [
                'code' => 'APT' . time() . '100',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:45:00', 'status' => 'pending',
                'patient_id' => 50, 'owner_id' => 52
            ],
            [
                'code' => 'APT' . time() . '101',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:00:00', 'status' => 'pending',
                'patient_id' => 51, 'owner_id' => 52
            ],
            [
                'code' => 'APT' . time() . '102',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:15:00', 'status' => 'pending',
                'patient_id' => 51, 'owner_id' => 52
            ],
            [
                'code' => 'APT' . time() . '103',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:30:00', 'status' => 'pending',
                'patient_id' => 52, 'owner_id' => 53
            ],
            [
                'code' => 'APT' . time() . '104',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:45:00', 'status' => 'pending',
                'patient_id' => 52, 'owner_id' => 53
            ],
            [
                'code' => 'APT' . time() . '105',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:00:00', 'status' => 'pending',
                'patient_id' => 53, 'owner_id' => 53
            ],
            [
                'code' => 'APT' . time() . '106',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '09:15:00', 'status' => 'pending',
                'patient_id' => 53, 'owner_id' => 53
            ],
            [
                'code' => 'APT' . time() . '107',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:30:00', 'status' => 'pending',
                'patient_id' => 54, 'owner_id' => 53
            ],
            [
                'code' => 'APT' . time() . '108',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '09:45:00', 'status' => 'pending',
                'patient_id' => 54, 'owner_id' => 53
            ],
            [
                'code' => 'APT' . time() . '109',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '10:00:00', 'status' => 'pending',
                'patient_id' => 55, 'owner_id' => 53
            ],
            [
                'code' => 'APT' . time() . '110',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '10:15:00', 'status' => 'pending',
                'patient_id' => 55, 'owner_id' => 53
            ],
            [
                'code' => 'APT' . time() . '111',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '10:30:00', 'status' => 'pending',
                'patient_id' => 56, 'owner_id' => 54
            ],
            [
                'code' => 'APT' . time() . '112',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '10:45:00', 'status' => 'pending',
                'patient_id' => 56, 'owner_id' => 54
            ],
            [
                'code' => 'APT' . time() . '113',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '13:00:00', 'status' => 'pending',
                'patient_id' => 57, 'owner_id' => 54
            ],
            [
                'code' => 'APT' . time() . '114',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '13:15:00', 'status' => 'pending',
                'patient_id' => 57, 'owner_id' => 54
            ],
            [
                'code' => 'APT' . time() . '115',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '13:30:00', 'status' => 'pending',
                'patient_id' => 58, 'owner_id' => 54
            ],
            [
                'code' => 'APT' . time() . '116',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '13:45:00', 'status' => 'pending',
                'patient_id' => 58, 'owner_id' => 54
            ],
            [
                'code' => 'APT' . time() . '117',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '14:00:00', 'status' => 'pending',
                'patient_id' => 59, 'owner_id' => 54
            ],
            [
                'code' => 'APT' . time() . '118',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '14:15:00', 'status' => 'pending',
                'patient_id' => 59, 'owner_id' => 54
            ],
            [
                'code' => 'APT' . time() . '119',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '14:30:00', 'status' => 'pending',
                'patient_id' => 60, 'owner_id' => 55
            ],
            [
                'code' => 'APT' . time() . '120',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '14:45:00', 'status' => 'pending',
                'patient_id' => 60, 'owner_id' => 55
            ],
            [
                'code' => 'APT' . time() . '121',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '15:00:00', 'status' => 'pending',
                'patient_id' => 61, 'owner_id' => 55
            ],
            [
                'code' => 'APT' . time() . '122',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '15:15:00', 'status' => 'pending',
                'patient_id' => 61, 'owner_id' => 55
            ],
            [
                'code' => 'APT' . time() . '123',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '15:30:00', 'status' => 'pending',
                'patient_id' => 62, 'owner_id' => 55
            ],
            [
                'code' => 'APT' . time() . '124',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '15:45:00', 'status' => 'pending',
                'patient_id' => 62, 'owner_id' => 55
            ],
            [
                'code' => 'APT' . time() . '125',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '16:00:00', 'status' => 'pending',
                'patient_id' => 63, 'owner_id' => 55
            ],
            [
                'code' => 'APT' . time() . '126',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '16:15:00', 'status' => 'pending',
                'patient_id' => 63, 'owner_id' => 55
            ],
            [
                'code' => 'APT' . time() . '127',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '16:30:00', 'status' => 'pending',
                'patient_id' => 64, 'owner_id' => 56
            ],
            [
                'code' => 'APT' . time() . '128',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '16:45:00', 'status' => 'pending',
                'patient_id' => 64, 'owner_id' => 56
            ],
            [
                'code' => 'APT' . time() . '129',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:00:00', 'status' => 'pending',
                'patient_id' => 65, 'owner_id' => 56
            ],
            [
                'code' => 'APT' . time() . '130',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:15:00', 'status' => 'pending',
                'patient_id' => 65, 'owner_id' => 56
            ],
            [
                'code' => 'APT' . time() . '131',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:30:00', 'status' => 'pending',
                'patient_id' => 66, 'owner_id' => 56
            ],
            [
                'code' => 'APT' . time() . '132',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:45:00', 'status' => 'pending',
                'patient_id' => 66, 'owner_id' => 56
            ],
            [
                'code' => 'APT' . time() . '133',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:00:00', 'status' => 'pending',
                'patient_id' => 67, 'owner_id' => 56
            ],
            [
                'code' => 'APT' . time() . '134',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:15:00', 'status' => 'pending',
                'patient_id' => 67, 'owner_id' => 56
            ],
            [
                'code' => 'APT' . time() . '135',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:30:00', 'status' => 'pending',
                'patient_id' => 68, 'owner_id' => 57
            ],
            [
                'code' => 'APT' . time() . '136',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:45:00', 'status' => 'pending',
                'patient_id' => 68, 'owner_id' => 57
            ],
            [
                'code' => 'APT' . time() . '137',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:00:00', 'status' => 'pending',
                'patient_id' => 69, 'owner_id' => 57
            ],
            [
                'code' => 'APT' . time() . '138',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '09:15:00', 'status' => 'pending',
                'patient_id' => 69, 'owner_id' => 57
            ],
            [
                'code' => 'APT' . time() . '139',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:30:00', 'status' => 'pending',
                'patient_id' => 70, 'owner_id' => 57
            ],
            [
                'code' => 'APT' . time() . '140',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '09:45:00', 'status' => 'pending',
                'patient_id' => 70, 'owner_id' => 57
            ],
            [
                'code' => 'APT' . time() . '141',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '10:00:00', 'status' => 'pending',
                'patient_id' => 71, 'owner_id' => 57
            ],
            [
                'code' => 'APT' . time() . '142',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '10:15:00', 'status' => 'pending',
                'patient_id' => 71, 'owner_id' => 57
            ],
            [
                'code' => 'APT' . time() . '143',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '10:30:00', 'status' => 'pending',
                'patient_id' => 72, 'owner_id' => 58
            ],
            [
                'code' => 'APT' . time() . '144',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '10:45:00', 'status' => 'pending',
                'patient_id' => 72, 'owner_id' => 58
            ],
            [
                'code' => 'APT' . time() . '145',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '13:00:00', 'status' => 'pending',
                'patient_id' => 73, 'owner_id' => 58
            ],
            [
                'code' => 'APT' . time() . '146',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '13:15:00', 'status' => 'pending',
                'patient_id' => 73, 'owner_id' => 58
            ],
            [
                'code' => 'APT' . time() . '147',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '13:30:00', 'status' => 'pending',
                'patient_id' => 74, 'owner_id' => 58
            ],
            [
                'code' => 'APT' . time() . '148',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '13:45:00', 'status' => 'pending',
                'patient_id' => 74, 'owner_id' => 58
            ],
            [
                'code' => 'APT' . time() . '149',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '14:00:00', 'status' => 'pending',
                'patient_id' => 75, 'owner_id' => 58
            ],
            [
                'code' => 'APT' . time() . '150',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '14:15:00', 'status' => 'pending',
                'patient_id' => 75, 'owner_id' => 58
            ],
            [
                'code' => 'APT' . time() . '151',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '14:30:00', 'status' => 'pending',
                'patient_id' => 76, 'owner_id' => 59
            ],
            [
                'code' => 'APT' . time() . '152',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '14:45:00', 'status' => 'pending',
                'patient_id' => 76, 'owner_id' => 59
            ],
            [
                'code' => 'APT' . time() . '153',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '15:00:00', 'status' => 'pending',
                'patient_id' => 77, 'owner_id' => 59
            ],
            [
                'code' => 'APT' . time() . '154',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '15:15:00', 'status' => 'pending',
                'patient_id' => 77, 'owner_id' => 59
            ],
            [
                'code' => 'APT' . time() . '155',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '15:30:00', 'status' => 'pending',
                'patient_id' => 78, 'owner_id' => 59
            ],
            [
                'code' => 'APT' . time() . '156',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '15:45:00', 'status' => 'pending',
                'patient_id' => 78, 'owner_id' => 59
            ],
            [
                'code' => 'APT' . time() . '157',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '16:00:00', 'status' => 'pending',
                'patient_id' => 79, 'owner_id' => 59
            ],
            [
                'code' => 'APT' . time() . '158',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '16:15:00', 'status' => 'pending',
                'patient_id' => 79, 'owner_id' => 59
            ],
            [
                'code' => 'APT' . time() . '159',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '16:30:00', 'status' => 'pending',
                'patient_id' => 80, 'owner_id' => 60
            ],
            [
                'code' => 'APT' . time() . '160',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '16:45:00', 'status' => 'pending',
                'patient_id' => 80, 'owner_id' => 60
            ],
            [
                'code' => 'APT' . time() . '161',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:00:00', 'status' => 'pending',
                'patient_id' => 81, 'owner_id' => 60
            ],
            [
                'code' => 'APT' . time() . '162',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:15:00', 'status' => 'pending',
                'patient_id' => 81, 'owner_id' => 60
            ],
            [
                'code' => 'APT' . time() . '163',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '07:30:00', 'status' => 'pending',
                'patient_id' => 82, 'owner_id' => 60
            ],
            [
                'code' => 'APT' . time() . '164',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '07:45:00', 'status' => 'pending',
                'patient_id' => 82, 'owner_id' => 60
            ],
            [
                'code' => 'APT' . time() . '165',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:00:00', 'status' => 'pending',
                'patient_id' => 83, 'owner_id' => 60
            ],
            [
                'code' => 'APT' . time() . '166',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:15:00', 'status' => 'pending',
                'patient_id' => 83, 'owner_id' => 60
            ],
        ];

        foreach ($appointments as $appt) {
            $appointment = Appointment::create([
                'appointment_code' => $appt['code'],
                'patient_profile_id' => $appt['patient_id'],
                'booked_by_user_id' => $appt['owner_id'],
                'specialty_id' => $appt['spec']->id,
                'doctor_level' => $appt['doc']->level,
                'room_id' => $appt['room']->id,
                'doctor_profile_id' => $appt['doc']->id,
                'appointment_date' => $today,
                'appointment_time' => $appt['time'],
                'reason' => 'Khám tổng quát định kỳ',
                'status' => $appt['status'],
                'source' => 'web',
            ]);

            if ($appt['status'] === 'completed') {
                $record = \App\Models\MedicalRecord::create([
                    'appointment_id' => $appointment->id,
                    'doctor_profile_id' => $appointment->doctor_profile_id,
                    'diagnosis' => 'Viêm họng cấp tính',
                    'icd10_code' => 'J02.9',
                    'conclusion' => 'Bệnh nhân cần nghỉ ngơi và uống thuốc đều đặn theo đơn.',
                    'advice' => 'Uống nhiều nước ấm, kiêng đồ lạnh và đồ cay nóng.',
                    'treatment_result' => 'outpatient',
                    'result_files' => [
                        [
                            'url' => 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf',
                        ],
                    ],
                ]);
                
                \App\Models\Prescription::create([
                    'medical_record_id' => $record->id,
                    'prescribed_date' => $appointment->appointment_date,
                    'diagnosis_note' => 'Viêm họng cấp tính',
                    'items' => [
                        [
                            'name' => 'Paracetamol 500mg',
                            'quantity' => 10,
                            'unit' => 'Viên',
                            'usage' => 'Ngày uống 2 lần, mỗi lần 1 viên sau ăn'
                        ],
                        [
                            'name' => 'Amoxicillin 500mg',
                            'quantity' => 15,
                            'unit' => 'Viên',
                            'usage' => 'Ngày uống 3 lần, mỗi lần 1 viên sau ăn'
                        ],
                        [
                            'name' => 'Vitamin C 1000mg',
                            'quantity' => 10,
                            'unit' => 'Viên sủi',
                            'usage' => 'Ngày uống 1 lần, mỗi lần 1 viên hòa tan trong nước'
                        ]
                    ],
                    'general_note' => 'Tái khám sau 5 ngày nếu triệu chứng không thuyên giảm.',
                ]);
            } elseif ($appt['status'] === 'examining') {
                $originVisit = \App\Models\ClinicalVisit::create([
                    'appointment_id' => $appointment->id,
                    'parent_visit_id' => null,
                    'doctor_profile_id' => $appointment->doctor_profile_id,
                    'room_id' => $appointment->room_id,
                    'visit_order' => 1,
                    'is_origin' => true,
                    'findings' => 'Bệnh nhân có biểu hiện sốt, ho nhiều. Đã khám sơ bộ, yêu cầu xét nghiệm máu.',
                    'result_files' => [
                        [
                            'url' => 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf',
                        ],
                    ],
                    'status' => 'completed',
                    'payment_amount' => 150000,
                    'payment_status' => 'paid',
                    'payment_method' => 'cash',
                    'paid_at' => \Carbon\Carbon::now()->subMinutes(45),
                    'started_at' => \Carbon\Carbon::now()->subMinutes(40),
                    'completed_at' => \Carbon\Carbon::now()->subMinutes(25),
                ]);

                \App\Models\ClinicalVisit::create([
                    'appointment_id' => $appointment->id,
                    'parent_visit_id' => $originVisit->id,
                    'doctor_profile_id' => $appointment->doctor_profile_id,
                    'room_id' => $appointment->room_id,
                    'visit_order' => 2,
                    'is_origin' => false,
                    'findings' => 'Đang chờ kết quả xét nghiệm. Bệnh nhân đang được theo dõi sinh hiệu.',
                    'status' => 'in_progress',
                    'payment_amount' => 200000,
                    'payment_status' => 'paid',
                    'payment_method' => 'qr',
                    'paid_at' => \Carbon\Carbon::now()->subMinutes(20),
                    'started_at' => \Carbon\Carbon::now()->subMinutes(15),
                    'completed_at' => null,
                ]);
            }
        }
    }
}