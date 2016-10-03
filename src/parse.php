<?php
	/**
	 * Parse data.txt file
	 * Create JSON version in dist directory
	 * @package RBK\SurveyDatatoJSON
	 * 
	 */
	
	$input = '../data.txt';
	$output = '../dist/output.json';
	$fh_out = fopen($output, 'w+');
	$fh_in = file($input);
	

	/**
	* Model of array to convert data to.
	*/
	$model = array(
		"male" => array(
			"count" => "",
			"average_age" => "",
			"top_species" => "",
			"popular_by_age" => array()
		),
		"female" => array(
			"count" => "",
			"average_age" => "",
			"top_species" => "",
			"popular_by_age" => array()
		),
		"unknown" => array(
			"count" => "",
			"average_age" => "",
			"top_species" => "",
			"popular_by_age" => array()
		)
	);


	/**
	 * Remove extra whitespace from string and normalize case.
	 * @param  string
	 * @return string converted to lowercase and trimmed of whitespace
	 */
	function cleanString( $string ) {
		return trim(strtolower($string));
	}

	/**
	 * Use cleanString on every item in numerical array.
	 * @param  array
	 * @return array with all items in array cleaned of whitespace and strings set to lowercase
	 */
	function cleanLine( $array ) {
		$clean_array = array();
		foreach( $array as $item ) {
			for( $i=0; $i<count($array); $i++ ) {
				$clean_array[$i] = cleanString($array[$i]);
			}
		}
		return $clean_array;
	}

	/**
	 * Caluclate average age from an array of ages
	 * @param  array
	 * @return integer representing the number average age of people surveyed
	 */
	function calculateAverage( $arrayOfNumbers ){
		$total = 0;
		$count = count( $arrayOfNumbers );
		foreach( $arrayOfNumbers as $number ) {
			if( is_numeric( $number ) ) {
				$total = $total + $number;
			}
		}
		return round($total/$count);
	}

	/**
	 * Noralize terms by checking removing plural form.
	 * @param  string
	 * @return string without pluralization
	 */
	function normalizeAnimalTerm( $animal ){
		if( $animal == 'dogs' ){
			return 'dog';
		} else if( $animal == 'cats' ) {
			return 'cat';
		} else {
			return $animal;
		}
	}

	/**
	 * Count the total number of cat and dog to find out which is more popular by gender
	 * @param  array
	 * @return string representing the most popular species
	 */
	function getTopSpecies( $array ) {
		$cats = 0;
		$dogs = 0;
		foreach( $array as $species ) {
			if( $species  == 'cat' ) {
				$cats++;
			} else if( $species == 'dog' ){
				$dogs++;
			}
		}
		if( $dogs > $cats ) {
			return 'dog';
		} else {
			return 'cat';
		}
	}

	/**
	 * Build an array of to represent popularity of dogs and cats by age group
	 * @param  array
	 * @return array
	 */
	function popularByAge( $surveys ) {

		$result = array();
		$ages = array();
		$unique_ages = array();

		foreach( $surveys as $survey ) {
			$ages[] = $survey[2];
		}

		$unique_ages = array_unique($ages);
		sort( $unique_ages );
		

		foreach( $unique_ages as $age_key ){
			
			$result[$age_key] = array(
				"dog" => 0,
				"cat" => 0
			);
			
			foreach( $surveys as $survey ) {

				$animal_type = normalizeAnimalTerm($survey[1]);

				if( $survey[2] == $age_key ) {
					@$result[$age_key][$animal_type]++;
				}
			}

		}
		return $result;
	}

	/**
	 * Main function to build array from data.txt
	 * @param filename
	 * @return array
	 */
	function buildOutput( $file ) {

		global $model;
		global $fh_in;

		foreach( $fh_in as $line ){
			
			$line = explode( '|', $line );
			$gender = cleanString($line[0]);
			$animal = cleanString($line[1]);
			$age 	= cleanString($line[2]);
			$line = cleanLine( $line );
			$animal = normalizeAnimalTerm( $animal );


			if( $gender == 'male' || $gender == 'boy' ){

				$model['male']['count']++;
				$model['male']['average_age'][] = $age;
				$model['male']['top_species'][] = $animal;
				$model['male']['popular_by_age'][] = $line;

			} else if( $gender == 'female' || $gender == 'girl' ) {
				
				$model['female']['count']++;
				$model['female']['average_age'][] = $age;
				$model['female']['top_species'][] = $animal;
				$model['female']['popular_by_age'][] = $line;

			} else {
				
				$model['unknown']['count']++;
				$model['unknown']['average_age'][] = $age;
				$model['unknown']['top_species'][] = $animal;
				$model['unknown']['popular_by_age'][] = $line;

			}

		}

		// Transform data from declared model
		foreach( $model as $key => $arr ) {
			$model[$key]['average_age'] = calculateAverage( $model[$key]['average_age'] );
			$model[$key]['top_species'] = getTopSpecies( $model[$key]['top_species'] );
			$model[$key]['popular_by_age'] = popularByAge( $model[$key]['popular_by_age'] );
		}

	}

	/**
	 * Run program
	 */
	buildOutput( $input );

	/**
	 * Write JSON to output.json 
	 */
	fwrite($fh_out, json_encode($model, JSON_PRETTY_PRINT)); 

?>
<script>
	var json = JSON.stringify(<?php echo json_encode($model); ?>, null, 2);
	document.write('<pre>' + json + '</pre>');
</script>