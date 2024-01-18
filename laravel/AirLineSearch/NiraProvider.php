<?php

namespace App\ThirdPartyApi\ApiProviders;


use App\Models\Flights;
use App\ThirdPartyApi\ApiProviderInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;

class NiraProvider implements ApiProviderInterface
{

    /**
     * constant Available Url from $Url[].
     */





    #region Constant Avilable Url Shares
    private const AVAILABLE = 'AvailabilityJS.jsp';
    private const AVAIFARE = 'AvailabilityFareJS.jsp';
    private const A_FARE = 'FareJS.jsp';
    private const RESERVE = 'ReservJS';
    private const NRSP = 'NRSPassport.jsp';
    private const ETISSU_BOOK = 'ETIssueJS';
    private static $niraUserNameApi = null;
    private static $niraPasswordApi = null;
    private static $apiType = null;


    #endregion Constant Avilable Url Shares






    #region Searches Flight To Nira
    public function __construct($niraUserName, $niraPassword, $apiType)
    {
        self::$niraUserNameApi = $niraUserName;
        self::$niraPasswordApi = $niraPassword;
        self::$apiType = $apiType;

    }



    public function searchFlights($origin, $destination, $date1, $date2, $numOfAdult, $numOfChild, $numOfInfant): array
    {

        list($urls, $https, $cunts, $params) = self::paramsHttpList($origin, $destination, $date1, $date2, $numOfAdult, $numOfChild, $numOfInfant);

        $url = config('NiraConfig.BaseConfig.BaseUrl.Availability') . self::AVAILABLE . '?' . $https;

        $requstsin = Http::post($url)->json();

        $collections = $requstsin['AvailableFlights'];
        return static::getCollectionData($collections, $params, $urls, $cunts);


    }


    /**
     *دریافت پارامتر و لینک  متد Fare  جهت اادغام و ارسال
     */
    protected static function paramsHttpList($origin, $destination, $date1, $date2, $numOfAdult, $numOfChild, $numOfInfant)
    {

        list($urls, $prameterFare) = self::fareOptionsSearche($origin, $destination, $date1);

        $parameter = self::searchesParameters($origin, $destination, $date1, $date2, $numOfAdult, $numOfChild, $numOfInfant);
        $https = http_build_query($parameter);
        $cunts = $numOfAdult + $numOfChild + $numOfInfant;

        $params = collect($parameter)->merge($prameterFare)->all();

        return [$urls, $https, $cunts, $params];
    }

    /**
     * دریافت پارامترهای سرچ.
     *
     * @param $request
     *
     * @return array
     */
    protected static function searchesParameters($origin, $destination, $date1, $date2, $numOfAdult, $numOfChild, $numOfInfant)
    {
        list($year, $mounth, $day) = static::localDateConverter($date1);
        $parameter = [
            'AirLine' => 'ZV',
            'cbSource' => $origin,
            'cbTarget' => $destination,
            'cbDay1' => $day,
            'cbMonth1' => $mounth,
            'cbAdultQty' => $numOfAdult,
            'cbChildQty' => $numOfChild,
            'cbInfantQty' => $numOfInfant,
            'OfficeUser' => self::getNiraUserNameApi(),
            'OfficePass' => self::getNiraPasswordApi(),
        ];

        return $parameter;
    }


    /**
     * دریافت کلیه اطلاعات بخش سرچ.
     *
     * @param $collections
     * @param $urls
     */
    protected static function getCollectionData($collections, array $params, $urls, $cunts)
    {
        try {
            $arraysMerged = [];
            foreach ($collections as $key => $item) {

                $AvailableFlights = [];
                $searchCollection = $AvailableFlights[$key] = $item;


                $fliteNo = $searchCollection['FlightNo'];
                $class = $searchCollection['ClassesStatus'];

                list($rdb, $chec, $classes) = self::machesFlightClasseStause($class);


                $price = self::sendRequestFromPiceAv($fliteNo, $classes, $params, $urls);
                $data = self::convertLatinUtf8($price);
                $priceAll = json_decode($data, true);
                $arraysMerged[] = array_merge([], $item, $priceAll);
                Log::info($arraysMerged);
            }

            return self::convertedPushing($arraysMerged, $cunts);

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

    }


    /**
     * دریافت کلاس های باز
     * @param $searchCollection
     * @return array
     */
    protected static function machesFlightClasseStause($class): array
    {
        $trime = trim($class, '/');
        $mache = Str::match("/\b[A-Z+]+[\d$|A$]\b/", $trime);
        $close = Str::match("/\b[A-Z+]+[C$]\b/", $trime);

        $rdb = !is_null($mache) && !empty($mache) ? $mache : $close;
        $chec = substr($rdb, -1, 1);
        $lenght = Str::length($rdb) <= 2 ? str_split($rdb, 1) : str_split($rdb, 2);

        $classes = $lenght[0];
        $tedad = $lenght[1];
        return array($rdb, $chec, $classes);
    }


    /**
     * ارسال درخواست به api  و دریافت قیمت کلاس
     * @param $fliteNo
     * @param $classes
     * @param array $params
     * @param $urls
     * @return string
     */
    protected static function sendRequestFromPiceAv($fliteNo, $classes, array $params, $urls): string
    {
        $prameterFare = array_merge(['FlightNo' => $fliteNo, 'RBD' => $classes], $params);
        $paramser = http_build_query($prameterFare);
        $post = Http::post($urls . '?' . $paramser);
        $price = $post->body();
        return $price;
    }


    /**
     * تبدیل دیتای  استرینگ دریافت شده به جیسون و آراای
     * @param $dat
     * @return array|bool|string|string[]|null
     */
    public static function convertLatinUtf8($dat)
    {
        if (is_string($dat)) {
            return mb_convert_encoding(utf8_decode($dat), "UTF-8", "UTF-8");
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[$i] = self::convertLatinUtf8($d);

            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convertLatinUtf8($d);

            return $dat;
        } else {
            return $dat;
        }
    }


    /**
     * تبدیل اطلاعات دریافتی و پاس دادن به Ui
     * @param $mergeTotalPrice
     * @param $cunts
     * @param $rdb
     */
    protected static function convertedPushing($arraysMerged, $cunts)
    {
        $AvailableFlights = [];
        foreach ($arraysMerged as $key => $val) {
            list($rdb, $chec, $classes) = self::machesFlightClasseStause($val['ClassesStatus']);
            $parvazNumber = ($chec == 'C' ? ' ظرفیت تکمیل است' :
                ($chec == 'A' ? 9 :
                    ($chec == 'X' ? 'پرواز کنسل شده است ' :
                        ($chec))));
            $AvailableFlights[] = collect([
                'ParvazId' => $val['FlightNo'] ?? $val->FlightNo,
                'FlightNo' => $val->OperatingFlightNo ?? $val['OperatingFlightNo'],
                'IataCodSource' => $val->Origin ?? $val['Origin'],
                'IataCodDestinate' => $val->Destination ?? $val['Destination'],
                'AirPlaneName' => $val->AircraftTypeName ?? $val['AircraftTypeName'],
                'FlightDateTime' => $val->DepartureDateTime ?? $val['DepartureDateTime'],
                'ArrivalDateTime' => $val->ArrivalDateTime ?? $val['ArrivalDateTime'],
                'StopQuantity' => $val->Transit ?? $val['Transit'],
                'AirlineCode' => $val->AircraftTypeCode ?? $val['AircraftTypeCode'],
                'AirlineICAO' => $val->Airline ?? $val['Airline'],
                'CabinType' => $classes,
                'Class' => $classes,
                'ClassId' => $chec,
                'comment' => $rdb,
                'AvailableSeatQuantity' => $parvazNumber,
                'PriceView' => $val->AdultTotalPrice ?? $val['AdultTotalPrice'],
                'PriceADL' => $val->AdultTotalPrice ?? $val['AdultTotalPrice'],
                'PriceCHD' => $val->ChildTotalPrice ?? $val['ChildTotalPrice'],
                'PriceINF' => $val->InfantTotalPrice ?? $val['InfantTotalPrice'],
                'flighSerial' => $val->AircraftTypeCode ?? $val['AircraftTypeCode'],
                'apiData' => json_encode([
                    'No' => $cunts,
                ]),
                'is_test' => null,
                'apiRes' => json_encode($val),
                //Todo:بایستی تکمیل شود

            ]);
        }
        return $AvailableFlights;

    }


    #region Parameters And Convert to Date Time

    /**
     * انتقال پارامترها Fare  به صورت Async.
     *
     * @param $request
     *
     * @throws \Laravel\Octane\Exceptions\DdException
     */
    public static function fareOptionsSearche($origin, $destination, $date1): array
    {
        $prameterFare = self::searchesMerginParameters($origin, $destination, $date1);
        $urls = config('NiraConfig.BaseConfig.BaseUrl.Fare') . self::A_FARE;

        return [$urls, $prameterFare];
    }


    /**
     *ریفکتور کردن تاریخ , parameters Fare Request Method.
     **/
    private static function searchesMerginParameters($origin, $destination, $date1)
    {
        $dates = static::convertingDateToAssociate($date1);

        $prameterFare = [
            'Route' => join('-', [$origin, $destination]),
            'DepartureDate' => $dates,
        ];

        return $prameterFare;
    }

    /**
     * گرفتن تاریخ و تبدیل به میلادی.
     *
     * @param $request
     */
    private static function convertingDateToAssociate($request): string
    {

        list($year, $mounth, $day) = self::localDateConverter($request);
        $capture = (new Jalalian($year, $mounth, $day, 0, 0, 0))->toCarbon()->toDateString();
        return $capture;
    }

    /**
     * تبدیل تاریخ نا منظم به صورت array.
     *
     * @param $request
     *
     * @return array
     */
    private static function localDateConverter($request)
    {

        $dates = strstr($request, '-') ? explode('-', $request) : explode(',', $request);
        $year = $dates[0];
        $day = Str::length($dates[2]) >= 2 ? $dates[2] : '0' . $dates[2];
        $mounth = Str::length($dates[1]) >= 2 ? $dates[1] : '0' . $dates[1];
        return [$year, $mounth, $day];
    }

    #endregion Parameters And Convert to Date Time


    #endregion Searches Flight To Nira







    #region Reserve Fligh

    /**
     * @param string $IataCodSource
     * @param string $IataCodDestinate
     * @param string $flightNo
     * @param string $flighSerial
     * @param string $apiData
     * @param int $ADL
     * @param int $CHD
     * @param int $INF
     * @param string $userip
     * @return array
     */
    public function reserveFlight($IataCodSource, $IataCodDestinate, $flightNo, $flighSerial, $apiData, $ADL, $CHD, $INF, $userip): array
    {

        return [
            'reservable' => true,
            'ReqNo' => 'a'
        ];

        try {

            $parameter = static::reserveParameters($IataCodSource, $IataCodDestinate, $flightNo, $flighSerial, $apiData, $ADL, $CHD, $INF);


        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     * update method reserve property to resve and send passengers to reseve about get Pnr
     * @param string $reqNumber
     * @return bool[]
     */
    public function reserve($reqNumber,$passengers): array
    {
        Log::error($passengers);
        $passnames = $passengers->passengers;
        $pnr = self::Nirareserve($passnames,$passengers);
        Log::error('$upPnr$$$$$$$$$$$$$$$$$$pnr');
        Log::error($pnr);
        if($pnr !== null){
            $flight = Flights::findOrFail($passengers->id);
            $flight->pnr = $pnr;
            $flight->save();
        }
        return [
            'success'=> true,
        ];
    }

    /**
     * رزرو پرواز نیرا.
     *
     * @param ReserveRequest $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     */
    public static function Nirareserve($passengers, $flightModel)
    {

        try {
            $url = config('NiraConfig.BaseConfig.BaseUrl.Reserve') . self::RESERVE;
            $parameters = self::jsonConverterToMergesParam($flightModel, $passengers);
            $response = self::sendRequest($url, $parameters);

            $resPnr = $response['AirReserve'];
            $pnr = self::getPnrsFlight($resPnr);
            $passport = self::checkParamsToMerginPassport($passengers);
            if ($passport === true) {
                //Todo::  تکمیل ساختار پاسپورت  در اینده بعد از درست کردن آن از طریق شرکت هواپیمایی نیرا
//                $params = self::passportParametersLoopFligh($passengers,$pnr);
//                $url = config('NiraConfig.BaseConfig.BaseUrl.NRSPass') . self::NRSP;
//                $resendpass = self::sendRequest($url,$params);
                return response(['message'=> '  در حال حاظر امکان خرید پروازهای خارجی ممکن نمیباشد'],401);

            }
            return $pnr;

        } catch (\Exception $e) {
            echo $e->getMessage();

        }
    }

    /**
     * check is not empty for data Passport :return true | Null
     * @param $request
     */
    private static function checkParamsToMerginPassport($request)
    {

        foreach ($request as $key => $item) {
            $adult = [];
            $adult = $adult[$key] = $item;
            $numbr = $adult->passport_number;
            if ($numbr !== null){
                return true;
            }
        }
        return null;

    }

    /**
     * دریافت اطلاعات از ورودی و انتقال متد رزور جهت دریافت  PNR
     * @param $flightModel
     * @param $passengers
     * @return array
     */
    protected static function jsonConverterToMergesParam($flightModel, $passengers): array
    {
        $flight = json_decode($flightModel);
        $passName = static::fetchAci($passengers);
        $paramsFlight = static::reserveParameters($flight);

        $parameters = array_merge($passName, $paramsFlight);
        return $parameters;
    }

    /**
     * دریافت پارامترهای رزرو.
     *
     * @param $request
     *
     * @return array
     */
    protected static function reserveParameters($apiData)
    {
        $No = count($apiData->passengers);
        list($year, $mounth, $day) = static::convertToPersian($apiData->flight_date_time);
        $parameter = [
            'AirLine' => $apiData->airline_icao,
            /*
            * کد استاندارد شهر مبدا
            **/
            'cbSource' => $apiData->origin,
            /*
            *کد استاندارد شهر مقصد
            **/
            'cbTarget' => $apiData->destination,
            /*
            *کلاس
            **/
            'FlightClass' => $apiData->class,
            /*
            *شماره پرواز
            **/
            'FlightNo' => $apiData->fligh_number,
            /*
            *روز
            **/
            'Day' => $day,
            /*
            *ماه
            **/
            'Month' => $mounth,
            /*
            *تعداد مسافران
            **/
            'No' => $No,
            /*
            *نام کاربری
            **/

            'OfficeUser' => self::getNiraUserNameApi(),
            'OfficePass' => self::getNiraPasswordApi(),
        ];
        return $parameter;
    }

    /**
     * دریافت بزرگسالان
     * {@inheritdoc}
     */
    public static function fetchAci($request)
    {
        foreach ($request as $key => $item) {
            $adult = [];
            $key = $key + 1;
            $adult = $adult[$key] = $item;
            $adults['edtName' . $key] = $adult->name_latin;
            $adults['edtLast' . $key] = $adult->family_latin;
            $adults['edtAge' . $key] = static::convertedDate($adult->year_of_birth);
            $adults['edtID' . $key] = $adult->national_code;
            $adults['edtContact' . $key] = $adult->mobile ?? null;
        }
        return $adults;

    }

    /**
     * دریافت سن  کاربر.
     */
    public static function convertedDate($yearOfBirth)
    {
        $timezone = Date::now();
        $date = Jalalian::forge($timezone)->getYear();

        return $date - $yearOfBirth;
    }

    /**
     * تبدیل ه فارسی.تاریخ ب.
     *
     * @param $request
     *
     * @return array
     */
    public static function convertToPersian($request)
    {
        $datable = Jalalian::forge($request)->format('Y-m-d');
        list($year, $mounth, $day) = static::localDateConverter($datable);

        return [$year, $mounth, $day];
    }

    /**
     * تکمیل فرایند بوک پرواز
     * @param object $flightModel
     * @param array $passengers
     * @param string $apiData
     * @return array|void
     */
    public function bookFlight($flightModel, $passengers, $apiData)
    {
        $pnr = self::getPnrsFlight($flightModel);
        $url = config('NiraConfig.BaseConfig.BaseUrl.Etissue') . static::ETISSU_BOOK;
        $params = [
            'Airline' => 'ZV',
            'PNR' => $pnr,
            'Email' => 'neyramipateh@gmail.com',
            'OfficeUser' => self::getNiraUserNameApi(),
            'OfficePass' => self::getNiraPasswordApi(),
        ];
        return self::sendRequest($url, $params);

    }


    /**
     * دریافت  pnr  پرواز
     * @param $pnr
     * @return array
     */
    protected static function getPnrsFlight($pnr)
    {

        $pnrs = [];
        foreach ($pnr as $key => $item) {

            $pnrs = $pnrKey[$key] = $item['PNR'];

        };
        return $pnrs;
    }

    /**
     * Booking Flight Nira Neyrami
     * @param $url
     * @param $params
     * @return mixed
     */
    public static function sendRequest($url, $params)
    {
        $buile = http_build_query($params);
        $book = Http::post($url . '?' . $buile);
        $responce = $book->json();
        return  $responce;
    }

    /**
     *  تبدیل جهت دریافت پاسپورت
     * @param $year_of_birth
     * @param $month_of_birth
     * @param $day_of_birth
     * @return string
     */
    private static function convertingPassportDateTime($year_of_birth, $month_of_birth, $day_of_birth)
    {
        $date = implode('-', [$year_of_birth, $month_of_birth, $day_of_birth]);
        return $date;
    }


    /**
     * دریافت اطلاعات مسافران خارجی  که در اینده تکمیل خواهد شد
     * @param $request
     * @return array
     */
    public static function passportParametersLoopFligh($request,$pnr)
    {
        //Todo::  تکمیل ساختار پاسپورت  این قسمت تکمبل میباشد ادامه روند  در اینده
        foreach ($request as $key => $item) {
            $adult = [];
            $adult = $adult[$key] = $item;
            $numbr = $adult->passport_number;
            $key = $key + 1;
            if ($numbr !== null)
            {
                $birth_country = json_decode($adult->birth_country, true);
                $birth_country = $birth_country['3-letter'];
                $issue_country = json_decode($adult->passport_country, true);
                $issue_country = $issue_country['3-letter'];

                $adults['Airline' . $key] = $adult->passport_number;
                $adults['PNR' . $key] = $pnr;
                $adults['BirthCountry' . $key] = $birth_country;//json_decode($adult->birth_country);
                $adults['BirthDate' . $key] = self::convertingPassportDateTime($adult->year_of_birth, $adult->month_of_birth, $adult->day_of_birth);;
                $adults['IssuerCountry' . $key]=$issue_country;
                $adults['PassportNo' . $key] = $adult->passport_number;
                $adults['ExpireDate' . $key] = $adult->passport_expire_date;
                $adults['FirstName' . $key] = $adult->name;
                $adults['LastNam' . $key] = $adult->family;
                $adults['Gender' . $key] = $adult->sex;
            }


        }
        return $adults;

    }

    #endregion Reserve Fligh  to






    #region Cancel And Updateing
    public function credit(): string
    {
        return 'hi';
    }

    public function updateFlight(object $flightModel, string $apiData)
    {
        // TODO: Implement updateFlight() method.
    }

    /**
     *  کنسل کردن نیرا.
     *
     * @param \App\Http\Requests\Nira\CancelRequest $request
     *
     * @return array|mixed|string
     */
    public static function cancel($request)
    {
        try {
            $url = config('NiraConfig.BaseConfig.BaseUrl.CancelPNR');
            $parameter = [
                'Airline' => $request->airline,
                'PNR' => $request->PNR,
                'OfficeUser' => self::getNiraUserNameApi(),
                'OfficePass' => self::getNiraPasswordApi(),
            ];
            $params = http_build_query($parameter);

            $responce = Http::post($url, $parameter);

            return $responce->json();
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }


    /**
     *از این تابع برای تکمیل فرایند استرداد بلیت و ثبت فرایند مالی استرداد استفاده می شود.
     */
    public static function cancelEtRefund()
    {

    }
    #endregion Cancel And Updateing






    #region Geter Priveting Property
    /**
     * @return null
     */
    public static function getNiraUserNameApi()
    {
        return self::$niraUserNameApi;
    }

    /**
     * @return null
     */
    public static function getNiraPasswordApi()
    {
        return self::$niraPasswordApi;
    }

    /**
     * @return null
     */
    public static function getApiType()
    {
        return self::$apiType;
    }


    #endregion Geter Priveting Property


}
