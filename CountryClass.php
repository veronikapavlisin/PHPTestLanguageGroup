<?php 

/**
 *
 * Class for checking same language spoken EU countries.
 * Created as part of coding challenge for developer position @YAS.life 
 *
 * @author     Veronika Pavlisin
 * @version    1.00
 * @date       19.06.2019
 */
class Country
{
    // country name
    private $name;
    // array of languages spoken in country
    private $languageCodes;
    // arrat of countries that speak languages spoken in country
    private $sameLanguageCountries;
    
    // flag that REST api data are loaded
    private $isLoaded;
    // flag that searched country was found
    private $isVallid;
    
    const NEWLINE = "\n";
    
    /**
     *
     * Country constructor
     *
     * @access   public
     * @param 	string 	$name  name of country
     *
     * @return
     */    
    public function __construct($name)
    {
        // initialization of properties
        $this->name = $name;
        $this->languageCodes = [];
        $this->sameLanguageCountries = [];
        
        $this->isLoaded = false;
        $this->isValid = false;    
    }
    
    /**
     *
     * Method for loading country's data from REST api service
     *
     * @access   public
     *
     * @return
     */    
    public function load()
    {
        // loading data for country from REST api with output limited just to required fields
        $url = 'http://restcountries.eu/rest/v2/name/'.$this->name.'?fields=name;languages';
        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL, $url);
        curl_setopt($cURL, CURLOPT_HTTPGET, true);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($cURL, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Accept: application/json']);
        
        $countryData = json_decode(curl_exec($cURL), true);
        
        if (curl_error($cURL))
        {
            $error_msg = curl_error($cURL);
        }
        else
        {
            $this->isLoaded = true;
        	 
            // if loaded name is same as searched - country was found and is valid
            if (isset($countryData[0]) && isset($countryData[0]['name']) && $countryData[0]['name'] == $this->name)
            {
                $this->isValid = true;
        	    
                // country can be multilingual, therefore array is used for storage
                for ($i = 0; $i < count($countryData[0]['languages']); $i++)
                {
                    $this->languageCodes[] = $countryData[0]['languages'][$i]['iso639_1'];
                }
            }
            else
            {
                $this->isValid = false;
            }
        }
        
        curl_close($cURL);
    }
    
    /**
     *
     * Method for loading data for languages spoken in the country
     *
     * @access   public
     *
     * @return
     */    
    public function loadLanguage()
    {
    	$this->sameLanguageCountries = [];
    	
        // for each stored language we will read all countries that speak it
    	for ($i = 0; $i < count($this->languageCodes); $i++)
    	{
    	    // loading data for each language from REST api with output limited just to required field
    	    $url = 'http://restcountries.eu/rest/v2/lang/'.$this->languageCodes[$i].'?fields=name';
    	    $cURL = curl_init();
    	    curl_setopt($cURL, CURLOPT_URL, $url);
    	    curl_setopt($cURL, CURLOPT_HTTPGET, true);
    	    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
    	    
            curl_setopt($cURL, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Accept: application/json']);
    	    
    	    $languageCountries = json_decode(curl_exec($cURL), true);
            
    	    $this->sameLanguageCountries[$this->languageCodes[$i]] = [];
    	    
    	    for ($j = 0; $j < count($languageCountries); $j++)
    	    {
    	    	if ($languageCountries[$j]['name'] != $this->name)
    	        {
    	    	    $this->sameLanguageCountries[$this->languageCodes[$i]][] = $languageCountries[$j]['name'];
    	        }
    	    }
    	    
    	    curl_close($cURL);
    	}    	 
    }
    
    /**
     *
     * Method returns output for code of language(s) spoken in country and 
     * list of countries that speak same language(s)
     *
     * @access   public
     *
     * @return   string  formated result
     */
    public function readLanguage()
    {
        // formated output
    	$outputString = '';
    	
        // returning information on all languages spoken in our country
        foreach ($this->sameLanguageCountries as $languageCode => $languageCountries)
        {
            if (count($languageCountries))
            {
                $outputString .= 'Country language code: '.$languageCode.self::NEWLINE;
                // slight change in formated text in case of only one same language country
                $outputString .= $this->name.' speaks same language with '.(count($languageCountries) == 1 ? 'this country' : 'these countries').': '.implode(', ', $languageCountries).self::NEWLINE;
            }
        }
        
        // if there were no other same language speaking countries for any of the languages
        if (!$outputString)
        {
            foreach ($this->sameLanguageCountries as $languageCode => $languageCountries)
            {
                $outputString .= 'Country language code: '.$languageCode.self::NEWLINE;
    	    }
            $outputString .= 'There are no countries that speak same language as '.$this->name.self::NEWLINE;
        }
        
        return $outputString;
    }
    
    /**
     *
     * Method returns output for comparison whether country speaks same language as country passed in parameter
     *
     * @access   public
     * @param 	string 	$secondCountry  name of second country that the country will be compared to
     *      
     * @return   string  formated result
     */
    public function compareLanguage($secondCountry)
    {
        $secondCountryFound = false;
        // we will look for secondCountry in subarrays of all languages spoken in our country
        foreach ($this->sameLanguageCountries as $languageCode => $languageCountries)
        {
            if (in_array($secondCountry, $languageCountries))
            {
                $secondCountryFound = true;
                break;
            }
        }
         
        return $this->name.' and '.$secondCountry.(!$secondCountryFound && $this->name != $secondCountry ? ' do not' : '').' speak the same language'.self::NEWLINE;
    }
    
    /**
     *
     * Method confirms whether data were read from REST api service without issue
     *
     * @access   public
     *      
     * @return   boolean  flag that data were read
     */
    public function isLoaded()
    {
        return $this->isLoaded;
    }
    
    /**
     *
     * Method confirms whether data read from from REST api belog to searched country
     *
     * @access   public
     *      
     * @return   boolean  flag that country was found
     */
    public function isValid()
    {
        return $this->isValid;
    }
}
?>
