<?php 
/**
 * PHP Test (Language Group)
 * Coding challenge for developer position @YAS.life
 *
 * @author     Veronika Pavlisin
 * @version    1.00
 * @date       19.06.2019
 */

include_once "CountryClass.php";

// scrip is limited to use only via command line
if (!isset($argc))
{
    exit ("Error: service usable via command line only");
}

// number of parameters limited to 2
// this means issue with countries with more than one word in their names (e.g. Czech Republic)
// solution doesn't address it, because of lack of specification
if ($argc < 2 || $argc > 3)
{
    exit ("Error: minimum one and maximum two parameters are required");
}

// object is created for country in first parameter
$country = new Country($argv[1]);

// data from REST api are loaded
$country->load();

// if successfully loaded
if ($country->isLoaded())
{
    // and name for country actually represents existing country
    if ($country->isValid())
    {
        // language data are read from REST api
        $country->loadLanguage();
    }
    else
    {
        exit ("Error: unknown country ".$argv[1]);
    }
}
else
{
    exit ("Error: unable to load country data from restcountries.eu");
}

// if there was just one parameter, we are listing language code and language related coutries
if ($argc == 2)
{
    exit($country->readLanguage());
}
// otherwise we are checking whether country from second parameter is present among language related countries
else
{
    exit($country->compareLanguage($argv[2]));
}

?>
