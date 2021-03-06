<?PHP
	/***********************
	Calendar1.php module

	the primary objective of this module is to present the holiday
	which determines U.S., christian, hebrew, and islamic holidays occuring
	during a specific gregorian year.  gregorian, hebrew, and islamic date handling
	and conversion functions are provided for use in the class, and any other uses
	you need.  note the julian day is used for all conversions.

	to use the module:
		delete html code at the beginning and end of this module,
		delete the test functions at the end of the module
		comment out undesired holidays
		add desired holidays
		declare a holiday variable for the desired year,
			e.g., $cal = new holiday($yr)
		use one of the output methods:
			$cal->ListHolidays([$yr]) to print the list of holidays to html output
			$cal->GetHolidays($jd) to return a string of any holidays
				corresponding to a julian day (use to list holidays on calendars)
			$cal->GetHoliday($s) to return a julian day of the holiday corresponding
				to the parameter $s (e.g. "Christmas")

	************************************/

	//julian day corresponding to the start of various functions
	define("GREGORIAN_EPOCH", 1721425.5, TRUE);
	define("HEBREW_EPOCH", 347995.5, TRUE);
	define("ISLAMIC_EPOCH", 1948439.5, TRUE);
	define("UNIX_EPOCH", 2440587.5, TRUE);
		//caution--php's mktime uses universal time
		//adjustments are made corresponding to the server's time zone

	define("SECS_IN_DAY", 86400.0, TRUE);

	//constants for day of week interpretation
	define("dSUNDAY", 0, TRUE);
	define("dMONDAY", 1, TRUE);
	define("dTUESDAY", 2, TRUE);
	define("dWEDNESDAY",3, TRUE);
	define("dTHURSDAY", 4, TRUE);
	define("dFRIDAY", 5, TRUE);
	define("dSATURDAY", 6, TRUE);

	$GREGORIAN_DAY = Array("Sunday", "Monday", "Tuesday", "Wednesday",
				"Thursday", "Friday", "Saturday");

	//gregorian month constants
	define("mJANUARY", 1, TRUE);
	define("mFEBRUARY", 2, TRUE);
	define("mMARCH", 3, TRUE);
	define("mAPRIL", 4, TRUE);
	define("mMAY", 5, TRUE);
	define("mJUNE", 6, TRUE);
	define("mJULY", 7, TRUE);
	define("mAUGUST", 8, TRUE);
	define("mSEPTEMBER", 9, TRUE);
	define("mOCTOBER", 10, TRUE);
	define("mNOVEMBER", 11, TRUE);
	define("mDECEMBER", 12, TRUE);

	$GREGORIAN_MONTH = Array(1 => "January", "February", "March",
				"April", "May", "June", "July", "August", "September",
				"October", "November", "December");

/*************************
	holiday -- class provides methods to determine and output holidays in any given gregorian year
		the primary objective is list any holidays associate with a given julian day
************************************************/
class holiday {

	var $holidays;
	var $bjd; //beginning julian day of the year
	var $ejd; //ending julian day of the year

	//initialization method
	function holiday ($yr=0)
	{
		if ($yr==0) $yr = (int)date("Y");
		$this->bjd = gregorian_to_jd(1,1,$yr);
		$this->ejd = gregorian_to_jd(12,31,$yr);

		//add holidays easily define in the gregorian calendar
		$this->holidays = Array(
			"newyearseve" => gregorian_to_jd(12,31,$yr),
			"valentinesday"=> gregorian_to_jd(2,14,$yr),
			"presidentsday" => nth_weekday_jd(3,dMONDAY,2,$yr),
			"stpatricksday" => gregorian_to_jd(3,17,$yr),
			"aprilfoolsday" => gregorian_to_jd(4,1,$yr),
			"cincodemayo" => gregorian_to_jd(5,5,$yr),
			"mothersday" => nth_weekday_jd(2,dSUNDAY,5,$yr),
			"memorialday" => $this->memorialDay($yr),
			"fathersday" => nth_weekday_jd(3,dSUNDAY,6,$yr),
			"fourthofjuly" => gregorian_to_jd(7,4,$yr),
			"laborday" => nth_weekday_jd(1,dMONDAY,9,$yr),
			"columbusday" => nth_weekday_jd(2,dMONDAY,10,$yr),
			"halloween" => gregorian_to_jd(10,31,$yr), 
			"thanksgiving" => nth_weekday_jd(4, dTHURSDAY, 11,$yr),
			"christmas" => gregorian_to_jd(12,24,$yr),
			"christmasday" => gregorian_to_jd(12,25,$yr),
			"mardigras" => easter_jd($yr,-47),
			"easter" =>	easter_jd($yr)
		);
			
		asort($this->holidays);
	}

	//method determines Memorial Day
	function memorialDay ($yr)
	{	//last mon in may
		$jd = gregorian_to_jd(5,31,$yr);

		$w = jd_to_weekday($jd);
		//if $w is sunday, it's the sunday FOLLOWING memorial day
		if ($w==dSUNDAY) $w = 7;

		return ( $jd - ($w - dMONDAY) );
	}

	//method prints/lists ALL holidays to the html output
	//modify for the html output you want, or to return a string
	//containing all output
	function listHolidays($y=0)
	{
		global $GREGORIAN_MONTH, $GREGORIAN_DAY;
		//call with year $y for other than current year
		if ($y!=0) $this->holiday($y);
		//reset($this->holidays);

		//print ("<pre>");
		//print_r ($this->holidays);
		//print ("</pre>");
		//return;

		foreach($this->holidays as $k => $f)
		{
			jd_to_gregorian($f, $m, $d, $y);
			$d = ": " . $GREGORIAN_DAY[jd_to_weekday($f)] . ", " .
				$GREGORIAN_MONTH[$m] . " $d, $y";

			//print("<pre>" . $f . "--" . $k . $d . "</pre>");
			print($k . $d . "<br />");

		}
	}


	//method returns a string listing all current holidays
	//falling on the input julian day parameter ($jd)
	//the string is empty is no holidays fall on that date
	function getHolidays($jd)
	{
		$s="";
		reset($this->holidays);

		//test each holiday for matching given date
		foreach($this->holidays as $k => $d)
		{
			if ($jd==$d) $s.=$k."<br/>\n\r\n";
		}

		//return string listing any holidays for given date
		return ($s);
	}

	//method returns the date of the holiday corresponding to
	//the input parameter $s = ct_holiday.slug (e.g.: "christmas")
	//zero (0) is returned if no holidays correspond to $s
	
	function getDate($s)
	{
		if (array_key_exists($s, $this->holidays)) return (strtotime(jdtogregorian(intval($this->holidays[$s],10)+1)));


		//the key doesn't exist, return 0
		return (0);
	}
	
	// static function to get the next ct_holidays
	function getUpcomingHolidays($date=NULL) {
		if (!$date) $date = time();
		$year = date('Y');
		$rs = aql::select("ct_holiday { slug, date where date is not null order by date_order asc }");
		foreach ($rs as $r) {
			if (strtotime($r['date'].'/'.$year) > $date ) $this_years_holidays[$r['slug']] = strtotime($r['date'].'/'.$year);
			else $next_years_holidays[$r['slug']] = strtotime($r['date'].'/'.($year+1));
		}
		return(array_merge($this_years_holidays,$next_years_holidays));
}

	

} //end class

	/*************************
	JD_TO_WEEKDAY -- determine the weekday from julian day
	***************************/
	function jd_to_weekday($jd) {
		return ( floor( ($jd+8.5) % 7 )  );
	}

	/***************************
	NTH_WEEKDAY_JD -- determine julian day of Nth ($n) weekday ($m) for given
		Gregorian month ($m) and year ($yr)
	*******************************************/
	function nth_weekday_jd($n, $w, $m, $yr)
	{
		$jd = gregorian_to_jd($m,1,$yr);
		//calculate days to first weekday (w) in month (m)
		$days = $w - jd_to_weekday($jd);
		//negative days points to previous month, increment by 7 for
		if ($days < 0) $days += 7;

		return ( $jd + $days + ($n-1) * 7 );
	}

	/************************
	 function returns Easter Date as a julian date for any given $year 1583 to 4099
	 based on Visual Basic code in Easter Dating Method by Ronald W. Mallen
	 see http://www.assa.org.au/edm.html

	 the optional $offset parameter allows calculations of other dates, e.g. the sundays of lent,
	 which are based on the easter date

	 problems with php's easter_date([year])
	**********************************************/
	function easter_jd ( $year, $offset=0 ) {

		$FirstDig = (int)($year/100);	//first 2 digits of year
		$Remain19 = $year % 19;			//remainder of year / 19

		//calculate PFM date

		$temp = ( (int)(($FirstDig - 15) /2) + 202 - 11 * $Remain19);

		switch ($FirstDig) {
			case 21:
			case 24:
			case 25:
			case 27:
			case 28:
			case 29:
			case 30:
			case 31:
			case 32:
			case 34:
			case 35:
			case 38:
				$temp = $temp - 1;
				break;

			case 33:
			case 36:
			case 37:
			case 39:
			case 40:
				$temp = $temp - 2;
				break;
		}	//end switch

		$temp = $temp % 30;

		$tA = $temp + 21;
		if ($temp == 29 ) $tA = $tA -1;
		if($temp == 28 And $Remain19 > 10) $tA = $tA - 1;

		//find the next Sunday
		$tB = ($tA - 19) % 7;

		$tC = (40 - $FirstDig) % 4;
		if ($tC == 3) $tC = $tC +1;
		if ($tC > 1) $tC = $tC +1;

		$temp = $year % 100;
		$tD = ($temp + ((int)($temp / 4)) ) % 7;

		$tE = ((20 -$tB - $tC - $tD) % 7 ) + 1;
		$da = $tA + $tE;

		//return the date
		if ( $da > 31 ) {
			$da = $da - 31;
			$mo = 4;
		} else {
			$mo = 3;
		}

		return( gregorian_to_jd($mo, $da, $year) + $offset );

	} //end EasterDate()

/************************************************
	the following functions are taken from the java script at
	http://www.fourmilab.com/documents/calendar/
**************************************************/

	/**********************
	LEAP_GREGORIAN -- Is a given Gregorian year a leap year?
	************************/
	function leap_gregorian($year) {

		return ( (($year % 4)==0) && (!( (($year % 100)==0) && (($year % 400) != 0) )));
	}

	/*************************
	GREGORIAN_TO_JD -- Determine Julian day number from Gregorian calendar date
	************************/
	function gregorian_to_jd($mo, $da, $yr) {

		return (	GREGORIAN_EPOCH - 1 +
					365 * ($yr-1) +
					floor(($yr-1)/4) -
					floor(($yr-1)/100) +
					floor(($yr-1)/400) +
					floor((367 * $mo - 362) / 12 ) +
					(($mo <= 2) ? 0 : (leap_gregorian($yr) ? -1 : -2 )) +
					$da );

	}

	/*******************
	JD_TO_GREGORIAN -- Calculate Gregorian calender date from Julian Date
		note $mo, $da, and $yr are returned by reference
	********************************/
	function jd_to_gregorian($jd, &$mo, &$da, &$yr) {

		$wjd = floor($jd - 0.5) + 0.5;
		$depoch = $wjd - GREGORIAN_EPOCH;
		$quadricent = floor($depoch / 146097);
		$dqc = $depoch % 146097;
		$cent = floor($dqc / 36524);
		$dcent = $dqc % 36524;
		$quad = floor($dcent / 1461);
		$dquad = $dcent % 1461;
		$yindex = floor($dquad / 365);
		$yr = $quadricent * 400 + $cent * 100 + $quad * 4 + $yindex;

		if (!(($cent == 4) || ($yindex == 4))) $yr++;

		$yearday = $wjd - gregorian_to_jd(1,1,$yr);
		$leapadj = (($wjd < gregorian_to_jd(3,1,$yr)) ? 0 : (leap_gregorian($yr) ? 1 : 2));
		$mo = floor(((($yearday + $leapadj) * 12) + 373) / 367);
		$da = $wjd - gregorian_to_jd($mo, 1, $yr) + 1;

	}


	/***********************
	HEBREW_LEAP -- Is the given Hebrew year a leap year?
	***********************/
	function hebrew_leap($yr) {
		return ( (($yr * 7 + 1) % 19 ) < 7 );
	}

	/**************************
	HEBREW_YEAR_MONTHS -- Months in Hebrew year ($yr), 12 = normal, 13 = leap
	***************************/
	function hebrew_year_months($yr) {
		return ( hebrew_leap($yr) ? 13 : 12 );
	}

	/***************************
	HEBREW_DELAY_1 -- Test for delay of start of new year and to avoid
		Sunday, Wednesday, and Friday as start of the new year
	*****************************/
	function hebrew_delay_1($yr) {

		$mos = floor( ((235 * $yr) - 234 ) / 19);
		$parts = 12084 + 13753 * $mos;
		$day = $mos * 29 + floor($parts / 25920);

		if ( (3*($day+1) % 7) < 3 ) $day++;

		return ($day);
	}

	/*************************
	HEWBREW_DELAY_2 -- Check for delay in start of new year due to length of adjacent years
	**************************/

	function hebrew_delay_2($yr) {

		$last = hebrew_delay_1($yr - 1);
		$present = hebrew_delay_1($yr);
		$next = hebrew_delay_1($yr + 1);

		return ( (($next - $present) == 356) ? 2 :
					((($present - $last) == 382) ? 1 : 0) );
	}


	/***************************
	HEBREW_YEAR_DAYS -- How many days in a Hebrew year?
	****************************/
	function hebrew_year_days($yr) {
		return ( hebrew_to_jd(7, 1, $yr + 1) - hebrew_to_jd(7, 1, $yr) );
	}

	/**************************
	HEBREW_MONTH_DAYS -- How many days in given month of given year?
	**************************/
	function hebrew_month_days($mo, $yr) {

		switch ($mo) {
			case 2:		//fixed length 29 day months
			case 4:
			case 6:
			case 10:
			case 13:
				return (29);
				break;
			case 12:
				if (! hebrew_leap($yr) ) return(29);
				break;
			case 8:
				//Heshvan depends on length of year
				if ( !( (hebrew_year_days($yr) % 10) == 5) ) return (29);
				break;
			case 9:
				//Kislev also varies with the length of year
				if ( (hebrew_year_days($yr) % 10) == 3 ) return (29);
				break;
		}

		//otherwise the month has 30 days
		return (30);
	}

	/***************************
	HEBREW_TO_JD -- Determine Julian date from Hebrew date
	****************************/
	function hebrew_to_jd($mo, $da, $yr) {

		$mos = hebrew_year_months($yr);

		$jd = HEBREW_EPOCH + hebrew_delay_1($yr) + hebrew_delay_2($yr) + $da + 1;

		if ($mo < 7 ) {
			for ($m = 7; $m <= $mos; $m++) $jd += hebrew_month_days($m, $yr);
			for ($m = 1; $m < $mo; $m++) $jd += hebrew_month_days($m, $yr);
		} else {
			for ($m = 7; $m < $mo; $m++ ) $jd += hebrew_month_days($m, $yr);
		}

		return ($jd);
	}

	/******************************
	JD_TO_HEBREW -- Deterime Hewbrew date from Julian date
		note: month, day, and year are set by reference
	*******************************/
	function jd_to_hebrew($jd, &$mo, &$da, &$yr) {

		$jd = floor($jd) + 0.5;

		$count = floor((($jd - HEBREW_EPOCH) * 98496.0) / 35975351.0);
		$yr = $count - 1;

		$jdtest = hebrew_to_jd(7,1,$count);
		for ( $i = $count; $jd >= $jdtest; ) {
			$yr++;
			$jdtest = hebrew_to_jd(7,1,++$i);
		}

		$first = ($jd < hebrew_to_jd(1,1,$yr)) ? 7 : 1;
		$mo = $first;

		$jdtest = hebrew_to_jd($mo, hebrew_month_days($mo,$yr), $yr);
		for ( $i = $first; $jd > $jdtest; ) {
			$mo++;
			$jdtest = hebrew_to_jd(++$i, hebrew_month_days($i,$yr), $yr);
		}


		$da = $jd - hebrew_to_jd($mo, 1, $yr) +1;
	}


	/*************
	LEAP_ISLAMIC -- Is a given year a leap year in the Islamic calendar?
	**********************/
	function leap_islamic($yr) {
		return (((($yr*11)+14)%30)<11);
	}

	/***************
	ISLAMIC_TO_JD -- determin Julian day from Islamic date
	****************/
	function islamic_to_jd($mo,$da,$yr) {
		return ( $da + ceil(29.5*($mo-1)) + ($yr-1)*354 +
			floor((3+(11*$yr))/30)+ ISLAMIC_EPOCH - 1);
	}

	/***************
	JD_TO_ISLAMIC -- determin Islamic date from Julian day
		note: mo da and yr set by reference
	*****************/
	function jd_to_islamic($jd, &$mo, &$da, &$yr) {
		$jd = floor($jd)+0.5;
		$yr = floor(((30*($jd-ISLAMIC_EPOCH)) + 10646) / 10631);

		$mo = min(12, ceil(($jd-(29+islamic_to_jd(1,1,$yr)))/29.5)+1);

		$da = $jd - islamic_to_jd($mo,1,$yr) + 1;

	}

	/********************
	JD_TO_UNIX -- determine unix timestamp from julian date
		note the mktime and date functions adjust for local time
	**********************/
	function jd_to_unix($jd) {
		$val=(($jd-UNIX_EPOCH) * SECS_IN_DAY * 1000);
		return ( round($val/1000) ) ;
	}

	/********************
	UNIX_TO_JD -- determin julian day from unix timestamp
	*************************/
	function unix_to_jd($t) {
		return ( UNIX_EPOCH + t / SECS_IN_DAY);
	}


/************8 test functions **********************

$cal = new holiday(2002);

$cal->ListHolidays();


for ($jd = $cal->bjd; $jd <= $cal->ejd; $jd++) {
	//reset($cal->holidays);
	$s = $cal->GetHolidays($jd);
	if (strlen($s) > 0) {
		jd_to_gregorian($jd, $mo, $da, $yr);
		printf("<p> %s, %s %d, %d <br />", $GREGORIAN_DAY[jd_to_weekday($jd)],
			$GREGORIAN_MONTH[$mo], $da, $yr);
		print($s . "</p>");
	}
}
*/
?>