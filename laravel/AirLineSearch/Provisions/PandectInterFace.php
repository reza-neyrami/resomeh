<?php


namespace App\Provisions;


interface PandectInterFace
{

    /**
     *  اطلاعات بایستی از ورودی منتقل شود و جهت تست داخل کش قرار دادم ...قوانین هر فلایتی که بروی اون کلیک میشود بایستی داخل  تابع پریسنت پرایس قرار داده شود تا زمانش با زمان اون قانون تطابق ایجاد شود
     * @param string|null $airline :'get airline name for compore'
     * @param string|null $origin :'get origin name for compore'
     * @param string|null $destinite :'get destinite name for compore'
     * @param string|null $options :'get options name for compore'
     * @param string|null $class :'get class name for compore'
      * @param string|\DateTime | null $ArrivalDateTime :'get ArrivalDateTime date flight example : 2021-08-22 13:26:00''Y-m-d H:i:s'
     * @return json
     */
    public static function syncRule(string $airline, ?string $origin, ?string $destinies, ?string $options, string $class,?string $ArrivalDateTime);


    /**
     * get flight an provisions and get date fligh  for precent cancelling
     * @param string|null $ArrivalDateTime :'get ArrivalDateTime date flight '
     * @param $airLine
     * @param $class
     * @Echo integer
     */
    public static function precentPrice($airLine,$class,$ArrivalDateTime);




}
