<?php


namespace App\Provisions;


use App\Http\Resources\Pandect\ProvisionsCollectionsResource;
use App\Models\Pandect\Provision;
use Carbon\Carbon;

class PandectInterFaceRepository implements PandectInterFace
{
    private static $AvilablesArray = [];


    /**
     * اعمال قوانین بروی ارایه
     * @param string|null $airline
     * @param string|null $origin
     * @param string|null $destinite
     * @param string|null $options
     * @param string|null $class
     * @param string|null $date
     * @return \Illuminate\Support\Collection
     */
    public static function syncRule(?string $airline, ?string $origin, ?string $destinite, ?string $options, ?string $class, ?string $ArrivalDateTime)
    {
        try {
            self::$AvilablesArray = ['origin' => $origin, 'destinite' => $destinite, 'options' => $options];

            $provision = Provision::all();
            $filterNull = self::filtersNotNull(self::$AvilablesArray);
            $dataChange = self::filtersProvisionRuleFlight($provision, $airline, $origin, $destinite, $options, $class, $ArrivalDateTime);

            $filterIn = collect($filterNull)->merge(['class' => $class, 'ArrivalDateTime' => $ArrivalDateTime, 'airline' => $airline])->all();

            $pushe = collect(['flight' => $filterIn])->merge(['provisionss' => $dataChange])->all();
            return (new ProvisionsCollectionsResource($pushe));

        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }



    /**
     * @param $provision
     * @param array $filterNull
     * @param string|null $class
     * @return array
     */
    protected static function filtersProvisionRuleFlight($provision, $airline, $origin, $destinies, $options, $class,$ArrivalDateTime): array
    {
        foreach ($provision as $key => $value) {
            $check[$key] = $value;
        }

        $dataChange = array_filter($check, function ($value) use ($airline, $origin, $destinies, $options, $class,$ArrivalDateTime) {

            if ($airline == $value['airline'] && array_intersect([$class],$value['class']) ){

                $date =   $ArrivalDateTime !== null && $ArrivalDateTime > $value['startDate']
                    && $ArrivalDateTime < $value['endDate'] ?? false ;

                $orgins = $origin !== null || (!empty($origin) && $origin == $value['origin']) ?? false ;
                $destinite = $destinies !== null && $destinies == $value['destinite'] ?? false;
                $optionsin = $options !== null && $options == $value['options'] ?? false;

                $dateActives = $value['startDate'] && $value['endDate'] !== null  ? $date :true;
                $originval = empty($value['origin']) !== true ? $orgins : true;
                $destiniteval = empty($value['destinite']) !== true ? $destinite : true;
                $optionsVal = empty($value['options']) !== true ? $optionsin :true;


                return $originval == true && $destiniteval == true && $optionsVal == true && $dateActives == true;



            }


        });
        return $dataChange;
    }

    /**
     * بدست اوردن مقدار جریمه
     * بایستی زمان  و همچنین قوانین متصل به هر ارایه یا فلایت به این تابع پاس داده شود
     * @param $flightDate
     * @return mixed
     */
    public static function precentPrice($airLine,$class,$ArricableDate)
    {
        try {

            $precent = Provision::all();
            if (!empty($precent)) {
                $jsonArray = json_decode($precent,true);

                $diff = Carbon::now()->diffInHours($ArricableDate) ;
                foreach ($jsonArray as $key => $value){
                    $data[$key] = $value;
                }

                $filter = array_filter($data, function ($val) use ($diff,$airLine,$class) {

                    return $airLine == $val['airline'] && array_intersect([$class], $val['class']) && $diff <= $val['times'];

                });
                foreach ($filter as $key => $values) {
                    echo $listes[$key] = $values['percent'] ;
                }

            }
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }

    }


    /**
     * @param $val
     * @return array
     */
    public static function filtersNotNull($val)
    {
        $data = array_filter($val, function ($value) {
            return $value !== null;
        });

        return $data;
    }


    /**
     * @param $vals
     * @return string
     */
    public static function arrayToSreings($vals)
    {

        return implode(' ', $vals);
    }


}
