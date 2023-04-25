<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\InstitutionShifts;
use App\Models\InstitutionStaff;
use App\Models\User;
use Illuminate\Console\Command;

class SSMMCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ssmm:data';
    public $institutionTypeId = [830, 831, 836];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $token = $this->login();

//        return $institutionData;
        $institutionResponseData = $this->institutionsData(1, $token);
        if ($institutionResponseData['status'] == 200) {
            $totalPages = $institutionResponseData['institutionData']->data->total;
            $institutionDetails = $institutionResponseData['institutionData']->data->data;
            foreach ($institutionDetails as $ins) {
                if (in_array($ins->institution_type_id, $this->institutionTypeId)) {
                    Institution::updateorCreate(['institutionId' => $ins->id, 'institution_name' => $ins->name], ['institutionId' => $ins->id, 'institution_name' => $ins->name, 'alternative_name' => $ins->alternative_name, 'institution_code' => $ins->code
                        , 'address', 'postal_code' => (integer)$ins->postal_code, 'date_opened' => $ins->date_opened, 'year_opened' => $ins->year_opened, 'longitude' => $ins->longitude, 'latitude' => $ins->latitude, 'area_id' => $ins->area_id,
                        'area_administrative_id' => $ins->area_administrative_id, 'institution_type_id' => $ins->institution_type_id, 'institution_ownership_id' => $ins->institution_ownership_id,
                        'institution_status_id' => $ins->institution_status_id, 'institution_sector_id' => $ins->institution_sector_id, 'institution_provider_id' => $ins->institution_provider_id,
                        'institution_gender_id' => $ins->institution_gender_id, 'created_user_id' => $ins->created_user_id, 'created' => $ins->created]);
                    $shiftsResponse = $this->institutionShifts(1, $ins->id, $token);
                    if ($shiftsResponse['status'] == 200) {
                        $shiftsPerPage = $shiftsResponse['institutionShifts']->data->total;
                        $institutionShiftDetails = $shiftsResponse['institutionShifts']->data->data;

                        foreach ($institutionShiftDetails as $shift) {
                            InstitutionShifts::updateorCreate(['institution_id' => $ins->id, 'shift_id' => $shift->id], ['institution_id' => $ins->id, 'shift_id' => $shift->id, 'shift_option_id' => $shift->shift_option_id, 'shift_option_name' => $shift->shift_option->name]);
                        }
                    }
                    else
                    {
                        $shiftsPerPage = [];
                    }
                    for ($i = 1; $i <= $shiftsPerPage; $i++) {
                        $shiftsResponse = $this->institutionShifts($i + 1, $ins->id, $token);
                        if ($shiftsResponse['status'] == 200) {
                            $institutionShiftDetails = $shiftsResponse['institutionShifts']->data->data;
                            foreach ($institutionShiftDetails as $shift) {
                                InstitutionShifts::updateorCreate(['institution_id' => $ins->id, 'shift_id' => $shift->id], ['institution_id' => $ins->id, 'shift_id' => $shift->id, 'shift_option_id' => $shift->shift_option_id, 'shift_option_name' => $shift->shift_option->name]);
                            }
                        }
                    }
                    $staffResponse = $this->institutionStaff(1, $ins->id, $token);
                    if ($staffResponse['status'] == 200) {
                        $staffPerPage = $staffResponse['institutionStaff']->data->total;
                        $institutionStaffDetails = $staffResponse['institutionStaff']->data->data;

                        foreach ($institutionStaffDetails as $staff) {
                            InstitutionStaff::updateorCreate(['staff_id' => $staff->staff_id], ['staff_id' => $staff->staff_id, 'institution_id' => $ins->id, 'staff_status_id' => $staff->staff_status_id,
                                'staff_status_name' => $staff->staff_status_name, 'institution_position_id' => $staff->institution_position_id, 'institution_position_name' => $staff->institution_position_name]);
                            $usersResponse = $this->institutionUsers($staff->staff_id, $token);
                            if ($usersResponse['status'] == 200) {
                                $usersData = $usersResponse['usersData']->data;
                                if (count($usersData) > 0) {
                                    $areaResponse = $this->areaCode($ins->id, $token);
                                    if ($areaResponse['status'] == 200) {
                                        $areaData = $areaResponse['areaCode']->data->data;
                                    } else {
                                        $areaData = [];
                                    }
                                    $areaCode = count($areaData) > 0 ? $areaData[0]->area_education->code : 0;
                                    $areaName = count($areaData) > 0 ? $areaData[0]->area_education->name : '';
                                    User::updateorCreate(['user_id' => $usersData[0]->id], ['user_id' => $usersData[0]->id, 'identity_number' => $usersData[0]->identity_number, 'moe_id' => '',
                                        'openemis_no' => $usersData[0]->openemis_no, 'username' => $usersData[0]->username, 'first_name' => $usersData[0]->first_name, 'middle_name' => $usersData[0]->middle_name, 'third_name' => $usersData[0]->third_name, 'last_name' => $usersData[0]->last_name,
                                        'full_name' => $usersData[0]->first_name . $usersData[0]->middle_name . $usersData[0]->third_name . $usersData[0]->last_name, 'nationality' => $usersData[0]->nationality_id->value, 'position_name' => $staff->institution_position_name, 'staff_position_title_id' => $staff->institution_position_id,
                                        'institution_name' => $ins->name, 'institution_code' => $ins->code, 'area_name' => $areaName, 'area_code' => $areaCode]);
                                }
                            }
                        }
                    }
                    else
                    {
                        $staffPerPage = [];
                    }
                    $totalStaffPages = $staffPerPage;
                    for ($i = 1; $i <= $totalStaffPages; $i++) {
                        $staffResponse = $this->institutionStaff($i + 1, $ins->id, $token);
                        if ($staffResponse['status'] == 200) {
                            $institutionStaffDetails = $staffResponse['institutionStaff']->data->data;
                            foreach ($institutionStaffDetails as $staff) {
                                InstitutionStaff::updateorCreate(['staff_id' => $staff->staff_id], ['staff_id' => $staff->staff_id, 'institution_id' => $ins->id, 'staff_status_id' => $staff->staff_status_id,
                                    'staff_status_name' => $staff->staff_status_name, 'institution_position_id' => $staff->institution_position_id, 'institution_position_name' => $staff->institution_position_name]);
                                $usersResponse = $this->institutionUsers($staff->staff_id, $token);
                                if ($usersResponse['status'] == 200) {
                                    $usersData = $usersResponse['usersData']->data;
                                    if (count($usersData) > 0) {
                                        $areaResponse = $this->areaCode($ins->id, $token);
                                        if ($areaResponse['status'] == 200) {
                                            $areaData = $areaResponse['areaCode']->data->data;
                                        } else {
                                            $areaData = [];
                                        }
                                        $areaCode = count($areaData) > 0 ? $areaData[0]->area_education->code : 0;
                                        $areaName = count($areaData) > 0 ? $areaData[0]->area_education->name : '';
                                        User::updateorCreate(['user_id' => $usersData[0]->id], ['user_id' => $usersData[0]->id, 'identity_number' => $usersData[0]->identity_number,
                                            'openemis_no' => $usersData[0]->openemis_no, 'username' => $usersData[0]->username, 'first_name' => $usersData[0]->first_name, 'middle_name' => $usersData[0]->middle_name, 'third_name' => $usersData[0]->third_name, 'last_name' => $usersData[0]->last_name,
                                            'full_name' => $usersData[0]->first_name . $usersData[0]->middle_name . $usersData[0]->third_name . $usersData[0]->last_name, 'nationality' => $usersData[0]->nationality_id->value, 'position_name' => $staff->institution_position_name, 'staff_position_title_id' => $staff->institution_position_id,
                                            'institution_name' => $ins->name, 'institution_code' => $ins->code, 'area_name' => $areaName, 'area_code' => $areaCode]);
                                    }
                                }
                            }
                        }
                    }
                }

            }

        }
        else
        {
            $totalPages = [];
        }
        for ($k = 1; $k <= $totalPages; $k++) {
            $institutionResponseData = $this->institutionsData($k + 1, $token);
            if ($institutionResponseData['status'] == 200) {
                $institutionDetails = $institutionResponseData['institutionData']->data->data;
                foreach ($institutionDetails as $ins) {

                    if (in_array($ins->institution_type_id, $this->institutionTypeId, $token)) {
                        Institution::updateorCreate(['institutionId' => $ins->id, 'institution_name' => $ins->name], ['institutionId' => $ins->id, 'institution_name' => $ins->name, 'alternative_name' => $ins->alternative_name, 'institution_code' => $ins->code
                            , 'address', 'postal_code' => (integer)$ins->postal_code, 'date_opened' => $ins->date_opened, 'year_opened' => $ins->year_opened, 'longitude' => $ins->longitude, 'latitude' => $ins->latitude, 'area_id' => $ins->area_id,
                            'area_administrative_id' => $ins->area_administrative_id, 'institution_type_id' => $ins->institution_type_id, 'institution_ownership_id' => $ins->institution_ownership_id,
                            'institution_status_id' => $ins->institution_status_id, 'institution_sector_id' => $ins->institution_sector_id, 'institution_provider_id' => $ins->institution_provider_id,
                            'institution_gender_id' => $ins->institution_gender_id, 'created_user_id' => $ins->created_user_id, 'created' => $ins->created]);

                        $shiftsResponse = $this->institutionShifts(1, $ins->id, $token);
                        if ($shiftsResponse['status'] == 200) {
                            $shiftsPerPage = $shiftsResponse['institutionShifts']->data->total;
                            $institutionShiftDetails = $shiftsResponse['institutionShifts']->data->data;

                            foreach ($institutionShiftDetails as $shift) {
                                InstitutionShifts::updateorCreate(['institution_id' => $ins->id, 'shift_id' => $shift->id], ['institution_id' => $ins->id, 'shift_id' => $shift->id, 'shift_option_id' => $shift->shift_option_id, 'shift_option_name' => $shift->shift_option->name]);
                            }
                        }
                        else
                        {
                            $shiftsPerPage = [];
                        }
                        for ($i = 1; $i <= $shiftsPerPage; $i++) {
                            $shiftsResponse = $this->institutionShifts($i + 1, $ins->id, $token);
                            if ($shiftsResponse['status'] == 200) {
                                $institutionShiftDetails = $shiftsResponse['institutionShifts']->data->data;
                                foreach ($institutionShiftDetails as $shift) {
                                    InstitutionShifts::updateorCreate(['institution_id' => $ins->id, 'shift_id' => $shift->id], ['institution_id' => $ins->id, 'shift_id' => $shift->id, 'shift_option_id' => $shift->shift_option_id, 'shift_option_name' => $shift->shift_option->name]);
                                }
                            }
                        }
                        $staffResponse = $this->institutionStaff(1, $ins->id, $token);
                        if ($staffResponse['status'] == 200) {
                            $staffPerPage = $staffResponse['institutionStaff']->data->total;
                            $institutionStaffDetails = $staffResponse['institutionStaff']->data->data;

                            foreach ($institutionStaffDetails as $staff) {
                                InstitutionStaff::updateorCreate(['staff_id' => $staff->staff_id], ['staff_id' => $staff->staff_id, 'institution_id' => $ins->id, 'staff_status_id' => $staff->staff_status_id,
                                    'staff_status_name' => $staff->staff_status_name, 'institution_position_id' => $staff->institution_position_id, 'institution_position_name' => $staff->institution_position_name]);
                                $usersResponse = $this->institutionUsers($staff->staff_id, $token);
                                if ($usersResponse['status'] == 200) {
                                    $usersData = $usersResponse['usersData']->data;
                                    if (count($usersData) > 0) {
                                        $areaResponse = $this->areaCode($ins->id, $token);
                                        if ($areaResponse['status'] == 200) {
                                            $areaResponse = $this->areaCode($ins->id, $token);
                                        } else {
                                            $areaResponse = [];
                                        }
                                        $areaData = $areaResponse['areaCode']->data->data;
                                        $areaCode = count($areaData) > 0 ? $areaData[0]->area_education->code : 0;
                                        $areaName = count($areaData) > 0 ? $areaData[0]->area_education->name : '';
                                        User::updateorCreate(['user_id' => $usersData[0]->id], ['user_id' => $usersData[0]->id, 'identity_number' => $usersData[0]->identity_number, 'moe_id' => '',
                                            'openemis_no' => $usersData[0]->openemis_no, 'username' => $usersData[0]->username, 'first_name' => $usersData[0]->first_name, 'middle_name' => $usersData[0]->middle_name, 'third_name' => $usersData[0]->third_name, 'last_name' => $usersData[0]->last_name,
                                            'full_name' => $usersData[0]->first_name . $usersData[0]->middle_name . $usersData[0]->third_name . $usersData[0]->last_name, 'nationality' => $usersData[0]->nationality_id->value, 'position_name' => $staff->institution_position_name, 'staff_position_title_id' => $staff->institution_position_id,
                                            'institution_name' => $ins->name, 'institution_code' => $ins->code, 'area_name' => $areaName, 'area_code' => $areaCode]);
                                    }
                                }
                            }
                        }
                        else
                        {
                            $staffPerPage = [];
                        }
                        $totalStaffPages = $staffPerPage;
                        for ($i = 1; $i <= $totalStaffPages; $i++) {
                            $staffResponse = $this->institutionStaff($i + 1, $ins->id, $token);
                            if ($staffResponse['status'] == 200) {
                                $institutionStaffDetails = $staffResponse['institutionStaff']->data->data;
                                foreach ($institutionStaffDetails as $staff) {
                                    InstitutionStaff::updateorCreate(['staff_id' => $staff->staff_id], ['staff_id' => $staff->staff_id, 'institution_id' => $ins->id, 'staff_status_id' => $staff->staff_status_id,
                                        'staff_status_name' => $staff->staff_status_name, 'institution_position_id' => $staff->institution_position_id, 'institution_position_name' => $staff->institution_position_name]);
                                    $usersResponse = $this->institutionUsers($staff->staff_id, $token);
                                    if ($usersResponse['status'] == 200) {
                                        $usersData = $usersResponse['usersData']->data;
                                        if (count($usersData) > 0) {
                                            $areaResponse = $this->areaCode($ins->id, $token);
                                            if ($areaResponse['status'] == 200) {
                                                $areaResponse = $this->areaCode($ins->id, $token);
                                            } else {
                                                $areaResponse = [];
                                            }
                                            $areaData = $areaResponse['areaCode']->data->data;
                                            $areaCode = count($areaData) > 0 ? $areaData[0]->area_education->code : 0;
                                            $areaName = count($areaData) > 0 ? $areaData[0]->area_education->name : '';
                                            User::updateorCreate(['user_id' => $usersData[0]->id], ['user_id' => $usersData[0]->id, 'identity_number' => $usersData[0]->identity_number,
                                                'openemis_no' => $usersData[0]->openemis_no, 'username' => $usersData[0]->username, 'first_name' => $usersData[0]->first_name, 'middle_name' => $usersData[0]->middle_name, 'third_name' => $usersData[0]->third_name, 'last_name' => $usersData[0]->last_name,
                                                'full_name' => $usersData[0]->first_name . $usersData[0]->middle_name . $usersData[0]->third_name . $usersData[0]->last_name, 'nationality' => $usersData[0]->nationality_id->value, 'position_name' => $staff->institution_position_name, 'staff_position_title_id' => $staff->institution_position_id,
                                                'institution_name' => $ins->name, 'institution_code' => $ins->code, 'area_name' => $areaName, 'area_code' => $areaCode]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function institutionsData($page, $token)
    {
        // institutions api
        $institutionCurl = curl_init();

        curl_setopt_array($institutionCurl, array(
            CURLOPT_URL => env('API_URL') . 'institutions?page=' . $page,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($institutionCurl);
        $instHttpCode = curl_getinfo($institutionCurl, CURLINFO_HTTP_CODE);

        curl_close($institutionCurl);
        $insData = json_decode($response);
        if ($instHttpCode === 401) {
            $token = $this->login();
            $this->institutionsData($page, $token);

        }

        return ['institutionData' => $insData, 'status' => $instHttpCode];

    }

    public function institutionShifts($page, $institutionId, $token)
    {
        // institutions api
        $institutionCurl = curl_init();

        curl_setopt_array($institutionCurl, array(
            CURLOPT_URL => env('API_URL') . 'institutions/' . $institutionId . '/shifts?page=' . $page,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($institutionCurl);
        $instHttpCode = curl_getinfo($institutionCurl, CURLINFO_HTTP_CODE);

        curl_close($institutionCurl);
        $shiftsData = json_decode($response);
        if ($instHttpCode == 401) {
            $token = $this->login();
            $this->institutionShifts($page, $institutionId, $token);

        }

        return ['institutionShifts' => $shiftsData, 'status' => $instHttpCode];

    }

    public function institutionStaff($page, $institutionId, $token)
    {
        // institutions api
        $institutionCurl = curl_init();

        curl_setopt_array($institutionCurl, array(
            CURLOPT_URL => env('API_URL') . 'institutions/' . $institutionId . '/staff?page=' . $page,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($institutionCurl);
        $instHttpCode = curl_getinfo($institutionCurl, CURLINFO_HTTP_CODE);

        curl_close($institutionCurl);
        $staffData = json_decode($response);
        if ($instHttpCode == 401) {
            $token = $this->login();
            $this->institutionStaff($page, $institutionId, $token);

        }

        return ['institutionStaff' => $staffData, 'status' => $instHttpCode];

    }

    public function institutionUsers($userId, $token)
    {
        // institutions api
        $institutionCurl = curl_init();

        curl_setopt_array($institutionCurl, array(
            CURLOPT_URL => env('API_URL') . 'users/' . $userId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($institutionCurl);
        $instHttpCode = curl_getinfo($institutionCurl, CURLINFO_HTTP_CODE);

        curl_close($institutionCurl);
        $userData = json_decode($response);
        if ($instHttpCode == 401) {
            $token = $this->login();
            $this->institutionUsers($userId, $token);

        }

        return ['usersData' => $userData, 'status' => $instHttpCode];

    }

    public function areaCode($insId, $token)
    {
        // institutions api
        $institutionCurl = curl_init();

        curl_setopt_array($institutionCurl, array(
            CURLOPT_URL => env('API_URL') . 'institutions/' . $insId . '/areas',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($institutionCurl);
        $instHttpCode = curl_getinfo($institutionCurl, CURLINFO_HTTP_CODE);

        curl_close($institutionCurl);
        $areaData = json_decode($response);
        if ($instHttpCode == 401) {
            $token = $this->login();
            $this->areaCode($insId, $token);

        }
        return ['areaCode' => $areaData, 'status' => $instHttpCode];
    }

    public function login()
    {
        $token = '';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('API_URL') . 'login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('username' => 'admin', 'password' => 'demo', 'api_key' => 'apikeytest'),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $responseData = json_decode($response);
        if ($httpcode == 200) {
            $token = $responseData->data->token;
        } else {
            $this->login();
        }
        return $token;
    }
}
