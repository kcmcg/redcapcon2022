<?php
namespace REDCapCon\REDCapConTestModule;

class REDCapConTestModule extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
		// Other code to run when object is instantiated
	}
	
	public function redcap_add_edit_records_page( int $project_id, string $instrument, int $event_id ) {
		
	}

	
	public function redcap_control_center(  ) {
		
	}

	
	public function redcap_custom_verify_username( string $username ) {
		
	}

	
	public function redcap_data_entry_form( int $project_id, string $record, string $instrument, int $event_id, int $group_id, int $repeat_instance ) {
		
	}

	
	public function redcap_data_entry_form_top( int $project_id, string $record, string $instrument, int $event_id, int $group_id, int $repeat_instance ) {
		
	}

	
	public function redcap_email( string $to, string $from, string $subject, string $message, string $cc, string $bcc, string $fromName, array $attachments ) {
		
	}

	
	public function redcap_every_page_before_render( int $project_id ) {
		
	}

	
	public function redcap_every_page_top( int $project_id ) {
		
	}

	
	public function redcap_pdf( int $project_id, array $metadata, array $data, string $instrument, string $record, int $event_id, int $instance ) {
		
	}

	
	public function redcap_project_home_page( int $project_id ) {
		
	}

	
	public function redcap_save_record( int $project_id, string $record, string $instrument, int $event_id, int $group_id, string $survey_hash, int $response_id, int $repeat_instance ) {
		
	}

	
	public function redcap_survey_acknowledgement_page( int $project_id, string $record, string $instrument, int $event_id, int $group_id, string $survey_hash, int $response_id, int $repeat_instance ) {
		
	}

	
	public function redcap_survey_complete( int $project_id, string $record, string $instrument, int $event_id, int $group_id, string $survey_hash, int $response_id, int $repeat_instance ) {
		
	}

	
	public function redcap_survey_page( int $project_id, string $record, string $instrument, int $event_id, int $group_id, string $survey_hash, int $response_id, int $repeat_instance ) {
		
	}

	
	public function redcap_survey_page_top( int $project_id, string $record, string $instrument, int $event_id, int $group_id, string $survey_hash, int $response_id, int $repeat_instance ) {
		
	}

	
	public function redcap_user_rights(  int $project_id ) {
		
	}

	
	public function redcap_module_system_enable( $version ) {
		
	}

	
	public function redcap_module_system_disable( $version ) {
		
	}

	
	public function redcap_module_system_change_version( $version, $old_version ) {
		
	}

	
	public function redcap_module_project_enable( $version, $project_id ) {
		
	}

	
	public function redcap_module_project_disable( $version, $project_id ) {
		
	}

	
	public function redcap_module_link_check_display( $project_id, $link ) {
		return $link;
	}

	
	public function redcap_module_save_configuration( $project_id ) {
		
	}

	

	public function run_cron( $cronParameters ) {
		$launchData = $this->connectToSpaceXAPI("launches");
		
		$projects = $this->framework->getProjectsWithModuleEnabled();
		
		foreach($projects as $projectId) {
			$this->saveLaunchDataToProject($projectId, $launchData);
		}
	}
	
	public function saveLaunchDataToProject( $project_id, $launchData) {
		$recordData = \REDCap::getData([
			"return_format" => "json",
			"project_id" => $project_id
		]);
		$recordData = json_decode($recordData,true);
		
		$recordIdField = $this->getRecordIdField($project_id);
		$launchField = "flight_number";
		$launchDateField = "launch_date";
		$nameField = "name";
		$staticFireField = "static_fire";
		$videoField = "video";
		$detailsField = "details";
		
		foreach($launchData as $thisLaunch) {
			$launchId = $thisLaunch["flight_number"];
			$launchDate = date("Y-m-d",$thisLaunch["date_unix"]);
			$staticFireDate = date("Y-m-d",$thisLaunch["static_fire_date_unix"]);
			$details = $thisLaunch["details"];
			$name = $thisLaunch["name"];
			$video = "";
			if(array_key_exists("links",$thisLaunch)) {
				$video = $thisLaunch["links"]["webcast"];
			}
			
			$existingRecord = false;
			foreach($recordData as $thisRecord) {
				if($thisRecord[$launchField] == $launchId) {
					$existingRecord = $thisRecord[$recordIdField];
				}
			}
			
			$dataRow = [
				$launchField => $launchId,
				$launchDateField => $launchDate,
				$nameField => $name,
				$staticFireField => $staticFireDate,
				$videoField => $video,
				$detailsField => $details
			];
			
			if($existingRecord !== false) {
				$dataRow[$recordIdField] = $existingRecord;
			}
			else {
				$dataRow[$recordIdField] = \REDCap::reserveNewRecordId($project_id);
			}
			
			$saveData = json_encode([$dataRow]);
			
			$results = \REDCap::saveData([
				"project_id" => $project_id,
				"data" => $saveData,
				"dataFormat" => "json"
			]);
			
			if((is_array($results["errors"]) && count($results["errors"]) > 0) || (is_string($results["errors"]) && $results["errors"] != "")) {
				echo "<br /><pre>";
				var_dump($results);
				echo "</pre><br />";
			}
		}
		
	}
	
	public function connectToSpaceXAPI( $dataType ) {
		$ch = curl_init("https://api.spacexdata.com/v4/".$dataType);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$results = curl_exec($ch);
		curl_close($ch);
		
		$data = json_decode($results,true);
		
		return $data;
	}
}