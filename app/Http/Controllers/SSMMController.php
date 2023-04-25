<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\InstitutionShifts;
use App\Models\InstitutionStaff;
use App\Models\User;
use Exception;

class SSMMController extends Controller
{
    public $token = '';
    public $institutionTypeId = [830, 831, 836];

    public function getData()
    {
        try {

            set_time_limit(3000);
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
                $this->token = $responseData->data->token;
            }


//        return $institutionData;
            $institutionResponseData = $this->institutionsData(1);

            if ($institutionResponseData['status'] == 200) {
                $institutionDetails = $institutionResponseData['institutionData']->data->data;
                foreach ($institutionDetails as $ins) {
                    if (in_array($ins->institution_type_id, $this->institutionTypeId)) {
                        Institution::updateorCreate(['institutionId' => $ins->id, 'institution_name' => $ins->name], ['institutionId' => $ins->id, 'institution_name' => $ins->name, 'alternative_name' => $ins->alternative_name, 'institution_code' => $ins->code
                            , 'address', 'postal_code' => (integer)$ins->postal_code, 'date_opened' => $ins->date_opened, 'year_opened' => $ins->year_opened, 'longitude' => $ins->longitude, 'latitude' => $ins->latitude, 'area_id' => $ins->area_id,
                            'area_administrative_id' => $ins->area_administrative_id, 'institution_type_id' => $ins->institution_type_id, 'institution_ownership_id' => $ins->institution_ownership_id,
                            'institution_status_id' => $ins->institution_status_id, 'institution_sector_id' => $ins->institution_sector_id, 'institution_provider_id' => $ins->institution_provider_id,
                            'institution_gender_id' => $ins->institution_gender_id, 'created_user_id' => $ins->created_user_id, 'created' => $ins->created]);
                        $shiftsResponse = $this->institutionShifts(1, $ins->id);
                        $shiftsPerPage = $shiftsResponse['institutionShifts']->data->total;
                        if ($shiftsResponse['status'] == 200) {
                            $institutionShiftDetails = $shiftsResponse['institutionShifts']->data->data;

                            foreach ($institutionShiftDetails as $shift) {
                                InstitutionShifts::updateorCreate(['institution_id' => $ins->id, 'shift_id' => $shift->id], ['institution_id' => $ins->id,'shift_id' => $shift->id, 'shift_option_id' => $shift->shift_option_id, 'shift_option_name' => $shift->shift_option->name]);
                            }
                        }
                        for ($i = 1; $i <= $shiftsPerPage; $i++) {
                            $shiftsResponse = $this->institutionShifts($i + 1, $ins->id);
                            if ($shiftsResponse['status'] == 200) {
                                $institutionShiftDetails = $shiftsResponse['institutionShifts']->data->data;
                                foreach ($institutionShiftDetails as $shift) {
                                    InstitutionShifts::updateorCreate(['institution_id' => $ins->id, 'shift_id' => $shift->id], ['institution_id' => $ins->id,'shift_id' => $shift->id, 'shift_option_id' => $shift->shift_option_id, 'shift_option_name' => $shift->shift_option->name]);
                                }
                            }
                        }
                        $staffResponse = $this->institutionStaff(1, $ins->id);
                        $staffPerPage = $staffResponse['institutionStaff']->data->total;
                        if ($staffResponse['status'] == 200) {
                            $institutionStaffDetails = $staffResponse['institutionStaff']->data->data;

                            foreach ($institutionStaffDetails as $staff) {
                                InstitutionStaff::updateorCreate(['staff_id' => $staff->staff_id], ['staff_id' => $staff->staff_id, 'institution_id' => $ins->id, 'staff_status_id' => $staff->staff_status_id,
                                    'staff_status_name' => $staff->staff_status_name, 'institution_position_id' => $staff->institution_position_id, 'institution_position_name' => $staff->institution_position_name]);
                                $usersResponse = $this->institutionUsers($staff->staff_id);
                                if ($usersResponse['status'] == 200) {
                                    $usersData = $usersResponse['usersData']->data;
                                    if (count($usersData) > 0) {
                                        $areaResponse = $this->areaCode($ins->id);
                                        $areaData = $areaResponse['areaCode']->data->data;
                                        $areaCode = count($areaData) > 0 ? $areaData[0]->area_education->code : '';
                                        $areaName = count($areaData) > 0 ? $areaData[0]->area_education->name : '';
                                        User::updateorCreate(['user_id' => $usersData[0]->id], ['user_id' => $usersData[0]->id, 'identity_number' => $usersData[0]->identity_number, 'moe_id' => '',
                                            'openemis_no' => $usersData[0]->openemis_no, 'username' => $usersData[0]->username, 'first_name' => $usersData[0]->first_name, 'middle_name' => $usersData[0]->middle_name, 'third_name' => $usersData[0]->third_name, 'last_name' => $usersData[0]->last_name,
                                            'full_name' => $usersData[0]->first_name . $usersData[0]->middle_name . $usersData[0]->third_name . $usersData[0]->last_name, 'nationality' => $usersData[0]->nationality_id->value, 'position_name' => $staff->institution_position_name, 'staff_position_title_id' => $staff->institution_position_id,
                                            'institution_name' => $ins->name, 'institution_code' => $ins->code,'area_name' => $areaName, 'area_code' => $areaCode]);
                                    }
                                }
                            }
                        }
                        $totalStaffPages = $staffPerPage;
                        for ($i = 1; $i <= $totalStaffPages; $i++) {
                            $staffResponse = $this->institutionStaff($i + 1, $ins->id);
                            if ($staffResponse['status'] == 200) {
                                $institutionStaffDetails = $staffResponse['institutionStaff']->data->data;
                                foreach ($institutionStaffDetails as $staff) {
                                    InstitutionStaff::updateorCreate(['staff_id' => $staff->staff_id], ['staff_id' => $staff->staff_id, 'institution_id' => $ins->id, 'staff_status_id' => $staff->staff_status_id,
                                        'staff_status_name' => $staff->staff_status_name, 'institution_position_id' => $staff->institution_position_id, 'institution_position_name' => $staff->institution_position_name]);
                                    $usersResponse = $this->institutionUsers($staff->staff_id);
                                    if ($usersResponse['status'] == 200) {
                                        $usersData = $usersResponse['usersData']->data;
                                        if (count($usersData) > 0) {
                                            User::updateorCreate(['user_id' => $usersData[0]->id], ['user_id' => $usersData[0]->id, 'identity_number' => $usersData[0]->identity_number,
                                                'openemis_no' => $usersData[0]->openemis_no, 'username' => $usersData[0]->username, 'first_name' => $usersData[0]->first_name, 'middle_name' => $usersData[0]->middle_name, 'third_name' => $usersData[0]->third_name, 'last_name' => $usersData[0]->last_name,
                                                'full_name' => $usersData[0]->first_name . $usersData[0]->middle_name . $usersData[0]->third_name . $usersData[0]->last_name, 'nationality' => $usersData[0]->nationality_id->value, 'position_name' => $staff->institution_position_name, 'staff_position_title_id' => $staff->institution_position_id,
                                                'institution_name' => $ins->name, 'institution_code' => $ins->code]);
                                        }
                                    }
                                }
                            }
                        }
                    }

                }

            }
            $totalPages = $institutionResponseData['institutionData']->data->total;
            for ($k = 1; $k <= $totalPages; $k++) {
                $institutionResponseData = $this->institutionsData($k + 1);
                if ($institutionResponseData['status'] == 200) {
                    $institutionDetails = $institutionResponseData['institutionData']->data->data;
                    foreach ($institutionDetails as $ins) {

                        if (in_array($ins->institution_type_id, $this->institutionTypeId)) {
                            Institution::updateorCreate(['institutionId' => $ins->id, 'institution_name' => $ins->name], ['institutionId' => $ins->id, 'institution_name' => $ins->name, 'alternative_name' => $ins->alternative_name, 'institution_code' => $ins->code
                                , 'address', 'postal_code' => (integer)$ins->postal_code, 'date_opened' => $ins->date_opened, 'year_opened' => $ins->year_opened, 'longitude' => $ins->longitude, 'latitude' => $ins->latitude, 'area_id' => $ins->area_id,
                                'area_administrative_id' => $ins->area_administrative_id, 'institution_type_id' => $ins->institution_type_id, 'institution_ownership_id' => $ins->institution_ownership_id,
                                'institution_status_id' => $ins->institution_status_id, 'institution_sector_id' => $ins->institution_sector_id, 'institution_provider_id' => $ins->institution_provider_id,
                                'institution_gender_id' => $ins->institution_gender_id, 'created_user_id' => $ins->created_user_id, 'created' => $ins->created]);

                            $shiftsResponse = $this->institutionShifts(1, $ins->id);
                            $shiftsPerPage = $shiftsResponse['institutionShifts']->data->total;
                            if ($shiftsResponse['status'] == 200) {
                                $institutionShiftDetails = $shiftsResponse['institutionShifts']->data->data;

                                foreach ($institutionShiftDetails as $shift) {
                                    InstitutionShifts::updateorCreate(['institution_id' => $ins->id, 'shift_id' => $shift->id], ['institution_id' => $ins->id,'shift_id' => $shift->id, 'shift_option_id' => $shift->shift_option_id, 'shift_option_name' => $shift->shift_option->name]);
                                }
                            }
                            for ($i = 1; $i <= $shiftsPerPage; $i++) {
                                $shiftsResponse = $this->institutionShifts($i + 1, $ins->id);
                                if ($shiftsResponse['status'] == 200) {
                                    $institutionShiftDetails = $shiftsResponse['institutionShifts']->data->data;
                                    foreach ($institutionShiftDetails as $shift) {
                                        InstitutionShifts::updateorCreate(['institution_id' => $ins->id, 'shift_id' => $shift->id], ['institution_id' => $ins->id,'shift_id' => $shift->id, 'shift_option_id' => $shift->shift_option_id, 'shift_option_name' => $shift->shift_option->name]);
                                    }
                                }
                            }
                            $staffResponse = $this->institutionStaff(1, $ins->id);
                            $staffPerPage = $staffResponse['institutionStaff']->data->total;
                            if ($staffResponse['status'] == 200) {
                                $institutionStaffDetails = $staffResponse['institutionStaff']->data->data;

                                foreach ($institutionStaffDetails as $staff) {
                                    InstitutionStaff::updateorCreate(['staff_id' => $staff->staff_id], ['staff_id' => $staff->staff_id, 'institution_id' => $ins->id, 'staff_status_id' => $staff->staff_status_id,
                                        'staff_status_name' => $staff->staff_status_name, 'institution_position_id' => $staff->institution_position_id, 'institution_position_name' => $staff->institution_position_name]);
                                    $usersResponse = $this->institutionUsers($staff->staff_id);
                                    if ($usersResponse['status'] == 200) {
                                        $usersData = $usersResponse['usersData']->data;
                                        if (count($usersData) > 0) {
                                            User::updateorCreate(['user_id' => $usersData[0]->id], ['user_id' => $usersData[0]->id, 'identity_number' => $usersData[0]->identity_number, 'moe_id' => '',
                                                'openemis_no' => $usersData[0]->openemis_no, 'username' => $usersData[0]->username, 'first_name' => $usersData[0]->first_name, 'middle_name' => $usersData[0]->middle_name, 'third_name' => $usersData[0]->third_name, 'last_name' => $usersData[0]->last_name,
                                                'full_name' => $usersData[0]->first_name . $usersData[0]->middle_name . $usersData[0]->third_name . $usersData[0]->last_name, 'nationality' => $usersData[0]->nationality_id->value, 'position_name' => $staff->institution_position_name, 'staff_position_title_id' => $staff->institution_position_id,
                                                'institution_name' => $ins->name, 'institution_code' => $ins->code]);
                                        }
                                    }
                                }
                            }
                            $totalStaffPages = $staffPerPage;
                            for ($i = 1; $i <= $totalStaffPages; $i++) {
                                $staffResponse = $this->institutionStaff($i + 1, $ins->id);
                                if ($staffResponse['status'] == 200) {
                                    $institutionStaffDetails = $staffResponse['institutionStaff']->data->data;
                                    foreach ($institutionStaffDetails as $staff) {
                                        InstitutionStaff::updateorCreate(['staff_id' => $staff->staff_id], ['staff_id' => $staff->staff_id, 'institution_id' => $ins->id, 'staff_status_id' => $staff->staff_status_id,
                                            'staff_status_name' => $staff->staff_status_name, 'institution_position_id' => $staff->institution_position_id, 'institution_position_name' => $staff->institution_position_name]);
                                        $usersResponse = $this->institutionUsers($staff->staff_id);
                                        if ($usersResponse['status'] == 200) {
                                            $usersData = $usersResponse['usersData']->data;
                                            if (count($usersData) > 0) {
                                                User::updateorCreate(['user_id' => $usersData[0]->id], ['user_id' => $usersData[0]->id, 'identity_number' => $usersData[0]->identity_number,
                                                    'openemis_no' => $usersData[0]->openemis_no, 'username' => $usersData[0]->username, 'first_name' => $usersData[0]->first_name, 'middle_name' => $usersData[0]->middle_name, 'third_name' => $usersData[0]->third_name, 'last_name' => $usersData[0]->last_name,
                                                    'full_name' => $usersData[0]->first_name . $usersData[0]->middle_name . $usersData[0]->third_name . $usersData[0]->last_name, 'nationality' => $usersData[0]->nationality_id->value, 'position_name' => $staff->institution_position_name, 'staff_position_title_id' => $staff->institution_position_id,
                                                    'institution_name' => $ins->name, 'institution_code' => $ins->code]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return ['status' => false, 'message' => 'Data fetched successfully'];
        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }


    public function institutionsData($page)
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
                'Authorization: Bearer ' . $this->token
            ),
        ));

        $response = curl_exec($institutionCurl);
        $instHttpCode = curl_getinfo($institutionCurl, CURLINFO_HTTP_CODE);

        curl_close($institutionCurl);
        return ['institutionData' => json_decode($response), 'status' => $instHttpCode];
    }

    public function institutionShifts($page, $institutionId)
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
                'Authorization: Bearer ' . $this->token
            ),
        ));

        $response = curl_exec($institutionCurl);
        $instHttpCode = curl_getinfo($institutionCurl, CURLINFO_HTTP_CODE);

        curl_close($institutionCurl);
        return ['institutionShifts' => json_decode($response), 'status' => $instHttpCode];
    }

    public function institutionStaff($page, $institutionId)
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
                'Authorization: Bearer ' . $this->token
            ),
        ));

        $response = curl_exec($institutionCurl);
        $instHttpCode = curl_getinfo($institutionCurl, CURLINFO_HTTP_CODE);

        curl_close($institutionCurl);
        return ['institutionStaff' => json_decode($response), 'status' => $instHttpCode];
    }

    public function institutionUsers($userId)
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
                'Authorization: Bearer ' . $this->token
            ),
        ));

        $response = curl_exec($institutionCurl);
        $instHttpCode = curl_getinfo($institutionCurl, CURLINFO_HTTP_CODE);

        curl_close($institutionCurl);
        return ['usersData' => json_decode($response), 'status' => $instHttpCode];
    }
    public function areaCode($insId)
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
                'Authorization: Bearer ' . $this->token
            ),
        ));

        $response = curl_exec($institutionCurl);
        $instHttpCode = curl_getinfo($institutionCurl, CURLINFO_HTTP_CODE);

        curl_close($institutionCurl);
        return ['areaCode' => json_decode($response), 'status' => $instHttpCode];
    }
}
