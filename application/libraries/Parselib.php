<?php

class Parselib
{
	function __construct()
	{
		include(APPPATH . 'third_party/simple_html_dom.php');
		$this->company_site = 'http://www.mycorporateinfo.com/';
		$this->ci =& get_instance();
	}

	function save_industry_details(){
		$affected_rows = 0;
		$html = file_get_html($this->company_site.'industry');
		if(!empty($html->find('.list-group .list-group-item a'))){
			foreach($html->find('.list-group .list-group-item a') as $e){
				$name = $e->plaintext;
				$section = str_replace('/industry/section/', '', $e->href);
				if($name && $section){
					$data = ['name' => $name, 'section' => $section];
					if($this->ci->company_model->save_details('industries', $data)){
						$affected_rows++;
					}
				}
			}
			if($affected_rows){
				return ['status' => 1, 'message' => "$affected_rows records saved in database"];
			}else{
				return ['status' => 2, 'message' => "Data already saved in database"];
			}
		}else{
			return ['status' => 3, 'message' => 'can not get the data from website'];
		}
	}

	function save_section_details($section, $page){

		$affected_rows = 0;

		$html = file_get_html($this->company_site.'industry/section/'.$section.'/page/'.$page);

		if(!empty($html) && !empty($html->find('table.table tr'))){

			foreach($html->find('table.table tr') as $row){
				if(isset($row->find('td', 0)->plaintext)){
					$link = str_replace('/business/', '', $row->find('td a', 0)->href);
					if($link != ''){
						$get_res = $this->save_company_details($link);
						if($get_res['status'] == 1){
							$affected_rows++;
						}
					}
				}
			}
			if($affected_rows){
				return ['status' => 1, 'message' => "$affected_rows companies saved in database"];
			}else{
				return ['status' => 2, 'message' => "Data already saved in database"];
			}
		}else{
			return ['status' => 3, 'message' => 'can not get the data from website'];
		}
	}

	function save_company_details($company_uri){
		$all_data = [];
		$html = file_get_html($this->company_site.'business/'.$company_uri);
		$company_info = $this->company_information($html);
		$contact_details = $this->contact_details($html);
		$listing_details = $this->listing_details($html);
		$location_details = $this->location_details($html);
		$classification_details = $this->classification_details($html);
		$directors_data = $this->directors_details($html);
		$directors_details['directors'] = json_encode($directors_data);
		if(!empty($company_info)){
			$all_data = array_merge($company_info, $contact_details, $listing_details, $location_details, $classification_details, $directors_details);

			$get_section = $this->ci->company_model->get_table_row('industries', ['name' => $classification_details['section']], 'id');
			if(!empty($get_section)){
				$all_data['industry_id'] = $get_section->id;
			}
			if($this->ci->company_model->save_details('companies', $all_data)){
				return ['status' => 1, 'message' => 'Company details saved'];
			}else{
				return ['status' => 2, 'message' => 'Company details already saved'];
			}
		}else{
			return ['status' => 3, 'message' => 'can not get the data from website'];
		}
	}

	function company_information($html){
		$data = [];
		$table = $html->find('table', 0);

		if(!empty($table))
		{
			if(isset($table->find('tr td', 0)->plaintext) && $table->find('tr td', 0)->plaintext == 'Corporate Identification Number'){
				$data['CIN'] = $table->find('tr td', 1)->plaintext;
			}
			if(isset($table->find('tr td', 2)->plaintext) && $table->find('tr td', 2)->plaintext == 'Company Name'){
				$data['company_name'] = $table->find('tr td', 3)->plaintext;
			}
			if(isset($table->find('tr td', 4)->plaintext) && $table->find('tr td', 4)->plaintext == 'Company Status'){
				$data['company_status'] = $table->find('tr td', 5)->plaintext;
			}
			if(isset($table->find('tr td', 6)->plaintext) && $table->find('tr td', 6)->plaintext == 'Age (Date of Incorporation)'){
				$age = $table->find('tr td', 7)->plaintext;
				$age = explode(PHP_EOL, $age);
				$data['age'] = str_replace('(', '', $age[0]);
				$data['age'] = trim(str_replace(')', '', $data['age']));
			}
			if(isset($table->find('tr td', 8)->plaintext) && $table->find('tr td', 8)->plaintext == 'Registration Number'){
				$data['registration_number'] = $table->find('tr td', 9)->plaintext;
			}
			if(isset($table->find('tr td', 10)->plaintext) && $table->find('tr td', 10)->plaintext == 'Company Category'){
				$get_data = $table->find('tr td', 11)->plaintext;
				$get_data = explode(PHP_EOL, $get_data);
				$data['company_category'] = $get_data[0];
			}
			if(isset($table->find('tr td', 12)->plaintext) && $table->find('tr td', 12)->plaintext == 'Company Subcategory'){
				$get_data = $table->find('tr td', 13)->plaintext;
				$get_data = explode(PHP_EOL, $get_data);
				$data['company_subcategory'] = $get_data[0];
			}
			if(isset($table->find('tr td', 14)->plaintext) && $table->find('tr td', 14)->plaintext == 'Class of Company'){
				$get_data = $table->find('tr td', 15)->plaintext;
				$get_data = explode(PHP_EOL, $get_data);
				$data['class_of_company'] = $get_data[0];
			}
			if(isset($table->find('tr td', 16)->plaintext) && $table->find('tr td', 16)->plaintext == 'ROC Code'){
				$get_data = $table->find('tr td', 17)->plaintext;
				$get_data = explode(PHP_EOL, $get_data);
				$data['roc_code'] = $get_data[0];
			}
			if(isset($table->find('tr td', 18)->plaintext) && $table->find('tr td', 18)->plaintext == 'Number of Members (Applicable only in case of company without Share Capital)'){
				$data['number_of_members'] = $table->find('tr td', 19)->plaintext;
			}
		}
		return $data;
	}

	function contact_details($html){
		$data = [];
		$table = $html->find('table', 1);
		if(!empty($table))
		{
			if(isset($table->find('tr td', 0)->plaintext) && $table->find('tr td', 0)->plaintext == 'Email Address'){
				$data['email_address'] = $table->find('tr td', 1)->plaintext;
			}
			if(isset($table->find('tr td', 2)->plaintext) && $table->find('tr td', 2)->plaintext == 'Registered Office'){
				$data['registered_office'] = $table->find('tr td', 3)->plaintext;
			}}
			return $data;
		}

		function listing_details($html){
			$data = [];
			$table = $html->find('table', 2);
			if(!empty($table))
			{
				if(isset($table->find('tr td', 0)->plaintext) && $table->find('tr td', 0)->plaintext == 'Whether listed or not'){
					$get_data = $table->find('tr td', 1)->plaintext;
					$get_data = explode(PHP_EOL, $get_data);
					$data['whether_listed_or_not'] = $get_data[0];
				}
				if(isset($table->find('tr td', 2)->plaintext) && $table->find('tr td', 2)->plaintext == 'Date of Last AGM'){
					$data['date_of_last_agm'] = $table->find('tr td', 3)->plaintext;
				}
				if(isset($table->find('tr td', 4)->plaintext) && $table->find('tr td', 4)->plaintext == 'Date of Balance sheet'){
					$data['date_of_balance_sheet'] = $table->find('tr td', 5)->plaintext;
				}
			}
			return $data;
		}

		function location_details($html){
			$data = [];
			$table = $html->find('table', 3);
			if(!empty($table))
			{
				if(isset($table->find('tr td', 0)->plaintext) && $table->find('tr td', 0)->plaintext == 'State'){
					$data['state'] = $table->find('tr td', 1)->plaintext;
				}
				if(isset($table->find('tr td', 2)->plaintext) && $table->find('tr td', 2)->plaintext == 'District'){
					$data['district'] = $table->find('tr td', 3)->plaintext;
				}
				if(isset($table->find('tr td', 4)->plaintext) && $table->find('tr td', 4)->plaintext == 'City'){
					$data['city'] = $table->find('tr td', 5)->plaintext;
				}
				if(isset($table->find('tr td', 6)->plaintext) && $table->find('tr td', 6)->plaintext == 'PIN'){
					$data['pin'] = $table->find('tr td', 7)->plaintext;
				}
			}
			return $data;
		}

		function classification_details($html){
			$data = [];
			$table = $html->find('table', 4);
			if(!empty($table))
			{
				if(isset($table->find('tr td', 0)->plaintext) && $table->find('tr td', 0)->plaintext == 'Section'){
					$get_data = $table->find('tr td', 1)->plaintext;
					$get_data = explode(PHP_EOL, $get_data);
					$data['section'] = $get_data[0];
				}
				if(isset($table->find('tr td', 2)->plaintext) && $table->find('tr td', 2)->plaintext == 'Division'){
					$get_data = $table->find('tr td', 3)->plaintext;
					$get_data = explode(PHP_EOL, $get_data);
					$data['division'] = $get_data[0];
				}
				if(isset($table->find('tr td', 4)->plaintext) && $table->find('tr td', 4)->plaintext == 'Main Group'){
					$get_data = $table->find('tr td', 4)->plaintext;
					$get_data = explode(PHP_EOL, $get_data);
					$data['main_group'] = $get_data[0];
				}
				if(isset($table->find('tr td', 6)->plaintext) && $table->find('tr td', 6)->plaintext == 'Main Class'){
					$data['main_class'] = $table->find('tr td', 7)->plaintext;
				}
			}
			return $data;
		}

		function directors_details($html){
			$all_data = [];
			$table = $html->find('table', 5);
			if(!empty($table))
			{
				foreach($table->find('tr') as $row){
					if(isset($row->find('td', 0)->plaintext) && $row->find('td', 0)->plaintext != ''){
						$data = [
							'director_identification_number' => $row->find('td', 0)->plaintext,
							'name' => $row->find('td', 1)->plaintext,
							'designation' => $row->find('td', 2)->plaintext,
							'date_of_appointment' => $row->find('td', 3)->plaintext,
						];
						$all_data[] = $data;
					}
				}
			}
			return $all_data;
		}

		public function check_industries(){
			if($this->ci->company_model->get_details_count('industries') == 0){
				$this->save_industry_details();
			}
		}

	}